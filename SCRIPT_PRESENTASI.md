# 🎙️ SCRIPT PRESENTASI LENGKAP PRAKTIKUM MINGGU 6
### SIPUS-Del (Tahap 2): Database Connectivity, Transaksi ACID & Pessimistic Locking, Fluent Query Builder, serta Manajemen Sesi & Autentikasi
**Presenter:** Lucas Pardede (NIM: 12S24015)  
**Mata Kuliah:** 12S3101 — Pemrograman dan Pengujian Web  
**Dosen Pengampu:** Chandro Pardede, S.Kom., M.Sc.  
**Institusi:** Institut Teknologi Del, Laguboti  
**Durasi Presentasi:** 7 – 10 Menit  

---

## 📋 DAFTAR PERSIAPAN SEBELUM PRESENTASI (CHECKLIST)
Sebelum memulai presentasi atau rekaman video, pastikan aplikasi dan alat pendukung sudah terbuka:
- [ ] **Web Browser (Chrome/Edge):**
  - Tab 1: Halaman Login (`http://localhost:8000/login` atau `http://ppw-2026-week5-12s24015.test/login`)
  - Tab 2: Buka DevTools (`F12`) ➔ Tab **Application** ➔ Menu **Cookies**
- [ ] **Database Client GUI (DBeaver / HeidiSQL / phpMyAdmin):**
  - Terhubung ke basis data `sipus_del` (MySQL) untuk melihat tabel `bukus`, `peminjamans`, dan `riwayat_transaksis`.
- [ ] **Code Editor (VS Code):**
  - Siapkan tab: `PeminjamanService.php`, `AuthController.php`, `KatalogController.php`, dan file migration `peminjamans`.
- [ ] **Terminal (PowerShell / Cmder):**
  - Siap menjalankan perintah `php artisan sipus:uji-balapan --proses=5` dan `php artisan test`.

---

## ⏱️ RANGKUMAN ALUR WAKTU (TIMELINE)

| Menit | Bagian | Fokus Utama |
|---|---|---|
| **00:00 – 01:00** | **Bagian 1: Perkenalan** | Salam pembuka, identitas presenter (Nama, NIM, Del), judul tugas. |
| **01:00 – 02:15** | **Bagian 2: Flashback Week 5 ke Week 6** | Mengapa Week 5 belum cukup dan urgensi pembaruan di Week 6. |
| **02:15 – 03:30** | **Bagian 3: Tujuan & Fitur Baru Week 6** | 4 pilar capaian: skema sirkulasi, transaksi ACID, query builder, auth & sesi. |
| **03:30 – 06:00** | **Bagian 4: Bedah Kode (File & Baris)** | Tunjuk file dan baris implementasi teknis kunci di code editor. |
| **06:00 – 08:45** | **Bagian 5: Pembuktian & Demo Live** | Demo web, cek DevTools, mutasi stok DB, uji balapan, dan unit test. |
| **08:45 – 09:30** | **Bagian 6: Kesimpulan & Penutup** | Ringkasan pencapaian dan salam penutup. |

---

# 🗣️ NASKAH LENGKAP PRESENTASI

---

### BAGIAN 1: PERKENALAN DIRI (00:00 – 01:00)

**[Tampilan Layar]:**  
*Tampilkan slide pembuka atau halaman utama SIPUS-Del.*

**[Dialog / Narasi Lisan]:**  
> *"Selamat pagi/siang kepada Bapak Dosen Pengampu, Bapak Chandro Pardede, dan rekan-rekan asisten laboratorium yang saya hormati.*  
>  
> *Perkenalkan, nama saya **Lucas Pardede** dengan **NIM 12S24015** dari Program Studi Sarjana Sistem Informasi / Informatika, Institut Teknologi Del.*  
>  
> *Pada kesempatan hari ini, saya akan mempresentasikan hasil implementasi tugas mandiri **Modul Praktikum Minggu ke-6** pada mata kuliah Pemrograman dan Pengujian Web. Tugas ini berfokus pada pengembangan lanjutan aplikasi **SIPUS-Del (Sistem Informasi Perpustakaan Kampus Del) Tahap 2**.*  
>  
> *Topik inti yang kami eksplorasi meliputi konektivitas basis data persisten, implementasi transaksi bergaransi ACID, penanganan konkurensi dengan Pessimistic Row Locking `lockForUpdate`, rekapitulasi data cepat dengan Fluent Query Builder, serta manajemen sesi pengguna dan keamanan autentikasi."*

---

### BAGIAN 2: FLASHBACK WEEK 5 DAN HUBUNGANNYA KE WEEK 6 (01:00 – 02:15)

