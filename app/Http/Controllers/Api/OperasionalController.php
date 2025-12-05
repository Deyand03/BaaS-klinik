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

        $clinicsList = [];
        $doctorsList = [];

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

        } else {
            // 2. DATA KUNJUNGAN (ANTRIAN)
            $clinicsList = Klinik::select('id', 'nama')->get();
            
            // Ambil Dokter dari Staff
            $doctorsList = Staff::where('peran', 'dokter')
                ->select('id', 'nama_lengkap as nama') 
                ->get();

            $queryTable = Kunjungan::with(['pasien', 'klinik', 'dokter'])
                ->orderBy('id', 'desc');

            // Filter Status
            if ($request->filled('status_filter')) {
                $queryTable->where('status', $request->status_filter);
            }

            // Filter Bulan (Tanpa Tahun, biar fleksibel)
            if ($request->filled('month_table')) {
                $queryTable->whereMonth('tgl_kunjungan', $request->month_table);
            }

            // --- PERBAIKAN PENTING DI SINI (Sesuai api.php) ---
            
            // 1. Filter Klinik: Gunakan 'id_klinik' bukan 'klinik_id'
            if ($request->filled('klinik_id')) {
                $queryTable->where('id_klinik', $request->klinik_id);
            }
            
            // 2. Filter Dokter: Gunakan 'id_dokter' bukan 'dokter_id'
            if ($request->filled('dokter_id')) {
                $queryTable->where('id_dokter', $request->dokter_id);
            }
            // ---------------------------------------------------

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
        }

        $tableData = $queryTable->paginate(10); 
        
        // Data Chart
        $queryChart = Kunjungan::with('klinik');
        if ($request->filled('month')) {
            $queryChart->whereMonth('tgl_kunjungan', $request->month);
            $queryChart->whereYear('tgl_kunjungan', date('Y'));
        }
        $chartData = $queryChart->get();
        $totalAllTime = Pasien::count(); 

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

}