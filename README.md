# Sistem Informasi Inventaris Barang (CRUD)
## Skema Pengembang Web Pratama / Junior Web Developer — LSP Teknologi Digital
**Kode Skema / Acuan Uji:** SKK-32-10/2024 / FR.IA.02 TPD  
**Target Pengguna Dokumen:** Asesor Penguji LSP & Asesi Uji Kompetensi

---

## 1. Deskripsi Proyek

Sistem Informasi Inventaris Barang merupakan aplikasi berbasis web yang dirancang untuk mengelola master data aset barang secara terstruktur. Aplikasi ini dibangun memenuhi standar unjuk kerja Skema Sertifikasi Pengembang Web Pratama (Junior Web Developer) Badan Nasional Sertifikasi Profesi (BNSP) melalui Lembaga Sertifikasi Profesi (LSP) Teknologi Digital.

Fitur utama mencakup autentikasi pengguna, dasbor metrik analitik aset, pengelolaan data barang (Create, Read, Update, Delete) dengan pencarian terpadu, serta modul pelaporan rekapitulasi inventaris yang terintegrasi dengan generator dokumen PDF.

---

## 2. Spesifikasi Teknis

| Komponen | Spesifikasi / Teknologi | Keterangan Implementasi |
|---|---|---|
| **Bahasa Pemrograman** | PHP 8.x (Native) | Pemrograman modular dan prosedural terstruktur |
| **Database Server** | MySQL / MariaDB | Engine InnoDB, Charset `utf8mb4` |
| **Database Driver** | PHP Data Objects (PDO) | Full Prepared Statements, Emulated Prepares nonaktif |
| **Frontend Framework** | Bootstrap 5.3.3 (CDN) | Antarmuka responsif tanpa dependensi jQuery |
| **Custom Styling** | CSS3 Murni (`assets/css/style.css`) | Palet warna netral, desain ergonomis, standar kontras WCAG |
| **PDF Generation Library** | FPDF v1.86 | Integrasi lokal di direktori `fpdf/` |
| **Autentikasi & Keamanan** | Bcrypt & Native PHP Session | `password_hash()`, `password_verify()`, `session_regenerate_id()` |
| **Proteksi Transaksi** | Anti-CSRF Token Protection | Token kriptografis 32-byte acak via `hash_equals()` di semua POST |
| **Sanitasi & Sanitasi XSS** | HTML Entity Encoding | Fungsi pembantu `e()` menggunakan `htmlspecialchars(..., ENT_QUOTES)` |

---

## 3. Struktur Direktori Proyek

```text
crud_barang/
├── assets/
│   └── css/
│       └── style.css              # Custom styling responsif
├── config/
│   └── database.php               # Konfigurasi koneksi database PDO
├── database/
│   └── db_inventaris_barang.sql   # Skrip DDL database & tabel (siap import 1x)
├── fpdf/
│   ├── font/                      # Font bawaan FPDF
│   ├── fpdf.php                   # Core library FPDF v1.86
│   └── license.txt
├── includes/
│   ├── auth.php                   # Pengelolaan sesi, login, & proteksi CSRF
│   ├── barang.php                 # Operasi CRUD barang via PDO prepared statement
│   └── functions.php              # Fungsi validasi form, format Rupiah, & helper
├── templates/
│   ├── header.php                 # Header & navigasi responsif
│   └── footer.php                 # Footer & proteksi double-submit
├── barang.php                     # Halaman tabel data barang & pencarian
├── cetak_pdf.php                  # Modul generate laporan PDF
├── edit.php                       # Form ubah data barang
├── hapus.php                      # Proses hapus barang (metode POST)
├── index.php                      # Dashboard ringkasan statistik barang
├── laporan.php                    # Halaman pratinjau laporan rekapitulasi
├── login.php                      # Halaman masuk pengguna
├── logout.php                     # Proses keluar sistem (metode POST)
├── register.php                   # Halaman registrasi akun baru
├── tambah.php                     # Form tambah data barang
└── README.md                      # Dokumentasi teknis proyek
```

---

## 4. Panduan Instalasi & Konfigurasi (XAMPP)

### Prasyarat
- XAMPP (Apache & MySQL aktif)
- Web browser (Chrome, Firefox, atau Edge)

### Langkah 1: Penempatan Folder Proyek
1. Pastikan Apache dan MySQL di XAMPP Control Panel berstatus **Running**.
2. Tempatkan folder proyek di direktori htdocs:
   ```text
   C:\xampp\htdocs\crud_barang
   ```

### Langkah 2: Import Database (Sekali Klik)
Skrip database sudah dilengkapi instruksi `CREATE DATABASE IF NOT EXISTS` dan `USE db_inventaris_barang`. Anda cukup mengimpor satu berkas SQL:

