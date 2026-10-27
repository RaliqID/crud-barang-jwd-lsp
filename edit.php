<?php
// edit.php - Form Ubah Data Barang

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/barang.php';

require_login();

// Validasi parameter ID dari POST atau GET
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id <= 0) {
    set_flash('danger', 'ID barang tidak valid.');
    header('Location: barang.php');
    exit;
}

// Ambil data barang yang akan diedit
$barang = get_barang_by_id($pdo, $id);
if (!$barang) {
    set_flash('danger', 'Data barang tidak ditemukan di sistem.');
    header('Location: barang.php');
    exit;
}

$errors = [];
$data = [
    'kode_barang' => $barang['kode_barang'],
    'nama_barang' => $barang['nama_barang'],
    'kategori'    => $barang['kategori'],
    'harga'       => (string)$barang['harga'],
    'stok'        => (string)$barang['stok'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi token CSRF
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Sesi kedaluwarsa atau token tidak valid.');
        header('Location: edit.php?id=' . $id);
        exit;
    }

    $data = [
        'kode_barang' => strtoupper(trim($_POST['kode_barang'] ?? '')),
        'nama_barang' => trim($_POST['nama_barang'] ?? ''),
        'kategori'    => trim($_POST['kategori'] ?? ''),
        'harga'       => trim($_POST['harga'] ?? ''),
        'stok'        => trim($_POST['stok'] ?? ''),
    ];

    // Validasi dengan mengecualikan ID barang ini pada pengecekan kode unik
    $errors = validate_barang($data, $pdo, $id);

    if (empty($errors)) {
        if (update_barang($pdo, $id, $data)) {
            set_flash('success', 'Data barang ' . $data['nama_barang'] . ' berhasil diperbarui.');
            header('Location: barang.php');
            exit;
        } else {
            $errors['general'] = 'Gagal memperbarui data. Silakan coba lagi.';
        }
    }
}

$page_title = 'Edit Barang';
$daftar_kategori = get_daftar_kategori();
require_once __DIR__ . '/templates/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-7">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-1">Edit Data Barang</h4>
                <p class="text-muted small mb-0">Perbarui rincian informasi barang</p>
            </div>
            <a href="barang.php" class="btn btn-outline-secondary btn-sm">&larr; Kembali</a>
        </div>

        <div class="card">
            <div class="card-body p-4">
                <?php if (isset($errors['general'])): ?>
                    <div class="alert alert-danger py-2 small" role="alert">
                        <?= e($errors['general']) ?>
                    </div>
                <?php endif; ?>

                <form action="edit.php?id=<?= $id ?>" method="POST" autocomplete="off">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $id ?>">

                    <div class="mb-3">
                        <label for="kode_barang" class="form-label">Kode Barang <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control <?= isset($errors['kode_barang']) ? 'is-invalid' : '' ?>"
                               id="kode_barang"
                               name="kode_barang"
                               value="<?= e($data['kode_barang']) ?>"
                               required>
                        <?php if (isset($errors['kode_barang'])): ?>
                            <div class="invalid-feedback"><?= e($errors['kode_barang']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="nama_barang" class="form-label">Nama Barang <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control <?= isset($errors['nama_barang']) ? 'is-invalid' : '' ?>"
                               id="nama_barang"
                               name="nama_barang"
                               value="<?= e($data['nama_barang']) ?>"
                               required>
                        <?php if (isset($errors['nama_barang'])): ?>
                            <div class="invalid-feedback"><?= e($errors['nama_barang']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="kategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select class="form-select <?= isset($errors['kategori']) ? 'is-invalid' : '' ?>"
                                id="kategori"
                                name="kategori"
                                required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach ($daftar_kategori as $kat): ?>
                                <option value="<?= e($kat) ?>" <?= $data['kategori'] === $kat ? 'selected' : '' ?>>
                                    <?= e($kat) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['kategori'])): ?>
                            <div class="invalid-feedback"><?= e($errors['kategori']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="harga" class="form-label">Harga Satuan (Rp) <span class="text-danger">*</span></label>
                            <input type="number"
                                   step="any"
                                   min="0"
                                   class="form-control <?= isset($errors['harga']) ? 'is-invalid' : '' ?>"
                                   id="harga"
                                   name="harga"
                                   value="<?= e($data['harga']) ?>"
                                   required>
                            <?php if (isset($errors['harga'])): ?>
                                <div class="invalid-feedback"><?= e($errors['harga']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="stok" class="form-label">Jumlah Stok <span class="text-danger">*</span></label>
                            <input type="number"
                                   step="1"
                                   min="0"
                                   class="form-control <?= isset($errors['stok']) ? 'is-invalid' : '' ?>"
                                   id="stok"
                                   name="stok"
                                   value="<?= e($data['stok']) ?>"
                                   required>
                            <?php if (isset($errors['stok'])): ?>
                                <div class="invalid-feedback"><?= e($errors['stok']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <a href="barang.php" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary px-4">Perbarui Barang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
