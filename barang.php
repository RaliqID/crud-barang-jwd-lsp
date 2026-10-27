<?php
// barang.php - Tampilan Data Barang & Fitur Pencarian

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/barang.php';

require_login();

$search = trim($_GET['cari'] ?? '');
$barang_list = get_all_barang($pdo, $search);

$page_title = 'Data Barang';
require_once __DIR__ . '/templates/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1 fw-bold text-dark">Data Barang</h4>
        <p class="text-muted small mb-0">Kelola master data barang inventaris</p>
    </div>
    <div class="d-flex gap-2">
        <a href="tambah.php" class="btn btn-primary btn-sm">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>Tambah Barang</span>
        </a>
        <a href="cetak_pdf.php" target="_blank" class="btn btn-outline-secondary btn-sm">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            <span>Cetak PDF</span>
        </a>
    </div>
</div>

<!-- Bar Pencarian -->
<div class="card mb-4">
    <div class="card-body p-3">
        <form action="barang.php" method="GET" class="row g-2 align-items-center">
            <div class="col-md-9 col-sm-8">
                <div class="position-relative">
                    <input type="text"
                           class="form-control"
                           name="cari"
                           placeholder="Cari berdasarkan kode, nama barang, atau kategori..."
                           value="<?= e($search) ?>">
                </div>
            </div>
            <div class="col-md-3 col-sm-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <span>Cari</span>
                </button>
                <?php if ($search !== ''): ?>
                    <a href="barang.php" class="btn btn-outline-secondary">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php if ($search !== ''): ?>
    <div class="mb-3 text-muted small d-flex align-items-center gap-1">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#b45309" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        <span>Menampilkan hasil pencarian untuk kata kunci: "<strong><?= e($search) ?></strong>" (Ditemukan <?= count($barang_list) ?> data)</span>
    </div>
<?php endif; ?>

<!-- Tabel Data Barang -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3 text-center" style="width: 5%;">No</th>
                        <th style="width: 15%;">Kode Barang</th>
                        <th style="width: 25%;">Nama Barang</th>
                        <th style="width: 15%;">Kategori</th>
                        <th class="text-end" style="width: 15%;">Harga</th>
                        <th class="text-center" style="width: 10%;">Stok</th>
                        <th class="text-center" style="width: 15%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($barang_list)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <?php if ($search !== ''): ?>
                                    <div class="mb-2">
                                        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#a1a1aa" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                    </div>
                                    <p class="mb-2">Data barang dengan kata kunci "<strong><?= e($search) ?></strong>" tidak ditemukan.</p>
                                    <a href="barang.php" class="btn btn-outline-secondary btn-sm">Reset Pencarian</a>
                                <?php else: ?>
                                    <div class="mb-2">
                                        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#a1a1aa" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                                    </div>
                                    <p class="mb-2">Belum ada data barang. Silakan <a href="tambah.php">tambah barang baru</a>.</p>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($barang_list as $index => $item): ?>
                            <tr>
                                <td class="ps-3 text-center text-muted small tabular-nums"><?= $index + 1 ?></td>
                                <td class="font-monospace small fw-medium"><?= e($item['kode_barang']) ?></td>
                                <td class="fw-medium text-dark"><?= e($item['nama_barang']) ?></td>
                                <td><span class="<?= badge_kategori_class($item['kategori']) ?>"><?= e($item['kategori']) ?></span></td>
                                <td class="text-end tabular-nums fw-medium"><?= format_rupiah($item['harga']) ?></td>
                                <td class="text-center tabular-nums"><?= number_format($item['stok'], 0, ',', '.') ?></td>
                                <td class="text-center">
                                    <div class="d-inline-flex gap-1">
                                        <a href="edit.php?id=<?= (int)$item['id'] ?>" class="btn btn-sm btn-outline-primary px-2" title="Ubah Data">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                            <span>Edit</span>
                                        </a>
                                        <form action="hapus.php" method="POST" class="d-inline form-hapus" data-nama="<?= e($item['nama_barang']) ?>" data-kode="<?= e($item['kode_barang']) ?>">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger px-2" title="Hapus Data">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                                <span>Hapus</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
