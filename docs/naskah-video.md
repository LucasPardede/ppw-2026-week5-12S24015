# Naskah Presentasi Video Demonstrasi: SIPUS-Del Tahap 2
### Modul Praktikum Minggu 6 — Database Transaction, Session & Cookie
**Durasi Total:** 5 — 7 Menit  
**Presenter:** Lucas Pardede (NIM: 12S24015)  
**Mata Kuliah:** 12S3101 Pemrograman dan Pengujian Web  
**Institusi:** Institut Teknologi Del, Laguboti  

---

## Ringkasan Alur Waktu (Timeline)

| Waktu | Durasi | Adegan | Fokus Utama |
|---|---|---|---|
| **00:00 - 00:45** | 45 detik | **Adegan 1: Pembukaan & Identitas** | Perkenalan diri, NIM, judul tugas, dan tujuan praktikum Minggu 6. |
| **00:45 - 01:45** | 60 detik | **Adegan 2: Arsitektur & ERD** | Penjelasan 6 tabel relasional, FK cascade, dan unsigned stock. |
| **01:45 - 02:45** | 60 detik | **Adegan 3: Demo Autentikasi & Cookie** | Login 2 peran, regenerasi sesi, inspeksi Cookie HttpOnly di DevTools. |
| **02:45 - 03:45** | 60 detik | **Adegan 4: Katalog & 4 Kartu Statistik** | Analitik koleksi via `DB::table()`, filter pencarian, session last search. |
| **03:45 - 05:00** | 75 detik | **Adegan 5: Sirkulasi ACID & Rollback** | Pinjam buku, kode acak unik, tolak pinjam ganda & tolak stok 0. |
| **05:00 - 06:15** | 75 detik | **Adegan 6: LockForUpdate & Uji Balapan** | Penjelasan pessimistic locking dan live demo `sipus:uji-balapan`. |
| **06:15 - 07:00** | 45 detik | **Adegan 7: Pengembalian & Penutup** | Pengembalian admin, increment stok, audit trail, kesimpulan. |

---

## Rincian Adegan demi Adegan (Script Breakdown)

### ADEGAN 1: Pembukaan & Identitas Diri (00:00 — 00:45)
- **Tampilan Layar:**  
  Kamera presenter / slide judul pembuka menampilkan:
  - *SIPUS-Del: Sistem Informasi Perpustakaan Kampus Del (Tahap 2)*
  - *Nama: Lucas Pardede | NIM: 12S24015*
  - *Institut Teknologi Del — Program Studi Sistem Informasi / Informatika*
- **Narasi Suara:**  
  > *"Halo semuanya, selamat pagi/siang. Salam sejahtera untuk bapak/ibu dosen pengampu dan asisten lab mata kuliah Pemrograman dan Pengujian Web.*  
  > *Nama saya Lucas Pardede dengan NIM 12S24015. Pada video ini, saya akan mendemonstrasikan hasil pengerjaan Tugas Mandiri Modul Praktikum Minggu ke-6, yaitu pengembangan lanjutan aplikasi SIPUS-Del Tahap 2.*  
  > *Fokus utama pengembangan tahap ini adalah penerapan Database Transaction bergaransi ACID, penanganan konkurensi data dengan Pessimistic Row Locking `lockForUpdate`, manajemen sesi aman terproteksi dari serangan Session Fixation, serta pemanfaatan Cookie bertanda HttpOnly dan SameSite."*

---

### ADEGAN 2: Penjelasan Arsitektur & ERD (00:45 — 01:45)
- **Tampilan Layar:**  
  Menampilkan diagram ERD Mermaid (dari README atau DBeaver) yang memperlihatkan 6 tabel: `users`, `kategoris`, `bukus`, `peminjamans`, `pengumumans`, dan `riwayat_transaksis`.
- **Narasi Suara:**  
  > *"Mari kita tinjau rancangan arsitektur basis data relasional SIPUS-Del.*  
  > *Tabel utama sirkulasi adalah `peminjamans`, yang telah dimodifikasi melalui Laravel Migration: kolom `tanggal_jatuh_tempo` menggantikan nama kolom lama, terdapat kolom `kode_pinjam` acak 30 karakter unik berformat `PJM-YYYYMMDD-XXXXXX`, serta relasi foreign key `buku_id` yang dilengkapi aturan `ON DELETE CASCADE`.*  
  > *Di sisi lain, kolom `stok` pada tabel `bukus` diatur sebagai `unsignedInteger` default 0. Ini adalah perlindungan tingkat basis data agar stok tidak pernah bernilai minus.*  
  > *Selain itu, kita menambahkan tabel baru `riwayat_transaksis` yang berfungsi sebagai audit log untuk merekam setiap aksi mutasi seperti pinjam, kembali, perpanjang, dan lunas."*

