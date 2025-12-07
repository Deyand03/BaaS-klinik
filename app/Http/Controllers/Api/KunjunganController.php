<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
class KunjunganController extends Controller
{
    public function index(Request $request)
    {
        // $obat = DB::table('obat')->get()
        $listKunjungan = DB::table("kunjungan")
                        ->join('pasiens', 'kunjungan.id_pasien', '=', 'pasiens.id')
                        ->select(
                            'kunjungan.id', 
                            'kunjungan.tgl_kunjungan',
                            'kunjungan.no_antrian',
                            'pasiens.nama_lengkap',
                            'kunjungan.keluhan'
                        )
                        ->where('kunjungan.id_dokter', '=', $request->id_dokter)
                        ->get();
        $obat = DB::table('obat')->get();
        return response()->json([
            'data' => $listKunjungan,
            'obat' => $obat
        ]);
    }
    public function detail(Request $request)
    {
        $detailKunjungan = DB::table("rekam_medis")
                        ->join('kunjungan', 'rekam_medis.id_kunjungan', '=', 'kunjungan.id')
                        ->select(
                            'rekam_medis.id',
                            'rekam_medis.anamnesa',
                            'rekam_medis.tensi_darah',
                            'rekam_medis.suhu_badan',
                            'rekam_medis.berat_badan'
                        )
                        ->where('rekam_medis.id_kunjungan', '=', $request->id)
                        ->first();
        return response()->json([
            'data' => $detailKunjungan,
        ]);
    }

    public function add(Request $request)
    {
        $diagnosa = $request->diagnosa;
        $id_kunjungan = $request->id_kunjungan;
        $jumlah = $request->jumlah;
        $harga = $request->harga;
        $rekam_medis_id = $request->rekam_medis_id;
        $obat = $request->obat;
        $catatan = $request->catatan;
        $id_staff = $request->id_staff;
        $poli = $request->poli ?? '';
        $alasan = $request->alasan ?? '';
        $tujuan = $request->tujuan ?? '';


        DB::beginTransaction();
        try{
            Log::info("Transaksi untuk input resep, pembayaran mulai");

            $insertResep = DB::table('resep')->insert([
                'id_rekam_medis' => $rekam_medis_id,
                'id_obat' => $obat,
                'jumlah' => $jumlah,
                'aturan_pakai' => $catatan,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $insertPembayaran = DB::table('pembayaran')->insert([
                'id_kunjungan' => $id_kunjungan,
                'id_staff' => $id_staff,
                'total_biaya' => $harga,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            if($alasan != null && $poli != null && $tujuan != null){
                DB::table('rujukan')->insert([
                    'id_kunjungan' => $id_kunjungan,
                    'rs_tujuan' => $tujuan,
                    'poli_tujuan' => $poli,
                    'alasan_rujukan' => $alasan,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            if($insertResep > 0 && $insertPembayaran > 0){
                DB::commit();
                return redirect()->back()->with('status', 'Berhasil menginsert data');
            }
            else {
                DB::rollback();
                return redirect()->back()->with('status', 'Gagal menginsert data');
            }
        }catch(\Exception $e){
            Log::error("Gagal menambahkan transaksi input resep, dan pembayaran");
            DB::rollback();
            return redirect()->back('status', 'berhasil');
        }

        return response()->json([
            'data' => 'Berhasil'
        ]);
    }
}