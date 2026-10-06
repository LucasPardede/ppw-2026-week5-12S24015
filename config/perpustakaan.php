<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Bisnis SIPUS-Del (Sistem Informasi Perpustakaan Kampus Del)
    |--------------------------------------------------------------------------
    | Sesuai Dokumen Spesifikasi Kebutuhan Sistem (SyRS) v2.0
    */

    'lama_pinjam_hari' => env('PERPUSTAKAAN_LAMA_PINJAM', 7),
    'maksimal_pinjam_buku' => env('PERPUSTAKAAN_MAX_PINJAM', 3),
    'tarif_denda_per_hari' => env('PERPUSTAKAAN_TARIF_DENDA', 1000),
    'lama_perpanjang_hari' => env('PERPUSTAKAAN_LAMA_PERPANJANG', 7),
    
    // Mode autentikasi CIS: 'mock' (simulasi) atau 'api'
    'cis_auth_mode' => env('CIS_AUTH_MODE', 'mock'),
    
    // Daftar email dengan hak akses admin pustakawan
    'admin_emails' => array_filter(array_map('trim', explode(',', env('ADMIN_EMAILS', 'admin@del.ac.id,pustakawan@del.ac.id')))),
    
    // Password default admin lokal
    'admin_default_password' => env('ADMIN_DEFAULT_PASSWORD', 'admin123'),
];