---

### ADEGAN 3: Demo Autentikasi, Sesi, dan Cookie HttpOnly (01:45 — 02:45)
- **Tampilan Layar:**  
  1. Halaman Login `http://ppw-2026-week5-12s24015.test/login`.
  2. Klik tombol demo akun `pustakawan@del.ac.id`, centang *"Ingat email saya (Cookie HttpOnly)"*, lalu klik Masuk.
  3. Buka Inspect Element / DevTools (`F12`) > tab **Application** > menu **Cookies**.
- **Narasi Suara:**  
  > *"Sekarang kita beralih ke demonstrasi aplikasi. Kita mulai dari halaman Login.*  
  > *SIPUS-Del menggunakan autentikasi `Auth::attempt()` standar Laravel. Saat login berhasil, aplikasi secara otomatis memanggil `session()->regenerate()`. Ini sangat penting untuk mencegah serangan Session Fixation.*  
  > *Ketika kotak 'Ingat email saya' dicentang, aplikasi mengantrekan cookie bernama `del_last_user_email`.*  
  > *Dapat kita lihat pada jendela DevTools di tab Application > Cookies: cookie `del_last_user_email` dan cookie sesi Laravel memiliki tanda centang pada kolom **HTTPOnly** dan nilai **SameSite: Lax**. Hal ini menjamin bahwa skrip JavaScript berbahaya dari serangan XSS tidak akan pernah bisa mencuri data cookie pengguna."*

---

### ADEGAN 4: Katalog & 4 Kartu Statistik Sirkulasi (02:45 — 03:45)
- **Tampilan Layar:**  
  1. Logout dari admin, login sebagai mahasiswa `lucas.pardede@del.ac.id`.
  2. Buka halaman Katalog Koleksi. Tunjukkan 4 kartu statistik di bagian atas.
  3. Lakukan pencarian buku *"Clean Code"*.
  4. Hapus pencarian, tunjukkan badge *"Pencarian terakhir: Clean Code"*.
- **Narasi Suara:**  
  > *"Sekarang kita masuk ke Portal Mahasiswa pada menu Katalog Koleksi.*  
  > *Di bagian atas, terdapat 4 kartu statistik sirkulasi: Total Judul Buku, Total Stok Tersedia, Sedang Dipinjam, dan Dikembalikan. Seluruh metrik ini dihitung menggunakan Query Builder `DB::table()` performa tinggi melalui kelas layanan `StatistikService`, bukan kueri Eloquent biasa, dan bebas dari masalah N+1 kueri.*  
  > *Ketika kita melakukan pencarian kata kunci tertentu, sistem menyimpannya ke dalam state Session `last_search`. Jika kita kembali ke katalog, muncul panel interaktif 'Pencarian terakhir' yang memungkinkan mahasiswa mengulang pencarian hanya dengan satu klik.*  
  > *Di pojok kanan navbar, waktu login pengguna juga ditampilkan secara dinamis dari session state `login_timestamp`."*

---

### ADEGAN 5: Sirkulasi ACID, Kode Acak Unik, dan Bukti Rollback (03:45 — 05:00)
- **Tampilan Layar:**  
  1. Klik tombol **Pinjam** pada buku yang stoknya tersedia. Tunjukkan notifikasi flash sukses beserta `kode_pinjam` (misal `PJM-20261017-...`).
  2. Buka menu Pinjaman Saya, tunjukkan data pinjaman baru dan banner peringatan jatuh tempo.
  3. Coba pinjam kembali buku yang sama -> Tunjukkan alert penolakan: *"Anda sedang meminjam buku ini..."*.
  4. Buka buku dengan stok 0 (*The Pragmatic Programmer*) -> tombol Pinjam berstatus dinonaktifkan (Habis). Coba trigger melalui URL/Form -> muncul penolakan *"Buku saat ini sedang tidak tersedia (stok habis)"*.
