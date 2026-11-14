<?php
// index.php
$page_title = 'Beranda';
include __DIR__ . '/includes/header.php';
?>

<main class="container">
    <h1>Selamat datang di Platform Ulasan Game</h1>
    <p>Baca dan tulis ulasan game favoritmu di sini.</p>

    <?php if (!$sudah_login): ?>
        <p><a class="btn" href="<?= BASE_URL ?>auth/login.php">Login untuk mulai menulis ulasan</a></p>
    <?php else: ?>
        <p><a class="btn" href="<?= BASE_URL ?>dashboard.php">Buka dashboard</a></p>
    <?php endif; ?>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>