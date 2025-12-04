<?php

use App\Models\Obat;
use App\Models\Staff;
use App\Models\Kunjungan;
use App\Models\RekamMedis;
use Illuminate\Http\Request;
use App\Models\PemeriksaanGizi;
use App\Models\PemeriksaanMata;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\OperasionalController;
use App\Http\Controllers\Api\JadwalDokterController;
use App\Http\Controllers\Api\PerawatController;


// Pasien
// Beranda, Login, Regis (Agne)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']); // <--- TAMBAHKAN INI

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout.process', [AuthController::class, 'logout']);
    Route::get('/profile', function (Request $request) {
        return $request->user();
    });

    // Route Booking
    Route::post('/booking', [BookingController::class, 'store']);
    Route::get('/booking/{id}', [BookingController::class, 'show']);

    // API OPERASIONAL
    Route::get('/admin/antrian', [OperasionalController::class, 'index']);
    Route::post('/admin/antrian/{id}/status', [OperasionalController::class, 'updateStatus']);
});
// Cari Dokter (Zikra)

// Fasilitas & Layanan

// Riwayat Reservasi()


// Admin
// Dashboard

// Rekam Medis
Route::get('/admin/rekam-medis', function (Request $request) {

    $staffId = $request->query('staff_id');

    // 1. Cari staff berdasarkan user_id
    $staff = Staff::where('user_id', $staffId)->first();

    if (!$staff) {
        return response()->json([
            'status' => 'error',
            'message' => 'Staff tidak ditemukan'
        ], 404);
    }

    // 2. Ambil id klinik staff
    $idKlinik = $staff->id_klinik;

    // 3. Cari semua dokter di klinik ini
    $dokterIds = Staff::where('id_klinik', $idKlinik)
        ->where('peran', 'dokter')
        ->pluck('id');

    $obat = Obat::where('id_klinik', $staff->id_klinik)->get();

    // 4. Ambil rekam medis berdasarkan dokter
    $rekam = RekamMedis::with([
        'kunjungan.pasien',
        'kunjungan.staff',
        'kunjungan.klinik',
        'pemeriksaan_gizi',
        'pemeriksaan_mata',
        'resep.obat'
    ])
        ->whereHas('kunjungan', function ($q) use ($dokterIds) {
            $q->whereIn('id_dokter', $dokterIds);
        })
        ->get();

    $kunjungan = $rekam->pluck('kunjungan')->unique('id')->values();

    return response()->json([
        'rekam_medis' => $rekam,
        'kunjungan' => $kunjungan,
        'klinik_id' => $idKlinik,
        'status' => 'success',
        'obat' => $obat
    ]);
});

// Route::middleware('auth:sanctum')->post('/rekam-medis/tambah', function (Request $request) {

//     $validated = $request->validate([
//         'id_kunjungan' => 'required|integer',
//         'anamnesa' => 'nullable|string',
//         'tanda_vital' => 'nullable|string',
//         'diagnosa' => 'nullable|string',
//         'tindakan_medis' => 'nullable|string',
//         'catatan_dokter' => 'nullable|string',

//         // Mata
//         'visus_od' => 'nullable|string',
//         'visus_os' => 'nullable|string',
//         'koreksi_sphere' => 'nullable|string',
//         'koreksi_cylinder' => 'nullable|string',
//         'axis' => 'nullable|string',

//         // Gizi
//         'tinggi_badan' => 'nullable|numeric',
//         'imt' => 'nullable|string',
//         'lingkar_perut' => 'nullable|numeric',
//         'status_gizi' => 'nullable|string',
//     ]);

//     // Insert rekam medis utama
//     $rekam = RekamMedis::create([
//         'id_kunjungan'    => $validated['id_kunjungan'],
//         'anamnesa'        => $validated['anamnesa'] ?? null,
//         'tensi_darah'     => $validated['tensi_darah'] ?? null,
//         'berat_badan'    => $validated['berat_badan'] ?? null,
//         'suhu_badan'   => $validated['suhu_badan'] ?? null,
//         'diagnosa'        => $validated['diagnosa'] ?? null,
//         'tindakan'  => $validated['tindakan'] ?? null,
//         'catatan_dokter'  => $validated['catatan_dokter'] ?? null,
//     ]);

//     // Jika klinik MATA
//     if ($request->has('visus_od')) {
//         PemeriksaanMata::create([
//             'id_rekam_medis' => $rekam->id,
//             'visus_od' => $validated['visus_od'],
//             'visus_os' => $validated['visus_os'],
//             'sphere_od' => $validated['sphere_od'],
//             'sphere_os' => $validated['sphere_os'],
//             'cylinder_od' => $validated['cylinder_od'],
//             'cylinder_os' => $validated['cylinder_os'],
//             'axis_od' => $validated['axis_od'],
//             'axis_os' => $validated['axis_os'],
//             'pd' => $validated['pd'],
//         ]);
//     }

//     // Jika klinik GIZI
//     if ($request->has('tinggi_badan')) {
//         PemeriksaanGizi::create([
//             'id_rekam_medis' => $rekam->id,
//             'tinggi_badan' => $validated['tinggi_badan'],
//             'imt' => $validated['imt'],
//             'lingkar_perut' => $validated['lingkar_perut'],
//             'status_gizi' => $validated['status_gizi'],
//         ]);
//     }

//     return response()->json([
//         'status' => 'success',
//         'message' => 'Rekam medis berhasil ditambahkan',
//         'data' => $rekam
//     ]);
// });



// Pembayaran

// Jadwal Dokter
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/admin/jadwal-dokter', [JadwalDokterController::class, 'index']);
    Route::post('/admin/jadwal-dokter/store', [JadwalDokterController::class, 'store']);
    Route::get('/admin/jadwal-dokter/list-dokter', [JadwalDokterController::class, 'getDoctorsList']);
    Route::put('/admin/jadwal-dokter/{id}', [JadwalDokterController::class, 'edit']);
});
// Rujukan Digital


// Perawat (yang saya pakai)
Route::get('/perawat/antrian', [PerawatController::class, 'index']);
Route::post('/perawat/input-vital/{id}', [PerawatController::class, 'storeVital']);