**[Tampilan Layar]:**  
*Buka diagram Entity Relationship Diagram (ERD) di `README.md`.*

**[Dialog / Narasi Lisan]:**  
> *"Sebelum kita masuk ke fitur-fitur baru, mari kita kilas balik sejenak ke apa yang telah kita selesaikan pada **Minggu ke-5**:*  
>  
> *Pada Minggu 5 lalu, kita telah meletakkan fondasi arsitektur Model-View-Controller (MVC) di Laravel 11. Kita membangun master tabel `kategoris` dan `bukus`, mengoperasikan 7 aksi baku RESTful CRUD, membuat layout modular dengan Blade `<x-layout>`, serta mengoptimasi relasi One-to-Many dengan Eager Loading `with('kategori')` untuk meniadakan masalah N+1 kueri.*  
>  
> *Namun, sistem pada Minggu 5 tersebut masih memiliki **tiga keterbatasan kritis** jika diterapkan di dunia nyata:*  
> 1. *Pertama, aplikasi masih sebatas katalog pasif, belum memiliki proses bisnis transaksi sirkulasi (peminjaman dan pengembalian).*  
> 2. *Kedua, belum ada proteksi terhadap **Race Condition**. Jika ada dua pengguna berebut buku dengan sisa stok 1 pada milidetik yang sama, kedua request bisa lolos validasi `stok > 0` sehingga stok basis data menjadi minus (-1).*  
> 3. *Ketiga, aplikasi belum memiliki autentikasi formal dan proteksi sesi. Belum ada mekanisme regenerasi ID sesi untuk mencegah **Session Fixation Attack**, dan belum ada cookie berstatus **HttpOnly** untuk menangkal pencurian data via XSS.*  
>  
> *Oleh karena itu, **Minggu ke-6 ini hadir sebagai solusi**: kita meningkatkan SIPUS-Del menjadi sistem yang memenuhi standar ACID, aman dari balapan data konkuren, memiliki analitik performa tinggi, serta mengamankan siklus hidup sesi pengguna."*

---

### BAGIAN 3: TUJUAN PRAKTIKUM & APA SAJA YANG DIBUAT DI WEEK 6 (02:15 – 03:30)

**[Tampilan Layar]:**  
*Tampilkan daftar fitur baru di antarmuka SIPUS-Del (Navbar, Menu Sirkulasi, dan Kartu Statistik).*

**[Dialog / Narasi Lisan]:**  
> *"Berdasarkan indikator ketercapaian pada Modul Praktikum 6, berikut adalah 4 pilar utama yang telah kami rancang dan implementasikan:*  
>  
> 1. ***Skema Sirkulasi & Relasi Eloquent:** Merancang tabel transaksi baru `peminjamans` dengan kode pinjam unik berformat `PJM-YYYYMMDD-XXXXXX`, relasi foreign key berintegritas `cascadeOnDelete`, serta tabel `riwayat_transaksis` sebagai audit trail.*  
> 2. ***Transaksi Basis Data ACID & Pessimistic Locking:** Membungkus seluruh alur pinjam dan kembali di dalam `DB::transaction()` dengan `lockForUpdate()`. Apabila stok 0, transaksi otomatis melempar exception dan di-rollback seketika.*  
> 3. ***Fluent Query Builder (`DB::table`) & Agregasi:** Menghitung 4 kartu ringkasan sirkulasi koleksi dan 5 buku terpopuler secara instan tanpa membebani memori Eloquent ORM, serta menerapkan filter dinamis `when()` dan paginasi `withQueryString()`.*  
> 4. ***Manajemen Sesi & Autentikasi Pustakawan:** Mengimplementasikan login multi-peran dengan `Auth::attempt()`, wajib menjalankan `session()->regenerate()` untuk menangkal Session Fixation, menyimpan cookie `del_last_user_email` dengan flag `HttpOnly` dan `SameSite: Lax`, serta memproteksi rute administratif via middleware `auth`."*

---

### BAGIAN 4: BEDAH KODE — IMPLEMENTASI TEKNIS & NOMOR BARIS (03:30 – 06:00)

**[Tampilan Layar]:**  
*Beralih ke Code Editor (VS Code), buka berkas-berkas berikut dan sorot baris yang disebutkan:*

