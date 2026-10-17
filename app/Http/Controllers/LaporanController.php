<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanController extends Controller
{
    /**
     * Menampilkan laporan peminjaman dengan filter rentang tanggal dan status.
     */
    public function index(Request $request): View
    {
        $query = $this->buildReportQuery($request);
        $peminjamans = $query->paginate(15)->withQueryString();

        $totalDenda = $this->buildReportQuery($request)->sum('denda');

        return view('admin.laporan.index', compact('peminjamans', 'totalDenda'));
    }

    /**
     * Ekspor data laporan peminjaman ke format berkas CSV.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $peminjamans = $this->buildReportQuery($request)->get();
        $fileName = 'laporan-peminjaman-' . date('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($peminjamans) {
            $handle = fopen('php://output', 'w');
            // Menulis BOM UTF-8 agar karakter rapi di Excel
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'ID Transaksi',
                'Nama Mahasiswa',
                'NIM',
                'Email',
                'Judul Buku',
                'ISBN',
                'Tanggal Pinjam',
                'Jatuh Tempo',
                'Tanggal Kembali',
                'Status',
                'Denda (Rp)',
                'Status Denda',
            ]);

            /** @var Peminjaman $p */
            foreach ($peminjamans as $p) {
                $statusText = $p->status === 'dipinjam'
                    ? ($p->is_terlambat ? "Terlambat ({$p->hari_terlambat} hari)" : 'Dipinjam')
                    : 'Dikembalikan';

                $dendaStatus = $p->denda > 0
                    ? ($p->denda_lunas ? 'Lunas' : 'Belum Lunas')
                    : '-';

                fputcsv($handle, [
                    $p->id,
                    $p->user->name ?? '-',
                    $p->user->nim ?? '-',
                    $p->user->email ?? '-',
                    $p->buku->judul ?? '-',
                    $p->buku->isbn ?? '-',
                    $p->tanggal_pinjam ? Carbon::parse($p->tanggal_pinjam)->format('Y-m-d') : '-',
                    $p->tanggal_jatuh_tempo ? Carbon::parse($p->tanggal_jatuh_tempo)->format('Y-m-d') : '-',
                    $p->tanggal_kembali ? Carbon::parse($p->tanggal_kembali)->format('Y-m-d') : '-',
                    $statusText,
                    $p->denda_efektif,
                    $dendaStatus,
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Membangun kueri laporan peminjaman terpadu dengan Eager Loading.
     */
    protected function buildReportQuery(Request $request)
    {
        $query = Peminjaman::with(['buku.kategori', 'user'])->latest();

        if ($dari = $request->input('dari_tanggal')) {
            $query->whereDate('tanggal_pinjam', '>=', $dari);
        }

        if ($sampai = $request->input('sampai_tanggal')) {
            $query->whereDate('tanggal_pinjam', '<=', $sampai);
        }

        $status = $request->input('status');
        if ($status === 'dipinjam') {
            $query->where('status', 'dipinjam');
        } elseif ($status === 'terlambat') {
            $today = now()->startOfDay()->toDateString();
            $query->where('status', 'dipinjam')->where('tanggal_jatuh_tempo', '<', $today);
        } elseif ($status === 'dikembalikan') {
            $query->where('status', 'dikembalikan');
        }

        return $query;
    }
}
