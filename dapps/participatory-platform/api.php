<?php
declare(strict_types=1);

session_start();

header('Content-Type: application/json');

require_once __DIR__ . '/config.php';

$responseCode = 200;

try {
    $pdo = participatory_pdo();
    initializeDatabase($pdo);

    if (!isset($_SESSION['voter_token'])) {
        $_SESSION['voter_token'] = bin2hex(random_bytes(16));
    }

    $action = $_GET['action'] ?? ($_SERVER['REQUEST_METHOD'] === 'POST' ? 'create' : 'ideas');
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    switch ($action) {
        case 'ideas':
            if ($method === 'GET') {
                $data = listIdeas($pdo);
                echo json_encode($data);
                break;
            }
            if ($method === 'POST') {
                $payload = jsonInput();
                $created = createIdea($pdo, $payload);
                echo json_encode(['success' => true, 'idea' => $created]);
                break;
            }
            throw new RuntimeException('Metodo non supportato per /ideas');

        case 'vote':
            ensurePost($method);
            $payload = jsonInput();
            $ok = voteIdea($pdo, (int)($payload['idea_id'] ?? 0));
            echo json_encode(['success' => $ok]);
            break;

        case 'comment':
            ensurePost($method);
            $payload = jsonInput();
            $comment = addComment($pdo, $payload);
            echo json_encode(['success' => true, 'comment' => $comment]);
            break;

        case 'comments':
            $ideaId = (int)($_GET['idea_id'] ?? 0);
            echo json_encode(listComments($pdo, $ideaId));
            break;

        case 'candidates':
            echo json_encode(listCandidates($pdo));
            break;

        case 'activity':
            echo json_encode(activitySnapshot($pdo));
            break;

        case 'simulate':
            ensureSimulationAllowed();
            $events = simulateActivity($pdo);
            echo json_encode(['simulated' => true, 'events' => $events]);
            break;

        case 'admin-ideas':
            requireAdminAuth();
            echo json_encode(listIdeas($pdo, true));
            break;

        case 'admin-stats':
            requireAdminAuth();
            echo json_encode(adminStats($pdo));
            break;

        case 'admin-status':
            requireAdminAuth();
            ensurePost($method);
            $payload = jsonInput();
            echo json_encode(updateIdeaStatus($pdo, (int)($payload['idea_id'] ?? 0), (string)($payload['status'] ?? 'pending')));
            break;

        case 'admin-delete':
            requireAdminAuth();
            ensurePost($method);
            $payload = jsonInput();
            deleteIdea($pdo, (int)($payload['idea_id'] ?? 0));
            echo json_encode(['success' => true]);
            break;

        case 'admin-create':
            requireAdminAuth();
            ensurePost($method);
            $payload = jsonInput();
            $idea = createIdea($pdo, $payload, true);
            echo json_encode(['success' => true, 'idea' => $idea]);
            break;

        default:
            $responseCode = 404;
            http_response_code($responseCode);
            echo json_encode(['error' => 'Endpoint non trovato']);
    }
} catch (Throwable $e) {
    $responseCode = 500;
    http_response_code($responseCode);
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    http_response_code($responseCode);
}

function jsonInput(): array
{
    $body = file_get_contents('php://input');
    $decoded = json_decode($body, true) ?? [];
    if (!is_array($decoded)) {
        throw new InvalidArgumentException('Payload non valido');
    }
    return $decoded;
}

function ensurePost(string $method): void
{
    if ($method !== 'POST') {
        throw new RuntimeException('Metodo non consentito');
    }
}

