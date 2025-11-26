<?php

use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Klinik Umum API

Route::get('/klinik-umum/doctors', function (Request $request) {

    // Ambil ID Klinik dari parameter '?clinic_id=1'
    $clinicId = $request->query('klinik_id');

    // Cari staf yang perannya dokter di klinik tersebut
    $doctors = Staff::where('id_klinik', $clinicId)
        ->where('peran', 'dokter')
        ->with('jadwal') // Pastikan relasi di Model Staf ada: public function jadwal_praktek()
        ->get();

    return response()->json([
        'status' => 'success',
        'data' => $doctors
    ]);
});



// Klinik Mata


// Klinik Gizi


// Klinik Umum
