<?php
// register.php - Halaman Pendaftaran Akun Pengguna

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

require_guest();

$errors = [];
$nama_lengkap = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi token CSRF
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Sesi kedaluwarsa atau token tidak valid.');
        header('Location: register.php');
        exit;
    }

    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $username     = trim($_POST['username'] ?? '');
    $password     = $_POST['password'] ?? '';
    $konfirmasi   = $_POST['konfirmasi_password'] ?? '';

    // Validasi input
    if ($nama_lengkap === '') {
        $errors['nama_lengkap'] = 'Nama lengkap wajib diisi.';
    } elseif (strlen($nama_lengkap) < 3) {
        $errors['nama_lengkap'] = 'Nama lengkap minimal 3 karakter.';
    } elseif (strlen($nama_lengkap) > 100) {
        $errors['nama_lengkap'] = 'Nama lengkap maksimal 100 karakter.';
    }

    if ($username === '') {
        $errors['username'] = 'Username wajib diisi.';
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $errors['username'] = 'Username harus 3 hingga 50 karakter.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors['username'] = 'Username hanya boleh berisi huruf, angka, dan underscore.';
    } else {
        // Cek username unik dengan prepared statement
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        if ((int)$stmt->fetchColumn() > 0) {
            $errors['username'] = 'Username sudah terdaftar, gunakan username lain.';
        }
    }

    if ($password === '') {
        $errors['password'] = 'Password wajib diisi.';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password minimal 6 karakter.';
    }

    if ($konfirmasi === '') {
        $errors['konfirmasi_password'] = 'Konfirmasi password wajib diisi.';
    } elseif ($password !== $konfirmasi) {
        $errors['konfirmasi_password'] = 'Konfirmasi password tidak cocok.';
    }

    // Jika valid, simpan user baru
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO users (nama_lengkap, username, password)
            VALUES (:nama, :user, :pass)
        ");
        $stmt->execute([
            ':nama' => $nama_lengkap,
            ':user' => $username,
            ':pass' => $hashed_password,
        ]);

        set_flash('success', 'Registrasi berhasil. Silakan masuk menggunakan akun baru Anda.');
        header('Location: login.php');
        exit;
    }
}

$page_title = 'Registrasi Pengguna';
require_once __DIR__ . '/templates/header.php';
?>

<div class="row justify-content-center py-sm-3">
    <div class="col-md-6 col-lg-5">
        <div class="card my-3 shadow-sm border">
            <div class="card-header text-center py-3 bg-white">
                <div class="mb-2">
                    <span class="d-inline-flex p-2 rounded-circle bg-primary-subtle text-primary">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="8.5" cy="7" r="4"></circle>
                            <line x1="20" y1="8" x2="20" y2="14"></line>
                            <line x1="23" y1="11" x2="17" y2="11"></line>
                        </svg>
                    </span>
                </div>
                <h5 class="mb-1 fw-bold text-dark">Daftar Akun Baru</h5>
                <p class="text-muted small mb-0">Buat akun untuk mengelola inventaris sistem</p>
            </div>
            <div class="card-body p-4">
                <form action="register.php" method="POST" autocomplete="off">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="nama_lengkap" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control <?= isset($errors['nama_lengkap']) ? 'is-invalid' : '' ?>"
                               id="nama_lengkap"
                               name="nama_lengkap"
                               placeholder="Nama lengkap Anda"
                               maxlength="100"
                               value="<?= e($nama_lengkap) ?>"
                               required>
                        <?php if (isset($errors['nama_lengkap'])): ?>
                            <div class="invalid-feedback"><?= e($errors['nama_lengkap']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>"
                               id="username"
                               name="username"
                               placeholder="Gunakan huruf, angka, atau underscore"
                               value="<?= e($username) ?>"
                               required>
                        <?php if (isset($errors['username'])): ?>
                            <div class="invalid-feedback"><?= e($errors['username']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password"
                               class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                               id="password"
                               name="password"
                               placeholder="Minimal 6 karakter"
                               required>
                        <?php if (isset($errors['password'])): ?>
                            <div class="invalid-feedback"><?= e($errors['password']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="konfirmasi_password" class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                        <input type="password"
                               class="form-control <?= isset($errors['konfirmasi_password']) ? 'is-invalid' : '' ?>"
                               id="konfirmasi_password"
                               name="konfirmasi_password"
                               placeholder="Ulangi password di atas"
                               required>
                        <?php if (isset($errors['konfirmasi_password'])): ?>
                            <div class="invalid-feedback"><?= e($errors['konfirmasi_password']) ?></div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 mt-2">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><polyline points="17 11 19 13 23 9"></polyline></svg>
                        <span>Daftar Sekarang</span>
                    </button>
                </form>

                <div class="text-center mt-4 pt-2 border-top">
                    <span class="text-muted small">Sudah memiliki akun?</span>
                    <a href="login.php" class="small text-decoration-none fw-semibold">Masuk di sini</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
