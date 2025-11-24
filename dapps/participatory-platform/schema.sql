-- Schema for the participatory platform (MySQL-friendly)
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
  status VARCHAR(20) DEFAULT 'pending',
  published_at TIMESTAMP NULL DEFAULT NULL,
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
