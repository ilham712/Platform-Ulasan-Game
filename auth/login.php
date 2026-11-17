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

// Pesan dari redirect (logout / belum login / selesai daftar)
if (isset($_GET['logout'])) {
    $info = 'Kamu sudah keluar dari akun.';
} elseif (isset($_GET['pesan']) && $_GET['pesan'] === 'login') {
    $info = 'Silakan login dulu untuk membuka halaman itu.';
} elseif (isset($_GET['pesan']) && $_GET['pesan'] === 'registered') {
    $info = 'Akun berhasil dibuat. Silakan login.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['demo'])) {
        // ---------- Masuk sebagai Demo ----------
        // Server yang menentukan akunnya (bukan input pengguna), dan akun demo
        // adalah user biasa: hanya bisa mengelola ulasannya sendiri.
        if (!DEMO_MODE) {
            $error = 'Fitur akun demo sedang dinonaktifkan.';
        } else {
            try {
                $demo_username = DEMO_USERNAME;
                $demo_email    = DEMO_EMAIL;

                $stmt = $koneksi->prepare('SELECT id, username, email FROM users WHERE username = ? LIMIT 1');
                $stmt->bind_param('s', $demo_username);
                $stmt->execute();
                $demo = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if (!$demo) {
                    // Belum ada: buat akun demo dengan password acak yang tidak diketahui siapa pun,
                    // jadi akun ini tidak bisa dimasuki lewat form login biasa.
                    $hash = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);

                    $stmt = $koneksi->prepare('INSERT INTO users (username, email, password) VALUES (?, ?, ?)');
                    $stmt->bind_param('sss', $demo_username, $demo_email, $hash);
                    $stmt->execute();
                    $demo = [
                        'id'       => $stmt->insert_id,
                        'username' => $demo_username,
                        'email'    => $demo_email,
                    ];
                    $stmt->close();
                }

                if (strcasecmp($demo['email'], $demo_email) !== 0) {
                    // Username "demo" ternyata milik user sungguhan: jangan masuk ke akun itu
                    error_log('Login demo ditolak: username ' . $demo_username . ' bukan akun demo.');
                    $error = 'Akun demo tidak tersedia saat ini.';
                } else {
                    session_regenerate_id(true); // cegah session fixation
                    $_SESSION['user_id']  = $demo['id'];
                    $_SESSION['username'] = $demo['username'];
                    $_SESSION['is_demo']  = true;
                    $_SESSION['role']     = 'user'; // akun demo tidak pernah jadi admin

                    header('Location: ' . BASE_URL . 'dashboard.php');
                    exit;
                }
            } catch (mysqli_sql_exception $e) {
                error_log('Login demo gagal: ' . $e->getMessage());
                $error = 'Akun demo tidak bisa dibuka. Coba lagi beberapa saat.';
            }
        }
    } else {
        // ---------- Login biasa ----------
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $error = 'Username dan password harus diisi.';
        } else {
            try {
                // Prepared statement supaya aman dari SQL injection
                $stmt = $koneksi->prepare('SELECT id, username, password, role FROM users WHERE username = ? LIMIT 1');
                $stmt->bind_param('s', $username);
                $stmt->execute();
                $user = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if ($user && password_verify($password, $user['password'])) {
                    session_regenerate_id(true); // cegah session fixation
                    $_SESSION['user_id']  = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role']     = $user['role']; // 'admin' atau 'user'

                    header('Location: ' . BASE_URL . 'dashboard.php');
                    exit;
                }

                $error = 'Username atau password salah.';
            } catch (mysqli_sql_exception $e) {
                error_log('Login gagal: ' . $e->getMessage());
                $error = 'Login gagal. Coba lagi beberapa saat.';
            }
        }
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

        <button type="submit" class="btn btn-block">Login</button> <br><br>

        <!-- Info akun dummy -->
        <div class="dummy-card">
            <h2 align=center >Akun dummy</h2>
            <p>Password semua akun: <code>password</code></p>
            <ul>
                <li>admin</li>
                <li>budi</li>
                <li>sari</li>
                <li>andi</li>
            </ul>
        </div>

        <?php if (DEMO_MODE): ?>
            <p class="auth-divider"><span>atau</span></p>

            <!-- formnovalidate: supaya kolom username/password yang "required" tidak menghalangi -->
            <button type="submit" name="demo" value="1" class="btn btn-ghost btn-block" formnovalidate>
                Masuk sebagai Demo
            </button>
            <p class="demo-hint">Coba semua fitur tanpa perlu mendaftar.</p>
        <?php endif; ?>

        <p class="auth-note">Belum punya akun? <a href="register.php">Daftar di sini</a></p>
    </form>
    
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>