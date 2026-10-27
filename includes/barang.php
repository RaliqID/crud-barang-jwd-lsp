<?php
// includes/barang.php - Operasi CRUD Data Barang menggunakan Prepared Statement

/**
 * Ambil semua data barang atau berdasarkan pencarian
 */
function get_all_barang(PDO $pdo, string $search = ''): array {
    $search = trim($search);
    if ($search !== '') {
        $stmt = $pdo->prepare("
            SELECT * FROM barang
            WHERE kode_barang LIKE :s1
               OR nama_barang LIKE :s2
               OR kategori LIKE :s3
            ORDER BY id DESC
        ");
        $term = "%{$search}%";
        $stmt->execute([
            ':s1' => $term,
            ':s2' => $term,
            ':s3' => $term,
        ]);
        return $stmt->fetchAll();
    }

    $stmt = $pdo->query("SELECT * FROM barang ORDER BY id DESC");
    return $stmt->fetchAll();
}

/**
 * Ambil daftar barang terbaru dengan limit tertentu
 */
function get_barang_terbaru(PDO $pdo, int $limit = 5): array {
    $stmt = $pdo->prepare("SELECT * FROM barang ORDER BY id DESC LIMIT :limit");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Ambil satu barang berdasarkan ID
 */
function get_barang_by_id(PDO $pdo, int $id): ?array {
    $stmt = $pdo->prepare("SELECT * FROM barang WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $result = $stmt->fetch();
    return $result ?: null;
}

/**
 * Cek apakah kode barang sudah ada di database
 */
function cek_kode_barang_exists(PDO $pdo, string $kode, int $exclude_id = 0): bool {
    if ($exclude_id > 0) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM barang WHERE kode_barang = :kode AND id != :id");
        $stmt->execute([
            ':kode' => $kode,
            ':id'   => $exclude_id,
        ]);
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM barang WHERE kode_barang = :kode");
        $stmt->execute([':kode' => $kode]);
    }

    return (int)$stmt->fetchColumn() > 0;
}

/**
 * Tambah data barang baru
 */
function tambah_barang(PDO $pdo, array $data): bool {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO barang (kode_barang, nama_barang, kategori, harga, stok)
            VALUES (:kode, :nama, :kategori, :harga, :stok)
        ");
        return $stmt->execute([
            ':kode'     => $data['kode_barang'],
            ':nama'     => $data['nama_barang'],
            ':kategori' => $data['kategori'],
            ':harga'    => $data['harga'],
            ':stok'     => $data['stok'],
        ]);
    } catch (PDOException $e) {
        error_log("Gagal tambah_barang: " . $e->getMessage());
        return false;
    }
}

/**
 * Perbarui data barang berdasarkan ID
 */
function update_barang(PDO $pdo, int $id, array $data): bool {
    try {
        $stmt = $pdo->prepare("
            UPDATE barang
            SET kode_barang = :kode,
                nama_barang = :nama,
                kategori    = :kategori,
                harga       = :harga,
                stok        = :stok
            WHERE id = :id
        ");
        return $stmt->execute([
            ':kode'     => $data['kode_barang'],
            ':nama'     => $data['nama_barang'],
            ':kategori' => $data['kategori'],
            ':harga'    => $data['harga'],
            ':stok'     => $data['stok'],
            ':id'       => $id,
        ]);
    } catch (PDOException $e) {
        error_log("Gagal update_barang (ID {$id}): " . $e->getMessage());
        return false;
    }
}

/**
 * Hapus data barang berdasarkan ID
 */
function hapus_barang(PDO $pdo, int $id): bool {
    try {
        $stmt = $pdo->prepare("DELETE FROM barang WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    } catch (PDOException $e) {
        error_log("Gagal hapus_barang (ID {$id}): " . $e->getMessage());
        return false;
    }
}
