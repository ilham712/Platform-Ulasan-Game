<?php
// reviews/delete.php - Menghapus ulasan (DELETE)
// Hanya menerima POST dari tombol "Hapus", jadi tidak bisa dihapus hanya dengan membuka link.

require_once __DIR__ . '/../includes/auth_check.php'; 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'dashboard.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$role    = $_SESSION['role'] ?? 'user'; // 'admin' atau 'user'
$id      = (int) ($_POST['id'] ?? 0);

// 1. Cari ulasannya untuk tahu pemiliknya dan nama file gambarnya
$stmt = $koneksi->prepare('SELECT user_id, gambar FROM reviews WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$ulasan = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Boleh menghapus kalau ulasan itu milik sendiri, atau kalau dia admin
if (!$ulasan || ($ulasan['user_id'] != $user_id && $role !== 'admin')) {
    header('Location: ' . BASE_URL . 'dashboard.php?pesan=tidak_ditemukan');
    exit;
}

$stmt = $koneksi->prepare('DELETE FROM reviews WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->close();

// Hapus file gambarnya dari folder (kalau ada)
if ($ulasan['gambar']) {
    @unlink(__DIR__ . '/../assets/uploads/' . $ulasan['gambar']);
}

header('Location: ' . BASE_URL . 'dashboard.php?pesan=hapus');
exit;
