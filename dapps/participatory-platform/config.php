<?php
declare(strict_types=1);

/**
 * Simple PDO factory for the participatory platform.
 * Uses MySQL when the PARTICIPATORY_DB_DRIVER env var is set to "mysql",
 * otherwise falls back to a local SQLite database for easier demos.
 */
function participatory_pdo(): PDO
{
    $driver = getenv('PARTICIPATORY_DB_DRIVER') ?: 'sqlite';

    if ($driver === 'mysql') {
        $host = getenv('PARTICIPATORY_DB_HOST') ?: '127.0.0.1';
        $name = getenv('PARTICIPATORY_DB_NAME') ?: 'participation';
        $user = getenv('PARTICIPATORY_DB_USER') ?: 'root';
        $pass = getenv('PARTICIPATORY_DB_PASS') ?: '';

        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $host, $name);
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } else {
        $path = __DIR__ . '/storage.sqlite';
        $dsn = 'sqlite:' . $path;
        $pdo = new PDO($dsn, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $pdo->exec('PRAGMA foreign_keys = ON');
    }

    return $pdo;
}

function initializeDatabase(PDO $pdo): void
{
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver === 'mysql') {
        $sql = <<<SQL
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120),
            email VARCHAR(255) UNIQUE,
            role_preference VARCHAR(80) DEFAULT 'assessore',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS ideas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            district VARCHAR(120) NOT NULL,
            theme VARCHAR(80),
            author_name VARCHAR(120),
            author_email VARCHAR(255),
            candidate_opt_in TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            user_id INT,
            CONSTRAINT fk_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        );

        CREATE TABLE IF NOT EXISTS votes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            idea_id INT NOT NULL,
            voter_token VARCHAR(120) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_votes_idea FOREIGN KEY (idea_id) REFERENCES ideas(id) ON DELETE CASCADE,
            UNIQUE KEY uniq_vote (idea_id, voter_token)
        );

        CREATE TABLE IF NOT EXISTS comments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            idea_id INT NOT NULL,
            author_name VARCHAR(120),
            body TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_comments_idea FOREIGN KEY (idea_id) REFERENCES ideas(id) ON DELETE CASCADE
        );
        SQL;
    } else {
        $sql = <<<SQL
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT,
            email TEXT UNIQUE,
            role_preference TEXT DEFAULT 'assessore',
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS ideas (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            description TEXT NOT NULL,
            district TEXT NOT NULL,
            theme TEXT,
            author_name TEXT,
            author_email TEXT,
            candidate_opt_in INTEGER DEFAULT 0,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            user_id INTEGER,
            FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL
        );

        CREATE TABLE IF NOT EXISTS votes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            idea_id INTEGER NOT NULL,
            voter_token TEXT NOT NULL,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(idea_id) REFERENCES ideas(id) ON DELETE CASCADE,
            UNIQUE(idea_id, voter_token)
        );

        CREATE TABLE IF NOT EXISTS comments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            idea_id INTEGER NOT NULL,
            author_name TEXT,
            body TEXT NOT NULL,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(idea_id) REFERENCES ideas(id) ON DELETE CASCADE
        );
        SQL;
    }

    $pdo->exec($sql);
}
