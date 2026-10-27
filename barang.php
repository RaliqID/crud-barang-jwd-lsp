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

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-1">Data Barang</h4>
        <p class="text-muted small mb-0">Kelola master data barang inventaris</p>
    </div>
    <div class="d-flex gap-2">
        <a href="tambah.php" class="btn btn-primary btn-sm">+ Tambah Barang</a>
        <a href="cetak_pdf.php" target="_blank" class="btn btn-outline-secondary btn-sm">Cetak PDF</a>
    </div>
</div>

<!-- Bar Pencarian -->
<div class="card mb-3">
    <div class="card-body p-3">
        <form action="barang.php" method="GET" class="row g-2 align-items-center">
            <div class="col-md-9 col-sm-8">
                <input type="text"
                       class="form-control"
                       name="cari"
                       placeholder="Cari berdasarkan kode, nama barang, atau kategori..."
                       value="<?= e($search) ?>">
            </div>
            <div class="col-md-3 col-sm-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">Cari</button>
                <?php if ($search !== ''): ?>
                    <a href="barang.php" class="btn btn-outline-secondary">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php if ($search !== ''): ?>
    <div class="mb-2 text-muted small">
        Menampilkan hasil pencarian untuk kata kunci: "<strong><?= e($search) ?></strong>" (Ditemukan <?= count($barang_list) ?> data)
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
                            <td colspan="7" class="text-center py-4 text-muted">
                                <?php if ($search !== ''): ?>
                                    <p class="mb-2">Data barang dengan kata kunci "<strong><?= e($search) ?></strong>" tidak ditemukan.</p>
                                    <a href="barang.php" class="btn btn-outline-secondary btn-sm">Reset Pencarian</a>
                                <?php else: ?>
                                    Belum ada data barang. Silakan <a href="tambah.php">tambah barang baru</a>.
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($barang_list as $index => $item): ?>
                            <tr>
                                <td class="ps-3 text-center text-muted small"><?= $index + 1 ?></td>
                                <td class="font-monospace small font-weight-bold"><?= e($item['kode_barang']) ?></td>
                                <td><?= e($item['nama_barang']) ?></td>
                                <td><span class="badge bg-light text-dark border"><?= e($item['kategori']) ?></span></td>
                                <td class="text-end"><?= format_rupiah($item['harga']) ?></td>
                                <td class="text-center"><?= number_format($item['stok'], 0, ',', '.') ?></td>
                                <td class="text-center">
                                    <div class="d-inline-flex gap-1">
                                        <a href="edit.php?id=<?= (int)$item['id'] ?>" class="btn btn-sm btn-outline-primary">
                                            Edit
                                        </a>
                                        <form action="hapus.php" method="POST" class="d-inline form-hapus" data-nama="<?= e($item['nama_barang']) ?>" data-kode="<?= e($item['kode_barang']) ?>">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
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
