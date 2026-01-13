<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PeramalanTesController extends Controller
{
    public function index()
    {
        $kendaraans = \App\Models\Kendaraan::all();
        $riwayat = \App\Models\PeramalanTes::with('kendaraan')->latest()->get();
        return view('menu.peramalan_tes', compact('kendaraans', 'riwayat'));
    }
    public function process(Request $request)
    {
        $request->validate([
            'id_kendaraan' => 'required|exists:kendaraan,id',
            'durasi_prediksi' => 'required|integer|min:1',
            'alpha' => 'required|numeric|min:0|max:1',
            'beta' => 'required|numeric|min:0|max:1',
            'gamma' => 'required|numeric|min:0|max:1',
        ]);

        $id_kendaraan = $request->id_kendaraan;
        $durasi = $request->durasi_prediksi;
        $alpha = $request->alpha;
        $beta = $request->beta;
        $gamma = $request->gamma;
        $L = 12; // Season Length (Monthly data => 12 months)

        // Fetch Historical Data
        $dataPemakaian = \App\Models\PemakaianKendaraan::where('id_kendaraan', $id_kendaraan)
            ->orderBy('tahun', 'asc')
            ->orderBy('bulan', 'asc')
            ->get();

        // Check sufficient data (Need at least L to initialize, better 2*L but simplistic initialization uses min L)
        if ($dataPemakaian->count() < $L) {
            return back()->with('error', 'Data historis harus minimal ' . $L . ' bulan untuk metode TES (Musiman Tahunan).');
        }

        $resultTable = [];
        $d = [];
        foreach ($dataPemakaian as $pem) {
            $d[] = [
                'bulan' => $pem->bulan,
                'tahun' => $pem->tahun,
                'aktual' => $pem->jumlah_transaksi,
                'bulan_tahun' => $this->getMonthName($pem->bulan) . ' ' . $pem->tahun
            ];
        }

        // --- Initialization (Simple Method) ---
        // L1 = Average of first year data (Not exactly, but for simple Level initialization let's use Average of first L)
        // Actually, typical init: L_L = average of first L observations.
        // T_L = (Average of second L - Average of first L) / L (Only if we have 2L data, else 0)
        // S_t = Y_t / L_L (For t=1..L) - but need to normalize S so sum(S) = L.

        // Simpler initialization for t=L (End of first season)
        // Let's assume we start calculating for t = L+1.
        // We need initial Level (L_L), Trend (T_L), and Seasonal Indices (S_1...S_L).

        $nData = count($d);
        $seasonals = [];

        // Calculate average of first L
        $avgFirstSeason = 0;
        for ($i = 0; $i < $L; $i++) $avgFirstSeason += $d[$i]['aktual'];
        $avgFirstSeason /= $L;

        // Initialize Seasonals (S_1 to S_L)
        // S_i = D_i / AvgFirstSeason
        for ($i = 0; $i < $L; $i++) {
            $seasonals[] = $d[$i]['aktual'] / ($avgFirstSeason ?: 1); // Avoid div zero
        }
        // Normalize Seasonals? Sum should be L roughly. Let's skip complex normalization for this impl unless required.

        // Initial Level and Trend at t=L
        // Basic: L_L = Y_L / S_L ?? No.
        // Let's use simpler HW init:
        // L_L (Level at end of 1st season) = AvgFirstSeason (approximated)
        // T_L (Trend at end of 1st season) = 0 (approximated if insufficient data)
        // If we have more data, T0 = (Mean(Y_L+1..Y_2L) - Mean(Y_1..Y_L)) / L

        $level = $avgFirstSeason;
        $trend = 0;
        if ($nData >= 2 * $L) {
            $sumSecond = 0;
            for ($i = $L; $i < 2 * $L; $i++) $sumSecond += $d[$i]['aktual'];
            $avgSecond = $sumSecond / $L;
            $trend = ($avgSecond - $avgFirstSeason) / $L;
        }

        // Store History of Params for Access
        // We track params indexed by data index $i. 
        // Initial setup for t=L-1 (index L-1).
        $levels = [];
        $trends = [];
        // Seasonal indices stored in a separate list or array. 
        // We need seasonals for time t. S_t is updated. 
        // We need an array spanning all time.
        // Let's pre-fill seasonals for indices 0 to L-1. (t=1..L)
        $seasonal_indices = $seasonals; // indices 0..11

        // Fill initial display data for the "Initialization Period" (First L items)
        for ($i = 0; $i < $L; $i++) {
            $resultTable[] = [
                'bulan_tahun' => $d[$i]['bulan_tahun'],
                'aktual' => $d[$i]['aktual'],
                'level' => '-',
                'trend' => '-',
                'seasonal' => round($seasonal_indices[$i], 3),
                'prediksi' => '-', // No prediction for first season in this simple init
                'error' => '-',
                'error_sqr' => '-',
                'ape' => '-'
            ];
        }

        // Current parameters referring to end of time t
        $currLevel = $level;
        $currTrend = $trend;

        // We need S at t-L.
        // seasonal_indices array will grow. seasonal_indices[t] is S_t.

        $total_error_abs = 0;
        $total_error_sqr = 0;
        $total_ape = 0;
        $count_error = 0;

        // Iterate from t = L (index L) to end
        // Prediction for time t (index i) is made at t-1.
        // F_t+m = (L_t + m*T_t) * S_t-L+m.
        // For forecast 1 step ahead (m=1): F_t+1 = (L_t + T_t) * S_t+1-L
        // So Pred for index i (time t) uses Level_{i-1}, Trend_{i-1}, Seasonal_{i-L}

        // BUT wait, we initialized Level and Trend at index L-1 (end of season 1).
        // So we can predict index L.

        for ($i = $L; $i < $nData; $i++) {
            $prevLevel = $currLevel;
            $prevTrend = $currTrend;
            $seasonalIndexLoc = $i - $L; // S_{t-L} is at this index in our list (0..11 for i=12)
            $prevSeasonal = $seasonal_indices[$seasonalIndexLoc];

            // 1. Forecast for current i (using params from i-1)
            $prediksi = ($prevLevel + $prevTrend) * $prevSeasonal;
            $prediksi = round($prediksi, 2);
            $aktual = $d[$i]['aktual'];

            // 2. Metrics
            $error_abs = abs($aktual - $prediksi);
            $error_sqr = pow($error_abs, 2);
            $ape = ($aktual != 0) ? ($error_abs / $aktual) * 100 : 0;

            $total_error_abs += $error_abs;
            $total_error_sqr += $error_sqr;
            $total_ape += $ape;
            $count_error++;

            // 3. Update Parameters (Level, Trend, Seasonal) for time i
            // L_t = alpha * (Y_t / S_{t-L}) + (1-alpha)*(L_{t-1} + T_{t-1})
            $newLevel = $alpha * ($aktual / $prevSeasonal) + (1 - $alpha) * ($prevLevel + $prevTrend);

            // T_t = beta * (L_t - L_{t-1}) + (1-beta)*T_{t-1}
            $newTrend = $beta * ($newLevel - $prevLevel) + (1 - $beta) * $prevTrend;

            // S_t = gamma * (Y_t / L_t) + (1-gamma)*S_{t-L}
            $newSeasonal = $gamma * ($aktual / $newLevel) + (1 - $gamma) * $prevSeasonal;

            // Update currents
            $currLevel = $newLevel;
            $currTrend = $newTrend;
            $seasonal_indices[] = $newSeasonal; // Store S_t for future use (at index i)

            $resultTable[] = [
                'bulan_tahun' => $d[$i]['bulan_tahun'],
                'aktual' => $aktual,
                'level' => round($newLevel, 2),
                'trend' => round($newTrend, 2),
                'seasonal' => round($newSeasonal, 3),
                'prediksi' => $prediksi,
                'error' => number_format($error_abs, 2),
                'error_sqr' => number_format($error_sqr, 2),
                'ape' => number_format($ape, 2)
            ];
        }

        // --- Metrics Aggregate ---
        $mae = ($count_error > 0) ? round($total_error_abs / $count_error, 2) : 0;
        $mse = ($count_error > 0) ? round($total_error_sqr / $count_error, 2) : 0;
        $mape = ($count_error > 0) ? round($total_ape / $count_error, 2) : 0;

        // --- Future Forecasting (Durasi) ---
        // From end of data (index nData-1). Params are $currLevel, $currTrend.
        // S indices available up to index nData-1.
        // Forecast for h = 1 to Durasi
        // F_n+h = (L_n + h*T_n) * S_n+h-L

        $lastMonth = $d[$nData - 1]['bulan'];
        $lastYear = $d[$nData - 1]['tahun'];

        for ($h = 1; $h <= $durasi; $h++) {
            // Date
            $lastMonth++;
            if ($lastMonth > 12) {
                $lastMonth = 1;
                $lastYear++;
            }

            // Seasonal Index needed is S_{n+h-L}. 
            // We have indices up to $nData - 1. 
            // We need index ($nData - 1 + h) - L ?? No.
            // Usually we recycle the last available seasonal indices if we don't update them?
            // Or better: S for "Month X" is the last calculated S for that Month X.
            // Index in $seasonal_indices: $nData + $h - 1 - L ??
            // Actually: we need S for the month corresponding to n+h.
            // In our array `seasonal_indices`, we have stored Seasonals up to $nData-1.
            // To forecast n+h, we need S_{n+h-L}. 
            // Since we have data up to n, we have calculated S up to n (index n-1).
            // If h=1 -> need S_{n+1-L} -> index n - L. (This index exists).
            // If h > L? We might need recycled indices if we don't have enough history or if we project very far?
            // Triple Exponential Smoothing (Holt-Winters) usually "projects" seasonals periodically?
            // "The seasonal component is ... the last observed seasonal value for that season."
            // So we define "offset" = (nData + h - 1) % L ?? No.
            // We look back L steps. If that step is within our computed range, we use it. 
            // If it's still future, we look back 2L? 
            // Simplest: `S_new = S_{nData + h - 1 - L * k}` where index exists.
            // Basically `index = (nData + h - 1) - L`. If this index < nData (it is for h<=L), good.
            // If h > L, we need to wrap back.

            $s_idx = ($nData + $h - 1) - $L;
            while ($s_idx >= count($seasonal_indices)) { // in case logic
                $s_idx -= $L; // Go back another year
            }
            if ($s_idx < 0) {
                // Should not happen if we have > L history and initialized
                $s_idx = ($s_idx % $L + $L) % $L; // Fallback to initial indices 0..L-1
            }

            $S_proj = $seasonal_indices[$s_idx];

            $predFuture = ($currLevel + $h * $currTrend) * $S_proj;
            $predFuture = round($predFuture, 2);

            $resultTable[] = [
                'bulan_tahun' => $this->getMonthName($lastMonth) . ' ' . $lastYear,
                'aktual' => '-',
                'level' => '-', // Usually not shown for pure forecast or is it?
                'trend' => '-',
                'seasonal' => '-', // Or show S_proj?
                'prediksi' => $predFuture,
                'error' => '-',
                'error_sqr' => '-',
                'ape' => '-'
            ];
        }

        // Prepare Chart
        $chartLabels = [];
        $actualData = [];
        $predictedData = [];

        foreach ($resultTable as $row) {
            $chartLabels[] = $row['bulan_tahun'];
            $actualData[] = ($row['aktual'] !== '-' && $row['aktual'] !== null) ? $row['aktual'] : null;
            $predictedData[] = ($row['prediksi'] !== '-' && $row['prediksi'] !== null) ? $row['prediksi'] : null;
        }

        // Prepare Data for View/Store
        // Note: 'data_peramalan' for store is usually JSON string of the table or full struct.
        // View uses variables directly.

        $kendaraans = \App\Models\Kendaraan::all();
        // Riwayat is for the bottom table, should strictly come from DB? 
        // Or we pass empty if we just processed? The view uses $riwayat variable. 
        // We should fetch latest to show.
        $riwayat = \App\Models\PeramalanTes::with('kendaraan')->latest()->get();

        return view('menu.peramalan_tes', compact(
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
            'durasi',
            'alpha',
            'beta',
            'gamma'
        ))->with('showResult', true)->with('input', $request->all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_kendaraan' => 'required',
            'alpha' => 'required',
            'beta' => 'required',
            'gamma' => 'required',
            'durasi_prediksi' => 'required',
            'mae' => 'required',
            'mse' => 'required',
            'mape' => 'required',
            'data_peramalan' => 'required', // json string
        ]);

        \App\Models\PeramalanTes::create([
            'id_kendaraan' => $request->id_kendaraan,
            'alfa' => $request->alpha, // Model uses 'alfa' usually? Checked model file, it says 'alfa'.
            'beta' => $request->beta,
            'gamma' => $request->gamma,
            'durasi_prediksi' => $request->durasi_prediksi,
            'mae' => $request->mae,
            'mse' => $request->mse,
            'mape' => $request->mape,
            'data_peramalan' => json_decode($request->data_peramalan, true)
        ]);

        return redirect()->route('peramalan_tes.index')->with('success', 'Hasil peramalan TES berhasil disimpan.');
    }

    public function destroy($id)
    {
        \App\Models\PeramalanTes::findOrFail($id)->delete();
        return redirect()->route('peramalan_tes.index')->with('success', 'Riwayat peramalan berhasil dihapus.');
    }

    private function getMonthName($monthNum)
    {
        $dateObj   = \DateTime::createFromFormat('!m', $monthNum);
        return $dateObj->format('F');
    }
}
