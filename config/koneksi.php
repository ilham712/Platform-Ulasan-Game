<?php
// config/koneksi.php

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'db_ulasan_game';

// Alamat dasar project di browser (dipakai untuk link & redirect).
// Ubah kalau nama folder project di htdocs berbeda.
if (!defined('BASE_URL')) {
    define('BASE_URL', '/Platform-Ulasan-Game/');
}

// Fitur "Masuk sebagai Demo" di halaman login.
// Ubah DEMO_MODE jadi false untuk mematikannya (tombol hilang dan login demo ditolak).
if (!defined('DEMO_MODE'))     { define('DEMO_MODE', true); }
if (!defined('DEMO_USERNAME')) { define('DEMO_USERNAME', 'demo'); }
if (!defined('DEMO_EMAIL'))    { define('DEMO_EMAIL', 'demo@gamereview.test'); }

// Error MySQL dilempar sebagai exception (perilaku sama di semua versi PHP),
// sehingga bisa ditangkap dengan try/catch di halaman yang memakainya.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $koneksi = new mysqli($host, $user, $pass, $db);
    $koneksi->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    die('Koneksi database gagal: ' . $e->getMessage());
}