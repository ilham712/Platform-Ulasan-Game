<?php
// includes/header.php
// Sebelum include, halaman bisa set judul: $page_title = 'Beranda';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/koneksi.php';

$page_title = $page_title ?? 'Beranda';
$sudah_login = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($page_title) ?> | Platform Ulasan Game</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>includes/stylelogin.css">
</head>
<body>

<header class="site-header">
    <a class="brand" href="<?= BASE_URL ?>index.php">Ulasan Game</a>

    <nav class="site-nav">
        <a href="<?= BASE_URL ?>index.php">Beranda</a>

        <?php if ($sudah_login): ?>
            <a href="<?= BASE_URL ?>dashboard.php">Dashboard</a>
            <span class="nav-user">Halo, <?= htmlspecialchars($_SESSION['username']) ?></span>
            <a class="btn btn-small btn-outline" href="<?= BASE_URL ?>auth/logout.php">Logout</a>
        <?php else: ?>
            <a href="<?= BASE_URL ?>auth/login.php">Login</a>
            <a class="btn btn-small" href="<?= BASE_URL ?>auth/register.php">Daftar</a>
        <?php endif; ?>
    </nav>
</header>