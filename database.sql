-- Quizwhiz Junction schema
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  points INT NOT NULL DEFAULT 0,
  avatar_seed VARCHAR(32) DEFAULT NULL,
  is_admin TINYINT(1) NOT NULL DEFAULT 0,
  invited_by INT NULL,
  created_at DATETIME NOT NULL,
  CONSTRAINT fk_users_invited_by FOREIGN KEY (invited_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS quizzes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  description VARCHAR(500) DEFAULT NULL,
  category VARCHAR(100) DEFAULT NULL,
  difficulty ENUM('easy','medium','hard') DEFAULT 'medium',
  points_per_question INT NOT NULL DEFAULT 10,
  question_time INT NOT NULL DEFAULT 30, -- seconds per question
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS questions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  quiz_id INT NOT NULL,
  text TEXT NOT NULL,
  CONSTRAINT fk_questions_quiz FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS options (
  id INT AUTO_INCREMENT PRIMARY KEY,
  question_id INT NOT NULL,
  text VARCHAR(500) NOT NULL,
  is_correct TINYINT(1) NOT NULL DEFAULT 0,
  CONSTRAINT fk_options_question FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS attempts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  quiz_id INT NOT NULL,
  user_id INT NOT NULL,
  score INT NOT NULL DEFAULT 0,
  total_time INT NOT NULL DEFAULT 0, -- seconds, server-validated
  created_at DATETIME NOT NULL,
  CONSTRAINT fk_attempts_quiz FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
  CONSTRAINT fk_attempts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_attempts_user (user_id),
  INDEX idx_attempts_quiz (quiz_id)
);

CREATE TABLE IF NOT EXISTS attempt_answers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  attempt_id INT NOT NULL,
  question_id INT NOT NULL,
  option_id INT NULL,
  is_correct TINYINT(1) NOT NULL DEFAULT 0,
  time_spent INT NOT NULL DEFAULT 0, -- seconds for this question, clamped to 30
  CONSTRAINT fk_ans_attempt FOREIGN KEY (attempt_id) REFERENCES attempts(id) ON DELETE CASCADE,
  CONSTRAINT fk_ans_question FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
  CONSTRAINT fk_ans_option FOREIGN KEY (option_id) REFERENCES options(id) ON DELETE SET NULL,
  INDEX idx_ans_attempt (attempt_id)
);

-- Invites support
CREATE TABLE IF NOT EXISTS invites (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  code VARCHAR(24) NOT NULL UNIQUE,
  used_by INT NULL,
  created_at DATETIME NOT NULL,
  redeemed_at DATETIME NULL,
  CONSTRAINT fk_invites_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_invites_used_by FOREIGN KEY (used_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Trigger-like logic can be implemented in app code for ranking and points. Indexes for performance:
CREATE INDEX idx_users_points ON users(points);

-- Admin-specific metadata table
CREATE TABLE IF NOT EXISTS admins (
  user_id INT PRIMARY KEY,
  super_admin TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('active','disabled') NOT NULL DEFAULT 'active',
  notes TEXT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NULL,
  CONSTRAINT fk_admins_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX idx_admins_status ON admins(status);

-- Dedicated admin accounts (separate from users)
CREATE TABLE IF NOT EXISTS admin_accounts (
  admin_id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(32) NOT NULL DEFAULT 'admin',
  created_at DATETIME NOT NULL
);

-- --------------------------------------------------
-- Bootstrap Admin Insert (Optional)
-- Run these lines ONCE to create an initial admin if the application bootstrap did not.
-- Password = pass123 (bcrypt hashed). You can regenerate with PHP: password_hash('pass123', PASSWORD_DEFAULT)
-- Remove or comment out after execution to avoid duplicates.
-- --------------------------------------------------
INSERT INTO users (name,email,password_hash,points,avatar_seed,is_admin,created_at)
VALUES (
  'Administrator',
  'admin@gmail.com',
  'pass123',
  0,
  LEFT(UUID(),12),
  1,
  NOW()
);
SET @admin_id = (SELECT id FROM users WHERE email='admin@gmail.com');
INSERT INTO admins (user_id, super_admin, status, notes, created_at)
VALUES (@admin_id, 1, 'active', 'Initial super admin', NOW());

-- --------------------------------------------------
-- Schema upgrade helper (run manually if upgrading an existing instance)
-- --------------------------------------------------
-- ALTER TABLE quizzes ADD COLUMN category VARCHAR(100) DEFAULT NULL;
-- ALTER TABLE quizzes ADD COLUMN difficulty ENUM('easy','medium','hard') DEFAULT 'medium';
-- ALTER TABLE quizzes ADD COLUMN points_per_question INT NOT NULL DEFAULT 10;
-- ALTER TABLE quizzes ADD COLUMN question_time INT NOT NULL DEFAULT 30;
-- ALTER TABLE quizzes ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1;
-- CREATE TABLE admin_accounts (
--   admin_id INT AUTO_INCREMENT PRIMARY KEY,
--   username VARCHAR(100) NOT NULL UNIQUE,
--   email VARCHAR(190) NOT NULL UNIQUE,
--   password_hash VARCHAR(255) NOT NULL,
--   role VARCHAR(32) NOT NULL DEFAULT 'admin',
--   created_at DATETIME NOT NULL
-- );

