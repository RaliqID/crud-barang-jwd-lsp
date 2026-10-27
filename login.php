<?php
// login.php - Halaman Masuk Pengguna

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

require_guest();

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi token CSRF
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Sesi kedaluwarsa atau token tidak valid.');
        header('Location: login.php');
        exit;
    }

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        // Ambil data user dengan prepared statement
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        // Verifikasi password hash
        if ($user && password_verify($password, $user['password'])) {
            // Mencegah Session Fixation
            session_regenerate_id(true);

            $_SESSION['user'] = [
                'id'           => $user['id'],
                'username'     => $user['username'],
                'nama_lengkap' => $user['nama_lengkap'],
            ];

            set_flash('success', 'Selamat datang kembali, ' . $user['nama_lengkap'] . '!');
            header('Location: index.php');
            exit;
        } else {
            $error = 'Username atau password yang Anda masukkan salah.';
        }
    }
}

$page_title = 'Login Pengguna';
require_once __DIR__ . '/templates/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-5 col-lg-4">
        <div class="card my-4">
            <div class="card-header text-center py-3">
                <h5 class="mb-0">Masuk ke Sistem</h5>
            </div>
            <div class="card-body p-4">
                <?php if ($error !== ''): ?>
                    <div class="alert alert-danger py-2 small" role="alert">
                        <?= e($error) ?>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST" autocomplete="off">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text"
                               class="form-control"
                               id="username"
                               name="username"
                               value="<?= e($username) ?>"
                               required
                               autofocus>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password"
                               class="form-control"
                               id="password"
                               name="password"
                               required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2">Masuk</button>
                </form>

                <div class="text-center mt-3">
                    <span class="text-muted small">Belum punya akun?</span>
                    <a href="register.php" class="small text-decoration-none">Daftar sekarang</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
