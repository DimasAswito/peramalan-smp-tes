<?php

namespace App\Http\Controllers;

use App\Models\Kendaraan;
use Illuminate\Http\Request;
use App\Models\PemakaianKendaraan;

class InputDataController extends Controller
{
    public function index()
    {
        $kendaraans = Kendaraan::all();
        $pemakaian = PemakaianKendaraan::with('kendaraan')->orderBy('tahun', 'desc')->orderBy('bulan', 'desc')->get();

        // Prepare Chart Data
        $chartData = $pemakaian->groupBy('kendaraan.nama_kendaraan')->map(function ($row) {
            return $row->sum('jumlah_transaksi');
        });

        $chartLabels = $chartData->keys();
        $chartValues = $chartData->values();

        return view('menu.management_data', compact('kendaraans', 'pemakaian', 'chartLabels', 'chartValues'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_kendaraan' => 'required|exists:kendaraan,id',
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'jumlah_transaksi' => 'required|integer|min:0',
        ]);

        // Check for duplicate
        $exists = PemakaianKendaraan::where('id_kendaraan', $request->id_kendaraan)
            ->where('bulan', $request->bulan)
            ->where('tahun', $request->tahun)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Data untuk kendaraan, bulan, dan tahun tersebut sudah ada.');
        }

        PemakaianKendaraan::create($request->all());

        return redirect()->route('input_data.index')->with('success', 'Data pemakaian berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'id_kendaraan' => 'required|exists:kendaraan,id',
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'jumlah_transaksi' => 'required|integer|min:0',
        ]);

        $data = PemakaianKendaraan::findOrFail($id);

        // Check for duplicate if changing keys
        if ($data->id_kendaraan != $request->id_kendaraan || $data->bulan != $request->bulan || $data->tahun != $request->tahun) {
            $exists = PemakaianKendaraan::where('id_kendaraan', $request->id_kendaraan)
                ->where('bulan', $request->bulan)
                ->where('tahun', $request->tahun)
                ->where('id', '!=', $id)
                ->exists();
            if ($exists) {
                return back()->with('error', 'Data untuk kendaraan, bulan, dan tahun tersebut sudah ada.');
            }
        }

        $data->update($request->all());

        return redirect()->route('input_data.index')->with('success', 'Data pemakaian berhasil diperbarui');
    }

    public function destroy($id)
    {
        PemakaianKendaraan::findOrFail($id)->delete();
        return redirect()->route('input_data.index')->with('success', 'Data pemakaian berhasil dihapus');
    }
}
