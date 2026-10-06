<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KatalogController extends Controller
{
    /**
     * Menampilkan katalog buku untuk portal mahasiswa
     * dengan Eager Loading with('kategori'), filter kategori, pencarian, dan pagination.
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
        $kategoris = Kategori::orderBy('nama_kategori')->get();

        return view('katalog.index', compact('bukus', 'kategoris'));
    }

    /**
     * Menampilkan detail buku di portal mahasiswa.
     */
    public function show(Buku $buku): View
    {
        $buku->load('kategori');
        return view('katalog.show', compact('buku'));
    }
}
