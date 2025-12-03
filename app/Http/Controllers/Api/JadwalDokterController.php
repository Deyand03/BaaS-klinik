<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JadwalPraktek;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JadwalDokterController extends Controller
{
    public function getDoctorsList(Request $request)
    {
        $staff = $request->query('staff');

        // Ambil ID Klinik dari user yang login (relasi staf)
        $clinicId = $request->query('clinic_id') ?? ($staff['id_klinik'] ?? null);

        $query = Staff::where('peran', 'dokter');

        if ($clinicId) {
            $query->where('id_klinik', $clinicId);
        }

        $doctors = $query->select('id', 'nama_lengkap')->get();

        return response()->json(['data' => $doctors]);
    }

    /**
     * 2. GET Daftar Jadwal (Grouping per Dokter)
     */
    public function index(Request $request)
    {
        $user = $request->query('staff');
        $clinicId = $request->query('clinic_id') ?? ($user['id_klinik'] ?? null);

        $todayName = Carbon::now()->locale('id')->dayName; // "Senin", "Selasa"
        $todayDate = date('Y-m-d'); // "2025-11-29"

        $query = JadwalPraktek::with('staff')
            ->whereHas('staff', function ($q) use ($clinicId) {
                if ($clinicId) {
                    $q->where('id_klinik', $clinicId);
                }
            })
            // --- LOGIKA HITUNG DADAKAN (withCount) ---
            ->withCount([
                'kunjungan as terisi_hari_ini' => function ($q) use ($todayDate) {
                    $q->whereDate('tgl_kunjungan', $todayDate) // Cuma hitung yg tanggalnya hari ini
                        ->where('status', '!=', 'batal');            // Cuma hitung yg statusnya 'booking'
                    // Note: Kalau mau lebih akurat, bisa pakai logic: ->where('status', '!=', 'batal');
                }
            ]);

        $rawSchedules = $query->get();

        // Grouping data berdasarkan Dokter
        $groupedSchedules = $rawSchedules->groupBy('id_staff')->map(function ($items) use ($todayName) {
            $firstItem = $items->first();
            $dokter = $firstItem->staff;

            // Cari jadwal dokter ini yang harinya sama dengan HARI INI
            // Gunanya buat nampilin progress bar yang akurat
            $jadwalHariIni = $items->first(function ($item) use ($todayName) {
                return strtolower($item->hari) == strtolower($todayName);
            });

            // Kalau hari ini ada jadwal, ambil hitungannya. Kalau tutup, ya 0.
            $terisi = $jadwalHariIni ? $jadwalHariIni->terisi_hari_ini : 0;

            // Ambil kuota hari ini (atau default dari jadwal pertama kalo tutup)
            $kuota = $jadwalHariIni ? $jadwalHariIni->kuota_harian : ($firstItem->kuota_harian ?? 0);

            return [
                'id' => $dokter->id,
                'staff_id' => $dokter->id,
                'dokter' => $dokter->nama_lengkap,
                'spesialis' => $dokter->spesialisasi ?? 'Umum',
                'hari' => $items->pluck('hari')->toArray(),

                // Jam Praktek (Prioritas hari ini, fallback ke data umum)
                'jam_mulai' => Carbon::parse($jadwalHariIni ? $jadwalHariIni->jam_mulai : $firstItem->jam_mulai)->format('H:i'),
                'jam_selesai' => Carbon::parse($jadwalHariIni ? $jadwalHariIni->jam_selesai : $firstItem->jam_selesai)->format('H:i'),

                'kuota' => $kuota,
                'terisi' => $terisi, // <--- INI SUDAH DINAMIS DARI DATABASE
                'status' => $firstItem->status_aktif ? 'aktif' : 'nonaktif',
            ];
        })->values();

        return response()->json(['data' => $groupedSchedules]);
    }

    /**
     * Simpan Jadwal Baru (Store)
     */
    public function store(Request $request)
    {
        $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'hari' => 'required|array|min:1',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'kuota' => 'required|integer|min:1',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        // 2. VALIDASI BISNIS (Strict Check)
        // Cek apakah dokter ini sudah punya jadwal APAPUN di database?
        $dokterSudahAda = JadwalPraktek::where('id_staff', $request->staff_id)->exists();

        if ($dokterSudahAda) {
            // STOP! Jangan simpan apapun. Return 422 Unprocessable Entity.
            return response()->json([
                'message' => 'Dokter ini sudah memiliki jadwal! Silakan cari kartu dokter tersebut di list dan klik tombol "Edit Jadwal" untuk menambah atau mengubah hari.'
            ], 422);
        }

        // 3. Simpan Data Baru
        DB::beginTransaction();
        try {
            $statusAktif = $request->status === 'aktif';

            foreach ($request->hari as $hari) {
                JadwalPraktek::create([
                    'id_staff' => $request->staff_id,
                    'hari' => $hari,
                    'jam_mulai' => $request->jam_mulai,
                    'jam_selesai' => $request->jam_selesai,
                    'kuota_harian' => $request->kuota,
                    'status_aktif' => $statusAktif,
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Jadwal dokter baru berhasil dibuat!'], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Store Schedule Error: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menyimpan data: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 4. Update Jadwal (Smart Sync)
     * Endpoint: PUT /api/admin/schedules/{id}
     */
    public function edit(Request $request, $id)
    {
        $request->validate([
            'hari'        => 'required|array|min:1',
            'jam_mulai'   => 'required',
            'jam_selesai' => 'required',
            'kuota'       => 'required|integer',
            'status'      => 'required|in:aktif,nonaktif',
        ]);

        // ID yang dikirim adalah ID DOKTER
        $idDokter = $id;
        $statusAktif = $request->status == 'aktif' ? true : false;
        Log::info("ID DOKTER NYA BABI: $id");
        Log::info("SEMUA DATANYA: $request->all()");
        // Cek apakah dokter valid?
        if (!Staff::where('id', $idDokter)->exists()) {
            return response()->json(['message' => 'Data Dokter tidak ditemukan'], 404);
        }

        DB::beginTransaction();
        try {
            // Ambil semua jadwal eksisting dokter ini
            $existingSchedules = JadwalPraktek::where('id_staff', $idDokter)->get();
            $inputHari = $request->hari;

            // A. Update yang ada atau Hapus yang dibuang (Logic Smart Sync)
            foreach ($existingSchedules as $schedule) {
                if (in_array($schedule->hari, $inputHari)) {
                    // Masih ada -> UPDATE detailnya
                    $schedule->update([
                        'jam_mulai'    => $request->jam_mulai,
                        'jam_selesai'  => $request->jam_selesai,
                        'kuota_harian'        => $request->kuota,
                        'status_aktif' => $statusAktif,
                    ]);

                    // Coret dari list input karena sudah beres
                    $key = array_search($schedule->hari, $inputHari);
                    unset($inputHari[$key]);
                } else {
                    // Gak ada di inputan -> HAPUS (Dokter berhenti praktek hari ini)
                    $schedule->delete();
                }
            }

            // B. Buat yang baru (Sisa array inputHari)
            foreach ($inputHari as $hariBaru) {
                JadwalPraktek::create([
                    'id_staff'      => $idDokter,
                    'hari'         => $hariBaru,
                    'jam_mulai'    => $request->jam_mulai,
                    'jam_selesai'  => $request->jam_selesai,
                    'kuota_harian'        => $request->kuota,
                    'status_aktif' => $statusAktif,
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Jadwal berhasil diperbarui']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update Schedule Error: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal update data'], 500);
        }
    }
}
