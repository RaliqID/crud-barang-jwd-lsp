<?php
// templates/header.php - Header & Navigasi Utama Aplikasi
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    ]);
    session_start();
}
$current_page = basename($_SERVER['PHP_SELF']);
$user = current_user();

if (!function_exists('badge_kategori_class')) {
    function badge_kategori_class(?string $kat): string {
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', (string)$kat));
        $slug = trim($slug, '-');
        return 'badge-cat badge-cat-' . ($slug ?: 'lainnya');
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? e($page_title) . ' - Inventaris Barang' : 'Inventaris Barang' ?></title>
    <!-- Google Fonts & Preconnect -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- CSS Kustom -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

<header>
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                    <line x1="12" y1="22.08" x2="12" y2="12"></line>
                </svg>
                <span>INVENTARIS</span>
            </a>
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarMain">
                <?php if (is_logged_in()): ?>
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3 gap-1">
                        <li class="nav-item">
                            <a class="nav-link <?= $current_page === 'index.php' ? 'active' : '' ?>" href="index.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= in_array($current_page, ['barang.php', 'tambah.php', 'edit.php'], true) ? 'active' : '' ?>" href="barang.php">Data Barang</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $current_page === 'laporan.php' ? 'active' : '' ?>" href="laporan.php">Laporan</a>
                        </li>
                    </ul>
                    <ul class="navbar-nav ms-auto align-items-lg-center">
                        <li class="nav-item me-lg-3 my-2 my-lg-0">
                            <div class="user-identity-badge">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                <span class="text-secondary small">Masuk sebagai: <strong class="fw-semibold text-dark"><?= e($user['nama_lengkap'] ?? $user['username']) ?></strong></span>
                            </div>
                        </li>
                        <li class="nav-item my-1 my-lg-0">
                            <form action="logout.php" method="POST" class="d-inline m-0 p-0">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger">Logout</button>
                            </form>
                        </li>
                    </ul>
                <?php else: ?>
                    <ul class="navbar-nav ms-auto gap-1">
                        <li class="nav-item">
                            <a class="nav-link <?= $current_page === 'login.php' ? 'active' : '' ?>" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $current_page === 'register.php' ? 'active' : '' ?>" href="register.php">Registrasi</a>
                        </li>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </nav>
</header>

<main class="main-content py-4">
    <div class="container">
        <?php
        $flash = get_flash();
        if ($flash):
        ?>
            <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
                <?= e($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
