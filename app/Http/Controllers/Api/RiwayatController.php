<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kunjungan;
use App\Models\Pasien;

class RiwayatController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // 1. Find Patient Data based on User ID
        $pasien = Pasien::where('user_id', $user->id)->first();

        if (!$pasien) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data pasien tidak ditemukan.'
            ], 404);
        }

        // 2. Fetch Visits (Visits)
        // Eager load 'dokter' (from Kunjungan model) and 'klinik'
        $riwayat = Kunjungan::with(['dokter', 'klinik'])
            ->where('id_pasien', $pasien->id)
            ->orderBy('tgl_kunjungan', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $riwayat
        ]);
    }
}
