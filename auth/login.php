<?php
// auth/login.php

session_start();
require_once __DIR__ . '/../config/koneksi.php';

// Kalau sudah login, tidak perlu lihat halaman login lagi
if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'dashboard.php');
    exit;
}

$error    = '';
$info     = '';
$username = '';

// Pesan dari redirect (logout / belum login)
if (isset($_GET['logout'])) {
    $info = 'Kamu sudah keluar dari akun.';
} elseif (isset($_GET['pesan']) && $_GET['pesan'] === 'login') {
    $info = 'Silakan login dulu untuk membuka halaman itu.';
} elseif (isset($_GET['pesan']) && $_GET['pesan'] === 'registered') {
    $info = 'Akun berhasil dibuat. Silakan login.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username dan password harus diisi.';
    } else {
        // Prepared statement supaya aman dari SQL injection
        $stmt = $koneksi->prepare('SELECT id, username, password FROM users WHERE username = ? LIMIT 1');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true); // cegah session fixation
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];

            header('Location: ' . BASE_URL . 'dashboard.php');
            exit;
        }

        $error = 'Username atau password salah.';
    }
}

$page_title = 'Login';
include __DIR__ . '/../includes/header.php';
?>

<main class="auth-page">
    <form class="auth-card" method="POST" action="">
        <h1>Masuk ke akunmu</h1>

        <?php if ($info): ?>
            <p class="alert alert-info"><?= htmlspecialchars($info) ?></p>
        <?php endif; ?>

        <?php if ($error): ?>
            <p class="alert alert-error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <label for="username">Username</label>
        <input type="text" id="username" name="username"
               value="<?= htmlspecialchars($username) ?>" autocomplete="username" required autofocus>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" autocomplete="current-password" required>

        <button type="submit" class="btn btn-block">Login</button>

        <p class="auth-note">Belum punya akun? <a href="register.php">Daftar di sini</a></p>
    </form>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>