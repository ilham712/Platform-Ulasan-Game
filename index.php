<?php
// index.php
$page_title = 'Beranda';
include __DIR__ . '/includes/header.php';

// Untuk cari ulasan dari URL
$cari = trim($_GET['q'] ?? '');

// Untuk Menghitung Statistik ulasan dan user
$total_ulasan = $koneksi->query('SELECT COUNT(*) AS n FROM reviews')->fetch_assoc()['n'];
$total_user = $koneksi->query('SELECT COUNT(*) AS n FROM users')->fetch_assoc()['n'];

// Untuk ambil ulasan dari database
$kata = '%' . $cari . '%';

$stmt = $koneksi->prepare(
    'SELECT r.id, r.judul_game, r.genre, r.platform, r.rating, r.isi_ulasan, r.gambar, r.created_at, u.username FROM reviews r JOIN users u ON u.id = r.user_id WHERE r.judul_game LIKE ? ORDER BY r.created_at DESC'
);
$stmt->bind_param('s', $kata);
$stmt->execute();
$stmt = $stmt->get_result();
?>

<main class="page">
    <!-- Bagian Pembuka -->
     <section class="hero">
        <div class="wrap">
            <span class="eyebrow">Komunitas Pemain Game</span>
            <h1>Cerita jujur dari para <em>GAMER</em>, sebelum kamu memainkan game tersebut.</h1>
            <p class="lead">Baca ulasan game dari pemain lain, lalu bagikan pengalamanmu sendiri.</p>

            <div class="hero-actions">
                <?php if($sudah_login): ?>
                    <a class="btn btn-lg" href="<?= BASE_URL ?>reviews/create.php">Tulis Ulasan</a>
                    <a class="btn btn-lg btn-ghost" href="<?= BASE_URL ?>dashboard.php">Buka Dashboard</a>
                <?php else: ?>
                    <a class="btn btn-lg" href="<?= BASE_URL ?>auth/register.php">Mulai Menulis</a>
                    <a class="btn btn-lg btn-ghost" href="#ulasan">Baca Ulasan</a>
                <?php endif; ?>
            </div>
        </div>
     </section>

     <!-- Statistik -->
      <section class="wrap">
        <div class="stats stats-2">
            <div class="stat">
                <strong><?= $total_ulasan ?></strong>
                <span>Ulasan</span>
            </div>
            <div class="stat">
                <strong><?= $total_user ?></strong>
                <span>User</span>
            </div>
        </div>
      </section>

      <!-- Daftar Ulasan -->
       <section class="wrap section" id="ulasan">
        <div class="section-head">
            <h2>Ulasan Terbaru</h2>

            <form class="filter" method="GET" action="index.php#ulasan">
                <input class="input" type="search" name="q" placeolder="Cari judul game..." value="<?= htmlspecialchars($cari)?>">
                <button class="btn btn-small" type="submit">Cari</button>
            </form>
        </div>

        <?php if($hasil->num_rows === 0): ?>
            <div class="empty"><p>Belum ada ulasan yang cocok.</p></div>
        <?php else: ?>
            <div class="grid">
                <?php while($r = $hasil->fetch_assoc()): ?>
                    <article class="review-card">
                        <a class="review-thumb" href="<?= BASE_URL ?>reviews/detail.php?id=<?= $r['id'] ?>">
                            <?php if($r['gambar']): ?>
                                <img src="<?= BASE_URL ?>assets/upload/<?= htmlspecialchars($r['gambar']) ?>" alt="">
                            <?php else: ?>
                                <div class="ph"><?= htmlspecialchars(mb_substr($r['judul_game'], 0, 1)) ?></div>
                            <?php endif; ?>
                        </a>

                        <div class="review-body">
                            <div class="chips">
                                <span class="chip"><?= htmlspecialchars($r['genre']) ?></span>
                                <span class="chip chip-alt"><?= htmlspecialchars($r['platform']) ?></span>
                            </div>

                            <h3>
                                <a href="<?= BASE_URL ?>reviews/detail.php?id=<?= $r['id'] ?>">
                                    <?= htmlspecialchars($r['judul_game']) ?>
                                </a>
                            </h3>

                            <span class="stars"><?= str_repeat('★', $r['rating']) . str_repeat('☆', 5 - $r['rating']) ?></span>

                            <p class="excerpt"><?= htmlspecialchars(mb_substr($r['isi_ulasan'], 0, 100)) ?>...</p>

                            <p class="meta">
                                oleh <strong><?= htmlspecialchars($r['username']) ?></strong>
                                &middot; <?= date('d/m/Y', strtotime($r['created_at'])) ?>
                            </p>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
       </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>