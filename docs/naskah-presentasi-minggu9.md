# Naskah Presentasi & Panduan Demonstrasi Praktikum Minggu 9
### Mata Kuliah: 12S3101 Pemrograman dan Pengujian Web (PPW)
**Topik:** Arsitektur Web Services Terdistribusi — Layanan SOAP Ringkas & RESTful Web API v1 (SIPUS-Del Tahap 3)  
**Penyusun:** Lucas Pardede (NIM: 12S24015) — Program Studi Sarjana Sistem Informasi, Institut Teknologi Del  
**Dosen Pengampu:** Chandro Pardede, S.Kom., M.Sc.

---

## Petunjuk Persiapan Sebelum Presentasi (Checklist 2 Menit)

1. **Jalankan Server Lokal:** Buka terminal dan ketik `php artisan serve`. Biarkan terminal tetap berjalan.
2. **Buka Aplikasi Postman Desktop:** Buka koleksi `SIPUS-Del Web Services & REST API v1`.
3. **Buka Browser / Dokumen Pendukung:**
   * Swagger / OpenAPI Viewer: `docs/openapi-sipus-v1.yaml`
   * Laporan Praktikum PDF: `docs/Laporan_Praktikum_Minggu_9_12S24015_Lucas_Pardede.pdf`
   * Terminal pengujian untuk mendemokan `php artisan test`.

---

## Susunan Naskah Presentasi (Estimasi Durasi: 7 – 10 Menit)

### 1. Pembukaan & Identitas (Durasi: ~1 Menit)

> *"Selamat pagi / siang kepada Bapak Chandro Pardede dan rekan-rekan asisten praktikum.*
>
> *Perkenalkan, nama saya **Lucas Pardede**, NIM **12S24015**, dari Program Studi Sarjana Sistem Informasi, Institut Teknologi Del.*
>
> *Pada kesempatan kali ini, saya akan mempresentasikan dan mendemonstrasikan hasil pengerjaan Tugas Mandiri Praktikum Minggu 9 mata kuliah Pemrograman dan Pengujian Web dengan judul: **'SIPUS-Del Tahap 3: Arsitektur Web Services Terdistribusi, Layanan SOAP Ringkas (WSDL & SOAP Fault), serta RESTful Web API v1 (Provider & Consumer)'**.*
>
> *Seluruh pekerjaan telah selesai 100% dan telah di-push secara rapi ke repositori GitHub pada branch **`week9-rest-api`** dengan total **96 Automated Tests lulus tanpa kegagalan (100% Green)**."*

---

### 2. Latar Belakang & Analisis Arsitektur (Durasi: ~1.5 Menit)

> *"Bapak dan rekan-rekan asisten, pada Minggu 5 dan 6 sebelumnya, SIPUS-Del dibangun sebagai sistem web monolitik berbasis Blade HTML. Namun, dalam ekosistem kampus nyata, perpustakaan dituntut untuk terhubung dengan sistem lain, seperti aplikasi mobile mahasiswa, kios peminjaman mandiri, dan sistem yudisium BAAK.*
>
> *Oleh karena itu, pada Minggu 9 ini, SIPUS-Del kami kembangkan memiliki peran ganda:*
> 1. ***Sebagai Web Service Provider:***
>    * *Kami menyediakan **Layanan SOAP Legacy** untuk integrasi formal contract-first dengan Sistem Akademik BAAK guna mengecek status bebas pinjaman mahasiswa.*
>    * *Kami menyediakan **RESTful Web API v1** modern untuk aplikasi mobile dan katalog publik.*
> 2. ***Sebagai Web Service Consumer:***
>    * *Kami mengintegrasikan sistem dengan **OpenLibrary Books API** untuk memperkaya metadata buku via ISBN, lengkap dengan caching 1 jam dan fallback proteksi timeout.*
>
> *Mengapa kami membedakan SOAP dan REST? Karena SOAP unggul dalam kontrak formal WSDL yang mengikat untuk sistem enterprise kampus, sedangkan REST berbasis JSON jauh lebih ringan, cepat, dan hemat bandwidth untuk perangkat mobile mahasiswa."*

---

### 3. Demonstrasi Fitur 1: Layanan SOAP Legacy (Clearance Check) (Durasi: ~1.5 Menit)

*(Buka Postman folder: `1. SOAP Legacy`)*

