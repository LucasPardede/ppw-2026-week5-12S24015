-- =====================================================================
-- SIPUS-DEL: SKRIP PEMBUKTIAN INTEGRITAS DATA & TRANSAKSI ACID (DBeaver)
-- Modul Praktikum Minggu 6: Database Transaction, Session & Cookie
-- Institut Teknologi Del
-- =====================================================================

-- 1. PEMBUKTIAN KONSISTENSI STOK DAN TIDAK ADA STOK NEGATIF
-- Hasil yang diharapkan: Tidak ada baris yang dikembalikan (count = 0).
SELECT 
    id, 
    isbn, 
    judul, 
    stok, 
    CASE 
        WHEN stok < 0 THEN 'ANOMALI: STOK NEGATIF (FAIL)'
        WHEN stok = 0 THEN 'VALID: STOK HABIS'
        ELSE 'VALID: STOK TERSEDIA'
    END AS status_audit
FROM bukus
WHERE stok < 0;

-- 2. PEMBUKTIAN KONSISTENSI STOK TERHADAP PINJAMAN AKTIF
-- Rumus: stok_saat_ini = stok_awal_koleksi - jumlah_pinjaman_aktif
-- Hasil: Periksa buku stok 0 (The Pragmatic Programmer) dan stok lainnya.
SELECT 
    b.id,
    b.isbn,
    b.judul,
    b.stok AS stok_tersedia_di_rak,
    COUNT(p.id) AS total_eksemplar_dipinjam,
    CASE 
        WHEN b.stok = 0 AND COUNT(p.id) > 0 THEN 'KONSISTEN (Seluruh eksemplar sedang dipinjam)'
        WHEN b.stok > 0 THEN 'KONSISTEN (Stok tersedia di perpustakaan)'
        ELSE 'KONSISTEN'
    END AS status_konsistensi
FROM bukus b
LEFT JOIN peminjamans p ON b.id = p.buku_id AND p.status = 'dipinjam'
GROUP BY b.id, b.isbn, b.judul, b.stok
ORDER BY b.stok ASC, total_eksemplar_dipinjam DESC;

-- 3. AUDIT SKEMA TABEL PEMINJAMANS (Struktur Kolom, Index, dan FK Cascade)
-- Memastikan kode_pinjam, tanggal_jatuh_tempo, nim_peminjam, nama_peminjam terpasang.
DESCRIBE peminjamans;

-- Menampilkan Foreign Key dan Aturan ON DELETE CASCADE pada tabel peminjamans
SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'peminjamans'
  AND REFERENCED_TABLE_NAME IS NOT NULL;

-- 4. PEMERIKSAAN LOG AUDIT TRANSAKSI ACID (Tabel riwayat_transaksis)
-- Memastikan setiap aksi mutasi (pinjam, kembali, perpanjang, lunas) tercatat rapi
SELECT 
    rt.id,
    rt.aksi,
    rt.keterangan,
    u.name AS nama_aktor,
    u.role AS peran_aktor,
    rt.created_at AS waktu_eksekusi
FROM riwayat_transaksis rt
LEFT JOIN users u ON rt.aktor_user_id = u.id
ORDER BY rt.id DESC
LIMIT 15;

-- 5. PEMERIKSAAN KELENGKAPAN AKUN UJI (Pustakawan Del & Mahasiswa)
SELECT 
    id, 
    name, 
    email, 
    nim, 
    role, 
    created_at 
FROM users 
WHERE email IN (
    'pustakawan@del.ac.id',
    'admin@del.ac.id',
    'lucas.pardede@del.ac.id',
    'budi.santoso@del.ac.id'
);
