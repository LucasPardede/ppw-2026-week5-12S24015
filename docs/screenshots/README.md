# Panduan Tangkapan Layar (Screenshots Guide) — SIPUS-Del
### Direktori: `docs/screenshots/`

Folder ini menampung 12 gambar bukti tangkapan layar antarmuka dan basis data untuk laporan evaluasi praktikum.

---

## Daftar Nama Berkas & Cara Pengambilan

Gunakan kombinasi tombol **`Win + Shift + S`** di Windows untuk mengambil tangkapan layar langsung ke *Clipboard*, lalu simpan ke folder ini dengan format `.png`.

| No | Nama Berkas | URL / Lokasi | Cara / Skenario Pengambilan |
|---|---|---|---|
| **01** | `01-login.png` | `http://ppw-2026-week5-12s24015.test/login` | Buka form login, tunjukkan isian email otomatis dari Cookie dan checkbox *"Ingat email saya (Cookie HttpOnly)"*. |
| **02** | `02-katalog-statistik.png` | `http://ppw-2026-week5-12s24015.test/katalog` | Login sebagai mahasiswa, buka katalog, tunjukkan 4 kartu analitik koleksi (`DB::table`) dan panel pencarian terakhir. |
| **03** | `03-pinjam-berhasil.png` | `http://ppw-2026-week5-12s24015.test/pinjaman` | Tangkap banner hijau notifikasi sukses meminjam buku beserta `kode_pinjam` acak unik (`PJM-20261017-...`). |
| **04** | `04-pinjam-stok-0-ditolak.png` | `http://ppw-2026-week5-12s24015.test/katalog` | Cari buku *The Pragmatic Programmer* (stok 0), tunjukkan tombol status Habis dan alert penolakan transaksi `StokHabisException`. |
| **05** | `05-pinjam-ganda-ditolak.png` | `http://ppw-2026-week5-12s24015.test/katalog` | Coba pinjam buku yang sedang dipinjam aktif oleh mahasiswa yang sama, tunjukkan alert merah penolakan peminjaman ganda. |
| **06** | `06-kembali-berhasil.png` | `http://ppw-2026-week5-12s24015.test/admin/peminjaman` | Login sebagai admin, klik tombol **Kembalikan**, tunjukkan alert hijau sukses dan penambahan kembali stok ke rak. |
| **07** | `07-skema-peminjamans-dbeaver.png` | DBeaver / HeidiSQL | Buka tabel `peminjamans` > tab *Columns* & *Foreign Keys*, tunjukkan kolom `kode_pinjam`, `tanggal_jatuh_tempo`, dan FK cascade. |
| **08** | `08-uji-balapan.png` | Terminal (PowerShell / Cmder) | Jalankan `php artisan sipus:uji-balapan --proses=5`, tangkap tabel hasil terminal (1 sukses, 4 ditolak, stok akhir 0). |
| **09** | `09-devtools-cookie-httponly.png` | Browser DevTools (`F12`) | Buka tab **Application** > **Cookies**, tunjukkan baris `del_last_user_email` dengan tanda centang pada kolom **HTTPOnly** dan **SameSite: Lax**. |
| **10** | `10-akses-ditolak-mahasiswa.png` | `http://ppw-2026-week5-12s24015.test/admin/dashboard` | Login sebagai mahasiswa, lalu akses paksa URL admin di bilah alamat, tunjukkan halaman respons HTTP 403 Forbidden. |
| **11** | `11-laporan-denda.png` | `http://ppw-2026-week5-12s24015.test/admin/laporan` | Buka menu Laporan & Denda di panel admin, tunjukkan ringkasan sirkulasi dan tombol ekspor berkas CSV. |
| **12** | `12-erd.png` | `README.md` (Diagram Mermaid) | Tangkap diagram Mermaid 6 entitas yang ter-render di browser/GitHub preview. |

---
*Gambar yang disimpan di folder ini akan otomatis ditampilkan pada laporan dan repositori proyek.*
