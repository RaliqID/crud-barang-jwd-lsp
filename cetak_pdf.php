<?php
// cetak_pdf.php - Cetak Dokumen Laporan Inventaris Barang dengan FPDF

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/barang.php';
require_once __DIR__ . '/fpdf/fpdf.php';

require_login();

// Ambil data barang dan rekap total
$barang_list = get_all_barang($pdo);
$total_stok  = hitung_total_stok($pdo);
$total_nilai = hitung_total_nilai($pdo);
$user        = current_user();

class PDF_Laporan extends FPDF {
    // Header Dokumen
    function Header() {
        // Judul Utama
        $this->SetFont('Arial', 'B', 15);
        $this->SetTextColor(15, 23, 42);
        $this->Cell(0, 7, 'SISTEM INVENTARIS DATA BARANG', 0, 1, 'C');

        // Subjudul
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(100, 116, 139);
        $this->Cell(0, 6, 'LAPORAN REKAPITULASI DATA BARANG & NILAI ASET', 0, 1, 'C');

        // Garis Pembatas
        $this->SetDrawColor(203, 213, 225);
        $this->SetLineWidth(0.5);
        $this->Line(10, 24, 200, 24);
        $this->Ln(6);

        // Metadata Tanggal
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(71, 85, 105);
        $this->Cell(100, 5, 'Tanggal Cetak: ' . date('d F Y, H:i') . ' WIB', 0, 0, 'L');
        $this->Ln(7);
    }

    // Footer Dokumen
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(148, 163, 184);
        $this->Cell(0, 10, 'Halaman ' . $this->PageNo() . ' dari {nb} - Dokumen Resmi Inventaris', 0, 0, 'C');
    }
}

// Inisialisasi dokumen A4 Portrait
$pdf = new PDF_Laporan('P', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->SetMargins(10, 10, 10);
$pdf->AddPage();

// Header Tabel
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetFillColor(241, 245, 249);
$pdf->SetTextColor(30, 41, 59);
$pdf->SetDrawColor(203, 213, 225);
$pdf->SetLineWidth(0.3);

// Lebar Kolom total = 190 mm
$pdf->Cell(10, 8, 'No', 1, 0, 'C', true);
$pdf->Cell(25, 8, 'Kode', 1, 0, 'C', true);
$pdf->Cell(55, 8, 'Nama Barang', 1, 0, 'L', true);
$pdf->Cell(32, 8, 'Kategori', 1, 0, 'L', true);
$pdf->Cell(26, 8, 'Harga', 1, 0, 'R', true);
$pdf->Cell(14, 8, 'Stok', 1, 0, 'C', true);
$pdf->Cell(28, 8, 'Subtotal', 1, 1, 'R', true);

// Isi Tabel
$pdf->SetFont('Arial', '', 8.5);
$pdf->SetTextColor(15, 23, 42);

if (empty($barang_list)) {
    $pdf->Cell(190, 10, 'Tidak ada data barang yang terdaftar.', 1, 1, 'C');
} else {
    $no = 1;
    foreach ($barang_list as $row) {
        $subtotal = (float)$row['harga'] * (int)$row['stok'];

        $pdf->Cell(10, 7, $no++, 1, 0, 'C');
        $pdf->Cell(25, 7, $row['kode_barang'], 1, 0, 'C');
        // Batasi nama barang jika terlalu panjang agar tidak merusak cell
        $nama_barang = strlen($row['nama_barang']) > 32 ? substr($row['nama_barang'], 0, 29) . '...' : $row['nama_barang'];
        $pdf->Cell(55, 7, $nama_barang, 1, 0, 'L');
        $pdf->Cell(32, 7, $row['kategori'], 1, 0, 'L');
        $pdf->Cell(26, 7, number_format((float)$row['harga'], 0, ',', '.'), 1, 0, 'R');
        $pdf->Cell(14, 7, number_format((int)$row['stok'], 0, ',', '.'), 1, 0, 'C');
        $pdf->Cell(28, 7, number_format($subtotal, 0, ',', '.'), 1, 1, 'R');
    }

    // Baris Total
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetFillColor(248, 250, 252);
    $pdf->Cell(122, 8, 'TOTAL KESELURUHAN', 1, 0, 'R', true);
    $pdf->Cell(26, 8, '', 1, 0, 'C', true);
    $pdf->Cell(14, 8, number_format($total_stok, 0, ',', '.'), 1, 0, 'C', true);
    $pdf->Cell(28, 8, 'Rp ' . number_format($total_nilai, 0, ',', '.'), 1, 1, 'R', true);
}

// Bagian Tanda Tangan
$pdf->Ln(10);
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(30, 41, 59);

// Posisi kanan
$pdf->SetX(130);
$pdf->Cell(60, 5, 'Dicetak pada: ' . date('d/m/Y'), 0, 1, 'C');
$pdf->SetX(130);
$pdf->Cell(60, 5, 'Petugas Penanggung Jawab,', 0, 1, 'C');
$pdf->Ln(18);
$pdf->SetX(130);
$nama_petugas = $user['nama_lengkap'] ?? 'Administrator';
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(60, 5, '( ' . $nama_petugas . ' )', 0, 1, 'C');

// Kirim PDF langsung ke browser
$pdf->Output('I', 'Laporan_Data_Barang_' . date('Ymd_His') . '.pdf');
exit;
