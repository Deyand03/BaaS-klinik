<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// Pasien
// Beranda, Login, Regis (Agne)
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function(){
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/p  rofile', function(Request $request){
        return $request->user();
    });
});
// Cari Dokter (Zikra)

// Fasilitas & Layanan

// Riwayat Reservasi()


// Admin
// Dashboard

// Rekam Medis

// Pembayaran

// Jadwal Dokter
Route::get('/admin/jadwal-dokter', function (Request $request) {
    $data = $request->query('user_id');
    $staff = Staff::where('user_id', $data)->first();
    $jadwal = Staff::where('id_klinik', $staff->id_klinik)
        ->where('peran', 'dokter')
        ->with('jadwal')->get();

    return response()->json([
        'status' => 'success',
        'data' => $jadwal
    ]);
});

// Rujukan Digital
