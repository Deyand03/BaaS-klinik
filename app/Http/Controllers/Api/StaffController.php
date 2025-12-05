<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        // Filter Klinik & Pencarian
        $clinicId = $request->query('clinic_id');
        $search = $request->query('search');

        $query = Staff::with(['user', 'klinik']); // Load relasi user & klinik
        if ($clinicId && $clinicId != 'all') {
            $query->where('id_klinik', $clinicId);
        }

        if ($search) {
            $query->where('nama_lengkap', 'like', "%{$search}%")
                ->orWhereHas('user', function ($q) use ($search) {
                    $q->where('email', 'like', "%{$search}%");
                });
        }

        $staffs = $query->get();

        return response()->json(['data' => $staffs]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'id_klinik' => 'required|exists:klinik,id',
            'peran' => 'required|in:dokter,admin,perawat,resepsionis,kasir',
            'no_hp' => 'nullable|string|max:15',
            // Validasi khusus jika peran adalah dokter
            'spesialisasi' => 'nullable|required_if:peran,dokter',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validasi foto
        ]);

        DB::beginTransaction(); // Mulai Transaksi Database
        try {
            // 1. Buat Akun User Dulu
            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'staff', // Sesuai enum di schema users
            ]);
            $fotoPath = null;
            if ($request->hasFile('foto_profil') && $request->peran == 'dokter') {
                // Simpan di folder: storage/app/public/dokter-photos
                $fotoPath = $request->file('foto_profil')->store('dokter-photos', 'public');
            }

            Staff::create([
                'user_id' => $user->id,
                'id_klinik' => $request->id_klinik,
                'nama_lengkap' => $request->nama_lengkap,
                'peran' => $request->peran,
                'no_hp' => $request->no_hp,
                'spesialisasi' => $request->peran == 'dokter' ? $request->spesialisasi : null,
                'tentang' => $request->peran == 'dokter' ? $request->tentang : null,
                'pengalaman' => $request->peran == 'dokter' ? $request->pengalaman : null,
                'foto_profil' => $fotoPath, // Simpan path-nya
            ]);

            DB::commit(); // Simpan permanen jika semua sukses
            return response()->json(['message' => 'Pegawai berhasil ditambahkan'], 201);

        } catch (\Exception $e) {
            DB::rollBack(); // Batalkan semua jika ada error
            return response()->json(['message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $staff = Staff::with('user')->find($id);
        if (!$staff)
            return response()->json(['message' => 'Not Found'], 404);
        return response()->json(['data' => $staff]);
    }

    public function update(Request $request, $id)
    {
        $staff = Staff::find($id);
        if (!$staff)
            return response()->json(['message' => 'Staff tidak ditemukan'], 404);

        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            // Ignore email milik user ini saat validasi unique
            'email' => ['required', 'email', Rule::unique('users')->ignore($staff->user_id)],
            'password' => 'nullable|min:6', // Password boleh kosong kalau gak mau diganti
            'id_klinik' => 'required|exists:klinik,id',
            'peran' => 'required|in:dokter,admin,perawat,resepsionis,kasir',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validasi foto
        ]);

        DB::beginTransaction();
        try {
            // 1. Update User (Email & Password jika ada)
            $userData = ['email' => $request->email];
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }
            // Update tabel users
            User::where('id', $staff->user_id)->update($userData);
            $fotoPath = $staff->foto_profil; // Default pakai foto lama

            if ($request->hasFile('foto_profil') && $request->peran == 'dokter') {
                // Hapus foto lama jika ada
                if ($staff->foto_profil && Storage::disk('public')->exists($staff->foto_profil)) {
                    Storage::disk('public')->delete($staff->foto_profil);
                }
                // Upload foto baru
                $fotoPath = $request->file('foto_profil')->store('dokter-photos', 'public');
            }
            // 2. Update Data Staff
            $staff->update([
                'id_klinik' => $request->id_klinik,
                'nama_lengkap' => $request->nama_lengkap,
                'peran' => $request->peran,
                'no_hp' => $request->no_hp,
                'spesialisasi' => $request->peran == 'dokter' ? $request->spesialisasi : null,
                'tentang' => $request->peran == 'dokter' ? $request->tentang : null,
                'pengalaman' => $request->peran == 'dokter' ? $request->pengalaman : null,
                'foto_profil' => $fotoPath,
            ]);

            DB::commit();
            return response()->json(['message' => 'Data pegawai diperbarui']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal update: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $staff = Staff::find($id);
        if (!$staff) return response()->json(['message' => 'Not Found'], 404);

        // Hapus Foto Fisik
        if ($staff->foto_profil && Storage::disk('public')->exists($staff->foto_profil)) {
            Storage::disk('public')->delete($staff->foto_profil);
        }

        // Hapus Data (User & Staff) - logic lama
        $user = User::find($staff->user_id);
        if ($user) $user->delete();
        else $staff->delete();

        return response()->json(['message' => 'Pegawai berhasil dihapus']);
    }
}