- **Narasi Suara:**  
  > *"Kini kita uji transaksi peminjaman buku. Seluruh alur ini ditangani oleh `PeminjamanService::pinjam`.*  
  > *Ketika mahasiswa meminjam buku, transaksi berjalan di dalam `DB::transaction` dengan isolasi locking. Stok langsung berkurang 1 dan dibuat data peminjaman dengan kode acak unik berawalan `PJM-`.*  
  > *Jika mahasiswa mencoba meminjam buku yang sama untuk kedua kalinya saat buku pertama belum dikembalikan, sistem menolak transaksi dengan exception `PeminjamanGandaException`.*  
  > *Bagaimana dengan buku yang stoknya 0? Ketika stok bernilai 0, sistem langsung melempar `StokHabisException` dan melakukan rollback penuh. Tidak ada pengurangan stok dan tidak ada rekaman peminjaman siluman yang tersimpan ke basis data. Ini membuktikan prinsip Atomicity dan Consistency bekerja sempurna."*

---

### ADEGAN 6: Pessimistic Locking & Uji Balapan Konkuren (05:00 — 06:15)
- **Tampilan Layar:**  
  Buka terminal PowerShell/Cmder, jalankan perintah:
  ```bash
  php artisan sipus:uji-balapan --proses=5
  ```
  Tunjukkan tabel hasil 5 proses konkuren.
- **Narasi Suara:**  
  > *"Pertanyaan terpenting dalam sistem web adalah: apa yang terjadi jika 5 mahasiswa mengklik tombol Pinjam secara bersamaan pada buku yang stoknya hanya tersisa 1?*  
  > *Untuk menguji hal ini secara nyata, saya membuat perintah Artisan khusus: `php artisan sipus:uji-balapan`.*  
  > *Mari kita jalankan dengan opsi 5 proses konkuren.*  
  > *Perintah ini memanfaatkan `Process::pool` untuk mengeksekusi 5 proses PHP paralel yang secara serentak memanggil `PeminjamanService::pinjam`.*  
  > *Perhatikan tabel keluarannya:*  
  > *Tepat 1 proses yang mendapatkan status **BERHASIL (200)**, sementara 4 proses lainnya langsung **DITOLAK (422)** dengan `StokHabisException`.*  
  > *Verifikasi basis data membuktikan bahwa stok akhir buku tetap 0 dan tidak menjadi minus, serta total pinjaman tercatat tepat 1.*  
  > *Inilah keunggulan `Buku::lockForUpdate()`. Transaksi pertama memegang kunci baris eksklusif, sementara transaksi lain menunggu antrean dan mendapati stok sudah habis ketika gilirannya tiba."*

---

### ADEGAN 7: Pengembalian Buku, Audit Trail, dan Penutup (06:15 — 07:00)
- **Tampilan Layar:**  
  1. Login kembali sebagai admin `pustakawan@del.ac.id`.
  2. Buka menu Sirkulasi Peminjaman, klik tombol **Kembalikan**.
  3. Tunjukkan stok buku bertambah kembali.
  4. Buka halaman Dashboard Admin, tunjukkan tabel **Log Audit Transaksi (ACID Audit Trail)** yang mencatat seluruh riwayat mutasi.
  5. Slide penutup.
- **Narasi Suara:**  
  > *"Terakhir, pada panel administrator, pustakawan mencatat pengembalian buku.*  
  > *Proses pengembalian menerapkan urutan penguncian deterministik: kunci baris buku terlebih dahulu, baru kunci baris peminjaman, untuk mencegah terjadinya deadlock.*  
  > *Stok buku bertambah kembali, status berubah menjadi 'dikembalikan', dan denda dihitung otomatis jika terlambat.*  
  > *Seluruh transaksi sukses ini tercatat rapi pada tabel Log Audit Transaksi di dashboard admin.*  
  > *Pengujian otomatis melalui PHPUnit juga telah membuktikan bahwa seluruh 37 pengujian fitur lolos 100 persen.*  
  > *Demikian demonstrasi pengembangan SIPUS-Del Tahap 2 oleh saya, Lucas Pardede (12S24015). Terima kasih atas perhatian bapak/ibu dosen dan rekan-rekan sekalian. Salam kampus Del!"*

---
*Naskah ini siap digunakan sebagai panduan rekam video presentasi (OBS Studio / Loom / Camtasia) dengan durasi tepat 5 hingga 7 menit.*
