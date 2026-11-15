<?php
// reviews/delete.php - Menghapus ulasan (DELETE)
// Hanya menerima POST dari tombol "Hapus", jadi tidak bisa dihapus hanya dengan membuka link.

require_once __DIR__ . '/../includes/auth_check.php'; 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'dashboard.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$id      = (int) ($_POST['id'] ?? 0);

// Cari ulasannya (hanya milik user yang sedang login) untuk tahu nama file gambarnya
$stmt = $koneksi->prepare('SELECT gambar FROM reviews WHERE id = ? AND user_id = ?');
$stmt->bind_param('ii', $id, $user_id);
$stmt->execute();
$ulasan = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$ulasan) {
    header('Location: ' . BASE_URL . 'dashboard.php?pesan=tidak_ditemukan');
    exit;
}

// Hapus datanya dari database
$stmt = $koneksi->prepare('DELETE FROM reviews WHERE id = ? AND user_id = ?');
$stmt->bind_param('ii', $id, $user_id);
$stmt->execute();
$stmt->close();

// Hapus file gambarnya dari folder (kalau ada)
if ($ulasan['gambar']) {
    @unlink(__DIR__ . '/../assets/uploads/' . $ulasan['gambar']);
}

header('Location: ' . BASE_URL . 'dashboard.php?pesan=hapus');
exit;