> *"Sekarang saya akan mendemonstrasikan layanan SOAP:*
>
> *1. Pertama, kontrak formal WSDL dapat diakses publik pada `GET /soap/sipus.wsdl`. Dokumen WSDL 1.1 ini mendefinisikan types, message, portType, dan binding secara valid.*
>
> *2. Kedua, kami menjalankan operasi `CheckClearanceStatus` melalui `POST /soap/sipus`.*
> *(Klik **Send** pada request '2. Check Clearance (Sukses - Bebas Pinjaman)')*
> *Bisa kita lihat responnya berformat `<soap:Envelope>` standar. Nilai `active_loans` adalah 0 dan tag `is_cleared` bernilai `true`.*
>
> *3. Bagaimana jika NIM mahasiswa tidak terdaftar di database atau XML yang dikirim cacat?*
> *(Klik **Send** pada request '3. Fault - NIM Tidak Terdaftar')*
> *Sistem kami tidak mengembalikan error sembarangan atau 200 palsu, melainkan mengembalikan standar **HTTP 500 dengan `<soap:Fault>`** (`faultcode: soap:Client`), sesuai spesifikasi modul praktikum."*

---

### 4. Demonstrasi Fitur 2: Otentikasi Stateless Sanctum & Global RFC 7807 (Durasi: ~1.5 Menit)

*(Buka Postman folder: `2. Auth (Sanctum)` dan `6. Skenario Negatif`)*

> *"Selanjutnya masuk ke layanan REST API v1:*
>
> *1. **Otentikasi Stateless:** Pustakawan melakukan login melalui `POST /api/v1/auth/login`.*
> *(Klik **Send** pada '1. Login Pustakawan')*
> *Server memverifikasi hash password dan mengembalikan Bearer Token Sanctum. Skrip Postman otomatis menyimpan token ini ke variabel koleksi `{{bearer_token}}`.*
>
> *2. **Isolasi Hak Akses (Role Separation):** Apa yang terjadi jika mahasiswa mencoba login ke API pustakawan?*
> *(Klik **Send** pada '2. Login Mahasiswa Ditolak')*
> *Sistem menolak dengan **HTTP 403 Forbidden**. Seluruh galat API kami konsisten mengadopsi standar internasional **RFC 7807 (`application/problem+json`)** yang memuat 5 atribut wajib: type, title, status, detail, dan instance.*
>
> *3. **Pencabutan Token (Logout):** Saat pustakawan memanggil `POST /api/v1/auth/logout`, sistem hanya menghapus token yang sedang aktif tanpa mengganggu login pustakawan dari perangkat lain."*

---

### 5. Demonstrasi Fitur 3: Katalog Buku, HATEOAS, & Semantik HTTP (Durasi: ~2 Menit)

*(Buka Postman folder: `3. Katalog Buku`)*

> *"Pada katalog buku, kami menerapkan kaidah RESTful tingkat tinggi (Richardson Maturity Model Level 3):*
>
> *1. **Bebas N+1 Query:** Pada `GET /api/v1/bukus`, kami mengimplementasikan eager loading `with('kategori')` dan `withCount('peminjamans')` sehingga performa kueri tetap stabil O(1) berapapun jumlah data.*
>
> *2. **HATEOAS Dinamis:** Perhatikan blok `_links` pada setiap buku:*
> *Jika stok buku masih tersedia (`stok > 0`), muncul tautan aksi `"pinjam_buku"`. Namun jika stok habis (`stok = 0`), tautan pinjam tersebut otomatis dihilangkan oleh `BukuResource`.*
>
> *3. **Semantik HTTP Status Codes:**
> * Saat pustakawan menambah buku (`POST /api/v1/bukus`), responnya adalah **`201 Created`** lengkap dengan header standar **`Location`** yang mengarah ke URI buku baru.*
> * Kami membuktikan perbedaan nyata antara **PUT** dan **PATCH**: `PUT` bersifat *full replacement* (menimpa seluruh entitas), sedangkan `PATCH` bersifat *partial update* (hanya mengubah stok atau sinopsis tanpa merusak data lain).*
> * Saat menghapus buku (`DELETE`), sistem mengembalikan **`204 No Content`** jika bersih, dan menolak dengan **`409 Conflict`** jika buku masih memiliki relasi peminjaman aktif guna mencegah error integritas foreign key."*

---

### 6. Demonstrasi Fitur 4: Transaksi Sirkulasi ACID & Concurrency Control (Durasi: ~2 Menit)

*(Buka Postman folder: `4. Sirkulasi Peminjaman (ACID)`)*

