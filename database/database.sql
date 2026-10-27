-- Database: db_inventaris_barang
-- Uji Kompetensi Keahlian / Sertifikasi BNSP LSP Junior Web Developer (JWD)
-- Berkas skema siap import phpMyAdmin / MySQL CLI

CREATE DATABASE IF NOT EXISTS db_inventaris_barang
DEFAULT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE db_inventaris_barang;

-- Tabel Users untuk Autentikasi Pengguna
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_lengkap VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Barang untuk Pengelolaan Data Inventaris
CREATE TABLE IF NOT EXISTS barang (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_barang VARCHAR(20) NOT NULL UNIQUE,
    nama_barang VARCHAR(100) NOT NULL,
    kategori VARCHAR(50) NOT NULL,
    harga DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    stok INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
