<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kunjungan;
use App\Models\JadwalPraktek;
use App\Models\Pasien;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validasi Input (UPDATE)
        $request->validate([
            'id_dokter' => 'required|exists:staff,id',
            'keluhan'   => 'required|string',

            // Aturan: Tanggal harus hari ini (today) atau besok (tomorrow)
            'tanggal'   => 'required|date|after_or_equal:today|before_or_equal:tomorrow',
        ], [
            // Pesan Error Custom (Opsional biar user ngerti)
            'tanggal.after_or_equal' => 'Tanggal tidak boleh di masa lalu.',
            'tanggal.before_or_equal' => 'Booking hanya bisa dilakukan untuk hari ini atau besok.',
        ]);

        $user = $request->user();
        $pasien = Pasien::where('user_id', $user->id)->first();

        if (!$pasien) {
            return response()->json(['message' => 'Data pasien tidak valid. Lengkapi profil Anda.'], 403);
        }

        // 1. Generate Nomor Antrian (Format: A-001)
        // Hitung jumlah pasien pada tanggal & dokter tersebut
        $count = Kunjungan::whereDate('tgl_kunjungan', $request->tanggal)
            ->where('id_dokter', $request->id_dokter)
            ->count();

        $no_antrian = 'A-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        // 2. Cari Jadwal ID (Opsional, untuk relasi)
        // Kita cari jadwal dokter di hari yang sesuai tanggal input
        $hariIndo = Carbon::parse($request->tanggal)->locale('id')->isoFormat('dddd');
        $jadwal = JadwalPraktek::where('id_staff', $request->id_dokter)
            ->where('hari', $hariIndo)
            ->first();

        // 3. Simpan ke Database
        $kunjungan = Kunjungan::create([
            'id_klinik' => 1, // Logic klinik bisa diambil dari relasi dokter->klinik
            'id_pasien' => $pasien->id,
            'id_dokter' => $request->id_dokter,
            'id_jadwal' => $jadwal ? $jadwal->id : null,
            'tgl_kunjungan' => $request->tanggal,
            'no_antrian' => $no_antrian,
            'keluhan' => $request->keluhan,
            'status' => 'booking',
        ]);

        $kunjungan->load('dokter');

        return response()->json([
            'status' => 'success',
            'message' => 'Booking berhasil!',
            'data' => $kunjungan // Sekarang data ini sudah berisi info dokter
        ]);
    }
}
