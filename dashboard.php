<?php
require_once __DIR__ . '/includes/auth_check.php';

$user_id = $_SESSION['user_id'];

// Pesan dari halaman lain (ex: setelah hapus ulasan)
$pesan = '';
if(isset($_GET['pesan']) && $_GET['pesan'] === 'hapus') {
    $pesan = 'Ulasan berhasil dihapus.';
} else if(isset($_GET['pesan']) && $_GET['pesan'] === 'tidak_ditemukan') {
    $pesan = 'Ulasan tidak ditemukan';
}

// Statistik jumlah ulasan dan rata-rata rating milik user yang sedang login
$stmt = $koneksi->prepare('SELECT COUNT(*) AS total, AVG(rating) AS rata FROM reviews WHERE user_id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stat = $stmt->get_result()->fetch_assoc();
$stmt->close();

$rata_rata = $stat['rata'] ? round($stat['rata'], 1) : '-';

// Daftar ulasan milik user yang sedang login
$stmt = $koneksi->prepare(
    'SELECT id, judul_game, genre, platform, rating, gambar, updated_at
     FROM reviews WHERE user_id = ? ORDER BY updated_at DESC'
);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$hasil = $stmt->get_result();

$page_title = 'Dashboard';
include __DIR__ . '/includes/header.php';
?>

<main class="wrap page-dashboard">

    <div class="dash-head">
        <div>
            <h1>Halo, <?= htmlspecialchars($_SESSION['username']) ?></h1>
            <p class="muted">Kelola semua ulasan game yang pernah kamu tulis.</p>
        </div>
        <a class="btn" href="<?= BASE_URL ?>reviews/create.php">+ Tulis ulasan</a>
    </div>

    <?php if ($pesan): ?>
        <p class="alert alert-success"><?= htmlspecialchars($pesan) ?></p>
    <?php endif; ?>

    <div class="stats stats-2">
        <div class="stat"><strong><?= $stat['total'] ?></strong><span>Ulasanmu</span></div>
        <div class="stat"><strong><?= $rata_rata ?></strong><span>Rata-rata rating</span></div>
    </div>

    <section class="panel">
        <h2>Ulasanmu</h2>

        <?php if ($hasil->num_rows === 0): ?>
            <div class="empty">
                <p>Kamu belum menulis ulasan apa pun.</p>
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
                                <td class="nowrap"><?= date('d/m/Y', strtotime($r['updated_at'])) ?></td>
                                <td class="nowrap">
                                    <a class="btn btn-small" href="<?= BASE_URL ?>reviews/edit.php?id=<?= $r['id'] ?>">Edit</a>

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
