<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Pengumuman;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KatalogController extends Controller
{
    /**
     * Menampilkan katalog buku untuk portal mahasiswa
     * dengan Eager Loading with('kategori'), filter kategori, pencarian, pagination, dan banner pengumuman.
     */
    public function index(Request $request): View
    {
        $query = Buku::with('kategori');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('penulis', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%");
            });
        }

        if ($kategoriId = $request->input('kategori_id')) {
            $query->where('kategori_id', $kategoriId);
        }

        $bukus = $query->latest()->paginate(9)->withQueryString();
        $kategoris = Kategori::withCount('bukus')->orderBy('nama_kategori')->get();
        
        // Pengumuman terbaru untuk ticker / hero banner
        $pengumumanTerbaru = Pengumuman::aktif()->latest('is_pinned')->latest()->take(3)->get();
        $totalKoleksi = Buku::count();
        $totalTersedia = Buku::where('stok', '>', 0)->count();

        return view('katalog.index', compact('bukus', 'kategoris', 'pengumumanTerbaru', 'totalKoleksi', 'totalTersedia'));
    }

    /**
     * Menampilkan detail buku di portal mahasiswa beserta rekomendasi buku serupa.
     */
    public function show(Buku $buku): View
    {
        $buku->load('kategori');

        // Rekomendasi buku dalam kategori yang sama (Inspirasi perpus.org)
        $relatedBukus = Buku::with('kategori')
            ->where('kategori_id', $buku->kategori_id)
            ->where('id', '!=', $buku->id)
            ->latest()
            ->take(4)
            ->get();

        return view('katalog.show', compact('buku', 'relatedBukus'));
    }
}
