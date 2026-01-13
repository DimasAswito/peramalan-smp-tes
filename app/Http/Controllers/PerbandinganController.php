<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PerbandinganController extends Controller
{
    public function index()
    {
        $kendaraans = \App\Models\Kendaraan::all();
        $riwayat = \App\Models\Perbandingan::with('kendaraan')->latest()->get();
        return view('menu.perbandingan', compact('kendaraans', 'riwayat'));
    }

    public function process(Request $request)
    {
        $request->validate([
            'id_kendaraan' => 'required',
            'periode_sma' => 'required|integer|min:1',
            'durasi_prediksi' => 'required|integer|min:1',
            'alpha' => 'required|numeric|min:0|max:1',
            'beta' => 'required|numeric|min:0|max:1',
            'gamma' => 'required|numeric|min:0|max:1',
        ]);

        // Placeholder logic for now to match UI request first.
        // We will implement actual Math later or reuse traits/services if strictly requested now.
        // User asked "buatkan saya tampilannya... controllernya... tabel perbandingan... tapi tidak usah migrate fresher".
        // Use Mock data for display.

        $mae_sma = 15.5; $mse_sma = 240.2; $mape_sma = 5.6;
        $mae_tes = 10.2; $mse_tes = 110.5; $mape_tes = 4.2;
        
        $metode_terbaik = ($mape_sma < $mape_tes) ? 'SMA' : 'TES';

        // Labels
        $chartLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May'];
        $actualData = [100, 110, 105, 120, 115];
        $smaData = [null, null, 108, 115, 118];
        $tesData = [102, 108, 106, 118, 114];

        // Mock Data Structure for Store
        $result = [
            'sma' => ['mae' => $mae_sma, 'mse' => $mse_sma, 'mape' => $mape_sma, 'data' => $smaData],
            'tes' => ['mae' => $mae_tes, 'mse' => $mse_tes, 'mape' => $mape_tes, 'data' => $tesData],
            'chart' => ['labels' => $chartLabels, 'actual' => $actualData],
            'best' => $metode_terbaik
        ];

        $kendaraans = \App\Models\Kendaraan::all();
        $riwayat = collect([]); // Or fetch? Layout usually needs only historical.

        return view('menu.perbandingan', compact(
            'kendaraans', 'riwayat',
            'mae_sma', 'mse_sma', 'mape_sma',
            'mae_tes', 'mse_tes', 'mape_tes',
            'metode_terbaik',
            'chartLabels', 'actualData', 'smaData', 'tesData',
            'result'
        ))->with('showResult', true)->with('input', $request->all());
    }

    public function store(Request $request)
    {
        // Validation...
        \App\Models\Perbandingan::create([
             'id_kendaraan' => $request->id_kendaraan,
             'periode_sma' => $request->periode_sma,
             'durasi_prediksi' => $request->durasi_prediksi,
             'alpha' => $request->alpha,
             'beta' => $request->beta,
             'gamma' => $request->gamma,
             'mae_sma' => $request->mae_sma,
             'mse_sma' => $request->mse_sma,
             'mape_sma' => $request->mape_sma,
             'mae_tes' => $request->mae_tes,
             'mse_tes' => $request->mse_tes,
             'mape_tes' => $request->mape_tes,
             'metode_terbaik' => $request->metode_terbaik,
             'data_perbandingan' => json_decode($request->data_perbandingan, true)
        ]);

        return redirect()->route('perbandingan.index')->with('success', 'Hasil perbandingan berhasil disimpan.');
    }

    public function destroy($id)
    {
        \App\Models\Perbandingan::findOrFail($id)->delete();
        return redirect()->route('perbandingan.index')->with('success', 'Riwayat perbandingan berhasil dihapus.');
    }
}
