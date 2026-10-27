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

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card my-3">
            <div class="card-header text-center py-3">
                <h5 class="mb-0">Daftar Akun Baru</h5>
            </div>
            <div class="card-body p-4">
                <form action="register.php" method="POST" autocomplete="off">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                        <input type="text"
                               class="form-control <?= isset($errors['nama_lengkap']) ? 'is-invalid' : '' ?>"
                               id="nama_lengkap"
                               name="nama_lengkap"
                               maxlength="100"
                               value="<?= e($nama_lengkap) ?>"
                               required>
                        <?php if (isset($errors['nama_lengkap'])): ?>
                            <div class="invalid-feedback"><?= e($errors['nama_lengkap']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text"
                               class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>"
                               id="username"
                               name="username"
                               value="<?= e($username) ?>"
                               required>
                        <?php if (isset($errors['username'])): ?>
                            <div class="invalid-feedback"><?= e($errors['username']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password"
                               class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                               id="password"
                               name="password"
                               required>
                        <?php if (isset($errors['password'])): ?>
                            <div class="invalid-feedback"><?= e($errors['password']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="konfirmasi_password" class="form-label">Konfirmasi Password</label>
                        <input type="password"
                               class="form-control <?= isset($errors['konfirmasi_password']) ? 'is-invalid' : '' ?>"
                               id="konfirmasi_password"
                               name="konfirmasi_password"
                               required>
                        <?php if (isset($errors['konfirmasi_password'])): ?>
                            <div class="invalid-feedback"><?= e($errors['konfirmasi_password']) ?></div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2">Daftar Sekarang</button>
                </form>

                <div class="text-center mt-3">
                    <span class="text-muted small">Sudah memiliki akun?</span>
                    <a href="login.php" class="small text-decoration-none">Masuk di sini</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
