-- ============================================================
-- Database: ulasan_game  (Platform Ulasan Game - PHP Native)
-- Import lewat phpMyAdmin atau: mysql -u root < database.sql
-- Semua user dummy memakai password: password
-- ============================================================

CREATE DATABASE IF NOT EXISTS ulasan_game
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE ulasan_game;

DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS users;

-- ---------------------------
-- Tabel users (login/register)
-- ---------------------------
CREATE TABLE users (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username    VARCHAR(50)  NOT NULL UNIQUE,
  email       VARCHAR(100) NOT NULL UNIQUE,
  password    VARCHAR(255) NOT NULL,
  role        ENUM('admin','user') NOT NULL DEFAULT 'user',
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------
-- Tabel reviews (CRUD ulasan)
-- ---------------------------
CREATE TABLE reviews (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id       INT UNSIGNED NOT NULL,
  judul_game    VARCHAR(150) NOT NULL,
  genre         VARCHAR(50)  NOT NULL,
  platform      VARCHAR(50)  NOT NULL,
  rating        TINYINT UNSIGNED NOT NULL,
  isi_ulasan    TEXT NOT NULL,
  gambar        VARCHAR(255) DEFAULT NULL,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_reviews_user FOREIGN KEY (user_id)
    REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT chk_rating CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB;

-- ---------------------------
-- Data dummy: users
-- ---------------------------
INSERT INTO users (username, email, password, role) VALUES
('admin',  'admin@gamereview.test',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('budi',   'budi@gamereview.test',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('sari',   'sari@gamereview.test',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('andi',   'andi@gamereview.test',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

-- ---------------------------
-- Data dummy: reviews
-- ---------------------------
INSERT INTO reviews (user_id, judul_game, genre, platform, rating, isi_ulasan, gambar) VALUES
(2, 'The Witcher 3: Wild Hunt', 'RPG', 'PC', 5,
 'Dunia terbukanya luas dan cerita sampingannya terasa seperti cerita utama. Sulit dicari tandingannya sampai sekarang.', NULL),
(2, 'Hollow Knight', 'Metroidvania', 'PC', 5,
 'Atmosfernya indah, musiknya memukau, dan pertarungannya menantang tapi adil. Sangat sepadan dengan harganya.', NULL),
(3, 'Genshin Impact', 'Action RPG', 'Mobile', 4,
 'Grafis cantik dan eksplorasi seru. Sistem gacha-nya cukup menguras kesabaran, tapi bisa dinikmati tanpa top up.', NULL),
(3, 'Stardew Valley', 'Simulasi', 'PC', 5,
 'Game santai yang bikin lupa waktu. Bertani, memancing, dan berteman dengan warga desa itu menenangkan.', NULL),
(4, 'Mobile Legends: Bang Bang', 'MOBA', 'Mobile', 3,
 'Seru dimainkan bareng teman, tapi kadang matchmaking kurang seimbang dan ada pemain yang toxic.', NULL),
(4, 'EA Sports FC 24', 'Olahraga', 'PS5', 3,
 'Gameplay terasa halus, namun perubahan dari versi sebelumnya tidak terlalu besar dan mode Ultimate Team terlalu menekan.', NULL),
(2, 'Cyberpunk 2077', 'RPG', 'PC', 4,
 'Setelah banyak patch, game ini jauh lebih stabil. Night City sangat hidup dan ceritanya kuat.', NULL),
(3, 'Minecraft', 'Sandbox', 'PC', 5,
 'Kebebasan berkreasi tanpa batas. Cocok dimainkan sendiri maupun bersama teman.', NULL);
 