#### 1. Skema Migrasi Transaksi & Proteksi Database
- **Berkas:** `database/migrations/2026_10_17_000001_alter_peminjamans_for_week6.php`
  - **Baris 16 – 21:** Menambahkan kolom `kode_pinjam` unik 30 karakter, `tanggal_jatuh_tempo`, dan snapshot identitas peminjam (`nim_peminjam`, `nama_peminjam`).
  - **Baris 47 – 52:** Mengonfigurasi foreign key `buku_id` dengan aturan `cascadeOnDelete()` dan memasang indeks komposit pada `['buku_id', 'nim_peminjam', 'status']` untuk menjamin kecepatan kueri verifikasi pinjaman aktif.
- **Berkas:** `database/migrations/2026_10_17_000002_make_bukus_stok_unsigned.php`
  - **Baris 14:** Mengubah kolom `stok` pada tabel `bukus` menjadi `unsignedInteger(default: 0)`. Ini adalah perlindungan lapis terendah (*database constraint*) agar stok secara fisik mustahil bernilai negatif.

#### 2. Transaksi ACID Peminjaman & Pessimistic Locking
- **Berkas:** `app/Services/PeminjamanService.php`
  - **Baris 30 – 34:** Metode `pinjam()` dibungkus di dalam `DB::transaction(..., 3)` dan mengeksekusi penguncian baris:  
    `$buku = Buku::where('id', $bukuId)->lockForUpdate()->firstOrFail();`  
    *Perintah ini menerbitkan SQL `SELECT ... FOR UPDATE` yang menahan transaksi lain yang mencoba membaca atau mengubah buku yang sama.*
  - **Baris 42 – 45:** Pemeriksaan stok; jika `stok < 1`, sistem melempar `StokHabisException` dan otomatis melakukan **Full Rollback**.
  - **Baris 54 – 63:** Mencegah **peminjaman ganda**: sistem memeriksa apakah NIM yang sama masih meminjam buku yang sama dengan status `dipinjam`. Jika ya, transaksi ditolak dengan `PeminjamanGandaException`.
  - **Baris 89 – 91:** Pengurangan stok secara atomik `$buku->decrement('stok')` dan pembuatan rekaman di tabel `peminjamans`.
  - **Baris 113 – 118:** Pencatatan log audit ke tabel `riwayat_transaksis` dalam unit transaksi yang sama.

#### 3. Transaksi Pengembalian & Deadlock Prevention Strategy
- **Berkas:** `app/Services/PeminjamanService.php`
  - **Baris 137 – 148:** Metode `kembali()` menerapkan **urutan penguncian konsisten (*Lock Ordering*)**: kunci baris buku terlebih dahulu (`Buku::lockForUpdate`), baru kemudian mengunci baris peminjaman (`Peminjaman::lockForUpdate`). Hal ini menjamin tidak terjadi **Deadlock** antar-dua transaksi paralel.
  - **Baris 160 – 169:** Status diubah menjadi `'dikembalikan'`, denda dihitung otomatis, dan stok dikembalikan dengan `$buku->increment('stok')`.

#### 4. Fluent Query Builder & Analitik Sirkulasi
- **Berkas:** `app/Services/StatistikService.php`
  - **Baris 20 – 28:** Menggunakan `DB::table('bukus')->count()`, `sum('stok')`, dan count peminjaman untuk menghasilkan 4 kartu statistik koleksi secara instan.
  - **Baris 58 – 80:** Metode `getBukuPopuler()` memanfaatkan `DB::table()` dipadukan dengan `join`, `groupBy`, dan `DB::raw('COUNT(peminjamans.id)')` untuk merekapitulasi buku terlaris.
- **Berkas:** `app/Http/Controllers/KatalogController.php`
  - **Baris 25 – 38:** Katalog memanggil `Buku::with('kategori')->withCount('peminjamans')`, dipadukan dengan pencarian kondisional `when()`, dan menyimpan histori pencarian di sesi: `$request->session()->put('last_search', $search)`.

#### 5. Keamanan Sesi, Regenerasi ID, & Cookie HttpOnly
- **Berkas:** `app/Http/Controllers/AuthController.php`
  - **Baris 98:** Autentikasi formal pengguna menggunakan `Auth::attempt()`.
  - **Baris 110:** Panggilan wajib pencegah serangan pembajakan sesi:  
    `$request->session()->regenerate();`
  - **Baris 118 – 129:** Mengantrekan cookie:  
    `Cookie::queue('del_last_user_email', $email, 10080, '/', null, false, true, false, 'lax');`  
    *Parameter ke-7 bernilai `true` (HttpOnly), dan parameter ke-9 bernilai `'lax'` (SameSite).*
  - **Baris 146 – 154:** Aksi logout yang menghancurkan sesi via `Auth::logout()`, `$request->session()->invalidate()`, dan `$request->session()->regenerateToken()`.

