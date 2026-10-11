<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PengumumanController extends Controller
{
    /**
     * Tampilkan daftar pengumuman untuk Admin (Kelola Pengumuman).
     */
    public function indexAdmin(Request $request): View
    {
        $query = Pengumuman::with('author')->latest('is_pinned')->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('konten', 'like', "%{$search}%");
            });
        }

        if ($kategori = $request->input('kategori')) {
            $query->where('kategori', $kategori);
        }

        if ($request->has('status') && $request->input('status') !== '') {
            $query->where('is_aktif', (bool) $request->input('status'));
        }

        $pengumumans = $query->paginate(10)->withQueryString();

        return view('admin.pengumuman.index', compact('pengumumans'));
    }

    /**
     * Form tambah pengumuman baru oleh Admin.
     */
    public function create(): View
    {
        return view('admin.pengumuman.create');
    }

    /**
     * Simpan pengumuman baru oleh Admin.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'judul' => 'required|string|min:5|max:255',
            'konten' => 'required|string|min:10',
            'kategori' => 'required|in:info,penting,layanan,kegiatan',
            'is_pinned' => 'nullable|boolean',
            'is_aktif' => 'nullable|boolean',
        ], [
            'judul.required' => 'Judul pengumuman wajib diisi.',
            'judul.min' => 'Judul pengumuman minimal 5 karakter.',
            'konten.required' => 'Isi konten pengumuman wajib diisi.',
            'konten.min' => 'Isi konten pengumuman minimal 10 karakter.',
            'kategori.required' => 'Pilih kategori pengumuman.',
            'kategori.in' => 'Kategori yang dipilih tidak valid.',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['is_pinned'] = $request->boolean('is_pinned');
        $validated['is_aktif'] = $request->boolean('is_aktif', true);
        $validated['slug'] = Str::slug($validated['judul']) . '-' . Str::random(5);

        Pengumuman::create($validated);

        return redirect()->route('admin.pengumuman.index')
            ->with('success', 'Pengumuman baru berhasil diterbitkan dan siap dilihat mahasiswa.');
    }

    /**
     * Tampilkan detail pengumuman di panel admin.
     */
    public function showAdmin(Pengumuman $pengumuman): View
    {
        $pengumuman->load('author');
        return view('admin.pengumuman.show', compact('pengumuman'));
    }

    /**
     * Form ubah data pengumuman oleh Admin.
     */
    public function edit(Pengumuman $pengumuman): View
    {
        return view('admin.pengumuman.edit', compact('pengumuman'));
    }

    /**
     * Perbarui data pengumuman oleh Admin.
     */
    public function update(Request $request, Pengumuman $pengumuman): RedirectResponse
    {
        $validated = $request->validate([
            'judul' => 'required|string|min:5|max:255',
            'konten' => 'required|string|min:10',
            'kategori' => 'required|in:info,penting,layanan,kegiatan',
            'is_pinned' => 'nullable|boolean',
            'is_aktif' => 'nullable|boolean',
        ], [
            'judul.required' => 'Judul pengumuman wajib diisi.',
            'judul.min' => 'Judul pengumuman minimal 5 karakter.',
            'konten.required' => 'Isi konten pengumuman wajib diisi.',
            'konten.min' => 'Isi konten pengumuman minimal 10 karakter.',
        ]);

        $validated['is_pinned'] = $request->boolean('is_pinned');
        $validated['is_aktif'] = $request->boolean('is_aktif');

        $pengumuman->update($validated);

        return redirect()->route('admin.pengumuman.index')
            ->with('success', 'Pengumuman "' . $pengumuman->judul . '" berhasil diperbarui.');
    }

    /**
     * Hapus pengumuman oleh Admin.
     */
    public function destroy(Pengumuman $pengumuman): RedirectResponse
    {
        $judul = $pengumuman->judul;
        $pengumuman->delete();

        return redirect()->route('admin.pengumuman.index')
            ->with('success', 'Pengumuman "' . $judul . '" berhasil dihapus.');
    }

    /**
     * Toggle sematkan (pinned/unpinned) pengumuman.
     */
    public function togglePin(Pengumuman $pengumuman): RedirectResponse
    {
        $pengumuman->update([
            'is_pinned' => !$pengumuman->is_pinned,
        ]);

        $status = $pengumuman->is_pinned ? 'disematkan di bagian atas' : 'dilepas dari sematan';

        return redirect()->back()
            ->with('success', 'Pengumuman "' . $pengumuman->judul . '" berhasil ' . $status . '.');
    }

    /**
     * Tampilkan daftar pengumuman untuk Portal Mahasiswa.
     */
    public function indexMahasiswa(Request $request): View
    {
        $query = Pengumuman::aktif()->with('author')->latest('is_pinned')->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('konten', 'like', "%{$search}%");
            });
        }

        if ($kategori = $request->input('kategori')) {
            $query->where('kategori', $kategori);
        }

        $pengumumans = $query->paginate(6)->withQueryString();
        $pinnedPengumumans = Pengumuman::aktif()->pinned()->latest()->take(3)->get();

        return view('pengumuman.index', compact('pengumumans', 'pinnedPengumumans'));
    }

    /**
     * Tampilkan rincian pengumuman untuk Mahasiswa.
     */
    public function showMahasiswa(Pengumuman $pengumuman): View
    {
        abort_unless($pengumuman->is_aktif, 404);

        $pengumuman->load('author');
        $recentPengumumans = Pengumuman::aktif()
            ->where('id', '!=', $pengumuman->id)
            ->latest('is_pinned')
            ->latest()
            ->take(4)
            ->get();

        return view('pengumuman.show', compact('pengumuman', 'recentPengumumans'));
    }
}
