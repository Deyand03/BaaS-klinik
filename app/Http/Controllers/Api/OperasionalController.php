<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Pasien;
use App\Models\Kunjungan;
use App\Models\Klinik; 
use App\Models\Staff; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class OperasionalController extends Controller
{
    public function index(Request $request)
    {
        // Debugging: Cek parameter yang masuk
        Log::info('API Data Hit. Filters:', $request->all());

        // --- SOLUSI CONFLICT: AMBIL DARI MAIN ---
        // Kita butuh inisialisasi ini agar tidak error di return bawah
        $clinicsList = [];
        $doctorsList = [];
        $tableData = null; // Inisialisasi default
        $chartData = [];
        $totalAllTime = 0;
        // ----------------------------------------
        
        // Data Chart
           $queryChart = Kunjungan::with('klinik');
           if ($request->filled('month')) {
               $queryChart->whereMonth('tgl_kunjungan', $request->month);
               $queryChart->whereYear('tgl_kunjungan', date('Y'));
           }
           $chartData = $queryChart->get();
           $totalAllTime = Pasien::count(); 
           
        // 1. DATA PASIEN
        if ($request->type == 'patients') {
            $queryTable = Pasien::orderBy('created_at', 'desc');

            if ($request->filled('search')) {
                $search = $request->search;
                $queryTable->where(function($q) use ($search) {
                    $q->where('nama_lengkap', 'like', "%{$search}%")
                      ->orWhere('nik', 'like', "%{$search}%");
                });
            }
            if ($request->filled('month_table')) {
                $queryTable->whereMonth('created_at', $request->month_table);
            }
            
            $tableData = $queryTable->paginate(10);
            $totalAllTime = Pasien::count(); // Hitung total pasien untuk meta

        } else {
            // 2. DATA KUNJUNGAN (ANTRIAN)
            $clinicsList = Klinik::select('id', 'nama')->get();
            
            // Ambil Dokter dari Staff
            $doctorsList = Staff::where('peran', 'dokter')
                ->select('id', 'nama_lengkap as nama') 
                ->get();

            $queryTable = Kunjungan::with(['pasien', 'klinik', 'dokter','jadwal'])
                ->orderBy('id', 'desc');

            // Filter Status
            if ($request->filled('status_filter')) {
                $queryTable->where('status', $request->status_filter);
            }

            // Filter Bulan (Tanpa Tahun, biar fleksibel)
            if ($request->filled('month_table')) {
                $queryTable->whereMonth('tgl_kunjungan', $request->month_table);
            }

            // 1. Filter Klinik: Gunakan 'id_klinik'
            if ($request->filled('klinik_id')) {
                $queryTable->where('id_klinik', $request->klinik_id);
            }
            
            // 2. Filter Dokter: Gunakan 'id_dokter'
            if ($request->filled('dokter_id')) {
                $queryTable->where('id_dokter', $request->dokter_id);
            }

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $queryTable->where(function($q) use ($search) {
                    $q->where('no_antrian', 'like', "%{$search}%")
                      ->orWhereHas('pasien', function($subQ) use ($search) {
                          $subQ->where('nama_lengkap', 'like', "%{$search}%");
                      });
                });
            }

            $tableData = $queryTable->paginate(10); 
            
           
        }

        return response()->json([
            'status' => 'success',
            'table' => $tableData, 
            'chart_source' => $chartData,
            'options' => [
                'clinics' => $clinicsList,
                'doctors' => $doctorsList
            ],
            'meta' => [
                'total_all_time' => $totalAllTime
            ]
        ]);
    }

    /**
     * 2. UPDATE STATUS (Estafet)
     */
    public function updateStatus(Request $request, $id)
    {
        $kunjungan = Kunjungan::find($id);
        
        if (!$kunjungan) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        // Validasi input status
        $request->validate([
            'status' => 'required|in:booking,menunggu_perawat,menunggu_dokter,menunggu_pembayaran,selesai,batal'
        ]);
        
        // Update Status
        $kunjungan->update([
            'status' => $request->status
        ]);

        return response()->json(['message' => 'Status berhasil diupdate', 'data' => $kunjungan]);
    }
}