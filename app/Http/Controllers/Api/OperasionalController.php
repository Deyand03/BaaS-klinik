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

        $query = Kunjungan::with(['pasien', 'dokter', 'klinik'])
            ->orderBy('id', 'desc');

        // BEST PRACTICE: Pakai 'filled' (Lebih aman daripada 'has')
        if ($request->filled('status_filter')) {
            $query->where('status', $request->status_filter);
        }

        $antrian = $query->get();

        return response()->json([
            'status' => 'success',
            'data' => $antrian
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
