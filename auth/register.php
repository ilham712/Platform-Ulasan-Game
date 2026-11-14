<?php
// auth/register.php

session_start();
require_once __DIR__ . '/../config/koneksi.php';

// Kalau sudah login, tidak perlu daftar lagi
if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'dashboard.php');
    exit;
}

$error    = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username   = trim($_POST['username'] ?? '');
    $password   = $_POST['password'] ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';

    if ($username === '' || $password === '' || $konfirmasi === '') {
        $error = 'Semua kolom harus diisi.';
    } elseif (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) {
        $error = 'Username 3-30 karakter, hanya boleh huruf, angka, dan underscore.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($password !== $konfirmasi) {
        $error = 'Konfirmasi password tidak sama dengan password.';
    } else {
        // Cek apakah username sudah dipakai
        $stmt = $koneksi->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $sudah_ada = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        if ($sudah_ada) {
            $error = 'Username sudah dipakai, coba username lain.';
        } else {
            // Simpan password dalam bentuk hash, bukan teks biasa
            $hash = password_hash($password, PASSWORD_DEFAULT);

            try {
                $stmt = $koneksi->prepare('INSERT INTO users (username, password) VALUES (?, ?)');
                $stmt->bind_param('ss', $username, $hash);
                $stmt->execute();
                $stmt->close();

                header('Location: ' . BASE_URL . 'auth/login.php?pesan=registered');
                exit;
            } catch (mysqli_sql_exception $e) {
                $error = 'Pendaftaran gagal. Coba lagi beberapa saat.';
            }
        }
    }
}

$page_title = 'Daftar';
include __DIR__ . '/../includes/header.php';
?>

<main class="auth-page">
    <form class="auth-card" method="POST" action="">
        <h1>Buat akun baru</h1>

        <?php if ($error): ?>
            <p class="alert alert-error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <label for="username">Username</label>
        <input type="text" id="username" name="username" maxlength="30"
               value="<?= htmlspecialchars($username) ?>" autocomplete="username" required autofocus>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" minlength="6"
               autocomplete="new-password" required>

        <label for="konfirmasi">Ulangi password</label>
        <input type="password" id="konfirmasi" name="konfirmasi" minlength="6"
               autocomplete="new-password" required>

        <button type="submit" class="btn btn-block">Daftar</button>

        <p class="auth-note">Sudah punya akun? <a href="login.php">Login di sini</a></p>
    </form>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>