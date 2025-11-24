<?php
require_once __DIR__ . '/config.php';

function enforceAdminPage(): void
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
        echo 'Accesso amministratore richiesto. Configura PARTICIPATORY_ADMIN_USER/PASS o PARTICIPATORY_ADMIN_TOKEN.';
        exit;
    }
}

enforceAdminPage();
$pdo = participatory_pdo();
initializeDatabase($pdo);
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Backoffice partecipazione civica</title>
  <style>
    :root{--ink:#0b1220;--muted:#6b7280;--blue:#1c68b7;--blue-600:#155799;--danger:#ef4444;--ok:#059669;--card:#fff;--bg:#f5f7fb;--radius:14px;--shadow:0 10px 30px rgba(15,23,42,.1);} 
    *{box-sizing:border-box;font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif}
    body{margin:0;background:var(--bg);color:var(--ink);} 
    header{background:#fff;padding:18px 22px;border-bottom:1px solid #e5e7eb;position:sticky;top:0;z-index:20;display:flex;justify-content:space-between;align-items:center;box-shadow:0 4px 20px rgba(0,0,0,.04);} 
    h1{margin:0;font-size:20px;} 
    .wrap{max-width:1200px;margin:0 auto;padding:20px;} 
    .grid{display:grid;grid-template-columns:repeat(12,1fr);gap:16px;} 
    .card{background:var(--card);padding:18px;border-radius:var(--radius);box-shadow:var(--shadow);} 
    .card h3{margin:0 0 8px;font-size:16px;color:var(--blue);} 
    .stat{display:flex;flex-direction:column;} 
    .stat strong{font-size:28px;color:var(--blue);} 
    table{width:100%;border-collapse:collapse;} 
    th,td{padding:10px 12px;border-bottom:1px solid #e5e7eb;text-align:left;font-size:14px;} 
    th{background:#f8fafc;color:#334155;} 
    .status{padding:4px 8px;border-radius:999px;font-size:12px;font-weight:700;} 
    .status.pending{background:#fff7ed;color:#9a3412;} 
    .status.published{background:#ecfdf3;color:#065f46;} 
    .status.archived{background:#eef2f7;color:#1f2937;} 
    .actions{display:flex;gap:6px;flex-wrap:wrap;} 
    button, select, input, textarea{border:1px solid #d1d5db;border-radius:10px;padding:10px;font-size:14px;font-weight:600;} 
    button{cursor:pointer;} 
    button.primary{background:var(--blue);color:#fff;border-color:var(--blue);} 
    button.danger{background:var(--danger);color:#fff;border-color:var(--danger);} 
    form .grid{grid-template-columns:repeat(12,1fr);} 
    form .half{grid-column:span 6;} 
    form textarea{min-height:120px;resize:vertical;} 
    .pill{display:inline-block;padding:6px 8px;border-radius:10px;background:#eef2f7;margin-right:8px;font-size:12px;} 
    .toast{position:fixed;bottom:18px;right:18px;background:#0b1220;color:#fff;padding:12px 16px;border-radius:10px;box-shadow:var(--shadow);opacity:0;transform:translateY(8px);transition:.3s;} 
    .toast.show{opacity:1;transform:translateY(0);} 
  </style>
</head>
<body>
  <header>
    <h1>Backoffice partecipativo</h1>
    <div style="display:flex;gap:10px;align-items:center;">
      <span class="pill" id="bestDistrict"></span>
      <button id="refresh" class="primary">Aggiorna</button>
    </div>
  </header>

  <div class="wrap">
    <div class="grid" style="margin-bottom:16px;">
      <div class="card" style="grid-column:span 3;"><div class="stat"><span class="muted">Pubblicate</span><strong id="statPublished">0</strong></div></div>
      <div class="card" style="grid-column:span 3;"><div class="stat"><span class="muted">In revisione</span><strong id="statPending">0</strong></div></div>
      <div class="card" style="grid-column:span 3;"><div class="stat"><span class="muted">Archiviate</span><strong id="statArchived">0</strong></div></div>
      <div class="card" style="grid-column:span 3;"><div class="stat"><span class="muted">Voti totali</span><strong id="statVotes">0</strong></div></div>
    </div>

    <div class="card" style="margin-bottom:16px;">
      <h3>Inserisci nuova idea</h3>
      <form id="adminCreate">
        <div class="grid" style="gap:12px;">
          <div class="half">
            <label for="title">Titolo</label>
            <input id="title" name="title" required />
          </div>
          <div class="half">
            <label for="district">Quartiere</label>
            <input id="district" name="district" required />
          </div>
          <div class="half">
            <label for="theme">Tema</label>
            <input id="theme" name="theme" placeholder="Mobilità, Ambiente…" />
          </div>
          <div class="half">
            <label for="status">Stato</label>
            <select id="status" name="status">
              <option value="pending">In revisione</option>
              <option value="published">Pubblicata</option>
              <option value="archived">Archiviata</option>
            </select>
          </div>
          <div class="half">
            <label for="author">Autore</label>
            <input id="author" name="author" />
          </div>
          <div class="half">
            <label for="email">Email autore</label>
            <input id="email" name="email" />
          </div>
          <div style="grid-column:span 12;">
            <label for="desc">Descrizione</label>
            <textarea id="desc" name="desc" required></textarea>
          </div>
          <div style="grid-column:span 12; display:flex; gap:10px; align-items:center;">
            <input type="checkbox" id="candidate" />
            <label for="candidate">Flag candidato (richiede email valida)</label>
          </div>
        </div>
        <button class="primary" type="submit" style="margin-top:10px;">Salva</button>
      </form>
    </div>

    <div class="card">
      <h3>Idee e moderazione</h3>
      <div style="overflow:auto;">
        <table id="ideasTable" aria-label="Elenco idee">
          <thead>
            <tr><th>ID</th><th>Titolo</th><th>Quartiere</th><th>Stato</th><th>Voti</th><th>Azioni</th></tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>

  <div id="toast" class="toast" role="status"></div>

<script>
const toast = (msg) => {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 2000);
};

async function fetchJson(action, options = {}) {
  const res = await fetch(`api.php?action=${action}`, {
    headers: { 'Content-Type': 'application/json' },
    credentials: 'same-origin',
    ...options,
  });
  if (!res.ok) {
    throw new Error(await res.text());
  }
  return res.json();
}

function statusPill(status) {
  return `<span class="status ${status}">${status}</span>`;
}

async function loadStats() {
  const stats = await fetchJson('admin-stats');
  document.getElementById('statPublished').textContent = stats.published;
  document.getElementById('statPending').textContent = stats.pending;
  document.getElementById('statArchived').textContent = stats.archived;
  document.getElementById('statVotes').textContent = stats.votes;
  document.getElementById('bestDistrict').textContent = stats.best_district ? `Top quartiere: ${stats.best_district}` : 'Top quartiere: n/d';
}

async function loadIdeas() {
  const ideas = await fetchJson('admin-ideas');
  const tbody = document.querySelector('#ideasTable tbody');
  tbody.innerHTML = '';
  ideas.forEach(idea => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>#${idea.id}</td>
      <td>${idea.title}</td>
      <td>${idea.district}</td>
      <td>${statusPill(idea.status)}</td>
      <td>${idea.votes_count || 0}</td>
      <td class="actions">
        <button data-action="publish" data-id="${idea.id}" ${idea.status === 'published' ? 'disabled' : ''}>Pubblica</button>
        <button data-action="pending" data-id="${idea.id}" ${idea.status === 'pending' ? 'disabled' : ''}>Metti in revisione</button>
        <button data-action="archive" data-id="${idea.id}" ${idea.status === 'archived' ? 'disabled' : ''}>Archivia</button>
        <button class="danger" data-action="delete" data-id="${idea.id}">Elimina</button>
      </td>
    `;
    tbody.appendChild(tr);
  });
}

async function updateStatus(id, status) {
  await fetchJson('admin-status', { method: 'POST', body: JSON.stringify({ idea_id: id, status }) });
  toast('Aggiornato');
  await loadIdeas();
  await loadStats();
}

async function deleteIdea(id) {
  if (!confirm('Eliminare definitivamente questa idea?')) return;
  await fetchJson('admin-delete', { method: 'POST', body: JSON.stringify({ idea_id: id }) });
  toast('Eliminata');
  await loadIdeas();
  await loadStats();
}

function wireTableActions() {
  document.querySelector('#ideasTable').addEventListener('click', (e) => {
    const btn = e.target.closest('button');
    if (!btn) return;
    const id = parseInt(btn.dataset.id, 10);
    const action = btn.dataset.action;
    if (action === 'publish') updateStatus(id, 'published');
    if (action === 'pending') updateStatus(id, 'pending');
    if (action === 'archive') updateStatus(id, 'archived');
    if (action === 'delete') deleteIdea(id);
  });
}

function wireForm() {
  const form = document.getElementById('adminCreate');
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = new FormData(form);
    const payload = {
      title: data.get('title'),
      desc: data.get('desc'),
      district: data.get('district'),
      theme: data.get('theme'),
      author: data.get('author'),
      author_email: data.get('email'),
      status: data.get('status'),
      candidate_opt_in: document.getElementById('candidate').checked,
    };
    await fetchJson('admin-create', { method: 'POST', body: JSON.stringify(payload) });
    toast('Idea inserita');
    form.reset();
    await loadIdeas();
    await loadStats();
  });
}

async function bootstrap() {
  wireTableActions();
  wireForm();
  document.getElementById('refresh').addEventListener('click', () => { loadStats(); loadIdeas(); });
  await loadStats();
  await loadIdeas();
  setInterval(() => { loadStats(); loadIdeas(); }, 20000);
}

document.addEventListener('DOMContentLoaded', bootstrap);
</script>
</body>
</html>
