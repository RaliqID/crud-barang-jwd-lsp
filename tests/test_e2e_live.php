<?php
/**
 * test_e2e_live.php - E2E Live Integration Test Suite
 * Sistem Inventaris Data Barang (LSP JWD)
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

$baseUrl = 'http://localhost/crud_barang';
$cookieFile = sys_get_temp_dir() . '/cookie_qa_test_' . uniqid() . '.txt';

$totalAssertions = 0;
$passedAssertions = 0;
$failedAssertions = [];

function assert_test(bool $condition, string $message, string $details = ''): void {
    global $totalAssertions, $passedAssertions, $failedAssertions;
    $totalAssertions++;
    if ($condition) {
        $passedAssertions++;
        echo "  [PASS] {$message}\n";
    } else {
        $failedAssertions[] = "{$message}" . ($details ? " - {$details}" : "");
        echo "  [FAIL] {$message}\n";
        if ($details) {
            echo "         Detail: {$details}\n";
        }
    }
}

function http_request(string $method, string $url, array $data = [], bool $followLocation = false, ?string $customCookieFile = null): array {
    global $cookieFile;
    $cFile = $customCookieFile ?? $cookieFile;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cFile);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $followLocation);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);

    if (strtoupper($method) === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }

    $response = curl_exec($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    $error = curl_error($ch);
    curl_close($ch);

    $headerText = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);

    // Extract headers into associative array
    $headers = [];
    foreach (explode("\r\n", $headerText) as $i => $line) {
        if ($i === 0) {
            $headers['http_code'] = $line;
        } else {
            $parts = explode(': ', $line, 2);
            if (count($parts) === 2) {
                $headers[strtolower($parts[0])] = $parts[1];
            }
        }
    }

    return [
        'code'         => $httpCode,
        'effective_url'=> $effectiveUrl,
        'headers'      => $headers,
        'header_raw'   => $headerText,
        'body'         => $body,
        'error'        => $error,
    ];
}

function extract_csrf(string $html): string {
    if (preg_match('/name="csrf_token"\s+value="([a-f0-9]+)"/i', $html, $matches)) {
        return $matches[1];
    }
    return '';
}

// Cleanup helper
function cleanup_test_data(PDO $pdo): void {
    $pdo->exec("DELETE FROM users WHERE username LIKE 'qa_%'");
    $pdo->exec("DELETE FROM barang WHERE kode_barang LIKE 'BRG-TEST%'");
}

// Register shutdown cleanup
register_shutdown_function(function() use ($pdo, $cookieFile) {
    cleanup_test_data($pdo);
    if (file_exists($cookieFile)) {
        @unlink($cookieFile);
    }
});

echo "=================================================================\n";
echo " LIVE E2E TEST SUITE: SISTEM INVENTARIS DATA BARANG\n";
echo " Base URL: {$baseUrl}\n";
echo " Date: " . date('Y-m-d H:i:s') . "\n";
echo "=================================================================\n\n";

// Pre-test cleanup
cleanup_test_data($pdo);

// -----------------------------------------------------------------
// TEST 1: Proteksi Akses Tamu Tanpa Sesi
// -----------------------------------------------------------------
echo "--- [TEST 1] Proteksi Akses Tamu Tanpa Sesi (Redirect 302 ke login.php) ---\n";
$protectedPages = [
    'index.php',
    'barang.php',
    'tambah.php',
    'edit.php?id=1',
    'laporan.php',
    'cetak_pdf.php',
];

$emptyCookieFile = sys_get_temp_dir() . '/empty_cookie_' . uniqid() . '.txt';
foreach ($protectedPages as $page) {
    $res = http_request('GET', "{$baseUrl}/{$page}", [], false, $emptyCookieFile);
    $location = $res['headers']['location'] ?? '';
    assert_test(
        $res['code'] === 302 && str_contains($location, 'login.php'),
        "Akses tamu ke {$page} -> 302 redirect ke login.php (HTTP {$res['code']}, Loc: {$location})",
        "Raw Header: " . trim($res['header_raw'])
    );
}
if (file_exists($emptyCookieFile)) @unlink($emptyCookieFile);
echo "\n";

// -----------------------------------------------------------------
// TEST 2: Registrasi User Baru
// -----------------------------------------------------------------
echo "--- [TEST 2] Registrasi User Baru (Form + CSRF + password_hash) ---\n";
// Ambil form registrasi untuk CSRF token
$resFormReg = http_request('GET', "{$baseUrl}/register.php");
assert_test($resFormReg['code'] === 200, "GET register.php mengembalikan HTTP 200");
$regCsrf = extract_csrf($resFormReg['body']);
assert_test(!empty($regCsrf), "Token CSRF ditemukan pada form register: " . substr($regCsrf, 0, 8) . "...");

$testUser = 'qa_user_test';
$testPass = 'password123';
$testNama = 'Penguji QA Terverifikasi';

$resReg = http_request('POST', "{$baseUrl}/register.php", [
    'csrf_token'          => $regCsrf,
    'nama_lengkap'        => $testNama,
    'username'            => $testUser,
    'password'            => $testPass,
    'konfirmasi_password' => $testPass,
]);

$regLoc = $resReg['headers']['location'] ?? '';
assert_test(
    $resReg['code'] === 302 && str_contains($regLoc, 'login.php'),
    "Registrasi user baru -> 302 redirect ke login.php (HTTP {$resReg['code']}, Loc: {$regLoc})"
);

// Verifikasi record di MySQL
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = :u LIMIT 1");
$stmt->execute([':u' => $testUser]);
$dbUser = $stmt->fetch();
assert_test(!empty($dbUser), "User '{$testUser}' berhasil tersimpan di tabel users database");
assert_test(
    !empty($dbUser) && password_verify($testPass, $dbUser['password']),
    "Password di database ter-hash secara aman (password_hash BCRYPT/Argon)"
);
echo "\n";

// -----------------------------------------------------------------
// TEST 3: Penolakan Duplikat Username pada Registrasi
// -----------------------------------------------------------------
echo "--- [TEST 3] Penolakan Duplikat Username pada Registrasi ---\n";
$resFormReg2 = http_request('GET', "{$baseUrl}/register.php");
$regCsrf2 = extract_csrf($resFormReg2['body']);

$resDupReg = http_request('POST', "{$baseUrl}/register.php", [
    'csrf_token'          => $regCsrf2,
    'nama_lengkap'        => 'Penguji Duplikat',
    'username'            => $testUser, // sama dengan yang ada di DB
    'password'            => 'password456',
    'konfirmasi_password' => 'password456',
]);

assert_test($resDupReg['code'] === 200, "Duplikat username tetap render form (HTTP 200)");
assert_test(
    str_contains($resDupReg['body'], 'Username sudah terdaftar'),
    "Pesan validasi duplikat muncul: 'Username sudah terdaftar, gunakan username lain.'"
);
echo "\n";

// -----------------------------------------------------------------
// TEST 4: Login User (Valid & Invalid Credentials)
// -----------------------------------------------------------------
echo "--- [TEST 4] Autentikasi Login (Password Salah vs Password Benar) ---\n";
// 4a. Password salah
$resFormLogin1 = http_request('GET', "{$baseUrl}/login.php");
$loginCsrf1 = extract_csrf($resFormLogin1['body']);

$resBadLogin = http_request('POST', "{$baseUrl}/login.php", [
    'csrf_token' => $loginCsrf1,
    'username'   => $testUser,
    'password'   => 'passwordsalah_xyz',
]);

assert_test($resBadLogin['code'] === 200, "Password salah tidak redirect (HTTP 200)");
assert_test(
    str_contains($resBadLogin['body'], 'Username atau password yang Anda masukkan salah'),
    "Pesan error kredensial salah tertampil di halaman login"
);

// 4b. Login sukses
$resFormLogin2 = http_request('GET', "{$baseUrl}/login.php");
$loginCsrf2 = extract_csrf($resFormLogin2['body']);

$resGoodLogin = http_request('POST', "{$baseUrl}/login.php", [
    'csrf_token' => $loginCsrf2,
    'username'   => $testUser,
    'password'   => $testPass,
]);

$loginLoc = $resGoodLogin['headers']['location'] ?? '';
assert_test(
    $resGoodLogin['code'] === 302 && str_contains($loginLoc, 'index.php'),
    "Login sukses -> 302 redirect ke index.php (HTTP {$resGoodLogin['code']}, Loc: {$loginLoc})"
);

// Akses index.php dengan sesi aktif
$resIndex = http_request('GET', "{$baseUrl}/index.php");
assert_test($resIndex['code'] === 200, "Akses index.php setelah login -> HTTP 200 OK");
assert_test(
    str_contains($resIndex['body'], $testNama) || str_contains($resIndex['body'], $testUser),
    "Sesi aktif menampilkan identitas pengguna '{$testNama}'"
);
echo "\n";

// -----------------------------------------------------------------
// TEST 5: Dashboard Aggregasi Awal
// -----------------------------------------------------------------
echo "--- [TEST 5] Dashboard: Akurasi Statistik Agregasi ---\n";
$dbTotalBarang = (int)$pdo->query("SELECT COUNT(*) FROM barang")->fetchColumn();
$dbTotalStok   = (int)$pdo->query("SELECT COALESCE(SUM(stok), 0) FROM barang")->fetchColumn();
$dbTotalNilai  = (float)$pdo->query("SELECT COALESCE(SUM(harga * stok), 0) FROM barang")->fetchColumn();

assert_test(
    str_contains($resIndex['body'], 'Jumlah Jenis Barang') &&
    str_contains($resIndex['body'], 'Total Unit Stok') &&
    str_contains($resIndex['body'], 'Total Nilai Inventaris'),
    "Ketiga kartu metrik (Jenis, Stok, Nilai) tertampil lengkap di Dashboard"
);
assert_test(
    str_contains($resIndex['body'], number_format($dbTotalBarang, 0, ',', '.')),
    "Nilai statistik Jenis Barang sesuai DB: {$dbTotalBarang}"
);
assert_test(
    str_contains($resIndex['body'], number_format($dbTotalStok, 0, ',', '.')),
    "Nilai statistik Total Stok sesuai DB: {$dbTotalStok}"
);
echo "\n";

// -----------------------------------------------------------------
// TEST 6: Tambah Barang (CRUD Create)
// -----------------------------------------------------------------
echo "--- [TEST 6] Tambah Barang (Validasi Input Kosong, Simpan Valid PRG, Penolakan Duplikat) ---\n";
// 6a. Input kosong
$resFormTambah1 = http_request('GET', "{$baseUrl}/tambah.php");
$csrfTambah1 = extract_csrf($resFormTambah1['body']);

$resTambahEmpty = http_request('POST', "{$baseUrl}/tambah.php", [
    'csrf_token'  => $csrfTambah1,
    'kode_barang' => '',
    'nama_barang' => '',
    'kategori'    => '',
    'harga'       => '',
    'stok'        => '',
]);

assert_test($resTambahEmpty['code'] === 200, "Input kosong ditolak (HTTP 200)");
assert_test(
    str_contains($resTambahEmpty['body'], 'Kode barang wajib diisi') &&
    str_contains($resTambahEmpty['body'], 'Nama barang wajib diisi') &&
    str_contains($resTambahEmpty['body'], 'Kategori barang wajib dipilih') &&
    str_contains($resTambahEmpty['body'], 'Harga barang wajib diisi') &&
    str_contains($resTambahEmpty['body'], 'Stok barang wajib diisi'),
    "Seluruh pesan error field wajib ditampilkan pada input kosong"
);

// 6b. Input valid item 1 (Laptop)
$resFormTambah2 = http_request('GET', "{$baseUrl}/tambah.php");
$csrfTambah2 = extract_csrf($resFormTambah2['body']);

$item1Kode = 'BRG-TEST-01';
$item1Nama = 'Laptop QA Verifier Pro';
$item1Kat  = 'Elektronik';
$item1Hrg  = '15000000.50';
$item1Stk  = '10';

$resTambahValid1 = http_request('POST', "{$baseUrl}/tambah.php", [
    'csrf_token'  => $csrfTambah2,
    'kode_barang' => $item1Kode,
    'nama_barang' => $item1Nama,
    'kategori'    => $item1Kat,
    'harga'       => $item1Hrg,
    'stok'        => $item1Stk,
]);

$tambahLoc1 = $resTambahValid1['headers']['location'] ?? '';
assert_test(
    $resTambahValid1['code'] === 302 && str_contains($tambahLoc1, 'barang.php'),
    "Simpan barang valid -> 302 PRG redirect ke barang.php (HTTP {$resTambahValid1['code']}, Loc: {$tambahLoc1})"
);

$stmt = $pdo->prepare("SELECT * FROM barang WHERE kode_barang = :k");
$stmt->execute([':k' => $item1Kode]);
$dbItem1 = $stmt->fetch();
assert_test(!empty($dbItem1), "Barang 1 '{$item1Kode}' berhasil tersimpan di DB");
assert_test((float)$dbItem1['harga'] === 15000000.50, "Presisi desimal harga tersimpan akurat: {$dbItem1['harga']}");

// 6c. Penolakan kode duplikat
$resFormTambah3 = http_request('GET', "{$baseUrl}/tambah.php");
$csrfTambah3 = extract_csrf($resFormTambah3['body']);

$resTambahDup = http_request('POST', "{$baseUrl}/tambah.php", [
    'csrf_token'  => $csrfTambah3,
    'kode_barang' => $item1Kode, // Duplikat
    'nama_barang' => 'Barang Duplikat Lain',
    'kategori'    => 'Elektronik',
    'harga'       => '500000',
    'stok'        => '5',
]);

assert_test($resTambahDup['code'] === 200, "Kode duplikat ditolak (HTTP 200)");
assert_test(
    str_contains($resTambahDup['body'], 'Kode barang sudah digunakan oleh barang lain'),
    "Pesan validasi penolakan kode duplikat muncul: 'Kode barang sudah digunakan oleh barang lain.'"
);

// 6d. Tambah item 2 (Buku) untuk pengujian pencarian & rekap
$resFormTambah4 = http_request('GET', "{$baseUrl}/tambah.php");
$csrfTambah4 = extract_csrf($resFormTambah4['body']);

$item2Kode = 'BRG-TEST-02';
$item2Nama = 'Buku Panduan LSP JWD';
$item2Kat  = 'Alat Tulis Kantor';
$item2Hrg  = '75000.00';
$item2Stk  = '40';

$resTambahValid2 = http_request('POST', "{$baseUrl}/tambah.php", [
    'csrf_token'  => $csrfTambah4,
    'kode_barang' => $item2Kode,
    'nama_barang' => $item2Nama,
    'kategori'    => $item2Kat,
    'harga'       => $item2Hrg,
    'stok'        => $item2Stk,
]);

$stmt = $pdo->prepare("SELECT * FROM barang WHERE kode_barang = :k");
$stmt->execute([':k' => $item2Kode]);
$dbItem2 = $stmt->fetch();
assert_test(!empty($dbItem2), "Barang 2 '{$item2Kode}' berhasil tersimpan di DB");
echo "\n";

// -----------------------------------------------------------------
// TEST 7: Data Barang & Fitur Pencarian (CRUD Read)
// -----------------------------------------------------------------
echo "--- [TEST 7] Data Barang & Pencarian (Keyword Cocok, Tombol Reset, Empty State) ---\n";
// 7a. Tampil semua
$resBarangAll = http_request('GET', "{$baseUrl}/barang.php");
assert_test($resBarangAll['code'] === 200, "GET barang.php -> HTTP 200 OK");
assert_test(
    str_contains($resBarangAll['body'], $item1Nama) && str_contains($resBarangAll['body'], $item2Nama),
    "Kedua barang uji tertampil pada daftar utama"
);

// 7b. Pencarian keyword cocok (Laptop)
$resCariLaptop = http_request('GET', "{$baseUrl}/barang.php?cari=Laptop");
assert_test($resCariLaptop['code'] === 200, "Pencarian '?cari=Laptop' -> HTTP 200 OK");
assert_test(
    str_contains($resCariLaptop['body'], $item1Nama) && !str_contains($resCariLaptop['body'], $item2Nama),
    "Hasil pencarian tepat: menampilkan '{$item1Nama}', mengecualikan '{$item2Nama}'"
);
assert_test(
    str_contains($resCariLaptop['body'], 'Menampilkan hasil pencarian untuk kata kunci') &&
    str_contains($resCariLaptop['body'], '>Reset</a>'),
    "Info kata kunci dan tombol Reset muncul saat parameter cari terisi"
);

// 7c. Empty state ketika keyword tidak ditemukan
$keywordNihil = 'XYZ_NONEXISTENT_999';
$resCariNihil = http_request('GET', "{$baseUrl}/barang.php?cari={$keywordNihil}");
assert_test($resCariNihil['code'] === 200, "Pencarian kata kunci nihil -> HTTP 200 OK");
assert_test(
    str_contains($resCariNihil['body'], 'tidak ditemukan') &&
    str_contains($resCariNihil['body'], $keywordNihil),
    "Empty state ramah pengguna tertampil dengan pesan kata kunci nihil"
);
echo "\n";

// -----------------------------------------------------------------
// TEST 8: Edit Barang (CRUD Update)
// -----------------------------------------------------------------
echo "--- [TEST 8] Edit Barang (Hidden ID, Penolakan Kode Item Lain, Presisi Desimal) ---\n";
$idItem1 = (int)$dbItem1['id'];
$idItem2 = (int)$dbItem2['id'];

// 8a. GET edit form
$resEditForm = http_request('GET', "{$baseUrl}/edit.php?id={$idItem1}");
assert_test($resEditForm['code'] === 200, "GET edit.php?id={$idItem1} -> HTTP 200 OK");
assert_test(
    str_contains($resEditForm['body'], 'type="hidden" name="id" value="' . $idItem1 . '"'),
    "Input hidden ID terpasang dengan benar di form edit"
);
$csrfEdit = extract_csrf($resEditForm['body']);

// 8b. Coba ganti kode menjadi kode milik item 2 (harus ditolak)
$resEditDup = http_request('POST', "{$baseUrl}/edit.php?id={$idItem1}", [
    'csrf_token'  => $csrfEdit,
    'id'          => $idItem1,
    'kode_barang' => $item2Kode, // Kode milik item 2
    'nama_barang' => $item1Nama,
    'kategori'    => $item1Kat,
    'harga'       => '16000000',
    'stok'        => '12',
]);

assert_test($resEditDup['code'] === 200, "Edit ke kode milik barang lain ditolak (HTTP 200)");
assert_test(
    str_contains($resEditDup['body'], 'Kode barang sudah digunakan oleh barang lain'),
    "Validasi penolakan kode milik item lain berhasil"
);

// 8c. Update valid dengan harga desimal baru
$resEditForm2 = http_request('GET', "{$baseUrl}/edit.php?id={$idItem1}");
$csrfEdit2 = extract_csrf($resEditForm2['body']);

$updateKode = 'BRG-TEST-01-REV';
$updateNama = 'Laptop QA Verifier Pro Max';
$updateKat  = 'Elektronik';
$updateHrg  = '18750450.75';
$updateStk  = '25';

$resEditValid = http_request('POST', "{$baseUrl}/edit.php?id={$idItem1}", [
    'csrf_token'  => $csrfEdit2,
    'id'          => $idItem1,
    'kode_barang' => $updateKode,
    'nama_barang' => $updateNama,
    'kategori'    => $updateKat,
    'harga'       => $updateHrg,
    'stok'        => $updateStk,
]);

$editLoc = $resEditValid['headers']['location'] ?? '';
assert_test(
    $resEditValid['code'] === 302 && str_contains($editLoc, 'barang.php'),
    "Update valid -> 302 PRG redirect ke barang.php (HTTP {$resEditValid['code']}, Loc: {$editLoc})"
);

$stmt = $pdo->prepare("SELECT * FROM barang WHERE id = :id");
$stmt->execute([':id' => $idItem1]);
$dbUpdated = $stmt->fetch();
assert_test($dbUpdated['kode_barang'] === $updateKode, "Kode barang berhasil diupdate ke '{$updateKode}'");
assert_test($dbUpdated['nama_barang'] === $updateNama, "Nama barang berhasil diupdate ke '{$updateNama}'");
assert_test((float)$dbUpdated['harga'] === 18750450.75, "Harga desimal presisi terupdate: {$dbUpdated['harga']}");
assert_test((int)$dbUpdated['stok'] === 25, "Stok terupdate: {$dbUpdated['stok']}");
echo "\n";

// -----------------------------------------------------------------
// TEST 9: Cetak Dokumen PDF
// -----------------------------------------------------------------
echo "--- [TEST 9] Cetak PDF (Binary Stream '%PDF', Ukuran > 1KB, Content-Type) ---\n";
$resPdf = http_request('GET', "{$baseUrl}/cetak_pdf.php");
$pdfBody = $resPdf['body'];
$pdfSize = strlen($pdfBody);
$isPdfHeader = substr($pdfBody, 0, 4) === '%PDF';

assert_test($resPdf['code'] === 200, "Akses cetak_pdf.php -> HTTP 200 OK");
assert_test($isPdfHeader, "Header stream binary valid diawali signature '%PDF'");
assert_test($pdfSize > 1024, "Ukuran dokumen PDF valid: " . number_format($pdfSize, 0, ',', '.') . " bytes (> 1KB)");
$contentType = $resPdf['headers']['content-type'] ?? '';
assert_test(str_contains(strtolower($contentType), 'application/pdf'), "Content-Type header adalah application/pdf ({$contentType})");
echo "\n";

// -----------------------------------------------------------------
// TEST 10: Hapus Data Barang (CRUD Delete)
// -----------------------------------------------------------------
echo "--- [TEST 10] Hapus Barang (Proteksi Akses GET, Proteksi CSRF, Eksekusi POST Valid) ---\n";
// 10a. Proteksi GET langsung ke hapus.php
$resGetHapus = http_request('GET', "{$baseUrl}/hapus.php?id={$idItem2}");
$getHapusLoc = $resGetHapus['headers']['location'] ?? '';
assert_test(
    $resGetHapus['code'] === 302 && str_contains($getHapusLoc, 'barang.php'),
    "Akses GET langsung ke hapus.php ditolak -> 302 redirect ke barang.php (HTTP {$resGetHapus['code']}, Loc: {$getHapusLoc})"
);

// 10b. POST tanpa CSRF token
$resPostHapusNoCsrf = http_request('POST', "{$baseUrl}/hapus.php", [
    'id' => $idItem2,
]);
assert_test(
    $resPostHapusNoCsrf['code'] === 302,
    "Hapus tanpa CSRF token ditolak -> 302 redirect"
);
$stmt = $pdo->prepare("SELECT COUNT(*) FROM barang WHERE id = :id");
$stmt->execute([':id' => $idItem2]);
assert_test((int)$stmt->fetchColumn() === 1, "Data barang 2 TIDAK terhapus jika tanpa CSRF token");

// 10c. POST valid dengan CSRF token (Hapus Item 2)
$resBarangForDel = http_request('GET', "{$baseUrl}/barang.php");
$csrfDel = extract_csrf($resBarangForDel['body']);

$resDelValid2 = http_request('POST', "{$baseUrl}/hapus.php", [
    'csrf_token' => $csrfDel,
    'id'         => $idItem2,
]);
assert_test($resDelValid2['code'] === 302, "POST valid hapus barang 2 -> 302 redirect");
$stmt = $pdo->prepare("SELECT COUNT(*) FROM barang WHERE id = :id");
$stmt->execute([':id' => $idItem2]);
assert_test((int)$stmt->fetchColumn() === 0, "Data barang 2 berhasil dihapus dari database");

// Hapus juga Item 1 agar DB kembali kosong
$resBarangForDel2 = http_request('GET', "{$baseUrl}/barang.php");
$csrfDel2 = extract_csrf($resBarangForDel2['body']);
$resDelValid1 = http_request('POST', "{$baseUrl}/hapus.php", [
    'csrf_token' => $csrfDel2,
    'id'         => $idItem1,
]);
$stmt = $pdo->prepare("SELECT COUNT(*) FROM barang WHERE id = :id");
$stmt->execute([':id' => $idItem1]);
assert_test((int)$stmt->fetchColumn() === 0, "Data barang 1 berhasil dihapus dari database");
echo "\n";

// -----------------------------------------------------------------
// TEST 11: Logout Pengguna & Penghancuran Sesi
// -----------------------------------------------------------------
echo "--- [TEST 11] Logout (Proteksi GET, POST CSRF, Session Destroy) ---\n";
// 11a. GET logout.php ditolak
$resGetLogout = http_request('GET', "{$baseUrl}/logout.php");
$getLogoutLoc = $resGetLogout['headers']['location'] ?? '';
assert_test(
    $resGetLogout['code'] === 302 && str_contains($getLogoutLoc, 'index.php'),
    "GET logout.php ditolak demi keamanan -> 302 redirect ke index.php (HTTP {$resGetLogout['code']}, Loc: {$getLogoutLoc})"
);

// 11b. POST logout dengan CSRF token valid
$resPageForLogout = http_request('GET', "{$baseUrl}/index.php");
$csrfLogout = extract_csrf($resPageForLogout['body']);

$resPostLogout = http_request('POST', "{$baseUrl}/logout.php", [
    'csrf_token' => $csrfLogout,
]);
$logoutLoc = $resPostLogout['headers']['location'] ?? '';
assert_test(
    $resPostLogout['code'] === 302 && str_contains($logoutLoc, 'login.php'),
    "POST logout valid -> 302 redirect ke login.php (HTTP {$resPostLogout['code']}, Loc: {$logoutLoc})"
);

// 11c. Cek sesi pasca logout (Akses index.php harus mental ke login.php)
$resAfterLogout = http_request('GET', "{$baseUrl}/index.php");
$afterLogoutLoc = $resAfterLogout['headers']['location'] ?? '';
assert_test(
    $resAfterLogout['code'] === 302 && str_contains($afterLogoutLoc, 'login.php'),
    "Sesi telah hancur total: akses index.php pasca logout mental 302 ke login.php"
);
echo "\n";

// -----------------------------------------------------------------
// TEST 12: Pembersihan Otomatis Data Uji (Zero Dummy Data)
// -----------------------------------------------------------------
echo "--- [TEST 12] Pembersihan Data Uji Otomatis (Zero Test Credentials) ---\n";
cleanup_test_data($pdo);

$remUsers = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE username LIKE 'qa_%'")->fetchColumn();
$remBarang = (int)$pdo->query("SELECT COUNT(*) FROM barang WHERE kode_barang LIKE 'BRG-TEST%'")->fetchColumn();

assert_test($remUsers === 0, "Zero test user credentials tersisa di tabel users (Sisa: {$remUsers})");
assert_test($remBarang === 0, "Zero test barang dummy tersisa di tabel barang (Sisa: {$remBarang})");
echo "\n";

// -----------------------------------------------------------------
// RINGKASAN AKHIR
// -----------------------------------------------------------------
echo "=================================================================\n";
echo " REKAP HASIL TEST SUITE:\n";
echo " Total Assertions : {$totalAssertions}\n";
echo " Passed           : {$passedAssertions}\n";
echo " Failed           : " . count($failedAssertions) . "\n";
echo "=================================================================\n";

if (count($failedAssertions) > 0) {
    echo "\nDAFTAR KEGAGALAN:\n";
    foreach ($failedAssertions as $fail) {
        echo " - {$fail}\n";
    }
    exit(1);
} else {
    echo "\nSTATUS: SELURUH RANGKAIAN UJI E2E LIVE BERHASIL DENGAN NILAI SEMPURNA (100% PASS)\n";
    exit(0);
}
