<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MahasiswaController extends Controller
{
    /**
     * Admin: Menampilkan daftar seluruh mahasiswa beserta ringkasan status pinjaman.
     */
    public function index(Request $request): View
    {
        $query = User::where('role', 'mahasiswa')
            ->withCount([
                'peminjamans as pinjaman_aktif_count' => function ($q) {
                    $q->where('status', 'dipinjam');
                },
                'peminjamans as total_pinjam_count',
            ]);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nim', 'like', "%{$search}%");
            });
        }

        $mahasiswas = $query->latest()->paginate(10)->withQueryString();

        return view('admin.mahasiswa.index', compact('mahasiswas'));
    }

    /**
     * Admin: Menampilkan detail riwayat peminjaman per mahasiswa.
     */
    public function show(User $user): View
    {
        if ($user->role !== 'mahasiswa') {
            abort(404);
        }

        $user->load([
            'peminjamans' => function ($query) {
                $query->with('buku.kategori')->latest();
            }
        ]);

        return view('admin.mahasiswa.show', compact('user'));
    }
}
