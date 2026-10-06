<?php

namespace Database\Seeders;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Akun Admin Default
        $admin = User::firstOrCreate(
            ['email' => 'admin@del.ac.id'],
            [
                'name' => 'Administrator Perpustakaan Del',
                'role' => 'admin',
                'password' => Hash::make(config('perpustakaan.admin_default_password', 'admin123')),
            ]
        );

        // 2. Akun Mahasiswa Default (Termasuk Lucas Pardede NIM 12S24015)
        $mhsLucas = User::firstOrCreate(
            ['email' => 'lucas.pardede@del.ac.id'],
            [
                'name' => 'Lucas Pardede',
                'nim' => '12S24015',
                'role' => 'mahasiswa',
                'password' => Hash::make('password123'),
            ]
        );

        $mhsBudi = User::firstOrCreate(
            ['email' => 'budi.santoso@del.ac.id'],
            [
                'name' => 'Budi Santoso',
                'nim' => '12S24001',
                'role' => 'mahasiswa',
                'password' => Hash::make('password123'),
            ]
        );

        $mhsMaria = User::firstOrCreate(
            ['email' => 'maria.simanjuntak@del.ac.id'],
            [
                'name' => 'Maria Simanjuntak',
                'nim' => '12S24002',
                'role' => 'mahasiswa',
                'password' => Hash::make('password123'),
            ]
        );

        $mhsJohannes = User::firstOrCreate(
            ['email' => 'johannes.hutapea@del.ac.id'],
            [
                'name' => 'Johannes Hutapea',
                'nim' => '12S24003',
                'role' => 'mahasiswa',
                'password' => Hash::make('password123'),
            ]
        );

        // 3. Kategori Koleksi Perpustakaan (Minimal 5, disediakan 6 kategori tematik)
        $kategoriData = [
            [
                'kode_kategori' => 'IF-WEB',
                'nama_kategori' => 'Pemrograman Web & Arsitektur Perangkat Lunak',
            ],
            [
                'kode_kategori' => 'IF-DB',
                'nama_kategori' => 'Basis Data & Rekayasa Sistem Data',
            ],
            [
                'kode_kategori' => 'SI-BIS',
                'nama_kategori' => 'Sistem Informasi & Manajemen TI',
            ],
            [
                'kode_kategori' => 'CS-SEC',
                'nama_kategori' => 'Keamanan Siber & Jaringan Komputer',
            ],
            [
                'kode_kategori' => 'AI-ML',
                'nama_kategori' => 'Kecerdasan Buatan & Pembelajaran Mesin',
            ],
            [
                'kode_kategori' => 'LIT-FIK',
                'nama_kategori' => 'Sains Terapan & Pengembangan Diri',
            ],
        ];

        $kategoris = [];
        foreach ($kategoriData as $k) {
            $kategoris[$k['kode_kategori']] = Kategori::firstOrCreate(
                ['kode_kategori' => $k['kode_kategori']],
                ['nama_kategori' => $k['nama_kategori']]
            );
        }

        // 4. Koleksi Buku Realistis (Minimal 20 buku realistis)
        $bukuData = [
            // IF-WEB
            [
                'isbn' => '978-602-03-8811-1',
                'judul' => 'Clean Code: A Handbook of Agile Software Craftsmanship',
                'penulis' => 'Robert C. Martin',
                'penerbit' => 'Prentice Hall',
                'tahun_terbit' => 2018,
                'kategori_id' => $kategoris['IF-WEB']->id,
                'stok' => 5,
                'sinopsis' => 'Panduan komprehensif menulis kode yang bersih, mudah dipelihara, dan berstandar profesional.',
            ],
            [
                'isbn' => '978-013-235088-4',
                'judul' => 'Design Patterns: Elements of Reusable Object-Oriented Software',
                'penulis' => 'Erich Gamma, Richard Helm, Ralph Johnson, John Vlissides',
                'penerbit' => 'Addison-Wesley',
                'tahun_terbit' => 2016,
                'kategori_id' => $kategoris['IF-WEB']->id,
                'stok' => 4,
                'sinopsis' => 'Buku klasik pola desain Gang of Four untuk arsitektur perangkat lunak berbasis objek.',
            ],
            [
                'isbn' => '978-149-191866-1',
                'judul' => 'Learning PHP, MySQL & JavaScript: With jQuery, CSS & HTML5',
                'penulis' => 'Robin Nixon',
                'penerbit' => "O'Reilly Media",
                'tahun_terbit' => 2021,
                'kategori_id' => $kategoris['IF-WEB']->id,
                'stok' => 6,
                'sinopsis' => 'Membangun aplikasi web interaktif berbasis tumpukan teknologi modern.',
            ],
            [
                'isbn' => '978-149-207721-3',
                'judul' => 'Laravel: Up & Running: A Framework for Building Modern PHP Apps',
                'penulis' => 'Matt Stauffer',
                'penerbit' => "O'Reilly Media",
                'tahun_terbit' => 2023,
                'kategori_id' => $kategoris['IF-WEB']->id,
                'stok' => 8,
                'sinopsis' => 'Eksplorasi mendalam ekosistem Laravel, Eloquent ORM, Blade, Routing, dan pengujian aplikasi.',
            ],

            // IF-DB
            [
                'isbn' => '978-007-352332-3',
                'judul' => 'Database System Concepts (Seventh Edition)',
                'penulis' => 'Abraham Silberschatz, Henry F. Korth, S. Sudarshan',
                'penerbit' => 'McGraw-Hill Education',
                'tahun_terbit' => 2020,
                'kategori_id' => $kategoris['IF-DB']->id,
                'stok' => 7,
                'sinopsis' => 'Buku teks standar perancangan relasional basis data, normalisasi, dan transaksi ACID.',
            ],
            [
                'isbn' => '978-144-937332-0',
                'judul' => 'Designing Data-Intensive Applications',
                'penulis' => 'Martin Kleppmann',
                'penerbit' => "O'Reilly Media",
                'tahun_terbit' => 2017,
                'kategori_id' => $kategoris['IF-DB']->id,
                'stok' => 3,
                'sinopsis' => 'Prinsip fundamental keandalan, skalabilitas, dan pemeliharaan sistem terdistribusi modern.',
            ],
            [
                'isbn' => '978-149-195424-9',
                'judul' => 'High Performance MySQL: Optimization, Backups, and Replication',
                'penulis' => 'Silvia Botros, Jeremy Tinley',
                'penerbit' => "O'Reilly Media",
                'tahun_terbit' => 2021,
                'kategori_id' => $kategoris['IF-DB']->id,
                'stok' => 2,
                'sinopsis' => 'Teknik optimasi indeks kueri, konkurensi tingkat lanjut, dan replikasi basis data.',
            ],
            [
                'isbn' => '978-161-729452-5',
                'judul' => 'SQL in Action: Real-World Database Manipulation',
                'penulis' => 'Anthony Molinaro',
                'penerbit' => 'Manning Publications',
                'tahun_terbit' => 2022,
                'kategori_id' => $kategoris['IF-DB']->id,
                'stok' => 5,
                'sinopsis' => 'Kiat praktis menulis kueri SQL efisien dan menghindari bottleneck kueri lambat.',
            ],

            // SI-BIS
            [
                'isbn' => '978-013-463976-5',
                'judul' => 'Management Information Systems: Managing the Digital Firm',
                'penulis' => 'Kenneth C. Laudon, Jane P. Laudon',
                'penerbit' => 'Pearson',
                'tahun_terbit' => 2022,
                'kategori_id' => $kategoris['SI-BIS']->id,
                'stok' => 4,
                'sinopsis' => 'Bagaimana perusahaan digital memanfaatkan sistem informasi untuk keunggulan kompetitif.',
            ],
            [
                'isbn' => '978-111-908029-9',
                'judul' => 'Systems Analysis and Design (Seventh Edition)',
                'penulis' => 'Alan Dennis, Barbara Haley Wixom, Roberta M. Roth',
                'penerbit' => 'Wiley',
                'tahun_terbit' => 2019,
                'kategori_id' => $kategoris['SI-BIS']->id,
                'stok' => 6,
                'sinopsis' => 'Metodologi analisis kebutuhan bisnis dan perancangan sistem informasi enterprise.',
            ],
            [
                'isbn' => '978-013-444421-5',
                'judul' => 'Enterprise Architecture as Strategy',
                'penulis' => 'Jeanne W. Ross, Peter Weill, David C. Robertson',
                'penerbit' => 'Harvard Business Review Press',
                'tahun_terbit' => 2018,
                'kategori_id' => $kategoris['SI-BIS']->id,
                'stok' => 3,
                'sinopsis' => 'Menyelaraskan kapabilitas teknologi informasi dengan strategi bisnis jangka panjang.',
            ],

            // CS-SEC
            [
                'isbn' => '978-013-359414-0',
                'judul' => 'Computer Networks (Sixth Edition)',
                'penulis' => 'Andrew S. Tanenbaum, David J. Wetherall, Nick Feamster',
                'penerbit' => 'Pearson',
                'tahun_terbit' => 2021,
                'kategori_id' => $kategoris['CS-SEC']->id,
                'stok' => 5,
                'sinopsis' => 'Buku rujukan utama arsitektur lapisan jaringan komputer, routing, protokol TCP/IP.',
            ],
            [
                'isbn' => '978-111-884394-9',
                'judul' => 'Cryptography and Network Security: Principles and Practice',
                'penulis' => 'William Stallings',
                'penerbit' => 'Pearson',
                'tahun_terbit' => 2020,
                'kategori_id' => $kategoris['CS-SEC']->id,
                'stok' => 4,
                'sinopsis' => 'Prinsip kriptografi simetris, asimetris, tanda tangan digital, dan keamanan perimeter.',
            ],
            [
                'isbn' => '978-159-327992-9',
                'judul' => 'The Web Application Hacker Handbook',
                'penulis' => 'Dafydd Stuttard, Marcus Pinto',
                'penerbit' => 'Wiley',
                'tahun_terbit' => 2019,
                'kategori_id' => $kategoris['CS-SEC']->id,
                'stok' => 2,
                'sinopsis' => 'Mendeteksi dan mengeksploitasi celah keamanan web seperti CSRF, XSS, dan SQL Injection.',
            ],

            // AI-ML
            [
                'isbn' => '978-013-604259-4',
                'judul' => 'Artificial Intelligence: A Modern Approach (Fourth Edition)',
                'penulis' => 'Stuart Russell, Peter Norvig',
                'penerbit' => 'Pearson',
                'tahun_terbit' => 2020,
                'kategori_id' => $kategoris['AI-ML']->id,
                'stok' => 5,
                'sinopsis' => 'Teks definitif teori dan aplikasi kecerdasan buatan dari pencarian hingga pembelajaran mesin.',
            ],
            [
                'isbn' => '978-149-203264-9',
                'judul' => 'Hands-On Machine Learning with Scikit-Learn, Keras, and TensorFlow',
                'penulis' => 'Aurélien Géron',
                'penerbit' => "O'Reilly Media",
                'tahun_terbit' => 2022,
                'kategori_id' => $kategoris['AI-ML']->id,
                'stok' => 6,
                'sinopsis' => 'Konsep praktis membangun model pembelajaran mesin dan deep neural networks.',
            ],
            [
                'isbn' => '978-026-203561-3',
                'judul' => 'Deep Learning (Adaptive Computation and Machine Learning series)',
                'penulis' => 'Ian Goodfellow, Yoshua Bengio, Aaron Courville',
                'penerbit' => 'MIT Press',
                'tahun_terbit' => 2017,
                'kategori_id' => $kategoris['AI-ML']->id,
                'stok' => 3,
                'sinopsis' => 'Dasar matematika dan algoritma representasi representasi jaringan saraf tiruan.',
            ],

            // LIT-FIK & Pengembangan Diri
            [
                'isbn' => '978-073-521129-2',
                'judul' => 'Atomic Habits: Perubahan Kecil yang Memberikan Hasil Luar Biasa',
                'penulis' => 'James Clear',
                'penerbit' => 'Gramedia Pustaka Utama',
                'tahun_terbit' => 2019,
                'kategori_id' => $kategoris['LIT-FIK']->id,
                'stok' => 7,
                'sinopsis' => 'Strategi membangun kebiasaan baik dan meruntuhkan kebiasaan buruk secara bertahap.',
            ],
            [
                'isbn' => '978-006-231609-7',
                'judul' => 'Sapiens: Riwayat Singkat Umat Manusia',
                'penulis' => 'Yuval Noah Harari',
                'penerbit' => 'KPG (Kepustakaan Populer Gramedia)',
                'tahun_terbit' => 2020,
                'kategori_id' => $kategoris['LIT-FIK']->id,
                'stok' => 5,
                'sinopsis' => 'Penelusuran sejarah evolusi manusia dari kera purba hingga penguasa planet bumi.',
            ],
            [
                'isbn' => '978-150-112402-0',
                'judul' => 'Principles: Life and Work',
                'penulis' => 'Ray Dalio',
                'penerbit' => 'Simon & Schuster',
                'tahun_terbit' => 2017,
                'kategori_id' => $kategoris['LIT-FIK']->id,
                'stok' => 4,
                'sinopsis' => 'Prinsip kepemimpinan, pengambilan keputusan radikal berbasis transparansi dan data.',
            ],
            [
                'isbn' => '978-020-161622-4',
                'judul' => 'The Pragmatic Programmer: Your Journey To Mastery',
                'penulis' => 'David Thomas, Andrew Hunt',
                'penerbit' => 'Addison-Wesley',
                'tahun_terbit' => 2019,
                'kategori_id' => $kategoris['IF-WEB']->id,
                'stok' => 1, // Stok menipis untuk pengujian badge
                'sinopsis' => 'Tips esensial profesional perangkat lunak untuk menjaga kode tetap fleksibel dan beradaptasi.',
            ],
        ];

        $createdBukus = [];
        foreach ($bukuData as $b) {
            $createdBukus[] = Buku::firstOrCreate(
                ['isbn' => $b['isbn']],
                $b
            );
        }

        // 5. Mock Transaksi Peminjaman untuk Demonstrasi Fitur & Laporan
        $now = Carbon::now();

        // Kasus 1: Mahasiswa Lucas meminjam buku Clean Code (Masih aktif, tepat waktu)
        Peminjaman::create([
            'user_id' => $mhsLucas->id,
            'buku_id' => $createdBukus[0]->id,
            'tanggal_pinjam' => $now->copy()->subDays(2)->toDateString(),
            'jatuh_tempo' => $now->copy()->addDays(5)->toDateString(),
            'status' => 'dipinjam',
            'perpanjangan' => 0,
            'denda' => 0,
            'denda_lunas' => false,
        ]);

        // Kasus 2: Mahasiswa Budi meminjam Database System Concepts (Terlambat 3 hari!)
        Peminjaman::create([
            'user_id' => $mhsBudi->id,
            'buku_id' => $createdBukus[4]->id,
            'tanggal_pinjam' => $now->copy()->subDays(10)->toDateString(),
            'jatuh_tempo' => $now->copy()->subDays(3)->toDateString(),
            'status' => 'dipinjam',
            'perpanjangan' => 0,
            'denda' => 0,
            'denda_lunas' => false,
        ]);

        // Kasus 3: Mahasiswa Maria telah mengembalikan buku tepat waktu (Selesai, denda 0, lunas)
        Peminjaman::create([
            'user_id' => $mhsMaria->id,
            'buku_id' => $createdBukus[3]->id,
            'tanggal_pinjam' => $now->copy()->subDays(14)->toDateString(),
            'jatuh_tempo' => $now->copy()->subDays(7)->toDateString(),
            'tanggal_kembali' => $now->copy()->subDays(8)->toDateString(),
            'status' => 'dikembalikan',
            'perpanjangan' => 0,
            'denda' => 0,
            'denda_lunas' => true,
        ]);

        // Kasus 4: Mahasiswa Johannes telah mengembalikan terlambat 2 hari, denda Rp2.000 (Belum Lunas)
        Peminjaman::create([
            'user_id' => $mhsJohannes->id,
            'buku_id' => $createdBukus[8]->id,
            'tanggal_pinjam' => $now->copy()->subDays(15)->toDateString(),
            'jatuh_tempo' => $now->copy()->subDays(8)->toDateString(),
            'tanggal_kembali' => $now->copy()->subDays(6)->toDateString(),
            'status' => 'dikembalikan',
            'perpanjangan' => 0,
            'denda' => 2000,
            'denda_lunas' => false,
        ]);
    }
}
