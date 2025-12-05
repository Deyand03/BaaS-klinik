<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kunjungan;
use App\Models\RekamMedis;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class PerawatController extends Controller
{
    public function index()
    {
        $kunjungan = Kunjungan::with('pasien') // relasi ke model Pasien
            ->where('status', 'menunggu_perawat')
            ->get()
            ->map(function ($item) {
                $usia = Carbon::parse($item->pasien->tgl_lahir)->age;
                return [
                    'id' => $item->id,
                    'no_antrian' => $item->no_antrian,
                    'keluhan' => $item->keluhan,
                    'nama_lengkap' => $item->pasien->nama_lengkap,
                    'jenis_kelamin' => $item->pasien->jenis_kelamin,
                    'usia' => $usia . ' Thn',
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $kunjungan
        ]);
    }

    public function storeVital(Request $request, $id)
    {
        // Validasi input tanpa id karena sudah ada di URL
        $request->validate([
            'berat_badan' => 'required',
            'tensi_darah' => 'required',
            'suhu_badan' => 'required',
            'anamnesa' => 'required',
        ]);

        // Pastikan kunjungan valid
        $kunjungan = Kunjungan::find($id);
        if (!$kunjungan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kunjungan tidak ditemukan'
            ], 404);
        }

        DB::beginTransaction();
        try {
            // 1. Insert rekam medis
            RekamMedis::create([
                'id_kunjungan' => $id,
                'berat_badan' => $request->berat_badan,
                'tensi_darah' => $request->tensi_darah,
                'suhu_badan' => $request->suhu_badan,
                'anamnesa' => $request->anamnesa,
            ]);

            // 2. Update status kunjungan
            $kunjungan->update([
                'status' => 'menunggu_dokter'
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Data vital berhasil disimpan!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
