<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Staff;
use App\Models\Klinik;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Kunjungan; // <--- WAJIB ADA!

class PublicController extends Controller
{
    // Ambil semua dokter + jadwal + klinik
    public function getDoctors(Request $request)
    {
        // 1. Ambil Dokter + Jadwal + Klinik
        $query = Staff::with(['klinik', 'jadwal'])->where('peran', 'dokter');

        if ($request->has('klinik_id') && $request->klinik_id != '') {
            $query->where('id_klinik', $request->klinik_id);
        }

        $doctors = $query->get();

        // 2. LOGIC SORTING & STATUS TAMBAHAN
        // Setup Hari
        $daysMap = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ];

        $todayName = $daysMap[\Carbon\Carbon::now()->format('l')];
        $tomorrowName = $daysMap[\Carbon\Carbon::now()->addDay()->format('l')];

        // Transformasi Data (Menambah info status)
        $doctors->transform(function ($doc) use ($todayName, $tomorrowName) {

            $doc->sort_priority = 0; // 0: Tidak ada, 1: Besok, 2: Hari Ini
            $doc->status_label = 'Tidak Ada Jadwal Praktik Hari Ini';
            $doc->status_color = 'text-red-500';
            $doc->jam_praktek = '';

            // Cek Jadwal
            foreach ($doc->jadwal as $jadwal) {
                $hariJadwal = trim($jadwal->hari);
                $jam = substr($jadwal->jam_mulai, 0, 5) . ' - ' . substr($jadwal->jam_selesai, 0, 5);

                if (strcasecmp($hariJadwal, $todayName) == 0) {
                    $doc->sort_priority = 2; // Prioritas Tertinggi
                    $doc->status_label = 'Ada Jadwal Praktik Hari Ini';
                    $doc->status_color = 'text-green-600'; // Hijau
                    $doc->jam_praktek = $jam;
                    break; // Ketemu hari ini, stop loop
                } elseif (strcasecmp($hariJadwal, $tomorrowName) == 0) {
                    // Hanya set besok jika belum ketemu jadwal hari ini
                    if ($doc->sort_priority < 1) {
                        $doc->sort_priority = 1;
                        $doc->status_label = 'Ada Jadwal Praktik Besok';
                        $doc->status_color = 'text-blue-500'; // Biru
                        $doc->jam_praktek = $jam;
                    }
                }
            }
            return $doc;
        });

        // 3. Urutkan: Priority Tinggi (Hari Ini) di paling atas
        $sortedDoctors = $doctors->sortByDesc('sort_priority')->values();

        return response()->json([
            'status' => 'success',
            'data' => $sortedDoctors
        ]);
    }

    // Ambil detail 1 dokter
    public function getDoctorProfile($id)
    {
        // 1. Ambil data dokter
        $doctor = Staff::with(['klinik', 'jadwal'])->where('peran', 'dokter')->find($id);

        if (!$doctor) {
            return response()->json(['status' => 'error', 'message' => 'Dokter tidak ditemukan'], 404);
        }

        // --- SETUP WAKTU (Mapping Manual) ---
        // Tidak perlu setLocale karena kita pakai mapping manual
        $todayEnglish = \Carbon\Carbon::now()->format('l');
        $tomorrowEnglish = \Carbon\Carbon::now()->addDay()->format('l');
        $todayDate = \Carbon\Carbon::now()->format('Y-m-d');
        $tomorrowDate = \Carbon\Carbon::now()->addDay()->format('Y-m-d');

        $daysMap = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ];

        $todayName = $daysMap[$todayEnglish];
        $tomorrowName = $daysMap[$tomorrowEnglish];

        // --- LOOP JADWAL & HITUNG ---
        foreach ($doctor->jadwal as $jadwal) {

            $dbHari = trim($jadwal->hari);
            $targetDate = null;

            // Cek Hari Ini
            if (strcasecmp($dbHari, $todayName) == 0) {
                $targetDate = $todayDate;
            }
            // Cek Besok
            elseif (strcasecmp($dbHari, $tomorrowName) == 0) {
                $targetDate = $tomorrowDate;
            }

            // Default Sisa = Kuota Max
            $jadwal->sisa_kuota = $jadwal->kuota_harian;

            // Hitung Booking Real
            if ($targetDate) {
                $terpakai = Kunjungan::where('id_dokter', $id)
                    ->whereDate('tgl_kunjungan', $targetDate)
                    ->where('status', '!=', 'batal')
                    ->count();

                $jadwal->sisa_kuota = max(0, $jadwal->kuota_harian - $terpakai);
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $doctor
        ]);
    }

    // Ambil daftar klinik untuk dropdown
    public function getClinics()
    {
        $clinics = Klinik::all();
        return response()->json(['status' => 'success', 'data' => $clinics]);
    }
}
