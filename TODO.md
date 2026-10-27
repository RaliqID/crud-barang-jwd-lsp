# TODO — Proyek CRUD Data Barang LSP JWD · update: 2026-10-27 oleh orchestrator

## Goal
Tingkatkan desain UI menjadi modern, menarik, dan berkelas profesional, serta siapkan folder khusus `database/` di dalam root project berisi file `.sql` siap import saat dipindah ke laptop lain / penguji LSP.

## Task Board
| ID | Task | Owner Agent | Status | Bukti / Verifikasi |
|---|---|---|---|---|
| T7 | Desain UI/UX Menarik & Modern (CSS, Cards, Navbar, Badges) | @designer | ✅ Done | assets/css/style.css, index.php, barang.php, laporan.php, tambah.php, edit.php, login.php, register.php ter-redesign; 69/69 live E2E assertions pass exit 0 |
| T8 | Siapkan Folder database/ & File SQL Siap Import | @fixer | ✅ Done | Folder database/ berisi database.sql & db_inventaris_barang.sql, root database.sql terjaga, UI bersih dari tombol backup, 67/67 live E2E assertions pass |
| T9 | Verifikasi Browser Real (Playwright 375px/768px/1440px) | @qa-verifier | ⏳ Queued | Menunggu T7 & T8 selesai |
| T10| Sinkronisasi Git & Push ke GitHub | orchestrator | ⏳ Queued | Menunggu T9 selesai |

## Blokir & Risiko
- T7 dan T8 berjalan paralel (T7 fokus styling/UI template, T8 fokus backend export database).

## Ringkasan
Progress 0/4 done · Lane aktif: 2 (T7, T8) · Blocked: 0
