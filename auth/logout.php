<?php
// auth/logout.php

session_start();

// Kosongkan semua data session
$_SESSION = [];

// Hapus cookie session di browser
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}

session_destroy();

// logout.php satu folder dengan login.php, jadi cukup path relatif
header('Location: login.php?logout=1');
exit;