#### Opsi A: Lewat phpMyAdmin
1. Buka browser ke `http://localhost/phpmyadmin`.
2. Klik tab **Import** pada menu utama atas.
3. Klik tombol **Choose File**, pilih file:
   ```text
   C:\xampp\htdocs\crud_barang\database\db_inventaris_barang.sql
   ```
4. Klik tombol **Import** di bagian bawah. Database dan tabel otomatis terpasang.

#### Opsi B: Lewat Terminal / Command Prompt
Jalankan satu perintah berikut:
```bash
mysql -u root < "C:\xampp\htdocs\crud_barang\database\db_inventaris_barang.sql"
```

### Langkah 3: Konfigurasi Koneksi Database
Konfigurasi koneksi di file `config/database.php` menggunakan setelan default XAMPP:
```php
$db_host = 'localhost';
$db_name = 'db_inventaris_barang';
$db_user = 'root';
$db_pass = ''; // Default XAMPP kosong
```

### Langkah 4: Mengakses Aplikasi
Buka peramban dan navigasikan ke URL lokal berikut:
```text
http://localhost/crud_barang
```
Sistem akan otomatis mengarahkan ke halaman `login.php` apabila belum ada sesi pengguna yang aktif.

---

## 5. Skenario Demonstrasi Uji Kompetensi (FR.IA.02 TPD)

Pengujian demonstrasi dibagi menjadi 2 tugas berurutan sesuai alur unjuk kerja asesi di hadapan asesor.

### Skenario Tugas 1: Autentikasi Pengguna & Penyiapan Akun
Tugas ini membuktikan kemampuan pengembang dalam mengelola akses pengguna, hash kata sandi, dan proteksi sesi.

#### 1. Registrasi Akun Asesi Baru
1. Pada halaman login (`http://localhost/crud_barang/login.php`), klik tautan **Daftar Akun Baru** atau akses `register.php`.
2. Masukkan data uji pada formulir:
   - **Nama Lengkap:** `Asesi JWD LSP`
   - **Username:** `asesi_jwd`
   - **Password:** `Admin#2026`
   - **Konfirmasi Password:** `Admin#2026`
3. Klik tombol **Daftar Sekarang**.
4. **Verifikasi Sistem:**
   - Input divalidasi (panjang nama minimal 3 karakter, username alfanumerik unik, password minimal 6 karakter, kesesuaian konfirmasi).
   - Password dienkripsi menggunakan fungsi `password_hash($password, PASSWORD_DEFAULT)` sebelum disimpan ke tabel `users`.
   - Browser diarahkan kembali ke `login.php` dengan pesan notifikasi sukses (*flash message*).

#### 2. Login ke Dalam Sistem
1. Pada formulir `login.php`, masukkan username `asesi_jwd` dan password `Admin#2026`.
2. Klik tombol **Masuk**.
3. **Verifikasi Sistem:**
   - Sistem memverifikasi kecocokan password menggunakan `password_verify()`.
   - Menjalankan `session_regenerate_id(true)` untuk memitigasi serangan *Session Fixation*.
   - Token CSRF dibuat ke dalam sesi.
   - Browser diarahkan ke dasbor utama (`index.php`) disertai sapaan nama pengguna di navigasi kanan atas.

---

### Skenario Tugas 2: Pengelolaan Data Barang & Modul Pelaporan
Tugas ini mendemonstrasikan implementasi CRUD penuh, validasi aturan bisnis, pencarian dinamis, dan pelaporan cetak PDF.

#### 1. Dasbor & Pembacaan Metrik Statistik
1. Akses halaman `index.php`.
2. **Verifikasi Sistem:**
   - Kartu 1: **Jumlah Jenis Barang** menampilkan total entitas barang (`COUNT(*)`).
   - Kartu 2: **Total Unit Stok** menampilkan akumulasi kuantitas fisik (`SUM(stok)`).
   - Kartu 3: **Total Nilai Inventaris** menampilkan kalkulasi akumulasi aset (`SUM(harga * stok)`) dalam format Rupiah.
   - Tabel ringkasan menampilkan maksimal 5 data barang terbaru yang ditambahkan.

#### 2. Tambah Data Barang Baru (Create)
1. Klik tombol **+ Tambah Barang** pada navbar atau dasbor (`tambah.php`).
2. Masukkan atribut data barang:
   - **Kode Barang:** `BRG-001` (otomatis dikonversi ke huruf besar / uppercase)
   - **Nama Barang:** `Monitor LED 24 Inch`
   - **Kategori:** Pilih salah satu dari pilihan yang tersedia (contoh: `Elektronik`)
   - **Harga (Rp):** `1850000`
   - **Jumlah Stok:** `15`