---

### BAGIAN 5: PEMBUKTIAN & LIVE DEMO SISTEM (06:00 – 08:45)

**[Tampilan Layar]:**  
*Beralih ke peramban web dan lakukan interaksi langsung:*

#### Skenario 1: Demo Login & Audit DevTools (Cookie HttpOnly)
1. Buka halaman `/login`. Klik tombol demo pustakawan: `pustakawan@del.ac.id`.
2. Centang kotak *"Ingat email saya (Cookie HttpOnly)"*, lalu klik **Masuk**.
3. Buka Inspect Element / DevTools (`F12`) ➔ Tab **Application** ➔ Menu **Cookies**.
4. **[Narasi Lisan]:**  
   > *"Dapat kita buktikan di tab Cookies DevTools: Cookie `del_last_user_email` dan `laravel_session` memiliki tanda centang aktif pada kolom **HTTPOnly** dan nilai **SameSite: Lax**. Hal ini menjamin bahwa skrip JavaScript jahat atau serangan XSS tidak akan pernah bisa mengakses atau mencuri cookie sesi pengguna."*

#### Skenario 2: Katalog Koleksi & 4 Kartu Statistik Real-Time
1. Masuk sebagai mahasiswa (`lucas.test@del.ac.id`), buka halaman **Katalog Koleksi**.
2. Tunjukkan 4 kartu statistik di atas: Total Judul, Total Stok Tersedia, Sedang Dipinjam, dan Dikembalikan.
3. Ketik kata kunci *"Clean Code"* di form pencarian. Tunjukkan buku terfilter dengan tepat.
4. Kosongkan kembali pencarian. Perlihatkan badge interaktif: *"Pencarian terakhir: Clean Code"*.
5. **[Narasi Lisan]:**  
   > *"4 kartu statistik ini terhubung secara real-time via `DB::table()`. Ketika mahasiswa mencari buku, sistem mengingatnya di session state `last_search` sehingga mempermudah penelusuran kembali."*

#### Skenario 3: Transaksi Peminjaman Normal (Stok Berkurang Real-Time)
1. Pilih buku dengan sisa stok = 1 (misal *"Refactoring: Improving the Design of Existing Code"*).
2. Tunjukkan stok awal = 1.
3. Klik tombol **Pinjam Buku**.
4. Sistem memproses via `PeminjamanService::pinjam` dan menampilkan flash message sukses dengan kode acak unik (misal `PJM-20261017-ABCDEF`).
5. Perlihatkan stok buku di katalog langsung berkurang dari **1 menjadi 0**, dan tombol otomatis berubah menjadi disabled (*"Stok Habis"*).
6. Buka menu **Pinjaman Saya**: transaksi baru langsung tampil lengkap dengan batas jatuh tempo 7 hari ke depan.

#### Skenario 4: Pembuktian Rollback ACID (Uji Buku Stok 0)
1. Coba lakukan peminjaman pada buku yang stoknya 0 tadi.
2. Di antarmuka, tombol dinonaktifkan. Jika request dikirimkan via form, sistem seketika melempar `StokHabisException` dan membatalkan transaksi.
3. **[Narasi Lisan]:**  
   > *"Buku dengan stok 0 tidak dapat dipinjam. Database seketika melakukan rollback penuh sehingga stok tetap 0 (tidak menjadi minus -1) dan tidak ada rekaman peminjaman siluman yang tersimpan. Ini membuktikan prinsip Atomicity dan Consistency bekerja sempurna."*

#### Skenario 5: Pencegahan Peminjaman Ganda
1. Coba pinjam buku lain yang saat ini sedang aktif dipinjam oleh mahasiswa yang sama.
2. Muncul alert penolakan: *"Anda sedang meminjam buku ini. Tidak dapat meminjam buku yang sama secara bersamaan."*

#### Skenario 6: Pengembalian Buku & Penambahan Stok Otomatis
1. Login kembali sebagai pustakawan ➔ buka menu **Sirkulasi Peminjaman**.
2. Cari rekaman pinjaman tadi, lalu klik tombol **Kembalikan**.
3. Status berubah menjadi **Dikembalikan** dan tanggal kembali tercatat hari ini.
4. Buka kembali halaman katalog: stok buku yang tadinya 0 kini telah **bertambah kembali menjadi 1**.
5. Buka **Dashboard Admin**: tunjukkan tabel **Log Audit Transaksi** yang merekam aksi `pinjam` dan `kembali` secara terperinci.

