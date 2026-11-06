<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Pengumuman;
use App\Services\StatistikService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KatalogController extends Controller
{
    public function __construct(
        protected StatistikService $statistikService
    ) {}

    /**
     * Menampilkan katalog buku untuk portal mahasiswa
     * dengan Eager Loading with('kategori'), withCount('peminjamans'), filter when(),
     * 4 kartu statistik DB::table(), dan session state pencarian terakhir.
     */
    public function index(Request $request): View
    {
        $query = Buku::with('kategori')->withCount('peminjamans');

        // Filter pencarian multi-kolom dibungkus closure where(...) agar logika AND/OR presisi
        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = trim($request->input('search'));
            // Simpan pencarian terakhir di Session (FITUR 3)
            $request->session()->put('last_search', $search);

            $q->where(function ($sub) use ($search) {
                $sub->where('judul', 'like', "%{$search}%")
                    ->orWhere('penulis', 'like', "%{$search}%")
                    ->orWhere('isbn', 'like', "%{$search}%");
            });
        });

        // Filter kategori
        $query->when($request->filled('kategori_id'), function ($q) use ($request) {
            $q->where('kategori_id', $request->input('kategori_id'));
        });

        $bukus = $query->latest()->paginate(9)->withQueryString();

        // Kategori dengan withCount bukus untuk mencegah N+1
        $kategoris = Kategori::withCount('bukus')->orderBy('nama_kategori')->get();

        // 4 Kartu Statistik Sirkulasi Koleksi menggunakan DB::table() via StatistikService
        $statistik = $this->statistikService->getStatistikKoleksi();

        // Session state pencarian terakhir (FITUR 3)
        $lastSearch = $request->session()->get('last_search');

        // Pengumuman terbaru untuk ticker / hero banner
        $pengumumanTerbaru = Pengumuman::aktif()->latest('is_pinned')->latest()->take(3)->get();

        return view('katalog.index', compact('bukus', 'kategoris', 'pengumumanTerbaru', 'statistik', 'lastSearch'));
    }

    /**
     * Menampilkan detail buku di portal mahasiswa beserta rekomendasi buku serupa.
     */
    public function show(Buku $buku): View
    {
        $buku->load('kategori');

        // Rekomendasi buku dalam kategori yang sama
        $relatedBukus = Buku::with('kategori')
            ->where('kategori_id', $buku->kategori_id)
            ->where('id', '!=', $buku->id)
            ->latest()
            ->take(4)
            ->get();

        return view('katalog.show', compact('buku', 'relatedBukus'));
    }
}
