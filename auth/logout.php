<?php
require_once __DIR__ . '/../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simpan pesan flash sebelum menghapus session
$flash_msg = 'Anda telah berhasil keluar dari sistem.';

// Kosongkan array session
$_SESSION = [];

// Hapus cookie session jika ada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hancurkan session
session_destroy();

// Mulai session baru hanya untuk pesan flash
session_start();
set_flash('info', $flash_msg);

header('Location: ' . base_url('index.php'));
exit;
