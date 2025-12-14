<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Pasien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email atau Password salah.'
            ], 401);
        }

        // Di function login()
        $token = $user->createToken('auth_token')->plainTextToken;

        if ($user->role === 'staff') {
            // Ambil detail staff biar frontend tau dia Dokter/Admin/Resepsionis
            $user->load('staff');
        } else {
            // Ambil detail pasien biar frontend tau Nama/NIK
            $user->load('pasien');
        }


        return response()->json([
            'message' => 'Login sukses',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    public function register(Request $request)
    {
        // 1. Validasi Lengkap
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8',
            'nik' => 'required|numeric|digits:16|unique:pasiens,nik',
            'no_hp' => 'required|string',
            'tgl_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'alamat_domisili' => 'required|string',
        ], [
            // --- TERJEMAHAN PESAN ERROR ---
            'required' => ':attribute wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar. Silakan login.',
            'min' => ':attribute minimal harus berisi :min karakter.',
            'numeric' => ':attribute harus berupa angka.',
            'digits' => ':attribute harus berjumlah :digits digit.',
            'nik.unique' => 'NIK ini sudah terdaftar dalam sistem.',
            'in' => 'Pilihan :attribute tidak valid.',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'pasien',
            ]);

            Pasien::create([
                'user_id' => $user->id,
                'nama_lengkap' => $request->name,
                'nik' => $request->nik,
                'no_hp' => $request->no_hp,
                'tgl_lahir' => $request->tgl_lahir,
                'jenis_kelamin' => $request->jenis_kelamin,
                'alamat_domisili' => $request->alamat_domisili,

                // --- UPDATE DATA REAL ---
                'golongan_darah' => $request->golongan_darah,
                'riwayat_alergi' => $request->riwayat_alergi,
            ]);

            DB::commit();

            // 4. Response
            $token = $user->createToken('auth_token')->plainTextToken;
            $user->load('pasien');

            return response()->json([
                'message' => 'Registrasi berhasil',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Token dihapus']);
    }
}
