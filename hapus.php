<?php
// hapus.php - Proses Penghapusan Data Barang

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/barang.php';

require_login();

// Hanya izinkan metode POST untuk mencegah aksi hapus tak disengaja/CSRF
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_flash('danger', 'Metode permintaan tidak diizinkan.');
    header('Location: barang.php');
    exit;
}

// Validasi token CSRF
if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    set_flash('danger', 'Permintaan kedaluwarsa atau token tidak valid.');
    header('Location: barang.php');
    exit;
}

// Validasi ID barang
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id || $id <= 0) {
    set_flash('danger', 'ID barang tidak valid.');
    header('Location: barang.php');
    exit;
}

// Cek keberadaan data sebelum dihapus
$barang = get_barang_by_id($pdo, $id);
if (!$barang) {
    set_flash('danger', 'Data barang tidak ditemukan atau sudah dihapus sebelumnya.');
    header('Location: barang.php');
    exit;
}

// Eksekusi penghapusan
if (hapus_barang($pdo, $id)) {
    set_flash('success', 'Data barang ' . $barang['nama_barang'] . ' (' . $barang['kode_barang'] . ') berhasil dihapus.');
} else {
    set_flash('danger', 'Gagal menghapus data barang.');
}

header('Location: barang.php');
exit;
