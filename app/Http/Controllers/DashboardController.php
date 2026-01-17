<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. CARDS DATA
        $countKendaraan = \App\Models\Kendaraan::count();
        $totalTransaksi = \App\Models\PemakaianKendaraan::sum('jumlah_transaksi');
        $countSma = \App\Models\PeramalanSma::count();
        $countTes = \App\Models\PeramalanTes::count(); // Assuming Model exists, user mentioned 'Peramalan_Tes' count

        // 3. DONUT CHART (Pie Chart) - Transaksi per Kendaraan
        // Group by vehicle
        $vehicles = \App\Models\Kendaraan::all();
        $donutLabels = [];
        $donutData = [];
        $colors = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796', '#5a5c69']; // Example palette
        
        foreach ($vehicles as $v) {
            $sum = \App\Models\PemakaianKendaraan::where('id_kendaraan', $v->id)->sum('jumlah_transaksi');
            // Include even if 0? Maybe not to keep chart clean.
            if ($sum > 0) {
                 $donutLabels[] = $v->merk . ' ' . $v->nama_kendaraan; 
                 $donutData[] = $sum;
            }
        }

        // 2. AREA CHART (Line Chart) - Data Aktual Transaksi
        // Allows filtering by vehicle via ID, defaults to first vehicle
        $selectedVehicleId = $request->query('vehicle_id');
        
        if ($selectedVehicleId) {
            $selectedVehicle = \App\Models\Kendaraan::find($selectedVehicleId);
        } else {
            $selectedVehicle = $vehicles->first();
        }

        $lineLabels = [];
        $lineData = [];
        $vehicleName = $selectedVehicle ? ($selectedVehicle->merk . ' ' . $selectedVehicle->nama_kendaraan) : 'Data Kosong';

        if ($selectedVehicle) {
            $transaksis = \App\Models\PemakaianKendaraan::where('id_kendaraan', $selectedVehicle->id)
                            ->orderBy('tahun', 'asc')
                            ->orderBy('bulan', 'asc')
                            ->get();
            
            foreach ($transaksis as $t) {
                $dateObj   = \DateTime::createFromFormat('!m', $t->bulan);
                $monthName = $dateObj->format('M'); // Jan, Feb...
                $lineLabels[] = $monthName . ' ' . $t->tahun;
                $lineData[] = $t->jumlah_transaksi;
            }
        }

        return view('menu.dashboard', compact(
            'countKendaraan', 'totalTransaksi', 'countSma', 'countTes',
            'donutLabels', 'donutData',
            'lineLabels', 'lineData', 'vehicleName',
            'vehicles', 'selectedVehicle'
        ));
    }
}