> *"Fitur paling kritis pada praktikum ini adalah transaksi sirkulasi peminjaman:*
>
> *1. Seluruh proses peminjaman dan pengembalian dibungkus di dalam `DB::transaction()` dengan penguncian baris pesimistik **`lockForUpdate()`**.*
>
> *2. Validasi bisnis ketat ditegakkan:*
> *(Buka folder 6. Skenario Negatif)*
> * *Jika buku dipinjam saat stok habis $\rightarrow$ sistem membatalkan transaksi dan mengembalikan **HTTP 422 Unprocessable Content**.*
> * *Jika mahasiswa mencoba meminjam judul buku yang sama dua kali $\rightarrow$ sistem menolak dengan **HTTP 409 Conflict**.*
> * *Jika mahasiswa memiliki pinjaman terlambat atau denda belum lunas $\rightarrow$ sistem menolak dengan **HTTP 409 Conflict**.*
>
> *3. Pada saat pengembalian (`PATCH /api/v1/peminjamans/{id}/kembalikan`), stok buku otomatis bertambah kembali (+1) dan denda keterlambatan dihitung secara dinamis sesuai selisih hari jatuh tempo."*

---

### 7. Demonstrasi Fitur 5: Konsumer Eksternal & Verifikasi Test (Durasi: ~1.5 Menit)

*(Buka Postman folder: `5. Konsumer Eksternal OpenLibrary`)*

> *"1. Untuk fitur konsumer, endpoint `GET /api/v1/bukus/1/referensi-eksternal` memanggil OpenLibrary Books API menggunakan facade `Http::timeout(3)`. Data eksternal seperti jumlah halaman, penerbit asli, dan tautan cover kami simpan di dalam cache memori selama 1 jam. Jika terjadi gangguan jaringan, sistem memiliki fallback aman berupa HTTP 504 Gateway Timeout tanpa membuat aplikasi crash.*
>
> *2. Terakhir, seluruh arsitektur ini telah diverifikasi melalui **Automated Test Suite**. Saya akan menjalankan perintah `php artisan test` di terminal:"*

*(Jalankan `php artisan test` di terminal)*

> *"Seperti yang terlihat di layar terminal:*
> * **96 Tests lolos 100% (436 Assertions terpenuhi).**
> * Tidak ada satupun test lama dari Minggu 5 dan 6 yang rusak (Zero Regression).*
> * Dokumentasi formal OpenAPI 3.0 (`openapi-sipus-v1.yaml`), koleksi Postman, serta **Laporan Praktikum Resmi dalam bentuk PDF 9 Halaman** telah tersedia rapi di dalam folder `docs/`.*
>
> *Demikian presentasi dari saya. Terima kasih banyak atas perhatian Bapak Dosen dan rekan-rekan asisten. Saya siap menjawab pertanyaan."*

---

## Jawaban Cepat untuk Pertanyaan Dosen / Asisten (FAQ Tanya Jawab)

| Pertanyaan Penguji | Kunci Jawaban Singkat & Berbobot |
|---|---|
| **Kenapa memilih Sanctum daripada JWT biasa atau Session?** | Sanctum sangat ringan, natively terintegrasi dengan Laravel Eloquent, *stateless* (cocok untuk API mobile/SPA), dan token dapat dicabut instan per device melalui `currentAccessToken()->delete()` tanpa overhead tabel blacklist JWT. |
| **Bagaimana cara mencegah race condition saat stok buku tinggal 1?** | Menggunakan `lockForUpdate()` di dalam blok `DB::transaction()`. Baris data buku dikunci di level MySQL InnoDB sehingga kueri konkuren lainnya harus antre (menunggu) sampai transaksi pertama selesai melakukan verifikasi dan pengurangan stok. |
| **Apa fungsi standar RFC 7807?** | RFC 7807 menetapkan format respons galat HTTP JSON yang seragam (`application/problem+json`). Klien tidak perlu menebak-nebak format pesan error karena selalu memiliki atribut baku: `type`, `title`, `status`, `detail`, `instance`, dan rincian validasi per-kolom pada `errors`. |
| **Kenapa WSDL penting pada SOAP?** | WSDL adalah kontrak formal mesin-ke-mesin (*machine-readable contract*). Klien SOAP dapat meng-generate stub/client secara otomatis berdasarkan skema tipe data XSD dan operasi yang tercantum pada WSDL tanpa perlu koordinasi manual. |
| **Kenapa GET bukus bebas dari masalah N+1?** | Karena kami menggunakan *eager loading* `with('kategori')` yang menggabungkan pembacaan kategori dalam 1 kueri `WHERE IN`, serta `withCount('peminjamans')` yang menghitung relasi peminjaman langsung di level SQL agregasi tanpa kueri berulang per baris. |
