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

$koneksi = new mysqli($host, $user, $pass, $db);

if ($koneksi->connect_error) {
    die('Koneksi database gagal: ' . $koneksi->connect_error);
}

$koneksi->set_charset('utf8mb4');