<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Staff;
use App\Models\Pasien;
use App\Models\Kunjungan;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class RegisterPasienController extends Controller
{
    public function listPasien(Request $request)
    {
        $staffId = $request->query('staff_id');

        $staff = Staff::where('user_id', $staffId)->first();

        if (!$staff) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Staff tidak ditemukan'
            ], 404);
        }
        $query = Pasien::query();
        
        if ($request->has('search') && !empty($request->search)) {
        $search = $request->search;
        $query->where('nama_lengkap', 'LIKE', '%' . $search . '%')
              ->orWhere('nik', 'LIKE', '%' . $search . '%');
        }

        $pasien = $query->paginate(10);
        // $pasien = Pasien::whereHas('kunjungan', function ($q) use ($staff) {
        //     $q->where('id_klinik', $staff->id_klinik);
        // })->get();  

        return response()->json([
            'status'  => 'success',
            'pasien'  => $pasien,
            'klinik'  => $staff->id_klinik
        ]);
    }

    // Tambah User (untuk Pasien)
    public function storeUser(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|string',
        ]);

        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'User berhasil dibuat',
            'user_id' => $user->id
        ], 201);
    }

    // Tambah Pasien
    public function storePasien(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'nama_lengkap' => 'required|string',
            'nik' => 'required|string|unique:pasiens,nik',
            'tgl_lahir' => 'required|date',
            'jenis_kelamin' => 'required|string',
            'alamat_domisili' => 'required|string',
            'no_hp' => 'required|string',
            'golongan_darah' => 'nullable|string',
            'riwayat_alergi' => 'nullable|string',
        ]);

        $pasien = Pasien::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Pasien berhasil ditambahkan',
            'data' => $pasien
        ], 201);
    }


    // =============================================
    // 3. LIST DOKTER + JADWAL DALAM KLINIK
    // =============================================
    public function listDokter(Request $request)
    {
        $staffId = $request->query('staff_id');

        $staff = Staff::where('user_id', $staffId)->first();

        if (!$staff) {
            return response()->json([
                'status' => 'error',
                'message' => 'Staff tidak ditemukan'
            ], 404);
        }

        $dokter = Staff::where('id_klinik', $staff->id_klinik)
            ->where('peran', 'dokter')
            ->with('jadwal')
            ->get();

        return response()->json([
            'status' => 'success',
            'dokter' => $dokter->map(function($d) {
                return [
                    'id' => $d->id,
                    'nama_lengkap' => $d->nama_lengkap,
                    'jadwal' => $d->jadwal->map(function($j){
                        return [
                            'id' => $j->id,
                            'hari' => $j->hari,
                            'jam_mulai' => $j->jam_mulai,
                            'jam_selesai' => $j->jam_selesai,
                        ];
                    })
                ];
            })
        ]);
    }

    // =============================================
    // 4. TAMBAH KUNJUNGAN (BOOKING)
    // =============================================
    public function storeKunjungan(Request $request)
    {
        $validated = $request->validate([
            'id_klinik'     => 'required|integer',
            'id_pasien'     => 'required|integer',
            'tgl_kunjungan' => 'required|date',
            'no_antrian'    => 'required|string',
            'status'        => 'required|string',
            'id_jadwal'     => 'required|integer',
            'id_dokter'     => 'required|integer',
            'keluhan'       => 'required|string',
        ]);

        $kunjungan = Kunjungan::create($validated);
        dd($kunjungan);
        return response()->json([
            'status'    => 'success',
            'message'   => 'Kunjungan berhasil ditambahkan',
            'kunjungan' => $kunjungan
        ]);
    }
}
