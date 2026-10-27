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
        <h4 class="mb-1">Laporan Inventaris Barang</h4>
        <p class="text-muted small mb-0">Rekapitulasi stok dan total nilai aset barang per <?= date('d/m/Y') ?></p>
    </div>
    <div>
        <a href="cetak_pdf.php" target="_blank" class="btn btn-primary btn-sm px-3">
            Cetak Laporan PDF
        </a>
    </div>
</div>

<!-- Ringkasan Nilai Laporan -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card stat-card p-3">
            <div class="stat-label">Total Jenis Barang</div>
            <div class="stat-value mt-1"><?= number_format($total_jenis, 0, ',', '.') ?></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card p-3">
            <div class="stat-label">Total Stok Fisik</div>
            <div class="stat-value mt-1"><?= number_format($total_stok, 0, ',', '.') ?> unit</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card p-3">
            <div class="stat-label">Total Nilai Aset</div>
            <div class="stat-value mt-1"><?= format_rupiah($total_nilai) ?></div>
        </div>
    </div>
</div>

<!-- Tabel Laporan Rinci -->
<div class="card">
    <div class="card-header bg-white">
        Rincian Data Inventaris Barang
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 5%;">No</th>
                        <th style="width: 15%;">Kode Barang</th>
                        <th style="width: 30%;">Nama Barang</th>
                        <th style="width: 15%;">Kategori</th>
                        <th class="text-end" style="width: 15%;">Harga Satuan</th>
                        <th class="text-center" style="width: 10%;">Stok</th>
                        <th class="text-end" style="width: 15%;">Subtotal Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($barang_list)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                Tidak ada data inventaris untuk dilaporkan.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($barang_list as $index => $item): ?>
                            <?php $subtotal = (float)$item['harga'] * (int)$item['stok']; ?>
                            <tr>
                                <td class="text-center text-muted small"><?= $index + 1 ?></td>
                                <td class="font-monospace small"><?= e($item['kode_barang']) ?></td>
                                <td><?= e($item['nama_barang']) ?></td>
                                <td><?= e($item['kategori']) ?></td>
                                <td class="text-end"><?= format_rupiah($item['harga']) ?></td>
                                <td class="text-center"><?= number_format($item['stok'], 0, ',', '.') ?></td>
                                <td class="text-end fw-semibold"><?= format_rupiah($subtotal) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="table-light fw-bold">
                        <td colspan="5" class="text-end pe-3">TOTAL KESELURUHAN:</td>
                        <td class="text-center"><?= number_format($total_stok, 0, ',', '.') ?></td>
                        <td class="text-end"><?= format_rupiah($total_nilai) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
