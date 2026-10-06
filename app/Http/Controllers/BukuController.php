<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BukuController extends Controller
{
    /**
     * Menampilkan daftar buku dengan Eager Loading (with('kategori')),
     * pencarian judul/penulis/ISBN, filter kategori, dan pagination.
     */
    public function index(Request $request): View
    {
        $query = Buku::with('kategori');

        // Pencarian multi-kolom
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('penulis', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%");
            });
        }

        // Filter berdasarkan kategori
        if ($kategoriId = $request->input('kategori_id')) {
            $query->where('kategori_id', $kategoriId);
        }

        $bukus = $query->latest()->paginate(10)->withQueryString();
        $kategoris = Kategori::orderBy('nama_kategori')->get();

        return view('buku.index', compact('bukus', 'kategoris'));
    }

    /**
     * Menampilkan formulir penambahan buku baru.
     */
    public function create(): View
    {
        $kategoris = Kategori::orderBy('nama_kategori')->get();
        return view('buku.create', compact('kategoris'));
    }

    /**
     * Menyimpan data buku baru ke database (Validasi Server-Side Ketat).
     */
    public function store(Request $request): RedirectResponse
    {
        $currentYear = date('Y');

        $validated = $request->validate([
            'isbn' => ['required', 'string', 'max:20', 'unique:bukus,isbn'],
            'judul' => ['required', 'string', 'min:5', 'max:200'],
            'penulis' => ['required', 'string', 'max:150'],
            'penerbit' => ['required', 'string', 'max:100'],
            'tahun_terbit' => ['required', 'integer', "between:1900,{$currentYear}"],
            'kategori_id' => ['required', 'exists:kategoris,id'],
            'stok' => ['required', 'integer', 'min:0'],
            'sinopsis' => ['nullable', 'string', 'max:2000'],
        ], [
            'isbn.required' => 'ISBN wajib diisi.',
            'isbn.unique' => 'Nomor ISBN ini sudah terdaftar di sistem.',
            'judul.required' => 'Judul buku wajib diisi.',
            'judul.min' => 'Judul buku minimal harus 5 karakter.',
            'penulis.required' => 'Nama penulis wajib diisi.',
            'penerbit.required' => 'Nama penerbit wajib diisi.',
            'tahun_terbit.required' => 'Tahun terbit wajib diisi.',
            'tahun_terbit.between' => "Tahun terbit harus antara 1900 hingga {$currentYear}.",
            'kategori_id.required' => 'Kategori buku wajib dipilih.',
            'kategori_id.exists' => 'Kategori yang dipilih tidak valid.',
            'stok.required' => 'Jumlah stok buku wajib diisi.',
            'stok.min' => 'Stok buku tidak boleh kurang dari 0.',
        ]);

        Buku::create($validated);

        return redirect()->route('buku.index')
            ->with('success', 'Buku baru berhasil ditambahkan!');
    }

    /**
     * Menampilkan detail buku beserta kategori dan riwayat peminjaman.
     */
    public function show(Buku $buku): View
    {
        $buku->load(['kategori', 'peminjamans.user']);
        return view('buku.show', compact('buku'));
    }

    /**
     * Menampilkan formulir pengeditan data buku.
     */
    public function edit(Buku $buku): View
    {
        $kategoris = Kategori::orderBy('nama_kategori')->get();
        return view('buku.edit', compact('buku', 'kategoris'));
    }

    /**
     * Memperbarui data buku di database.
     */
    public function update(Request $request, Buku $buku): RedirectResponse
    {
        $currentYear = date('Y');

        $validated = $request->validate([
            'isbn' => [
                'required',
                'string',
                'max:20',
                Rule::unique('bukus', 'isbn')->ignore($buku->id),
            ],
            'judul' => ['required', 'string', 'min:5', 'max:200'],
            'penulis' => ['required', 'string', 'max:150'],
            'penerbit' => ['required', 'string', 'max:100'],
            'tahun_terbit' => ['required', 'integer', "between:1900,{$currentYear}"],
            'kategori_id' => ['required', 'exists:kategoris,id'],
            'stok' => ['required', 'integer', 'min:0'],
            'sinopsis' => ['nullable', 'string', 'max:2000'],
        ], [
            'isbn.required' => 'ISBN wajib diisi.',
            'isbn.unique' => 'Nomor ISBN ini sudah terdaftar untuk buku lain.',
            'judul.required' => 'Judul buku wajib diisi.',
            'judul.min' => 'Judul buku minimal harus 5 karakter.',
            'penulis.required' => 'Nama penulis wajib diisi.',
            'penerbit.required' => 'Nama penerbit wajib diisi.',
            'tahun_terbit.required' => 'Tahun terbit wajib diisi.',
            'tahun_terbit.between' => "Tahun terbit harus antara 1900 hingga {$currentYear}.",
            'kategori_id.required' => 'Kategori buku wajib dipilih.',
            'kategori_id.exists' => 'Kategori yang dipilih tidak valid.',
            'stok.required' => 'Jumlah stok buku wajib diisi.',
            'stok.min' => 'Stok buku tidak boleh kurang dari 0.',
        ]);

        $buku->update($validated);

        return redirect()->route('buku.index')
            ->with('success', 'Data buku berhasil diperbarui!');
    }

    /**
     * Menghapus buku (dilindungi aturan bisnis jika terdapat riwayat peminjaman).
     */
    public function destroy(Buku $buku): RedirectResponse
    {
        if ($buku->peminjamans()->exists()) {
            return redirect()->route('buku.index')
                ->with('error', "Buku '{$buku->judul}' tidak dapat dihapus karena memiliki riwayat data peminjaman di sistem.");
        }

        $judul = $buku->judul;
        $buku->delete();

        return redirect()->route('buku.index')
            ->with('success', "Buku '{$judul}' berhasil dihapus!");
    }
}
