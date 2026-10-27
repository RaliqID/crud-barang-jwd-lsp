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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? e($page_title) . ' - Inventaris Barang' : 'Inventaris Barang' ?></title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- CSS Kustom -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

<header>
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="index.php">INVENTARIS</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarMain">
                <?php if (is_logged_in()): ?>
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
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
                            <span class="text-dark small">Masuk sebagai: <strong class="fw-medium text-dark"><?= e($user['nama_lengkap'] ?? $user['username']) ?></strong></span>
                        </li>
                        <li class="nav-item my-1 my-lg-0">
                            <form action="logout.php" method="POST" class="d-inline m-0 p-0">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger">Logout</button>
                            </form>
                        </li>
                    </ul>
                <?php else: ?>
                    <ul class="navbar-nav ms-auto">
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
