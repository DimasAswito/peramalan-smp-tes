<?php

namespace App\Http\Controllers;

use App\Models\Kendaraan;
use App\Models\PeramalanSma;
use App\Models\PemakaianKendaraan;
use Illuminate\Http\Request;

class PeramalanSmaController extends Controller
{
    public function index()
    {
        $kendaraans = Kendaraan::all();
        $riwayat = PeramalanSma::with('kendaraan')->latest()->get();
        return view('menu.peramalan_sma', compact('kendaraans', 'riwayat'));
    }
    public function process(Request $request)
    {
        $request->validate([
            'id_kendaraan' => 'required|exists:kendaraan,id',
            'periode' => 'required|integer|min:1',
            'durasi_prediksi' => 'required|integer|min:1',
        ]);

        $id_kendaraan = $request->id_kendaraan;
        $periode = $request->periode; // n
        $durasi = $request->durasi_prediksi;

        // Fetch Historical Data
        $dataPemakaian = PemakaianKendaraan::where('id_kendaraan', $id_kendaraan)
            ->orderBy('tahun', 'asc')
            ->orderBy('bulan', 'asc')
            ->get();

        if ($dataPemakaian->count() < $periode) {
            return back()->with('error', 'Data historis tidak cukup untuk melakukan peramalan dengan periode (n) = ' . $periode);
        }

        $resultTable = [];
        $d = []; // Data array for easier access
        foreach ($dataPemakaian as $idx => $pem) {
            $d[] = [
                'bulan' => $pem->bulan,
                'tahun' => $pem->tahun,
                'aktual' => $pem->jumlah_transaksi,
                'bulan_tahun' => $this->getMonthName($pem->bulan) . ' ' . $pem->tahun
            ];
        }

        $total_error_abs = 0;
        $total_error_sqr = 0;
        $total_ape = 0;
        $count_error = 0;

        // 1. Calculate SMA on History (Testing) from index = $periode (since we need n prior data points)
        // Adjusting loop to start calculating prediction for index $i based on $i-1...$i-$periode
        // Actually, prediction for time t uses t-1, t-2... t-n.
        // So prediction for index $periode (the (n+1)th item) uses index 0 to n-1.

        for ($i = 0; $i < count($d); $i++) {
            $prediksi = null;
            $error_abs = null;
            $error_sqr = null;
            $ape = null;

            if ($i >= $periode) {
                $sum = 0;
                for ($k = 1; $k <= $periode; $k++) {
                    $sum += $d[$i - $k]['aktual'];
                }
                $prediksi = $sum / $periode;

                // Precision check
                $prediksi = round($prediksi, 2);
                $aktual = $d[$i]['aktual'];

                $error_abs = abs($aktual - $prediksi);
                $error_sqr = pow($error_abs, 2);
                $ape = ($aktual != 0) ? ($error_abs / $aktual) * 100 : 0;

                $total_error_abs += $error_abs;
                $total_error_sqr += $error_sqr;
                $total_ape += $ape;
                $count_error++;
            }

            $d[$i]['prediksi'] = $prediksi;
            $d[$i]['error_abs'] = $error_abs;
            $d[$i]['error_sqr'] = $error_sqr;
            $d[$i]['ape'] = $ape;

            // Format for table
            $resultTable[] = [
                'bulan_tahun' => $d[$i]['bulan_tahun'],
                'aktual' => $d[$i]['aktual'],
                'prediksi' => $prediksi,
                'error' => $error_abs !== null ? number_format($error_abs, 2) : '-',
                'error_sqr' => $error_sqr !== null ? number_format($error_sqr, 2) : '-',
                'ape' => $ape !== null ? number_format($ape, 2) : '-',
            ];
        }

        // Metrics
        $mae = ($count_error > 0) ? round($total_error_abs / $count_error, 2) : 0;
        $mse = ($count_error > 0) ? round($total_error_sqr / $count_error, 2) : 0;
        $mape = ($count_error > 0) ? round($total_ape / $count_error, 2) : 0;

        // 2. Forecast Future
        // To forecast index T+1... we use moving window.
        // We append predicted values to be used for next predictions if needed? 
        // Standard SMA uses ACTUAL data if available. For pure future, we must use predicted data (if we assume no actuals).
        // OR we just use the last n Actuals to predict T+1. Then (Last n-1 Actuals + T+1 Predicted) to predict T+2?
        // "Rolling forecast" usually uses predicted values.

        $futureData = [];
        $tempData = $d; // Copy to extend

        // Last available month/year
        $lastMonth = $d[count($d) - 1]['bulan'];
        $lastYear = $d[count($d) - 1]['tahun'];

        for ($j = 0; $j < $durasi; $j++) {
            // Next Date
            $lastMonth++;
            if ($lastMonth > 12) {
                $lastMonth = 1;
                $lastYear++;
            }

            // Calculate Prediction
            // Uses last $periode values from $tempData (which might contain mixed actuals and predictions?)
            // Usually SMA implies using Actuals. If we run out of actuals, we use the predictions we just made.
            $len = count($tempData);
            $sum = 0;
            for ($k = 1; $k <= $periode; $k++) {
                // Determine value: if we are in history, use 'aktual'. If future, use 'prediksi'.
                // Wait, $tempData structure above has 'aktual'.
                // For future rows, 'aktual' is undefined. We should use 'prediksi' as 'aktual' placeholder for further calc?
                // Let's use 'val' helper.
                $idx = $len - $k;
                if (isset($tempData[$idx]['aktual'])) {
                    $val = $tempData[$idx]['aktual'];
                } else {
                    $val = $tempData[$idx]['prediksi'];
                }
                $sum += $val;
            }
            $predFuture = round($sum / $periode, 2);

            $newRow = [
                'bulan' => $lastMonth,
                'tahun' => $lastYear,
                'bulan_tahun' => $this->getMonthName($lastMonth) . ' ' . $lastYear,
                'aktual' => null, // Future
                'prediksi' => $predFuture,
                'error_abs' => null,
                'error_sqr' => null,
                'ape' => null
            ];

            $tempData[] = $newRow;
            $resultTable[] = [
                'bulan_tahun' => $newRow['bulan_tahun'],
                'aktual' => '-',
                'prediksi' => $predFuture,
                'error' => '-',
                'error_sqr' => '-',
                'ape' => '-'
            ];
        }


        // Prepare Chart Data
        $chartLabels = [];
        $actualData = [];
        $predictedData = [];

        foreach ($resultTable as $row) {
            $chartLabels[] = $row['bulan_tahun'];
            $actualData[] = $row['aktual'] !== '-' ? $row['aktual'] : null;
            $predictedData[] = $row['prediksi'] !== '-' ? $row['prediksi'] : null;
        }

        $data_peramalan = [
            'metrics' => ['mae' => $mae, 'mse' => $mse, 'mape' => $mape],
            'table' => $resultTable,
            'chart' => ['labels' => $chartLabels, 'actual' => $actualData, 'predicted' => $predictedData]
        ];

        $data_json = json_encode($data_peramalan); // Not strictly used since blade uses $resultTable directly for table and vars for chart, but good for saving. Hidden input uses json_encode($resultTable). Wait, I should ensure consistency. 
        // The Blade I wrote uses `json_encode($resultTable)` for the hidden input `data_peramalan`. 
        // But the `store` method expects `data_peramalan` to be the JSON string.
        // And the `PeramalanSma` model's `data_peramalan` field stores JSON.
        // My implementation in `store` uses `json_decode` then creates record.
        // So the hidden input should be the full JSON structure I want to save.
        // I'll stick to saving just the table array for now as typical usage, or do I want chart data too? 
        // The modal reconstructs chart from table data, so saving table is enough.

        $kendaraans = Kendaraan::all();
        $riwayat = PeramalanSma::with('kendaraan')->latest()->get();

        return view('menu.peramalan_sma', compact(
            'kendaraans',
            'riwayat',
            'mae',
            'mse',
            'mape',
            'chartLabels',
            'actualData',
            'predictedData',
            'resultTable',
            'id_kendaraan',
            'periode',
            'durasi',
            'data_peramalan',
            'data_json'
        ))->with('showResult', true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_kendaraan' => 'required|exists:kendaraan,id',
            'periode' => 'required',
            'durasi_prediksi' => 'required',
            'mae' => 'required',
            'mse' => 'required',
            'mape' => 'required',
            'data_peramalan' => 'required', // JSON string
        ]);

        PeramalanSma::create([
            'id_kendaraan' => $request->id_kendaraan,
            'periode_sma' => $request->periode,
            'durasi_prediksi' => $request->durasi_prediksi,
            'mae' => $request->mae,
            'mse' => $request->mse,
            'mape' => $request->mape,
            'data_peramalan' => json_decode($request->data_peramalan, true) // Decode to array because Cast handles encoding
        ]);

        return redirect()->route('peramalan_sma.index')->with('success', 'Hasil peramalan berhasil disimpan.');
    }

    public function destroy($id)
    {
        PeramalanSma::findOrFail($id)->delete();
        return redirect()->route('peramalan_sma.index')->with('success', 'Riwayat peramalan berhasil dihapus.');
    }

    private function getMonthName($monthNum)
    {
        $dateObj   = \DateTime::createFromFormat('!m', $monthNum);
        return $dateObj->format('F');
    }
}
