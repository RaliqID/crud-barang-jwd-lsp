<?php
// logout.php - Menghapus Sesi dan Mengeluarkan Pengguna

require_once __DIR__ . '/includes/auth.php';

// Fallback jika pengguna belum login atau sesi sudah tidak ada
if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

// Validasi request POST dan CSRF token untuk mencegah logout via CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Sesi kedaluwarsa atau token tidak valid.');
        header('Location: index.php');
        exit;
    }
} else {
    // Permintaan non-POST saat login ditolak demi keamanan CSRF
    set_flash('warning', 'Permintaan logout harus menggunakan metode yang aman.');
    header('Location: index.php');
    exit;
}

// Kosongkan array sesi
$_SESSION = [];

// Hapus cookie sesi jika ada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Hancurkan sesi
session_destroy();

// Mulai sesi baru khusus untuk flash message logout
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    ]);
    session_start();
}
set_flash('info', 'Anda telah berhasil keluar dari sistem.');
header('Location: login.php');
exit;