3. Klik tombol **Simpan Data Barang**.
4. **Verifikasi Sistem:**
   - Validasi server memeriksa keunikan kode barang via prepared statement.
   - Nilai harga harus berupa angka positif, stok berupa bilangan bulat non-negatif.
   - Data berhasil disimpan ke tabel `barang`.
   - Browser berpindah ke `barang.php` disertai alert hijau notifikasi keberhasilan.

#### 3. Pencarian Data Barang (Read & Filter)
1. Pada halaman `barang.php`, masukkan kata kunci pada kolom pencarian (contoh: `Monitor` atau `BRG-001` atau `Elektronik`).
2. Klik tombol **Cari**.
3. **Verifikasi Sistem:**
   - Query PDO mengeksekusi parameter `:s1`, `:s2`, `:s3` dengan klausa `LIKE` pada kode, nama, dan kategori.
   - Tabel menampilkan daftar barang yang sesuai serta indikator jumlah data yang ditemukan.
   - Klik tombol **Reset** untuk menampilkan kembali seluruh data inventaris.

#### 4. Pembaruan Data Barang (Update)
1. Pada baris data yang ingin diubah, klik tombol **Edit** (`edit.php?id={ID}`).
2. Formulir secara otomatis terisi (*pre-filled*) dengan data terkini dari database.
3. Ubah nilai stok dari `15` menjadi `20` dan harga menjadi `1900000`.
4. Klik tombol **Simpan Perubahan**.
5. **Verifikasi Sistem:**
   - Validasi memeriksa keunikan kode terhadap data lain (mengecualikan ID data yang sedang diedit).
   - Data diperbarui melalui query `UPDATE` dengan parameter terikat.
   - Sistem mengarahkan kembali ke `barang.php` dengan pesan konfirmasi sukses.

#### 5. Penghapusan Data Barang (Delete)
1. Pada baris data barang target, klik tombol **Hapus**.
2. **Verifikasi Sistem Dialog:**
   - Muncul dialog konfirmasi interaktif peramban: *"Apakah Anda yakin ingin menghapus [Nama Barang] ([Kode Barang])?"*.
   - Jika pengguna membatalkan (klik *Cancel*), operasi dibatalkan.
   - Jika pengguna mengonfirmasi (klik *OK*), form dikirimkan via metode **POST** ke `hapus.php` lengkap dengan token CSRF valid (URL browser tidak mengekspos parameter delete pada query string GET).
   - Data dihapus dari tabel dan tabel master langsung diperbarui.

#### 6. Pratinjau Laporan & Cetak Dokumen PDF (FPDF Integration)
1. Klik menu **Laporan** pada navbar atas (`laporan.php`).
2. Halaman menampilkan rekapitulasi data barang, subtotal per item (`harga * stok`), serta baris total akumulasi keseluruhan nilai inventaris.
3. Klik tombol **Cetak Laporan PDF** / **Cetak PDF**.
4. Berkas `cetak_pdf.php` dieksekusi dan membuka dokumen PDF langsung di tab peramban (*stream output inline*).
5. **Verifikasi Komponen PDF:**
   - Dokumen berukuran standar A4 Portrait.
   - Terdapat Header resmi: nama sistem, subjudul rekapitulasi, garis pembatas, dan tanggal cetak aktual.
   - Tabel data tersusun rapi dengan perataan teks sesuai konteks (angka di kanan, teks di kiri, kode di tengah).
   - Terdapat baris **TOTAL KESELURUHAN** di akhir tabel.
   - Bagian bawah dokumen menyajikan kolom tanggal pengesahan dan tanda tangan petugas penanggung jawab yang mencetak laporan (mengambil data nama asesi yang sedang login).
   - Footer halaman mencantumkan penomoran dinamis: *"Halaman X dari Y - Dokumen Resmi Inventaris"*.

#### 7. Logout Aman (Selesai Pengujian)
1. Klik tombol **Logout** berwarna merah pada pojok kanan atas navbar.
2. **Verifikasi Sistem:**
   - Form logout dieksekusi via metode POST dengan validasi token CSRF (mencegah eksploitasi serangan *Forced Logout CSRF*).
   - Seluruh variabel sesi `$_SESSION` dikosongkan.
   - Cookie identitas sesi klien dihancurkan dengan masa kedaluwarsa lampau.
   - Fungsi `session_destroy()` dipanggil.
   - Pengguna diarahkan kembali ke `login.php` dengan status sesi netral. Mencoba menekan tombol *Back* pada peramban tidak akan menampilkan data internal karena middleware `require_login()` memblokir akses tanpa sesi valid.

---

## 6. Matriks Pemenuhan Kriteria Unjuk Kerja (KUK)

