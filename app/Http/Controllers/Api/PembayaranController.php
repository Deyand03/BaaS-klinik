<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pembayaran;
use App\Models\Kunjungan;
use Illuminate\Support\Facades\DB;

class PembayaranController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validasi
        $request->validate([
            'id_kunjungan' => 'required|exists:kunjungan,id',
            'id_staff'     => 'required|exists:staff,id',
            'total_biaya'  => 'required|numeric',
            'metode_bayar' => 'required|in:cash,qris,transfer,ewallet',
        ]);

        DB::beginTransaction();
        try {
            // 2. Simpan ke Tabel Pembayaran
            $pembayaran = Pembayaran::create([
                'id_kunjungan' => $request->id_kunjungan,
                'id_staff'     => $request->id_staff,
                'total_biaya'  => $request->total_biaya,
                'metode_bayar' => $request->metode_bayar,
                'status'       => 'sudah_bayar'
            ]);

            // 3. Update Status Kunjungan jadi 'selesai'
            // Agar tidak muncul lagi di dashboard dokter/kasir
            Kunjungan::where('id', $request->id_kunjungan)->update(['status' => 'selesai']);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Pembayaran berhasil disimpan',
                'data' => $pembayaran
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error', 
                'message' => 'Gagal menyimpan: ' . $e->getMessage()
            ], 500);
        }
    }
}