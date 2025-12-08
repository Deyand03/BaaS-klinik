<?php

namespace App\Http\Controllers\Api;

use App\Models\Kunjungan;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class KasirOperasionalController extends Controller
{
    public function index(Request $request)
    {
        // 1. Query Dasar Kunjungan
        $query = Kunjungan::with(['pasien', 'klinik', 'dokter', 'jadwal', 'pembayaran'])
            ->orderBy('id', 'desc');
        // 2. Filter Status (Wajib untuk Kasir)
        // Jika tidak ada filter, default ke 'menunggu_pembayaran' agar aman
        $status = $request->status_filter ?? 'menunggu_pembayaran';
        $query->where('status', $status);

        // 3. Filter Pencarian (Copy dari OperasionalController agar fitur search jalan)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('no_antrian', 'like', "%{$search}%")
                    ->orWhereHas('pasien', function ($subQ) use ($search) {
                        $subQ->where('nama_lengkap', 'like', "%{$search}%");
                    });
            });
        }

        // 4. EKSEKUSI DATA (Gunakan get() agar SEMUA data muncul)
        $data = $query->get();

        // 5. Return JSON
        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }
}