<?php
// includes/auth_check.php
// Include di paling atas halaman yang wajib login (dashboard.php, reviews/*.php),
// SEBELUM ada output HTML apa pun.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'auth/login.php?pesan=login');
    exit;
}