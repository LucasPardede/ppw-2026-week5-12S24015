<?php

namespace Database\Seeders;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Database\Seeder;

class BukuSeeder extends Seeder
{
    /**
     * Membuat koleksi buku perpustakaan secara idempoten (minimal 20 buku realistis).
     * Karakter penulis diperbaiki ke UTF-8 yang benar.
     */
    public function run(): void
    {
        $kategoris = Kategori::pluck('id', 'kode_kategori');

        $bukuData = [
            // IF-WEB (4 buku)
            [
                // [ASUMSI: verifikasi ISBN] ISBN ini bukan ISBN resmi Clean Code (978-0-13-235088-2) tapi dipertahankan dari seeder lama
                'isbn' => '978-602-03-8811-1',
                'judul' => 'Clean Code: A Handbook of Agile Software Craftsmanship',
                'penulis' => 'Robert C. Martin',
                'penerbit' => 'Prentice Hall',
                'tahun_terbit' => 2018,
                'kategori_kode' => 'IF-WEB',
                'stok' => 5,
                'sinopsis' => 'Panduan komprehensif menulis kode yang bersih, mudah dipelihara, dan berstandar profesional.',
            ],
            [
                // [ASUMSI: verifikasi ISBN] ISBN resmi Design Patterns GoF = 978-0-201-63361-0
                'isbn' => '978-013-235088-4',
                'judul' => 'Design Patterns: Elements of Reusable Object-Oriented Software',
                'penulis' => 'Erich Gamma, Richard Helm, Ralph Johnson, John Vlissides',
                'penerbit' => 'Addison-Wesley',
                'tahun_terbit' => 2016,
                'kategori_kode' => 'IF-WEB',
                'stok' => 4,
                'sinopsis' => 'Buku klasik pola desain Gang of Four untuk arsitektur perangkat lunak berbasis objek.',
            ],
            [
                'isbn' => '978-149-191866-1',
                'judul' => 'Learning PHP, MySQL & JavaScript: With jQuery, CSS & HTML5',
                'penulis' => 'Robin Nixon',
                'penerbit' => "O'Reilly Media",
                'tahun_terbit' => 2021,
                'kategori_kode' => 'IF-WEB',
                'stok' => 6,
                'sinopsis' => 'Membangun aplikasi web interaktif berbasis tumpukan teknologi modern.',
            ],
            [
                'isbn' => '978-149-207721-3',
                'judul' => 'Laravel: Up & Running: A Framework for Building Modern PHP Apps',
                'penulis' => 'Matt Stauffer',
                'penerbit' => "O'Reilly Media",
                'tahun_terbit' => 2023,
                'kategori_kode' => 'IF-WEB',
                'stok' => 8,
                'sinopsis' => 'Eksplorasi mendalam ekosistem Laravel, Eloquent ORM, Blade, Routing, dan pengujian aplikasi.',
            ],

            // IF-DB (4 buku)
            [
                'isbn' => '978-007-352332-3',
                'judul' => 'Database System Concepts (Seventh Edition)',
                'penulis' => 'Abraham Silberschatz, Henry F. Korth, S. Sudarshan',
                'penerbit' => 'McGraw-Hill Education',
                'tahun_terbit' => 2020,
                'kategori_kode' => 'IF-DB',
                'stok' => 7,
                'sinopsis' => 'Buku teks standar perancangan relasional basis data, normalisasi, dan transaksi ACID.',
            ],
            [
                'isbn' => '978-144-937332-0',
                'judul' => 'Designing Data-Intensive Applications',
                'penulis' => 'Martin Kleppmann',
                'penerbit' => "O'Reilly Media",
                'tahun_terbit' => 2017,
                'kategori_kode' => 'IF-DB',
                'stok' => 3,
                'sinopsis' => 'Prinsip fundamental keandalan, skalabilitas, dan pemeliharaan sistem terdistribusi modern.',
            ],
            [
                'isbn' => '978-149-195424-9',
                'judul' => 'High Performance MySQL: Optimization, Backups, and Replication',
                'penulis' => 'Silvia Botros, Jeremy Tinley',
                'penerbit' => "O'Reilly Media",
                'tahun_terbit' => 2021,
                'kategori_kode' => 'IF-DB',
                'stok' => 2,
                'sinopsis' => 'Teknik optimasi indeks kueri, konkurensi tingkat lanjut, dan replikasi basis data.',
            ],
            [
                'isbn' => '978-161-729452-5',
                'judul' => 'SQL in Action: Real-World Database Manipulation',
                'penulis' => 'Anthony Molinaro',
                'penerbit' => 'Manning Publications',
                'tahun_terbit' => 2022,
                'kategori_kode' => 'IF-DB',
                'stok' => 5,
                'sinopsis' => 'Kiat praktis menulis kueri SQL efisien dan menghindari bottleneck kueri lambat.',
            ],

            // SI-BIS (3 buku)
            [
                'isbn' => '978-013-463976-5',
                'judul' => 'Management Information Systems: Managing the Digital Firm',
                'penulis' => 'Kenneth C. Laudon, Jane P. Laudon',
                'penerbit' => 'Pearson',
                'tahun_terbit' => 2022,
                'kategori_kode' => 'SI-BIS',
                'stok' => 4,
                'sinopsis' => 'Bagaimana perusahaan digital memanfaatkan sistem informasi untuk keunggulan kompetitif.',
            ],
            [
                'isbn' => '978-111-908029-9',
                'judul' => 'Systems Analysis and Design (Seventh Edition)',
                'penulis' => 'Alan Dennis, Barbara Haley Wixom, Roberta M. Roth',
                'penerbit' => 'Wiley',
                'tahun_terbit' => 2019,
                'kategori_kode' => 'SI-BIS',
                'stok' => 6,
                'sinopsis' => 'Metodologi analisis kebutuhan bisnis dan perancangan sistem informasi enterprise.',
            ],
            [
                'isbn' => '978-013-444421-5',
                'judul' => 'Enterprise Architecture as Strategy',
                'penulis' => 'Jeanne W. Ross, Peter Weill, David C. Robertson',
                'penerbit' => 'Harvard Business Review Press',
                'tahun_terbit' => 2018,
                'kategori_kode' => 'SI-BIS',
                'stok' => 3,
                'sinopsis' => 'Menyelaraskan kapabilitas teknologi informasi dengan strategi bisnis jangka panjang.',
            ],

            // CS-SEC (3 buku)
            [
                'isbn' => '978-013-359414-0',
                'judul' => 'Computer Networks (Sixth Edition)',
                'penulis' => 'Andrew S. Tanenbaum, David J. Wetherall, Nick Feamster',
                'penerbit' => 'Pearson',
                'tahun_terbit' => 2021,
                'kategori_kode' => 'CS-SEC',
                'stok' => 5,
                'sinopsis' => 'Buku rujukan utama arsitektur lapisan jaringan komputer, routing, protokol TCP/IP.',
            ],
            [
                'isbn' => '978-111-884394-9',
                'judul' => 'Cryptography and Network Security: Principles and Practice',
                'penulis' => 'William Stallings',
                'penerbit' => 'Pearson',
                'tahun_terbit' => 2020,
                'kategori_kode' => 'CS-SEC',
                'stok' => 4,
                'sinopsis' => 'Prinsip kriptografi simetris, asimetris, tanda tangan digital, dan keamanan perimeter.',
            ],
            [
                'isbn' => '978-159-327992-9',
                'judul' => 'The Web Application Hacker Handbook',
                'penulis' => 'Dafydd Stuttard, Marcus Pinto',
                'penerbit' => 'Wiley',
                'tahun_terbit' => 2019,
                'kategori_kode' => 'CS-SEC',
                'stok' => 1,
                'sinopsis' => 'Mendeteksi dan mengeksploitasi celah keamanan web seperti CSRF, XSS, dan SQL Injection.',
            ],

            // AI-ML (3 buku)
            [
                'isbn' => '978-013-604259-4',
                'judul' => 'Artificial Intelligence: A Modern Approach (Fourth Edition)',
                'penulis' => 'Stuart Russell, Peter Norvig',
                'penerbit' => 'Pearson',
                'tahun_terbit' => 2020,
                'kategori_kode' => 'AI-ML',
                'stok' => 5,
                'sinopsis' => 'Teks definitif teori dan aplikasi kecerdasan buatan dari pencarian hingga pembelajaran mesin.',
            ],
            [
                // Perbaikan UTF-8: "Aurélien Géron" (bukan "AurÃ©lien GÃ©ron")
                'isbn' => '978-149-203264-9',
                'judul' => 'Hands-On Machine Learning with Scikit-Learn, Keras, and TensorFlow',
                'penulis' => 'Aurélien Géron',
                'penerbit' => "O'Reilly Media",
                'tahun_terbit' => 2022,
                'kategori_kode' => 'AI-ML',
                'stok' => 6,
                'sinopsis' => 'Konsep praktis membangun model pembelajaran mesin dan deep neural networks.',
            ],
            [
                'isbn' => '978-026-203561-3',
                'judul' => 'Deep Learning (Adaptive Computation and Machine Learning series)',
                'penulis' => 'Ian Goodfellow, Yoshua Bengio, Aaron Courville',
                'penerbit' => 'MIT Press',
                'tahun_terbit' => 2017,
                'kategori_kode' => 'AI-ML',
                'stok' => 3,
                'sinopsis' => 'Dasar matematika dan algoritma representasi jaringan saraf tiruan.',
            ],

            // LIT-FIK (4 buku)
            [
                'isbn' => '978-073-521129-2',
                'judul' => 'Atomic Habits: Perubahan Kecil yang Memberikan Hasil Luar Biasa',
                'penulis' => 'James Clear',
                'penerbit' => 'Gramedia Pustaka Utama',
                'tahun_terbit' => 2019,
                'kategori_kode' => 'LIT-FIK',
                'stok' => 7,
                'sinopsis' => 'Strategi membangun kebiasaan baik dan meruntuhkan kebiasaan buruk secara bertahap.',
            ],
            [
                'isbn' => '978-006-231609-7',
                'judul' => 'Sapiens: Riwayat Singkat Umat Manusia',
                'penulis' => 'Yuval Noah Harari',
                'penerbit' => 'KPG (Kepustakaan Populer Gramedia)',
                'tahun_terbit' => 2020,
                'kategori_kode' => 'LIT-FIK',
                'stok' => 5,
                'sinopsis' => 'Penelusuran sejarah evolusi manusia dari kera purba hingga penguasa planet bumi.',
            ],
            [
                'isbn' => '978-150-112402-0',
                'judul' => 'Principles: Life and Work',
                'penulis' => 'Ray Dalio',
                'penerbit' => 'Simon & Schuster',
                'tahun_terbit' => 2017,
                'kategori_kode' => 'LIT-FIK',
                'stok' => 4,
                'sinopsis' => 'Prinsip kepemimpinan, pengambilan keputusan radikal berbasis transparansi dan data.',
            ],
            [
                // Stok 1 — untuk pengujian badge "menipis"
                'isbn' => '978-020-161622-4',
                'judul' => 'The Pragmatic Programmer: Your Journey To Mastery',
                'penulis' => 'David Thomas, Andrew Hunt',
                'penerbit' => 'Addison-Wesley',
                'tahun_terbit' => 2019,
                'kategori_kode' => 'IF-WEB',
                'stok' => 1,
                'sinopsis' => 'Tips esensial profesional perangkat lunak untuk menjaga kode tetap fleksibel dan beradaptasi.',
            ],
        ];

        foreach ($bukuData as $b) {
            $kategoriId = $kategoris[$b['kategori_kode']] ?? null;
            unset($b['kategori_kode']);
            $b['kategori_id'] = $kategoriId;
            Buku::firstOrCreate(['isbn' => $b['isbn']], $b);
        }
    }
}
