<?php
// dashboard.php - Halaman utama setelah login
// User biasa melihat ulasan miliknya sendiri, admin melihat semua ulasan.

require_once __DIR__ . '/includes/auth_check.php'; // wajib login

$user_id = $_SESSION['user_id'];
$role    = $_SESSION['role'] ?? 'user'; // 'admin' atau 'user'

// Pesan dari halaman lain (misalnya setelah hapus ulasan)
$pesan = '';
if (isset($_GET['pesan']) && $_GET['pesan'] === 'hapus') {
    $pesan = 'Ulasan berhasil dihapus.';
} elseif (isset($_GET['pesan']) && $_GET['pesan'] === 'tidak_ditemukan') {
    $pesan = 'Ulasan tidak ditemukan.';
}

if ($role === 'admin') {
    // ADMIN: ambil statistik dan semua ulasan dari semua user
    $stat = $koneksi->query('SELECT COUNT(*) AS total, AVG(rating) AS rata FROM reviews')->fetch_assoc();

    $hasil = $koneksi->query(
        'SELECT r.id, r.judul_game, r.genre, r.platform, r.rating, r.updated_at, u.username
         FROM reviews r
         JOIN users u ON u.id = r.user_id
         ORDER BY r.updated_at DESC'
    );
} else {
    // USER BIASA: hanya ulasan milik user yang sedang login
    $stmt = $koneksi->prepare('SELECT COUNT(*) AS total, AVG(rating) AS rata FROM reviews WHERE user_id = ?');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stat = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $stmt = $koneksi->prepare(
        'SELECT r.id, r.judul_game, r.genre, r.platform, r.rating, r.updated_at, u.username
         FROM reviews r
         JOIN users u ON u.id = r.user_id
         WHERE r.user_id = ?
         ORDER BY r.updated_at DESC'
    );
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $hasil = $stmt->get_result();
}

$rata_rata = $stat['rata'] ? round($stat['rata'], 1) : '-';

$page_title = 'Dashboard';
include __DIR__ . '/includes/header.php';
?>

<main class="wrap page-dashboard">

    <div class="dash-head">
        <div>
            <h1>Halo, <?= htmlspecialchars($_SESSION['username']) ?></h1>
            <?php if ($role === 'admin'): ?>
                <p class="muted">Kamu masuk sebagai admin, jadi bisa mengelola semua ulasan.</p>
            <?php else: ?>
                <p class="muted">Kelola semua ulasan game yang pernah kamu tulis.</p>
            <?php endif; ?>
        </div>
        <a class="btn" href="<?= BASE_URL ?>reviews/create.php">+ Tulis ulasan</a>
    </div>

    <?php if ($pesan): ?>
        <p class="alert alert-success"><?= htmlspecialchars($pesan) ?></p>
    <?php endif; ?>

    <div class="stats stats-2">
        <div class="stat">
            <strong><?= $stat['total'] ?></strong>
            <span><?= $role === 'admin' ? 'Total ulasan' : 'Ulasanmu' ?></span>
        </div>
        <div class="stat"><strong><?= $rata_rata ?></strong><span>Rata-rata rating</span></div>
    </div>

    <section class="panel">
        <h2><?= $role === 'admin' ? 'Semua ulasan' : 'Ulasanmu' ?></h2>

        <?php if ($hasil->num_rows === 0): ?>
            <div class="empty">
                <p>Belum ada ulasan.</p>
                <a class="btn" href="<?= BASE_URL ?>reviews/create.php">Tulis ulasan pertamamu</a>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Game</th>
                            <th>Genre</th>
                            <th>Platform</th>
                            <th>Rating</th>
                            <?php if ($role === 'admin'): ?>
                                <th>Penulis</th>
                            <?php endif; ?>
                            <th>Diperbarui</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($r = $hasil->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <a href="<?= BASE_URL ?>reviews/detail.php?id=<?= $r['id'] ?>">
                                        <strong><?= htmlspecialchars($r['judul_game']) ?></strong>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($r['genre']) ?></td>
                                <td><?= htmlspecialchars($r['platform']) ?></td>
                                <td><span class="stars"><?= str_repeat('★', $r['rating']) ?></span></td>
                                <?php if ($role === 'admin'): ?>
                                    <td><?= htmlspecialchars($r['username']) ?></td>
                                <?php endif; ?>
                                <td class="nowrap"><?= date('d/m/Y', strtotime($r['updated_at'])) ?></td>
                                <td class="nowrap">
                                    <a class="btn btn-small" href="<?= BASE_URL ?>reviews/edit.php?id=<?= $r['id'] ?>">Edit</a>

                                    <!-- Hapus memakai form POST supaya tidak terhapus tanpa sengaja lewat link -->
                                    <form class="inline" method="POST" action="<?= BASE_URL ?>reviews/delete.php"
                                          onsubmit="return confirm('Yakin ingin menghapus ulasan ini?');">
                                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                        <button type="submit" class="btn btn-small btn-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
