<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\JadwalDokterController;
use App\Models\Kunjungan;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// Pasien
// Beranda, Login, Regis (Agne)
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function(){
    Route::post('/logout.process', [AuthController::class, 'logout']);
    Route::get('/profile', function(Request $request){
        return $request->user();
    });
});
// Cari Dokter (Zikra)

// Fasilitas & Layanan

// Riwayat Reservasi()


// Admin
// Dashboard

// Rekam Medis
Route::get('/admin/rekam-medis', function(Request $request){
    //
});
// Pembayaran

// Jadwal Dokter
Route::middleware('auth:sanctum')->group(function(){
    Route::get('/admin/jadwal-dokter', [JadwalDokterController::class, 'index']);
    Route::post('/admin/jadwal-dokter/store', [JadwalDokterController::class, 'store']);
    Route::get('/admin/jadwal-dokter/list-dokter', [JadwalDokterController::class, 'getDoctorsList']);
    Route::put('/admin/jadwal-dokter/{id}', [JadwalDokterController::class, 'edit']);
});
// Rujukan Digital
