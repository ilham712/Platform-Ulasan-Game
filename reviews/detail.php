<?php
// reviews/detail.php - Menampilkan satu ulasan lengkap (READ)
// Halaman ini boleh dibuka siapa saja, tidak wajib login.

session_start();
require_once __DIR__ . '/../config/koneksi.php';

$id = (int) ($_GET['id'] ?? 0);

// Untuk Ambil ulasan beserta nama penulisnya
$stmt = $koneksi->prepare(
    'SELECT r.*, u.username
     FROM reviews r
     JOIN users u ON u.id = r.user_id
     WHERE r.id = ?'
);
$stmt->bind_param('i', $id);
$stmt->execute();
$r = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Kalau tidak ketemu, tampilkan pesan
if (!$r) {
    http_response_code(404);
    $page_title = 'Tidak ditemukan';
    include __DIR__ . '/../includes/header.php';
    ?>
    <main class="container">
        <h1>Ulasan tidak ditemukan</h1>
        <p>Ulasan yang kamu cari tidak ada atau sudah dihapus.</p>
        <p><a class="btn" href="<?= BASE_URL ?>index.php#ulasan">Kembali ke daftar ulasan</a></p>
    </main>
    <?php
    include __DIR__ . '/../includes/footer.php';
    exit;
}

// 3. Tombol edit/hapus muncul untuk pemilik ulasan dan untuk admin
$role = $_SESSION['role'] ?? 'user';
$boleh_kelola = isset($_SESSION['user_id']) && ($_SESSION['user_id'] == $r['user_id'] || $role === 'admin');

// Pesan sukses setelah tambah/edit
$pesan = '';
if (isset($_GET['pesan']) && $_GET['pesan'] === 'tambah') {
    $pesan = 'Ulasan berhasil dipublikasikan.';
} elseif (isset($_GET['pesan']) && $_GET['pesan'] === 'edit') {
    $pesan = 'Ulasan berhasil diperbarui.';
}

$page_title = $r['judul_game'];
include __DIR__ . '/../includes/header.php';
?>

<main class="container container-detail">

    <?php if ($pesan): ?>
        <p class="alert alert-success"><?= htmlspecialchars($pesan) ?></p>
    <?php endif; ?>

    <p class="back"><a href="<?= BASE_URL ?>index.php#ulasan">&larr; Semua ulasan</a></p>

    <article class="detail">
        <div class="detail-cover">
            <?php if ($r['gambar']): ?>
                <img src="<?= BASE_URL ?>assets/uploads/<?= htmlspecialchars($r['gambar']) ?>"
                     alt="Sampul <?= htmlspecialchars($r['judul_game']) ?>">
            <?php else: ?>
                <div class="ph"><?= htmlspecialchars(mb_substr($r['judul_game'], 0, 1)) ?></div>
            <?php endif; ?>
        </div>

        <div class="detail-body">
            <div class="chips">
                <span class="chip"><?= htmlspecialchars($r['genre']) ?></span>
                <span class="chip chip-alt"><?= htmlspecialchars($r['platform']) ?></span>
            </div>

            <h1><?= htmlspecialchars($r['judul_game']) ?></h1>

            <p class="detail-rating">
                <span class="stars"><?= str_repeat('★', $r['rating']) . str_repeat('☆', 5 - $r['rating']) ?></span>
                <strong><?= $r['rating'] ?>/5</strong>
            </p>

            <p class="meta">
                Ditulis oleh <strong><?= htmlspecialchars($r['username']) ?></strong>
                &middot; <?= date('d/m/Y', strtotime($r['created_at'])) ?>
            </p>

            <!-- nl2br mengubah baris baru di teks menjadi <br> supaya paragraf tetap terlihat -->
            <div class="review-text"><?= nl2br(htmlspecialchars($r['isi_ulasan'])) ?></div>

            <?php if ($boleh_kelola): ?>
                <div class="form-actions">
                    <a class="btn" href="<?= BASE_URL ?>reviews/edit.php?id=<?= $r['id'] ?>">Edit</a>

                    <form class="inline" method="POST" action="<?= BASE_URL ?>reviews/delete.php"
                          onsubmit="return confirm('Yakin ingin menghapus ulasan ini?');">
                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </article>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
