# Panduan Tangkapan Layar (Screenshots Guide) — SIPUS-Del
### Direktori: `docs/screenshots/`

Folder ini menampung berkas bukti visual antarmuka dan web services untuk laporan evaluasi praktikum Minggu 5-6 (Tahap 2) dan Minggu 9 (Tahap 3).

---

## Daftar Berkas Bukti Uji Minggu 9 (Web Services & REST API v1)

| No | Nama Berkas | Fitur / Skenario | Endpoint Terkait | Status & Catatan |
|---|---|---|---|---|
| **01** | `01-soap-clearance.svg` / `.png` | SOAP XML Clearance Check & Fault | `POST /soap/sipus` | Validasi WSDL, respon XML, dan penanganan `soap:Fault`. |
| **02** | `02-api-auth-login.svg` / `.png` | Autentikasi Sanctum Bearer Token | `POST /api/v1/auth/login` | Login admin dapat Bearer token, login mahasiswa ditolak 403. |
| **03** | `03-api-katalog-hateoas.svg` / `.png` | GET Katalog Terpaginasi & HATEOAS | `GET /api/v1/bukus` | Metadata paginasi, query search/sort, bebas N+1 query. |
| **04** | `04-api-post-buku.svg` / `.png` | POST Tambah Buku Baru | `POST /api/v1/bukus` | Respon 201 Created + Header `Location`. |
| **05** | `05-api-put-patch-buku.svg` / `.png` | PUT vs PATCH Buku | `PUT` & `PATCH /api/v1/bukus/{id}` | Perbedaan full replacement vs partial modification. |
| **06** | `06-api-sirkulasi-acid.svg` / `.png` | Sirkulasi ACID & Penolakan 422/409 | `POST /api/v1/peminjamans` | Pengujian stok habis (422), pinjam ganda (409), kembali (200). |
| **07** | `07-api-konsumer-eksternal.svg` / `.png` | Konsumer OpenLibrary & GitHub | `GET /bukus/{id}/referensi-eksternal` | Konsumsi REST API pihak ketiga dengan timeout & caching. |

*Catatan: Tersedia berkas placeholder `.svg` terstruktur. Pengembang/mahasiswa dapat menimpa dengan tangkapan layar Postman GUI format `.png` sewaktu-waktu.*
