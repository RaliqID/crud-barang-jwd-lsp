<?php
// index.php - Dashboard Sistem Inventaris Barang

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/barang.php';

require_login();

// Ambil data agregasi melalui fungsi terpisah
$total_barang = hitung_total_barang($pdo);
$total_stok   = hitung_total_stok($pdo);
$total_nilai  = hitung_total_nilai($pdo);

// Ambil 5 barang terbaru untuk ringkasan cepat
$barang_terbaru = get_barang_terbaru($pdo, 5);

$page_title = 'Dashboard';
require_once __DIR__ . '/templates/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1">Dashboard</h4>
        <p class="text-muted small mb-0">Ringkasan status data inventaris barang</p>
    </div>
    <div class="d-flex gap-2">
        <a href="tambah.php" class="btn btn-primary btn-sm">+ Tambah Barang</a>
        <a href="laporan.php" class="btn btn-outline-secondary btn-sm">Lihat Laporan</a>
    </div>
</div>

<!-- Kartu Statistik Utama -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-4">
        <div class="card stat-card h-100 p-3">
            <div class="stat-label">Jumlah Jenis Barang</div>
            <div class="stat-value mt-2"><?= number_format($total_barang, 0, ',', '.') ?></div>
            <div class="text-muted small mt-1">Total varian barang aktif</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-4">
        <div class="card stat-card h-100 p-3">
            <div class="stat-label">Total Unit Stok</div>
            <div class="stat-value mt-2"><?= number_format($total_stok, 0, ',', '.') ?></div>
            <div class="text-muted small mt-1">Total fisik seluruh barang</div>
        </div>
    </div>
    <div class="col-sm-12 col-lg-4">
        <div class="card stat-card h-100 p-3">
            <div class="stat-label">Total Nilai Inventaris</div>
            <div class="stat-value mt-2"><?= format_rupiah($total_nilai) ?></div>
            <div class="text-muted small mt-1">Akumulasi (Harga x Stok)</div>
        </div>
    </div>
</div>

<!-- Ringkasan Barang Terbaru -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Barang Terakhir Ditambahkan</span>
        <a href="barang.php" class="small text-decoration-none">Lihat Semua Data &rarr;</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3" style="width: 15%;">Kode</th>
                        <th style="width: 35%;">Nama Barang</th>
                        <th style="width: 20%;">Kategori</th>
                        <th class="text-end" style="width: 15%;">Harga</th>
                        <th class="text-center" style="width: 15%;">Stok</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($barang_terbaru)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                Belum ada data barang. Silakan <a href="tambah.php">tambah barang baru</a>.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($barang_terbaru as $item): ?>
                            <tr>
                                <td class="ps-3 font-monospace small"><?= e($item['kode_barang']) ?></td>
                                <td><?= e($item['nama_barang']) ?></td>
                                <td><span class="badge bg-light text-dark border"><?= e($item['kategori']) ?></span></td>
                                <td class="text-end"><?= format_rupiah($item['harga']) ?></td>
                                <td class="text-center"><?= number_format($item['stok'], 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
