<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KategoriController extends Controller
{
    /**
     * Menampilkan daftar semua kategori (dengan hitungan buku dan pagination).
     */
    public function index(): View
    {
        $kategoris = Kategori::withCount('bukus')->latest()->paginate(10);
        return view('kategori.index', compact('kategoris'));
    }

    /**
     * Menampilkan formulir penambahan kategori baru.
     */
    public function create(): View
    {
        return view('kategori.create');
    }

    /**
     * Menyimpan data kategori baru ke database (Validasi Server-Side Ketat).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode_kategori' => ['required', 'string', 'max:10', 'unique:kategoris,kode_kategori'],
            'nama_kategori' => ['required', 'string', 'min:3', 'max:100'],
        ], [
            'kode_kategori.required' => 'Kode kategori wajib diisi.',
            'kode_kategori.unique' => 'Kode kategori sudah digunakan.',
            'kode_kategori.max' => 'Kode kategori maksimal 10 karakter.',
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.min' => 'Nama kategori minimal 3 karakter.',
            'nama_kategori.max' => 'Nama kategori maksimal 100 karakter.',
        ]);

        Kategori::create($validated);

        return redirect()->route('kategori.index')
            ->with('success', 'Kategori berhasil ditambahkan!');
    }

    /**
     * Menampilkan detail kategori dan buku-buku yang ada di dalamnya.
     */
    public function show(Kategori $kategori): View
    {
        $kategori->load(['bukus' => function ($query) {
            $query->latest();
        }]);

        return view('kategori.show', compact('kategori'));
    }

    /**
     * Menampilkan formulir pengeditan data kategori.
     */
    public function edit(Kategori $kategori): View
    {
        return view('kategori.edit', compact('kategori'));
    }

    /**
     * Memperbarui data kategori di database.
     */
    public function update(Request $request, Kategori $kategori): RedirectResponse
    {
        $validated = $request->validate([
            'kode_kategori' => [
                'required',
                'string',
                'max:10',
                Rule::unique('kategoris', 'kode_kategori')->ignore($kategori->id),
            ],
            'nama_kategori' => ['required', 'string', 'min:3', 'max:100'],
        ], [
            'kode_kategori.required' => 'Kode kategori wajib diisi.',
            'kode_kategori.unique' => 'Kode kategori sudah digunakan oleh kategori lain.',
            'kode_kategori.max' => 'Kode kategori maksimal 10 karakter.',
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.min' => 'Nama kategori minimal 3 karakter.',
            'nama_kategori.max' => 'Nama kategori maksimal 100 karakter.',
        ]);

        $kategori->update($validated);

        return redirect()->route('kategori.index')
            ->with('success', 'Kategori berhasil diperbarui!');
    }

    /**
     * Menghapus kategori (dilindungi aturan bisnis jika masih ada buku).
     */
    public function destroy(Kategori $kategori): RedirectResponse
    {
        $jumlahBuku = $kategori->bukus()->count();

        if ($jumlahBuku > 0) {
            return redirect()->route('kategori.index')
                ->with('error', "Kategori '{$kategori->nama_kategori}' tidak dapat dihapus karena masih memuat {$jumlahBuku} buku. Silakan pindahkan atau hapus buku terkait terlebih dahulu.");
        }

        $nama = $kategori->nama_kategori;
        $kategori->delete();

        return redirect()->route('kategori.index')
            ->with('success', "Kategori '{$nama}' berhasil dihapus!");
    }
}
