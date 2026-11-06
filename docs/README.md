# Dokumentasi Teknis & Artefak Pengujian SIPUS-Del (Tahap 2)
### Modul Praktikum Minggu 6 — Database Transaction, Session & Cookie
**Penyusun:** Lucas Pardede (NIM: 12S24015)  
**Institusi:** Institut Teknologi Del  

Direktori `docs/` ini menyimpan seluruh berkas bukti pengujian empiris, dokumentasi skema SQL, rekaman log, dan naskah presentasi video untuk pemenuhan 5 kriteria rubrik evaluasi Tugas Mandiri SIPUS-Del Tahap 2:

---

## Daftar Berkas & Struktur Direktori

```text
docs/
├── README.md                 # Berkas ini (Daftar isi & panduan artefak)
├── bukti-uji.sql             # Skrip SQL untuk DBeaver/HeidiSQL (audit stok, FK cascade, dll.)
├── naskah-video.md           # Naskah video demonstrasi berdurasi 5-7 menit (format per adegan)
├── hasil-uji-balapan.txt     # Rekaman output terminal 'php artisan sipus:uji-balapan --proses=5'
├── hasil-test-suite.txt      # Rekaman eksekusi 'php artisan test' (37 tests passed 100%)
└── screenshots/              # Folder tangkapan layar antarmuka & bukti sistem
    └── README.md             # Panduan nama berkas & cara pengambilan screenshot 01-12
```

---

## Ringkasan Fungsi Tiap Berkas

### 1. [`bukti-uji.sql`](file:///c:/laragon/www/ppw-2026-week5-12S24015/docs/bukti-uji.sql)
Skrip SQL siap pakai yang dirancang untuk dieksekusi di klien basis data (DBeaver / HeidiSQL / MySQL CLI) guna memverifikasi 5 aspek kritis:
- **Query 1:** Membuktikan tidak ada buku dengan stok negatif (`stok < 0`).
- **Query 2:** Membuktikan konsistensi stok buku terhadap pinjaman aktif (`stok_tersedia = stok_awal - dipinjam`).
- **Query 3:** Mengaudit skema tabel `peminjamans` (kolom baru, indeks komposit, dan `ON DELETE CASCADE`).
- **Query 4:** Menampilkan 15 baris audit log transaksi terbaru pada tabel `riwayat_transaksis`.
- **Query 5:** Memeriksa kesiapan akun pustakawan dan mahasiswa uji.

### 2. [`naskah-video.md`](file:///c:/laragon/www/ppw-2026-week5-12S24015/docs/naskah-video.md)
Panduan rekam presentasi video (5–7 menit) yang terbagi menjadi 7 adegan lengkap dengan alokasi waktu (*timestamp*), apa yang harus ditampilkan di layar (*screen capture*), dan narasi dialog lisan bahasa Indonesia.

### 3. [`hasil-uji-balapan.txt`](file:///c:/laragon/www/ppw-2026-week5-12S24015/docs/hasil-uji-balapan.txt)
Bukti riil dari eksekusi perintah `php artisan sipus:uji-balapan --proses=5` yang menjalankan 5 proses paralel secara serentak memperebutkan buku stok 1:
- **Hasil:** Tepat 1 proses berhasil, 4 proses ditolak dengan `StokHabisException`, stok akhir tepat 0, dan total pinjaman tercatat tepat 1.

### 4. [`hasil-test-suite.txt`](file:///c:/laragon/www/ppw-2026-week5-12S24015/docs/hasil-test-suite.txt)
Keluaran asli pengujian otomatis PHPUnit yang menjalankan seluruh test suite aplikasi:
- **Hasil:** 37 tests (117 assertions) Passed 100% tanpa kegagalan.

### 5. [`screenshots/`](file:///c:/laragon/www/ppw-2026-week5-12S24015/docs/screenshots/)
Folder khusus untuk menaruh gambar tangkapan layar pembuktian (01 hingga 12) yang dirujuk oleh berkas utama `README.md`.
