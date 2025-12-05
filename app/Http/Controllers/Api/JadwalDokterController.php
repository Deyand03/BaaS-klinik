<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JadwalPraktek;
use App\Models\Staff;
use App\Models\Kunjungan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JadwalDokterController extends Controller
{
     // --- 1. Endpoint Baru: Ambil List Semua Klinik ---
    public function getClinicsList()
    {
        // Ambil ID dan Nama klinik buat dropdown
        $clinics = DB::table('klinik')->select('id', 'nama')->get();
        return response()->json(['data' => $clinics]);
    }

    // --- 2. Update Get Doctors List (Filter by Clinic) ---
    public function getDoctorsList(Request $request)
    {
        // Kalau ada parameter 'clinic_id' dari frontend, pakai itu.
        // Kalau gak ada, baru fallback ke klinik asal si user yang login.
        $targetClinicId = $request->query('clinic_id');

        // Logic ini nge-bypass staff id_klinik kalau admin milih klinik spesifik
        if (!$targetClinicId) {
             $staff = $request->query('staff');
             $targetClinicId = $staff['id_klinik'] ?? null;
        }

        $query = Staff::where('peran', 'dokter');

        if ($targetClinicId && $targetClinicId != 'all') {
            $query->where('id_klinik', $targetClinicId);
        }

        $doctors = $query->select('id', 'nama_lengkap')->get();
        return response()->json(['data' => $doctors]);
    }

    // --- 3. Update Index (Filter Jadwal by Clinic) ---
    public function index(Request $request)
    {
        $todayName = Carbon::now()->locale('id')->dayName;
        $todayDate = date('Y-m-d');

        // Cek Filter Klinik dari Request
        $targetClinicId = $request->query('clinic_id');

        // Kalau gak ada filter (misal baru buka halaman), bisa default ke klinik user login
        // ATAU tampilkan semua (sesuai request 'admin bisa atur semua')
        // Disini kita biarkan query jalan.

        $query = JadwalPraktek::with(['staff.klinik']); // Load relasi klinik biar tau dokternya di mana

        // Filter Query berdasarkan Klinik (Jika dipilih)
        if ($targetClinicId && $targetClinicId != 'all') {
            $query->whereHas('staff', function ($q) use ($targetClinicId) {
                $q->where('id_klinik', $targetClinicId);
            });
        }

        // Filter Search Nama Dokter
        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $query->whereHas('staff', function($q) use ($searchTerm) {
                $q->where('nama_lengkap', 'like', "%{$searchTerm}%");
            });
        }

        $query->withCount([
            'kunjungan as terisi_hari_ini' => function ($q) use ($todayDate) {
                $q->whereDate('tgl_kunjungan', $todayDate)
                    ->where('status', '!=', 'batal');
            }
        ]);

        $rawSchedules = $query->get();

        $groupedSchedules = $rawSchedules->groupBy('id_staff')->map(function ($items) use ($todayName, $request) {
            $firstItem = $items->first();
            $dokter = $firstItem->staff;

            // Filter Hari (Backend side filtering untuk collection)
            if ($request->has('hari') && $request->hari != '' && $request->hari != 'Semua') {
                $hasDay = $items->contains('hari', $request->hari);
                if (!$hasDay) return null;
            }

            $jadwalHariIni = $items->first(function ($item) use ($todayName) {
                return strtolower($item->hari) == strtolower($todayName);
            });

            $terisi = $jadwalHariIni ? $jadwalHariIni->terisi_hari_ini : 0;
            $kuota = $jadwalHariIni ? $jadwalHariIni->kuota_harian : ($firstItem->kuota_harian ?? 0);

            $details = [];
            foreach ($items as $item) {
                $details[$item->hari] = [
                    'jam_mulai' => Carbon::parse($item->jam_mulai)->format('H:i'),
                    'jam_selesai' => Carbon::parse($item->jam_selesai)->format('H:i'),
                    'kuota' => $item->kuota_harian,
                    'status' => $item->status,
                ];
            }

            return [
                'id' => $dokter->id,
                'staff_id' => $dokter->id,
                'dokter' => $dokter->nama_lengkap,
                'spesialis' => $dokter->spesialisasi ?? 'Umum',
                'klinik' => $dokter->klinik->nama ?? '-', // Info tambahan nama klinik
                'hari' => $items->pluck('hari')->toArray(),
                'details' => $details,
                'jam_mulai' => Carbon::parse($jadwalHariIni ? $jadwalHariIni->jam_mulai : $firstItem->jam_mulai)->format('H:i'),
                'jam_selesai' => Carbon::parse($jadwalHariIni ? $jadwalHariIni->jam_selesai : $firstItem->jam_selesai)->format('H:i'),
                'kuota' => $kuota,
                'terisi' => $terisi,
            ];
        })->filter()->values();

        return response()->json(['data' => $groupedSchedules]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'jadwal' => 'required|array',
        ]);

        if (JadwalPraktek::where('id_staff', $request->staff_id)->exists()) {
            return response()->json(['message' => 'Dokter ini sudah punya jadwal. Gunakan Edit.'], 422);
        }

        DB::beginTransaction();
        try {
            $insertedCount = 0;
            foreach ($request->jadwal as $hari => $data) {
                $isActive = isset($data['aktif']) && ($data['aktif'] == 1 || $data['aktif'] === 'true' || $data['aktif'] === 'on');

                if ($isActive) {
                    $statusString = isset($data['status']) ? $data['status'] : 'Aktif';

                    JadwalPraktek::create([
                        'id_staff' => $request->staff_id,
                        'hari' => $hari,
                        'jam_mulai' => $data['jam_mulai'],
                        'jam_selesai' => $data['jam_selesai'],
                        'kuota_harian' => $data['kuota'],
                        'status' => $statusString,
                    ]);
                    $insertedCount++;
                }
            }

            if ($insertedCount == 0) {
                DB::rollBack();
                return response()->json(['message' => 'Pilih minimal satu hari praktek!'], 422);
            }

            DB::commit();
            return response()->json(['message' => 'Jadwal berhasil dibuat!'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function edit(Request $request, $id)
    {
        $request->validate(['jadwal' => 'required|array']);
        $idDokter = $id;

        if (!Staff::where('id', $idDokter)->exists()) {
            return response()->json(['message' => 'Dokter tidak ditemukan'], 404);
        }

        DB::beginTransaction();
        try {
            // Ambil jadwal yang ada untuk referensi update/delete
            $existingSchedules = JadwalPraktek::where('id_staff', $idDokter)->get();

            // Loop semua input hari (Senin-Sabtu) yang dikirim frontend
            foreach ($request->jadwal as $hari => $data) {

                // 1. Cek Parameter
                // Aktif = Dicentang
                $isActive = isset($data['aktif']) && ($data['aktif'] == 1 || $data['aktif'] === 'true' || $data['aktif'] === 'on');

                // Existing = Sudah ada di DB (dikirim via hidden input)
                $isExisting = isset($data['existing']) && ($data['existing'] == 1 || $data['existing'] === 'true');

                $statusString = isset($data['status']) ? $data['status'] : 'Aktif';

                // 2. Logic Smart Update
                if ($isActive) {
                    // KASUS A: User mencentang hari ini (entah baru atau lama)
                    // Action: Update atau Create

                    // Cari record di DB (biar aman kalau existing=0 tapi ternyata data nyangkut di DB)
                    $jadwalItem = $existingSchedules->first(function ($item) use ($hari) {
                        return strtolower($item->hari) === strtolower($hari);
                    });

                    if ($jadwalItem) {
                        $jadwalItem->update([
                            'jam_mulai' => $data['jam_mulai'],
                            'jam_selesai' => $data['jam_selesai'],
                            'kuota_harian' => $data['kuota'],
                            'status' => $statusString,
                        ]);
                    } else {
                        JadwalPraktek::create([
                            'id_staff' => $idDokter,
                            'hari' => $hari,
                            'jam_mulai' => $data['jam_mulai'],
                            'jam_selesai' => $data['jam_selesai'],
                            'kuota_harian' => $data['kuota'],
                            'status' => $statusString,
                        ]);
                    }

                } elseif ($isExisting && !$isActive) {
                    // KASUS B: Dulu ada (existing=1), tapi sekarang di-uncheck (active=0)
                    // Action: Hapus atau Nonaktifkan (Safe Delete)

                    $jadwalItem = $existingSchedules->first(function ($item) use ($hari) {
                        return strtolower($item->hari) === strtolower($hari);
                    });

                    if ($jadwalItem) {
                        $isUsed = DB::table('kunjungan')->where('id_jadwal', $jadwalItem->id)->exists();

                        if ($isUsed) {
                            $jadwalItem->update(['status' => 'Nonaktif']);
                        } else {
                            $jadwalItem->delete();
                        }
                    }
                }
                // KASUS C: (!isActive && !isExisting) -> Hari baru yang tidak dipilih. ABAIKAN.
            }

            DB::commit();
            return response()->json(['message' => 'Jadwal berhasil diperbarui']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}
