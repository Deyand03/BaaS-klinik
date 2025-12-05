<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Kunjungan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class OperasionalController extends Controller
{
    /**
     * 1. AMBIL DAFTAR ANTRIAN HARI INI
     */
    public function index(Request $request)
    {
        // Debugging: Cek apa yang dikirim Frontend di file laravel.log
        Log::info('API Antrian Hit. Filters:', $request->all());

        $queryTable = Kunjungan::with(['pasien', 'klinik', 'dokter'])
            ->orderBy('id', 'desc');

        // BEST PRACTICE: Pakai 'filled' (Lebih aman daripada 'has')
        if ($request->filled('status_filter')) {
            $queryTable->where('status', $request->status_filter);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $queryTable->where(function($q) use ($search) {
                // Cari berdasarkan No Antrian
                $q->where('no_antrian', 'like', "%{$search}%")
                  // ATAU cari berdasarkan Nama Pasien (lewat relasi)
                  ->orWhereHas('pasien', function($subQ) use ($search) {
                      $subQ->where('nama_lengkap', 'like', "%{$search}%");
                  });
            });
        }

        $tableData = $queryTable->paginate(10); 
        $queryChart = Kunjungan::with('klinik');

        if ($request->filled('month')) {
            $queryChart->whereMonth('tgl_kunjungan', $request->month);
            // Opsional: Filter tahun juga (default tahun sekarang)
            $year = $request->input('year', date('Y'));
            $queryChart->whereYear('tgl_kunjungan', date('Y'));
        }
        $chartData = $queryChart->get();
        $totalAllTime = Kunjungan::count();

        return response()->json([
            'status' => 'success',
            'table' => $tableData, 
            'chart_source' => $chartData,
            'meta' => [
                'total_all_time' => $totalAllTime
            ]
        ]);
    }


    /**
     * 2. UPDATE STATUS (Estafet)
     * Resepsionis: booking -> menunggu_perawat
     * Perawat: menunggu_perawat -> menunggu_dokter
     * dst.
     */
    public function updateStatus(Request $request, $id)
    {
        $kunjungan = Kunjungan::find($id);

        if (!$kunjungan) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        // Validasi input status
        $request->validate([
            'status' => 'required|in:booking,menunggu_perawat,menunggu_dokter,menunggu_pembayaran,selesai,batal'
        ]);

        // Update Status
        $kunjungan->update([
            'status' => $request->status
        ]);

        return response()->json([
            'message' => 'Status berhasil diperbarui',
            'data' => $kunjungan
        ]);
    }
}