#### Skenario 7: Uji Balapan (Race Condition) & Test Suite Otomatis via Terminal
1. Buka Terminal CLI di folder proyek.
2. Jalankan simulasi uji balapan 5 proses paralel:
   ```bash
   php artisan sipus:uji-balapan --proses=5
   ```
3. **[Narasi Lisan]:**  
   > *"Untuk membuktikan keandalan Pessimistic Locking `lockForUpdate`, kita jalankan `sipus:uji-balapan` dengan 5 proses PHP paralel yang serentak memperebutkan buku stok 1. Terlihat pada tabel terminal: **hanya 1 proses yang berhasil (200)**, dan **4 proses lainnya ditolak (422)**. Stok akhir buku tetap 0 dan tidak pernah minus."*
4. Terakhir, jalankan seluruh rangkaian pengujian fitur:
   ```bash
   php artisan test
   ```
5. Tunjukkan hasil keluaran terminal: **Tests: 37 passed (117 assertions) — 100% Lulus**.

---

### BAGIAN 6: KESIMPULAN & SALAM PENUTUP (08:45 – 09:30)

**[Tampilan Layar]:**  
*Tampilkan halaman GitHub repositori pada branch `week6-db-session`.*

**[Dialog / Narasi Lisan]:**  
> *"Sebagai kesimpulan dari pengembangan SIPUS-Del Tahap 2 pada Minggu ke-6:*  
>  
> 1. *Penerapan **Database Transaction (ACID)** yang dipadukan dengan **Pessimistic Locking (`lockForUpdate`)** terbukti mutlak diperlukan untuk mencegah anomali race condition pada sistem sirkulasi peminjaman.*  
> 2. *Pemanfaatan **Fluent Query Builder (`DB::table`)** memberikan efisiensi tinggi pada kalkulasi metrik analitik dan pelaporan tanpa membebani memori server.*  
> 3. *Keamanan aplikasi diperkuat secara menyeluruh melalui **Regenerasi Session ID** anti-Session Fixation dan atribut cookie **HttpOnly / SameSite: Lax** anti-XSS.*  
> 4. *Seluruh kode telah terintegrasi, lulus 37 pengujian otomatis, dan di-push ke repositori GitHub pada branch `week6-db-session`.*  
>  
> *Sekian presentasi dari saya, Lucas Pardede (12S24015). Terima kasih atas perhatian dan bimbingan Bapak Dosen serta rekan-rekan asisten laboratorium. Jika ada tanggapan atau pertanyaan, saya persilakan. Salam Del!"*

---

## 🎯 CHEAT SHEET ANTHISIPASI PERTANYAAN DOSEN (Q&A)

| Pertanyaan Penguji | Jawaban Teknis yang Tepat & Percaya Diri |
|---|---|
| **Apa perbedaan `lockForUpdate()` dengan query biasa?** | *"`lockForUpdate()` mengeksekusi SQL `SELECT ... FOR UPDATE` pada transaksi InnoDB MySQL. Perintah ini memasang exclusive row-level lock sehingga transaksi lain yang mencoba membaca atau mengubah baris yang sama harus mengantre sampai transaksi pertama commit atau rollback. Kueri biasa tidak mengunci baris, sehingga dua transaksi bisa membaca stok 1 secara bersamaan dan meloloskan keduanya."* |
| **Mengapa `session()->regenerate()` wajib dijalankan saat login?** | *"Untuk mencegah serangan Session Fixation. Tanpa regenerasi, penyerang bisa menyuntikkan ID sesi yang sudah diketahuinya ke browser korban sebelum korban login. Ketika korban berhasil login, penyerang dapat membajak hak akses akun tersebut karena ID sesinya tidak pernah diganti."* |
| **Kapan kita harus pakai `DB::table()` dan kapan pakai `Eloquent ORM`?** | *"Eloquent ORM digunakan untuk domain bisnis yang memerlukan relasi objek, event model, dan mutasi data terenkapsulasi. Sebaliknya, `DB::table()` (Query Builder) digunakan untuk agregasi agregat (COUNT, SUM, GROUP BY), laporan cepat, dan dashboard analitik karena tidak melakukan overhead hidrasi model objek sehingga konsumsi memori jauh lebih hemat."* |
| **Bagaimana sistem mencegah Deadlock saat pengembalian buku?** | *"Dengan menerapkan Konsistensi Urutan Penguncian (Lock Ordering). Baik pada alur pinjam maupun kembali di `PeminjamanService`, baris buku selalu dikunci terlebih dahulu sebelum baris peminjaman. Karena urutan perolehan kunci selalu sama dari atas ke bawah, kondisi saling menunggu (circular wait) penyebab deadlock dapat dicegah."* |
