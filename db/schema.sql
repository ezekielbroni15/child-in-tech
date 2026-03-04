-- ============================================================
--  Child In Tech — Innoventure Registration System
--  Run this in phpMyAdmin on database: cit_innoventure
-- ============================================================

CREATE DATABASE IF NOT EXISTS u275225649_cit_innoventur CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE u275225649_cit_innoventur;

-- Tours (created by admin)
CREATE TABLE IF NOT EXISTS tours (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  tour_number  VARCHAR(10)   NOT NULL,          -- e.g. "3.0"
  tour_date    DATE          NOT NULL,           -- e.g. 2026-03-07
  time_start   TIME          NOT NULL DEFAULT '09:00:00',
  time_end     TIME          NOT NULL DEFAULT '14:00:00',
  location     VARCHAR(255)  NOT NULL DEFAULT 'TBA',
  max_slots    INT           NOT NULL DEFAULT 50,
  is_active    TINYINT(1)    NOT NULL DEFAULT 1,
  created_at   TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
);

-- Registrations
CREATE TABLE IF NOT EXISTS registrations (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  ticket_id     VARCHAR(25)   UNIQUE NOT NULL,   -- e.g. CIT-20260307-0042
  tour_id       INT           NOT NULL,
  full_name     VARCHAR(150)  NOT NULL,
  email         VARCHAR(150)  NOT NULL,
  phone         VARCHAR(30),
  age_group     VARCHAR(30),
  school        VARCHAR(150),
  checked_in    TINYINT(1)    NOT NULL DEFAULT 0,
  email_sent    TINYINT(1)    NOT NULL DEFAULT 0,
  registered_at TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (tour_id) REFERENCES tours(id) ON DELETE CASCADE
);

-- Admin users
CREATE TABLE IF NOT EXISTS admin_users (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  username      VARCHAR(80)   UNIQUE NOT NULL,
  password_hash VARCHAR(255)  NOT NULL,
  created_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
--  Seed Data
-- ============================================================

-- Seed default admin: username=admin  password=citadmin2026
INSERT IGNORE INTO admin_users (username, password_hash)
VALUES ('admin', '$2y$10$tGecq8AFWaj3.YH0yDNda.BuDCXxzMF1LDsp5w2yOumEOV/TKNM1Tu');
-- NOTE: Change this password immediately via phpMyAdmin or the admin portal!
-- The hash above is for "password" — replace with your real hash.
-- To generate: php -r "echo password_hash('yourpassword', PASSWORD_DEFAULT);"

