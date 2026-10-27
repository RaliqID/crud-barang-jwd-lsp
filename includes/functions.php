<?php
// includes/functions.php - Fungsi Pembantu, Validasi, dan Kalkulasi Total

/**
 * Escape string untuk pencegahan XSS
 */
function e(?string $str): string {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

/**
 * Format angka ke format mata uang Rupiah
 */
function format_rupiah($angka): string {
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}

/**
 * Bersihkan input string
 */
function sanitize(string $data): string {
    return trim(strip_tags($data));
}

/**
 * Daftar kategori barang yang valid
 */
function get_daftar_kategori(): array {
    return [
        'Elektronik',
        'Alat Tulis Kantor',
        'Pakaian',
        'Makanan & Minuman',
        'Perabotan',
        'Lainnya',
    ];
}

/**
 * Validasi form data barang
 */
function validate_barang(array $data, PDO $pdo, int $current_id = 0): array {
    $errors = [];

    // Validasi Kode Barang
    $kode = trim($data['kode_barang'] ?? '');
    if ($kode === '') {
        $errors['kode_barang'] = 'Kode barang wajib diisi.';
    } elseif (strlen($kode) > 20) {
        $errors['kode_barang'] = 'Kode barang maksimal 20 karakter.';
    } elseif (!preg_match('/^[A-Za-z0-9\-_]+$/', $kode)) {
        $errors['kode_barang'] = 'Kode barang hanya boleh berisi huruf, angka, tanda strip, atau garis bawah.';
    } elseif (cek_kode_barang_exists($pdo, $kode, $current_id)) {
        $errors['kode_barang'] = 'Kode barang sudah digunakan oleh barang lain.';
    }

    // Validasi Nama Barang
    $nama = trim($data['nama_barang'] ?? '');
    if ($nama === '') {
        $errors['nama_barang'] = 'Nama barang wajib diisi.';
    } elseif (strlen($nama) < 3) {
        $errors['nama_barang'] = 'Nama barang minimal 3 karakter.';
    } elseif (strlen($nama) > 100) {
        $errors['nama_barang'] = 'Nama barang maksimal 100 karakter.';
    }

    // Validasi Kategori
    $kategori = trim($data['kategori'] ?? '');
    if ($kategori === '') {
        $errors['kategori'] = 'Kategori barang wajib dipilih.';
    } elseif (!in_array($kategori, get_daftar_kategori(), true)) {
        $errors['kategori'] = 'Kategori yang dipilih tidak valid.';
    }

    // Validasi Harga
    $harga = $data['harga'] ?? '';
    if ($harga === '' || $harga === null) {
        $errors['harga'] = 'Harga barang wajib diisi.';
    } elseif (!is_numeric($harga) || (float)$harga < 0) {
        $errors['harga'] = 'Harga barang harus berupa angka positif.';
    }

    // Validasi Stok
    $stok = $data['stok'] ?? '';
    if ($stok === '' || $stok === null) {
        $errors['stok'] = 'Stok barang wajib diisi.';
    } elseif (!ctype_digit((string)$stok) || (int)$stok < 0) {
        $errors['stok'] = 'Stok barang harus berupa bilangan bulat tidak negatif.';
    }

    return $errors;
}

/**
 * Hitung jumlah jenis barang terdaftar
 */
function hitung_total_barang(PDO $pdo): int {
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM barang");
    return (int)$stmt->fetchColumn();
}

/**
 * Hitung total seluruh unit stok barang
 */
function hitung_total_stok(PDO $pdo): int {
    $stmt = $pdo->query("SELECT COALESCE(SUM(stok), 0) AS total_stok FROM barang");
    return (int)$stmt->fetchColumn();
}

/**
 * Hitung total nilai inventaris (harga x stok)
 */
function hitung_total_nilai(PDO $pdo): float {
    $stmt = $pdo->query("SELECT COALESCE(SUM(harga * stok), 0) AS total_nilai FROM barang");
    return (float)$stmt->fetchColumn();
}
