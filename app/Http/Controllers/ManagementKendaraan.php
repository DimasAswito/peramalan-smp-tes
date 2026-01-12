<?php

namespace App\Http\Controllers;

use App\Models\Kendaraan;
use Illuminate\Http\Request;

class ManagementKendaraan extends Controller
{
    public function index()
    {
        $kendaraans = Kendaraan::all();
        $totalKendaraan = $kendaraans->count();

        return view('menu.management_kendaraan', compact('kendaraans', 'totalKendaraan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kendaraan' => 'required|string|max:255',
        ]);

        Kendaraan::create([
            'nama_kendaraan' => $request->nama_kendaraan,
        ]);

        return redirect()->route('management_kendaraan.index')->with('success', 'Kendaraan berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_kendaraan' => 'required|string|max:255',
        ]);

        $kendaraan = Kendaraan::findOrFail($id);
        $kendaraan->update([
            'nama_kendaraan' => $request->nama_kendaraan,
        ]);

        return redirect()->route('management_kendaraan.index')->with('success', 'Kendaraan berhasil diperbarui');
    }

    public function destroy($id)
    {
        Kendaraan::findOrFail($id)->delete();
        return redirect()->route('management_kendaraan.index')->with('success', 'Kendaraan berhasil dihapus');
    }
}
