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
        <h4 class="mb-1 fw-bold text-dark">Dashboard</h4>
        <p class="text-muted small mb-0">Ringkasan status data inventaris barang</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="tambah.php" class="btn btn-primary btn-sm">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>Tambah Barang</span>
        </a>
        <a href="laporan.php" class="btn btn-outline-secondary btn-sm">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
            <span>Lihat Laporan</span>
        </a>
        <a href="unduh_database.php" class="btn btn-outline-success btn-sm">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
            <span>Backup DB SQL</span>
        </a>
    </div>
</div>

<!-- Kartu Statistik Utama -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-4">
        <div class="card stat-card stat-primary h-100">
            <div class="d-flex align-items-start justify-content-between mb-2">
                <div class="stat-label">Jumlah Jenis Barang</div>
                <div class="stat-icon-wrapper">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                        <line x1="12" y1="22.08" x2="12" y2="12"></line>
                    </svg>
                </div>
            </div>
            <div class="stat-value tabular-nums"><?= number_format($total_barang, 0, ',', '.') ?></div>
            <div class="stat-desc mt-1">Total varian barang aktif</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-4">
        <div class="card stat-card stat-emerald h-100">
            <div class="d-flex align-items-start justify-content-between mb-2">
                <div class="stat-label">Total Unit Stok</div>
                <div class="stat-icon-wrapper">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                        <polyline points="2 17 12 22 22 17"></polyline>
                        <polyline points="2 12 12 17 22 12"></polyline>
                    </svg>
                </div>
            </div>
            <div class="stat-value tabular-nums"><?= number_format($total_stok, 0, ',', '.') ?></div>
            <div class="stat-desc mt-1">Total fisik seluruh barang</div>
        </div>
    </div>
    <div class="col-sm-12 col-lg-4">
        <div class="card stat-card stat-violet h-100">
            <div class="d-flex align-items-start justify-content-between mb-2">
                <div class="stat-label">Total Nilai Inventaris</div>
                <div class="stat-icon-wrapper">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="1" x2="12" y2="23"></line>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                    </svg>
                </div>
            </div>
            <div class="stat-value tabular-nums"><?= format_rupiah($total_nilai) ?></div>
            <div class="stat-desc mt-1">Akumulasi (Harga x Stok)</div>
        </div>
    </div>
</div>

<!-- Ringkasan Barang Terbaru -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Barang Terakhir Ditambahkan</span>
        <a href="barang.php" class="small text-decoration-none fw-medium d-inline-flex align-items-center gap-1">
            <span>Lihat Semua Data</span>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
        </a>
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
                            <td colspan="5" class="text-center py-5 text-muted">
                                <div class="mb-2">
                                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                    </svg>
                                </div>
                                <p class="mb-1">Belum ada data barang.</p>
                                <a href="tambah.php" class="btn btn-sm btn-outline-primary mt-1">+ Tambah Barang Baru</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($barang_terbaru as $item): ?>
                            <tr>
                                <td class="ps-3 font-monospace small fw-medium"><?= e($item['kode_barang']) ?></td>
                                <td class="fw-medium text-dark"><?= e($item['nama_barang']) ?></td>
                                <td><span class="<?= badge_kategori_class($item['kategori']) ?>"><?= e($item['kategori']) ?></span></td>
                                <td class="text-end tabular-nums fw-medium"><?= format_rupiah($item['harga']) ?></td>
                                <td class="text-center tabular-nums"><?= number_format($item['stok'], 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