Matriks berikut memetakan pemenuhan unit kompetensi SKKNI Pengembang Web Pratama / Junior Web Developer terhadap implementasi riil pada kode sumber:

| Elemen / KUK Standar Kompetensi | Bukti Implementasi pada Kode Sumber | File Acuan |
|---|---|---|
| **1. UI Responsif Lintas Perangkat**<br>Antarmuka dapat beradaptasi pada breakpoint layar 375px (mobile), 768px (tablet), dan 1440px (desktop). | - Grid Bootstrap 5 adaptif (`col-sm-*`, `col-md-*`, `col-lg-*`).<br>- Tabel data dibungkus kelas `.table-responsive` agar dapat digeser mulus pada layar kecil.<br>- Touch target tombol aksi minimal 44px.<br>- Navbar toggler collapse untuk kemudahan navigasi perangkat seluler. | `templates/header.php`<br>`assets/css/style.css`<br>`barang.php` |
| **2. Pemisahan Fungsi & Prosedur Modular**<br>Pemisahan tanggung jawab kode antara konfigurasi, logika bisnis, autentikasi, dan presentasi antarmuka. | - `config/database.php`: Khusus penanganan koneksi PDO & konfigurasi DSN.<br>- `includes/auth.php`: Isolasi autentikasi, status login, & CSRF.<br>- `includes/functions.php`: Utilitas validasi, kalkulasi matematis, & format rupiah.<br>- `includes/barang.php`: Layer Data Access Object (DAO) fungsi CRUD.<br>- `templates/`: Layout header & footer yang dapat digunakan berulang (*reusable*). | `config/database.php`<br>`includes/auth.php`<br>`includes/functions.php`<br>`includes/barang.php`<br>`templates/` |
| **3. Validasi Data & Penanganan Error**<br>Memeriksa kelengkapan tipe data masukan dan menyajikan feedback informatif tanpa membocorkan celah sistem. | - Validasi server-side menyeluruh pada fungsi `validate_barang()`.<br>- Pengecekan duplikasi kode barang via query prepared statement.<br>- Pemberian kelas CSS `is-invalid` dan pesan kesalahan di bawah tiap input.<br>- Tangkapan blok `try-catch PDOException` dengan pencatatan `error_log` tanpa mengekspos struktur query ke pengguna akhir. | `includes/functions.php`<br>`includes/barang.php`<br>`tambah.php`<br>`edit.php` |
| **4. Integrasi Pustaka Pihak Ketiga**<br>Pemanfaatan pustaka eksternal untuk melengkapi fungsionalitas aplikasi. | - Pustaka FPDF v1.86 diintegrasikan secara lokal tanpa manajer paket eksternal yang rumit.<br>- Pembuatan kelas turunan `class PDF_Laporan extends FPDF` untuk kustomisasi kop dan nomor halaman otomatis.<br>- Ekspor data tabel inventaris langsung ke dokumen PDF siap unduh/cetak. | `cetak_pdf.php`<br>`fpdf/fpdf.php` |
| **5. Pencegahan Serangan Siber Dasar (OWASP)**<br>Penerapan praktik pemrograman aman (*Secure Coding*). | - **SQL Injection:** Seluruh query dinamis memanfaatkan PDO Parameter Binding.<br>- **XSS:** Filter karakter khusus melalui `htmlspecialchars()` via fungsi `e()`.<br>- **CSRF:** Token kriptografis acak pada formulir POST.<br>- **Session Hijacking:** Cookie berstatus `HttpOnly` dan `SameSite=Lax`. | `includes/auth.php`<br>`includes/functions.php`<br>`config/database.php` |

---

## 7. Catatan Keamanan & Integritas Pengujian

1. **Tanpa Kredensial Uji Bawaan (Zero Default Hardcoded Credentials):**
   Basis data awal sengaja disediakan dalam kondisi bersih tanpa akun pengguna bawaan (`table users` kosong). Hal ini dirancang agar Asesi membuktikan secara mandiri alur pembuatan akun pertama melalui formulir pendaftaran resmi pada saat uji kompetensi dimulai.
2. **Pencegahan Double-Submit:**
   Skrip JavaScript pada `templates/footer.php` mendeteksi pengiriman formulir ganda dan secara otomatis menonaktifkan tombol submit dengan indikator spinner pemrosesan aktif, mencegah duplikasi entri data akibat klik berulang.
3. **Pemberian Hak Akses Terproteksi:**
   Setiap berkas controller fungsional (`index.php`, `barang.php`, `tambah.php`, `edit.php`, `hapus.php`, `laporan.php`, `cetak_pdf.php`) dilindungi oleh prosedur `require_login()`. Permintaan tanpa sesi yang valid akan langsung ditolak dan dialihkan ke `login.php`.
