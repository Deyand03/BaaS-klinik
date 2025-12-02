<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JadwalPraktek;
use App\Models\Kunjungan;
use App\Models\Staff;
use App\Models\Pasien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingController extends Controller
{
    /**
     * Menyimpan Data Booking Baru (Ambil Antrian)
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'id_dokter' => 'required|exists:staff,id',
            'id_jadwal' => 'required|exists:jadwal_praktek,id',
            'tgl_kunjungan' => 'required|date|after_or_equal:today',
            'keluhan' => 'required|string|max:500',
        ]);

        // Ambil User yang login (Pasien)
        $user = $request->user();

        // Pastikan user punya profil pasien
        $pasien = Pasien::where('user_id', $user->id)->first();
        if (!$pasien) {
            return response()->json(['message' => 'Profil pasien tidak ditemukan.'], 404);
        }

        DB::beginTransaction();
        try {
            // 2. Cek Kuota Dokter
            $jadwal = JadwalPraktek::findOrFail($request->id_jadwal);

            // Hitung berapa orang yang sudah booking di jadwal & tanggal ini
            $jumlahBooking = Kunjungan::where('id_jadwal', $request->id_jadwal)
                ->whereDate('tgl_kunjungan', $request->tgl_kunjungan)
                ->where('status', '!=', 'batal')
                ->count();

            if ($jumlahBooking >= $jadwal->kuota_harian) {
                return response()->json(['message' => 'Mohon maaf, kuota antrian untuk sesi ini sudah penuh.'], 422);
            }

            // 3. Cek Double Booking (Optional)
            // Biar pasien gak iseng booking berkali-kali di hari yang sama
            $sudahBooking = Kunjungan::where('id_pasien', $pasien->id)
                ->where('id_dokter', $request->id_dokter)
                ->whereDate('tgl_kunjungan', $request->tgl_kunjungan)
                ->exists();

            if ($sudahBooking) {
                return response()->json(['message' => 'Anda sudah memiliki antrian untuk dokter ini di tanggal tersebut.'], 422);
            }

            // 4. Generate Nomor Antrian (Format: A-001)
            // Logic: Jumlah antrian hari ini + 1
            $noUrut = $jumlahBooking + 1;
            $kodeAntrian = 'A-' . str_pad($noUrut, 3, '0', STR_PAD_LEFT);

            // 5. Ambil ID Klinik dari Dokter
            $dokter = Staff::find($request->id_dokter);

            // 6. Simpan ke Database
            $kunjungan = Kunjungan::create([
                'id_klinik' => $dokter->id_klinik,
                'id_pasien' => $pasien->id,
                'id_dokter' => $dokter->id,
                'id_jadwal' => $jadwal->id,
                'tgl_kunjungan' => $request->tgl_kunjungan,
                'no_antrian' => $kodeAntrian,
                'keluhan' => $request->keluhan,
                'status' => 'booking', // Status awal
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Booking berhasil!',
                'data' => [
                    'no_antrian' => $kodeAntrian,
                    'tgl_kunjungan' => $request->tgl_kunjungan,
                    'dokter' => $dokter->nama_lengkap,
                    'id_kunjungan' => $kunjungan->id
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Melihat Riwayat Booking (Opsional buat halaman tiket)
     */
    public function show($id)
    {
        $kunjungan = Kunjungan::with(['dokter', 'klinik', 'jadwal'])->find($id);

        if (!$kunjungan) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json(['data' => $kunjungan]);
    }
}
