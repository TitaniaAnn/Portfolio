-- ============================================================
-- Portfolio Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS bfpcdjmy_programming CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bfpcdjmy_programming;

-- Allowed admin users (whitelist)
CREATE TABLE IF NOT EXISTS admins (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    email       VARCHAR(255) NOT NULL UNIQUE,
    name        VARCHAR(255),
    avatar_url  VARCHAR(512),
    provider    ENUM('google','github') NOT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Sessions
CREATE TABLE IF NOT EXISTS sessions (
    id          VARCHAR(128) PRIMARY KEY,
    admin_id    INT NOT NULL,
    ip          VARCHAR(64),
    user_agent  TEXT,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at  DATETIME NOT NULL,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
);

-- Projects
CREATE TABLE IF NOT EXISTS projects (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    title             VARCHAR(255) NOT NULL,
    short_description TEXT,
    description       TEXT NOT NULL,
    language          VARCHAR(100) NOT NULL,
    tags              VARCHAR(512),
    github_url        VARCHAR(512),
    demo_url          VARCHAR(512),
    summary_image     VARCHAR(512),
    status            ENUM('active','wip','archived') DEFAULT 'active',
    sort_order        INT DEFAULT 0,
    year              YEAR NULL,
    created_at        DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at        DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Project images (one-to-many)
CREATE TABLE IF NOT EXISTS project_images (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    project_id  INT NOT NULL,
    url         VARCHAR(512) NOT NULL,
    sort_order  INT DEFAULT 0,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

-- Skill groups (About section)
CREATE TABLE IF NOT EXISTS skill_groups (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    label      VARCHAR(255) NOT NULL,
    skills     TEXT,
    sort_order INT DEFAULT 0
);

-- Site settings (key-value)
CREATE TABLE IF NOT EXISTS settings (
    `key`       VARCHAR(100) PRIMARY KEY,
    `value`     TEXT,
    updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Seed default settings
INSERT INTO settings (`key`, `value`) VALUES
  ('name',     'Your Name'),
  ('role',     'Full-Stack Developer'),
  ('email',    'you@example.com'),
  ('github',   'https://github.com/yourusername'),
  ('linkedin', ''),
  ('location', 'San Francisco, CA'),
  ('tagline',  'Building fast, reliable software'),
  ('years_exp','5+'),
  ('bio',      'A passionate developer who loves building great software.')
ON DUPLICATE KEY UPDATE `key` = `key`;

-- Seed whitelisted admin (replace with your actual email)
INSERT IGNORE INTO admins (email, name, provider)
VALUES ('titaniaandroid@gmail.com', 'Admin', 'google');

-- Sample projects
INSERT INTO projects (title, description, language, tags, github_url, demo_url, status, sort_order) VALUES
  ('Project Alpha', 'A high-performance REST API with real-time capabilities built on Node.js and PostgreSQL.', 'TypeScript', 'Node.js,PostgreSQL,REST', 'https://github.com', '', 'active', 1),
  ('DataSync CLI', 'Command-line tool for bidirectional database sync across environments with conflict resolution.', 'Go', 'CLI,Database,DevOps', 'https://github.com', '', 'active', 2),
  ('ReactFlow UI', 'Component library of 40+ accessible, animated React components with Storybook documentation.', 'JavaScript', 'React,Storybook,A11y', 'https://github.com', 'https://example.com', 'active', 3);
-- Migration for existing installs:
-- ALTER TABLE projects DROP COLUMN IF EXISTS image_url;
-- CREATE TABLE IF NOT EXISTS project_images (id INT AUTO_INCREMENT PRIMARY KEY, project_id INT NOT NULL, url VARCHAR(512) NOT NULL, sort_order INT DEFAULT 0, FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE);
-- ALTER TABLE projects ADD COLUMN short_description TEXT AFTER title;
-- ALTER TABLE projects ADD COLUMN summary_image VARCHAR(512) AFTER demo_url;
-- ALTER TABLE projects ADD COLUMN year YEAR NULL AFTER sort_order;