function requireAdminAuth(): void
{
    $token = trim((string)(getenv('PARTICIPATORY_ADMIN_TOKEN') ?: ''));
    $user = getenv('PARTICIPATORY_ADMIN_USER') ?: 'stanislao';
    $pass = getenv('PARTICIPATORY_ADMIN_PASS') ?: 'Stanislao08!!';

    $authorized = false;

    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if ($token !== '' && stripos($authHeader, 'Bearer ') === 0) {
        $provided = trim(substr($authHeader, 7));
        $authorized = hash_equals($token, $provided);
    }

    if (!$authorized && $user !== '' && $pass !== '') {
        $providedUser = $_SERVER['PHP_AUTH_USER'] ?? null;
        $providedPass = $_SERVER['PHP_AUTH_PW'] ?? null;
        $authorized = $providedUser === $user && $providedPass === $pass;
    }

    if (!$authorized) {
        header('WWW-Authenticate: Basic realm="Backoffice"');
        http_response_code(401);
        echo json_encode(['error' => 'Autorizzazione amministratore richiesta']);
        exit;
    }
}

function listIdeas(PDO $pdo, bool $includeAll = false): array
{
    $where = $includeAll ? '' : "WHERE i.status = 'published'";
    $sql = <<<SQL
    SELECT i.*,
           COALESCE(v.votes_count, 0) AS votes_count,
           COALESCE(c.comments_count, 0) AS comments_count
    FROM ideas i
    LEFT JOIN (
      SELECT idea_id, COUNT(*) AS votes_count FROM votes GROUP BY idea_id
    ) v ON v.idea_id = i.id
    LEFT JOIN (
      SELECT idea_id, COUNT(*) AS comments_count FROM comments GROUP BY idea_id
    ) c ON c.idea_id = i.id
    {$where}
    ORDER BY votes_count DESC, i.created_at DESC
    LIMIT 100;
    SQL;

    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

function createIdea(PDO $pdo, array $payload, bool $isAdmin = false): array
{
    $title = trim((string)($payload['title'] ?? ''));
    $description = trim((string)($payload['desc'] ?? ''));
    $district = trim((string)($payload['district'] ?? ''));

    if ($title === '' || $description === '' || $district === '') {
        throw new InvalidArgumentException('Titolo, descrizione e quartiere sono obbligatori.');
    }

    if (strlen($title) > 255) {
        throw new InvalidArgumentException('Il titolo è troppo lungo.');
    }

    $theme = trim((string)($payload['theme'] ?? 'Altro'));
    $authorName = trim((string)($payload['author'] ?? 'Anonimo'));
    $authorEmail = trim((string)($payload['author_email'] ?? ''));
    $candidateOptIn = !empty($payload['candidate_opt_in']);
    $status = $isAdmin ? (string)($payload['status'] ?? 'published') : ((string)getenv('PARTICIPATORY_DEFAULT_STATUS') ?: 'pending');
    if (!in_array($status, ['pending', 'published', 'archived'], true)) {
        $status = $isAdmin ? 'published' : 'pending';
    }

    $pdo->beginTransaction();

    $userId = null;
    if ($authorEmail !== '') {
        $userId = ensureUser($pdo, $authorEmail, $authorName);
    }

    $publishedAt = $status === 'published' ? date('c') : null;

    $stmt = $pdo->prepare('INSERT INTO ideas(title, description, district, theme, author_name, author_email, candidate_opt_in, status, published_at, user_id) VALUES(?,?,?,?,?,?,?,?,?,?)');
    $stmt->execute([
        $title,
        $description,
        $district,
        $theme,
        $authorName,
        $authorEmail !== '' ? $authorEmail : null,
        $candidateOptIn ? 1 : 0,
        $status,
        $publishedAt,
        $userId,
    ]);

    $ideaId = (int)$pdo->lastInsertId();
    $pdo->commit();

    $idea = getIdea($pdo, $ideaId);
    return $idea;
}

function ensureUser(PDO $pdo, string $email, string $name): int
{
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $existing = $stmt->fetch();
    if ($existing) {
        return (int)$existing['id'];
    }

    $stmt = $pdo->prepare('INSERT INTO users(name, email) VALUES (?, ?)');
    $stmt->execute([$name, $email]);
    return (int)$pdo->lastInsertId();
}

function getIdea(PDO $pdo, int $ideaId): array
{
    $stmt = $pdo->prepare('SELECT * FROM ideas WHERE id = ?');
    $stmt->execute([$ideaId]);
    $idea = $stmt->fetch();
    if (!$idea) {
        throw new RuntimeException('Idea non trovata');
    }
    return $idea;
}

function voteIdea(PDO $pdo, int $ideaId): bool
{
    if ($ideaId <= 0) {
        throw new InvalidArgumentException('ID idea mancante');
    }

    $idea = getIdea($pdo, $ideaId);
    if (!$idea) {
        throw new RuntimeException('Idea non trovata');
    }

    $token = $_SESSION['voter_token'];
    $stmt = $pdo->prepare('SELECT 1 FROM votes WHERE idea_id = ? AND voter_token = ?');
    $stmt->execute([$ideaId, $token]);
    if ($stmt->fetch()) {
        return false;
    }

    $insert = $pdo->prepare('INSERT INTO votes(idea_id, voter_token, created_at) VALUES (?,?,?)');
    $insert->execute([$ideaId, $token, date('c')]);
    return true;
}

function addComment(PDO $pdo, array $payload): array
{
    $ideaId = (int)($payload['idea_id'] ?? 0);
    $body = trim((string)($payload['body'] ?? ''));
    $author = trim((string)($payload['author'] ?? 'Anonimo'));

    if ($ideaId <= 0 || $body === '') {
        throw new InvalidArgumentException('Commento non valido.');
    }

    getIdea($pdo, $ideaId); // Ensure idea exists

    $stmt = $pdo->prepare('INSERT INTO comments(idea_id, author_name, body, created_at) VALUES (?,?,?,?)');
    $stmt->execute([$ideaId, $author, $body, date('c')]);

    return [
        'id' => (int)$pdo->lastInsertId(),
        'idea_id' => $ideaId,
        'author_name' => $author,
        'body' => $body,
    ];
}

function listComments(PDO $pdo, int $ideaId): array
{
    if ($ideaId <= 0) {
        return [];
    }
    $stmt = $pdo->prepare('SELECT * FROM comments WHERE idea_id = ? ORDER BY created_at DESC LIMIT 30');
    $stmt->execute([$ideaId]);
    return $stmt->fetchAll();
}

function listCandidates(PDO $pdo): array
{
    $sql = <<<SQL
    SELECT 
      COALESCE(author_email, CONCAT('guest-', id)) AS candidate_key,
      COALESCE(author_name, 'Cittadino attivo') AS name,
      MIN(district) AS district,
      SUM(COALESCE(v.votes_count, 0)) AS votes,
      COUNT(*) AS ideas,
      MAX(candidate_opt_in) AS opt_in
    FROM ideas i
    LEFT JOIN (
      SELECT idea_id, COUNT(*) AS votes_count FROM votes GROUP BY idea_id
    ) v ON v.idea_id = i.id
    WHERE author_email IS NOT NULL AND author_email != '' AND i.status = 'published'
    GROUP BY candidate_key, name
    HAVING votes >= 10 OR opt_in = 1
    ORDER BY votes DESC, ideas DESC
    LIMIT 10;
    SQL;

    $stmt = $pdo->query($sql);
    $candidates = [];
    foreach ($stmt->fetchAll() as $row) {
        $candidates[] = [
            'id' => $row['candidate_key'],
            'name' => $row['name'],
            'district' => $row['district'],
            'ideas' => (int)$row['ideas'],
            'votes' => (int)$row['votes'],
            'role' => $row['votes'] >= 30 ? 'sindaco' : ($row['votes'] >= 20 ? 'presidente del consiglio' : 'assessore'),
            'bio' => 'Attivo sul territorio con idee validate dal pubblico.',
        ];
    }

    return $candidates;
}

function adminStats(PDO $pdo): array
{
    $published = (int)$pdo->query("SELECT COUNT(*) FROM ideas WHERE status = 'published'")->fetchColumn();
    $pending = (int)$pdo->query("SELECT COUNT(*) FROM ideas WHERE status = 'pending'")->fetchColumn();
    $archived = (int)$pdo->query("SELECT COUNT(*) FROM ideas WHERE status = 'archived'")->fetchColumn();

    $topDistrict = $pdo->query("SELECT district, COUNT(*) AS n FROM ideas WHERE status = 'published' GROUP BY district ORDER BY n DESC LIMIT 1")->fetch();

    return [
        'published' => $published,
        'pending' => $pending,
        'archived' => $archived,
        'votes' => (int)$pdo->query('SELECT COUNT(*) FROM votes')->fetchColumn(),
        'comments' => (int)$pdo->query('SELECT COUNT(*) FROM comments')->fetchColumn(),
        'best_district' => $topDistrict ? $topDistrict['district'] : null,
    ];
}

function updateIdeaStatus(PDO $pdo, int $ideaId, string $status): array
{
    if ($ideaId <= 0) {
        throw new InvalidArgumentException('ID idea mancante');
    }

    if (!in_array($status, ['pending', 'published', 'archived'], true)) {
        throw new InvalidArgumentException('Stato non valido');
    }

    getIdea($pdo, $ideaId);

    $publishedAt = $status === 'published' ? date('c') : null;
    $stmt = $pdo->prepare('UPDATE ideas SET status = ?, published_at = ? WHERE id = ?');
    $stmt->execute([$status, $publishedAt, $ideaId]);

    return getIdea($pdo, $ideaId);
}

function deleteIdea(PDO $pdo, int $ideaId): void
{
    if ($ideaId <= 0) {
        throw new InvalidArgumentException('ID idea mancante');
    }
    $stmt = $pdo->prepare('DELETE FROM ideas WHERE id = ?');
    $stmt->execute([$ideaId]);
}

function activitySnapshot(PDO $pdo): array
{
    $stats = [
        'total_ideas' => (int)$pdo->query("SELECT COUNT(*) FROM ideas WHERE status = 'published'")->fetchColumn(),
        'total_votes' => (int)$pdo->query('SELECT COUNT(*) FROM votes')->fetchColumn(),
        'active_users' => (int)$pdo->query('SELECT COUNT(DISTINCT voter_token) FROM votes')->fetchColumn(),
    ];

    $latestIdea = $pdo->query("SELECT title, district FROM ideas WHERE status = 'published' ORDER BY COALESCE(published_at, created_at) DESC LIMIT 1")->fetch();
    $latestComment = $pdo->query('SELECT body FROM comments ORDER BY created_at DESC LIMIT 1')->fetch();

    $recent = 'Community attiva in tutti i quartieri.';
    if ($latestIdea) {
        $recent = 'Nuova proposta per ' . $latestIdea['district'] . ': "' . $latestIdea['title'] . '".';
    }
    if ($latestComment) {
        $recent .= ' Ultimo commento: "' . trim(mb_substr($latestComment['body'], 0, 80)) . '"';
    }

    return ['stats' => $stats, 'recent_activity' => $recent];
}

function ensureSimulationAllowed(): void
{
    $allowed = getenv('PARTICIPATORY_ALLOW_SIMULATION');
    if ($allowed === false || $allowed === '') {
        throw new RuntimeException('Simulazione disabilitata');
    }

    if (!filter_var($allowed, FILTER_VALIDATE_BOOLEAN)) {
        throw new RuntimeException('Simulazione disabilitata');
    }
}

function simulateActivity(PDO $pdo): array
{
    $events = [];
    $ideas = listIdeas($pdo);

    if (!$ideas) {
        return ['Nessuna idea disponibile per simulare voti'];
    }

    $idea = $ideas[array_rand($ideas)];
    voteIdea($pdo, (int)$idea['id']);
    $events[] = 'Voto simulato su "' . $idea['title'] . '"';

    return $events;
}
