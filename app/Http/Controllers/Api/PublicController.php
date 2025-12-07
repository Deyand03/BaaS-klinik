<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Staff;
use App\Models\Klinik;

class PublicController extends Controller
{
    // Ambil semua dokter + jadwal + klinik
    public function getDoctors(Request $request)
    {
        // Ambil staff yang perannya 'dokter'
        $query = Staff::with(['klinik', 'jadwal'])->where('peran', 'dokter');

        // Filter berdasarkan ID Klinik (jika ada request)
        if ($request->has('klinik_id') && $request->klinik_id != '') {
            $query->where('id_klinik', $request->klinik_id);
        }

        $doctors = $query->get();

        return response()->json([
            'status' => 'success',
            'data' => $doctors
        ]);
    }

    // Ambil detail 1 dokter
    public function getDoctorProfile($id)
    {
        $doctor = Staff::with(['klinik', 'jadwal'])->where('peran', 'dokter')->find($id);

        if (!$doctor) {
            return response()->json(['status' => 'error', 'message' => 'Dokter tidak ditemukan'], 404);
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