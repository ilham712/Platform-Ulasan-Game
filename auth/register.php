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
$email    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username   = trim($_POST['username'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $password   = $_POST['password'] ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';

    if ($username === '' || $email === '' || $password === '' || $konfirmasi === '') {
        $error = 'Semua kolom harus diisi.';
    } elseif (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) {
        $error = 'Username 3-30 karakter, hanya boleh huruf, angka, dan underscore.';
    } elseif (strcasecmp($username, DEMO_USERNAME) === 0 || strcasecmp($email, DEMO_EMAIL) === 0) {
        $error = 'Username atau email itu dicadangkan untuk akun demo.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
        $error = 'Format email tidak valid.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($password !== $konfirmasi) {
        $error = 'Konfirmasi password tidak sama dengan password.';
    } else {
        try {
            // Cek apakah username atau email sudah dipakai
            $stmt = $koneksi->prepare('SELECT username, email FROM users WHERE username = ? OR email = ? LIMIT 1');
            $stmt->bind_param('ss', $username, $email);
            $stmt->execute();
            $duplikat = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($duplikat) {
                $error = (strcasecmp($duplikat['username'], $username) === 0)
                    ? 'Username sudah dipakai, coba username lain.'
                    : 'Email sudah terdaftar. Login atau pakai email lain.';
            } else {
                // Simpan password dalam bentuk hash, bukan teks biasa
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $koneksi->prepare('INSERT INTO users (username, email, password) VALUES (?, ?, ?)');
                $stmt->bind_param('sss', $username, $email, $hash);
                $stmt->execute();
                $stmt->close();

                header('Location: ' . BASE_URL . 'auth/login.php?pesan=registered');
                exit;
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062) {
                // Bentrok data unik (mis. dua orang daftar bersamaan)
                $error = 'Username atau email sudah terdaftar.';
            } else {
                // Detail error masuk ke log server (mis. kolom email belum ada di tabel users)
                error_log('Register gagal: ' . $e->getMessage());
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

        <label for="email">Email</label>
        <input type="email" id="email" name="email" maxlength="100"
               value="<?= htmlspecialchars($email) ?>" autocomplete="email" required>

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