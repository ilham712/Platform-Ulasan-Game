<?php
$koneksi = mysqli_connect("localhost", "root", "", "ulasan_game");
if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>