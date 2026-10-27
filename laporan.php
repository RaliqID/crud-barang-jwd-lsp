<?php
// laporan.php - Halaman Rekapitulasi Laporan Inventaris Barang

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/barang.php';

require_login();

// Ambil seluruh data barang
$barang_list = get_all_barang($pdo);

// Ambil statistik total
$total_jenis = hitung_total_barang($pdo);
$total_stok  = hitung_total_stok($pdo);
$total_nilai = hitung_total_nilai($pdo);

$page_title = 'Laporan Inventaris Barang';
require_once __DIR__ . '/templates/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1 fw-bold text-dark">Laporan Inventaris Barang</h4>
        <p class="text-muted small mb-0">Rekapitulasi stok dan total nilai aset barang per <?= date('d/m/Y') ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="unduh_database.php" class="btn btn-outline-secondary btn-sm px-3">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
            <span>Unduh Database SQL</span>
        </a>
        <a href="cetak_pdf.php" target="_blank" class="btn btn-primary btn-sm px-3">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            <span>Cetak Laporan PDF</span>
        </a>
    </div>
</div>

<!-- Ringkasan Nilai Laporan -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card stat-card stat-primary h-100">
            <div class="d-flex align-items-start justify-content-between mb-2">
                <div class="stat-label">Total Jenis Barang</div>
                <div class="stat-icon-wrapper">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                        <line x1="12" y1="22.08" x2="12" y2="12"></line>
                    </svg>
                </div>
            </div>
            <div class="stat-value tabular-nums"><?= number_format($total_jenis, 0, ',', '.') ?></div>
            <div class="stat-desc mt-1">Jenis barang terdaftar</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card stat-emerald h-100">
            <div class="d-flex align-items-start justify-content-between mb-2">
                <div class="stat-label">Total Stok Fisik</div>
                <div class="stat-icon-wrapper">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                        <polyline points="2 17 12 22 22 17"></polyline>
                        <polyline points="2 12 12 17 22 12"></polyline>
                    </svg>
                </div>
            </div>
            <div class="stat-value tabular-nums"><?= number_format($total_stok, 0, ',', '.') ?> <span class="fs-6 fw-normal text-muted">unit</span></div>
            <div class="stat-desc mt-1">Total unit barang di gudang</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card stat-violet h-100">
            <div class="d-flex align-items-start justify-content-between mb-2">
                <div class="stat-label">Total Nilai Aset</div>
                <div class="stat-icon-wrapper">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="1" x2="12" y2="23"></line>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                    </svg>
                </div>
            </div>
            <div class="stat-value tabular-nums"><?= format_rupiah($total_nilai) ?></div>
            <div class="stat-desc mt-1">Akumulasi nilai seluruh aset</div>
        </div>
    </div>
</div>

<!-- Tabel Laporan Rinci -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Rincian Data Inventaris Barang</span>
        <span class="badge bg-light text-muted border font-monospace"><?= count($barang_list) ?> Barang</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3 text-center" style="width: 5%;">No</th>
                        <th style="width: 15%;">Kode Barang</th>
                        <th style="width: 25%;">Nama Barang</th>
                        <th style="width: 15%;">Kategori</th>
                        <th class="text-end" style="width: 15%;">Harga Satuan</th>
                        <th class="text-center" style="width: 10%;">Stok</th>
                        <th class="text-end pe-3" style="width: 15%;">Subtotal Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($barang_list)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                Tidak ada data inventaris untuk dilaporkan.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($barang_list as $index => $item): ?>
                            <?php $subtotal = (float)$item['harga'] * (int)$item['stok']; ?>
                            <tr>
                                <td class="ps-3 text-center text-muted small tabular-nums"><?= $index + 1 ?></td>
                                <td class="font-monospace small fw-medium"><?= e($item['kode_barang']) ?></td>
                                <td class="fw-medium text-dark"><?= e($item['nama_barang']) ?></td>
                                <td><span class="<?= badge_kategori_class($item['kategori']) ?>"><?= e($item['kategori']) ?></span></td>
                                <td class="text-end tabular-nums"><?= format_rupiah($item['harga']) ?></td>
                                <td class="text-center tabular-nums"><?= number_format($item['stok'], 0, ',', '.') ?></td>
                                <td class="text-end pe-3 tabular-nums fw-semibold text-dark"><?= format_rupiah($subtotal) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="table-light fw-bold">
                        <td colspan="5" class="text-end pe-3">TOTAL KESELURUHAN:</td>
                        <td class="text-center tabular-nums"><?= number_format($total_stok, 0, ',', '.') ?></td>
                        <td class="text-end pe-3 tabular-nums text-primary"><?= format_rupiah($total_nilai) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
