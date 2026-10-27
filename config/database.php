<?php
// config/database.php - Koneksi Database MySQL menggunakan PDO

$db_host = 'localhost';
$db_name = 'db_inventaris_barang';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO(
        "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    die("Terjadi gangguan koneksi pada sistem database. Silakan hubungi administrator atau coba beberapa saat lagi.");
}
