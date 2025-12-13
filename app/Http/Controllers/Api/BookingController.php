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
        // 1. Validasi Input
        $request->validate([
            'id_dokter' => 'required|exists:staff,id',
            'keluhan'   => 'required|string',
            'tanggal'   => 'required|date|after_or_equal:today',
        ]);

        $user = $request->user();
        $pasien = Pasien::where('user_id', $user->id)->first();

        if (!$pasien) {
            return response()->json(['message' => 'Data pasien tidak valid.'], 403);
        }

        // ===============================================================
        // VALIDASI BARU: SINGLE ACTIVE BOOKING POLICY
        // ===============================================================
        // Cek apakah ada kunjungan yang statusnya BUKAN 'selesai' dan BUKAN 'batal'
        $activeBooking = Kunjungan::where('id_pasien', $pasien->id)
            ->whereNotIn('status', ['selesai', 'batal'])
            ->first();

        if ($activeBooking) {
            // Kita format tanggalnya biar user ingat
            $tgl = Carbon::parse($activeBooking->tgl_kunjungan)->translatedFormat('d F Y');
            $status = ucwords(str_replace('_', ' ', $activeBooking->status));

            return response()->json([
                'message' => "Anda masih memiliki reservasi aktif pada tanggal {$tgl} (Status: {$status}). Selesaikan transaksi ini sebelum membuat janji baru."
            ], 422);
        }

        // ===============================================================
        // MAPPING HARI
        // ===============================================================
        $dayEnglish = Carbon::parse($request->tanggal)->format('l');
        $daysMap = [
            'Sunday'    => 'Minggu',
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu',
        ];
        $hariIndo = $daysMap[$dayEnglish] ?? 'Senin';

        // Cari Jadwal
        $jadwal = JadwalPraktek::where('id_staff', $request->id_dokter)
            ->where('hari', $hariIndo)
            ->first();

        if (!$jadwal) {
            return response()->json(['message' => 'Dokter tidak memiliki jadwal praktek pada hari ' . $hariIndo], 422);
        }

        // ===============================================================
        // CEK KUOTA HARIAN
        // ===============================================================
        $currentBookingCount = Kunjungan::where('id_dokter', $request->id_dokter)
            ->whereDate('tgl_kunjungan', $request->tanggal)
            ->where('status', '!=', 'batal')
            ->count();

        if ($currentBookingCount >= $jadwal->kuota_harian) {
            return response()->json([
                'message' => 'Mohon maaf, kuota antrian untuk tanggal ini sudah habis (' . $jadwal->kuota_harian . ' pasien).'
            ], 422);
        }

        // ===============================================================
        // SIMPAN DATA
        // ===============================================================
        $no_antrian = 'A-' . str_pad($currentBookingCount + 1, 3, '0', STR_PAD_LEFT);

        // Pastikan ID Klinik valid (ambil dari jadwal/dokter atau default 1)
        // Sebaiknya ambil dari relasi dokter agar aman jika klinik ID 1 dihapus
        $idKlinik = $jadwal->staff->id_klinik ?? 1;

        $kunjungan = Kunjungan::create([
            'id_klinik'     => $idKlinik,
            'id_pasien'     => $pasien->id,
            'id_dokter'     => $request->id_dokter,
            'id_jadwal'     => $jadwal->id,
            'tgl_kunjungan' => $request->tanggal,
            'no_antrian'    => $no_antrian,
            'keluhan'       => $request->keluhan,
            'status'        => 'booking',
        ]);

        $kunjungan->load('dokter');

        return response()->json([
            'status' => 'success',
            'message' => 'Booking berhasil!',
            'data' => $kunjungan
        ]);
    }
}
