@extends('layouts.app')

@section('content')

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
  <h1 class="h3 mb-0 text-gray-800">Perbandingan Metode (SMA vs TES)</h1>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
  {{ session('success') }}
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
<script>
  setTimeout(function() {
    var alert = document.getElementById('success-alert');
    if (alert) {
      alert.classList.remove('show');
      setTimeout(function() {
        alert.remove();
      }, 150);
    }
  }, 3000);
</script>
@endif

<div class="row">
    <!-- Form Perbandingan -->
    <div class="col-xl-12 col-lg-12 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Form Perbandingan</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('perbandingan.process') }}" method="POST">
                    @csrf
                    <!-- Kendaraan & Durasi -->
                     <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="id_kendaraan">Pilih Kendaraan</label>
                            <select class="form-control" id="id_kendaraan" name="id_kendaraan" required>
                                <option value="">-- Pilih Kendaraan --</option>
                                @foreach($kendaraans as $k)
                                    <option value="{{ $k->id }}" {{ (isset($input) && $input['id_kendaraan'] == $k->id) ? 'selected' : '' }}>{{ $k->nama_kendaraan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="durasi_prediksi">Jumlah Periode Prediksi (Bulan)</label>
                            <input type="number" class="form-control" id="durasi_prediksi" name="durasi_prediksi" placeholder="Contoh: 12" value="{{ $input['durasi_prediksi'] ?? '' }}" required>
                        </div>
                    </div>
                    <hr>
                    <!-- Params SMA -->
                    <h6 class="font-weight-bold">Parameter SMA</h6>
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label for="periode_sma">Periode SMA (n)</label>
                            <input type="number" class="form-control" id="periode_sma" name="periode_sma" placeholder="Contoh: 3" value="{{ $input['periode_sma'] ?? '' }}" required>
                        </div>
                    </div>
                     <hr>
                    <!-- Params TES -->
                    <h6 class="font-weight-bold">Parameter TES</h6>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="alpha">Alpha</label>
                            <input type="number" step="0.01" min="0" max="1" class="form-control" id="alpha" name="alpha" placeholder="0 - 1" value="{{ $input['alpha'] ?? '' }}" required>
                        </div>
                         <div class="form-group col-md-4">
                            <label for="beta">Beta</label>
                            <input type="number" step="0.01" min="0" max="1" class="form-control" id="beta" name="beta" placeholder="0 - 1" value="{{ $input['beta'] ?? '' }}" required>
                        </div>
                         <div class="form-group col-md-4">
                            <label for="gamma">Gamma</label>
                            <input type="number" step="0.01" min="0" max="1" class="form-control" id="gamma" name="gamma" placeholder="0 - 1" value="{{ $input['gamma'] ?? '' }}" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">Proses Perbandingan</button>
                </form>
            </div>
        </div>
    </div>
</div>

@if(isset($showResult) && $showResult)
<div class="row">
    <div class="col-xl-12 col-lg-12 mb-4">
        <div class="card shadow h-100">
             <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Hasil Perbandingan</h6>
            </div>
            <div class="card-body">
                <!-- Accuracy Table -->
                <div class="table-responsive mb-4">
                    <table class="table table-bordered text-center">
                        <thead class="thead-light">
                            <tr>
                                <th>Metode</th>
                                <th>MAE</th>
                                <th>MSE</th>
                                <th>MAPE</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>SMA</td>
                                <td>{{ $mae_sma }}</td>
                                <td>{{ $mse_sma }}</td>
                                <td>{{ $mape_sma }}%</td>
                                <td>
                                    @if($metode_terbaik == 'SMA')
                                        <span class="badge badge-success">Terbaik</span>
                                    @endif
                                </td>
                            </tr>
                             <tr>
                                <td>TES</td>
                                <td>{{ $mae_tes }}</td>
                                <td>{{ $mse_tes }}</td>
                                <td>{{ $mape_tes }}%</td>
                                <td>
                                    @if($metode_terbaik == 'TES')
                                        <span class="badge badge-success">Terbaik</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Chart -->
                <div class="chart-area mb-4">
                    <canvas id="comparisonChart"></canvas>
                </div>

                <hr>
                
                <!-- Buttons -->
                 <div class="d-flex justify-content-end">
                    <form action="{{ route('perbandingan.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id_kendaraan" value="{{ $input['id_kendaraan'] }}">
                        <input type="hidden" name="periode_sma" value="{{ $input['periode_sma'] }}">
                        <input type="hidden" name="durasi_prediksi" value="{{ $input['durasi_prediksi'] }}">
                        <input type="hidden" name="alpha" value="{{ $input['alpha'] }}">
                        <input type="hidden" name="beta" value="{{ $input['beta'] }}">
                        <input type="hidden" name="gamma" value="{{ $input['gamma'] }}">
                        
                        <input type="hidden" name="mae_sma" value="{{ $mae_sma }}">
                        <input type="hidden" name="mse_sma" value="{{ $mse_sma }}">
                        <input type="hidden" name="mape_sma" value="{{ $mape_sma }}">
                        
                        <input type="hidden" name="mae_tes" value="{{ $mae_tes }}">
                        <input type="hidden" name="mse_tes" value="{{ $mse_tes }}">
                        <input type="hidden" name="mape_tes" value="{{ $mape_tes }}">
                        
                        <input type="hidden" name="metode_terbaik" value="{{ $metode_terbaik }}">
                        
                        {{-- Store result data mainly for details --}}
                        <input type="hidden" name="data_perbandingan" value="{{ json_encode($result) }}">
                        
                        <button type="submit" class="btn btn-success mr-2">Simpan Hasil</button>
                    </form>
                    <a href="{{ route('perbandingan.index') }}" class="btn btn-secondary">Buang</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- History Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Riwayat Perbandingan</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kendaraan</th>
                        <th>SMA (MAPE)</th>
                        <th>TES (MAPE)</th>
                        <th>Terbaik</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($riwayat as $index => $item)
                    <tr>
                         <td>{{ $index + 1 }}</td>
                         <td>{{ $item->kendaraan->nama_kendaraan ?? '-' }}</td>
                         <td>{{ $item->mape_sma }}%</td>
                         <td>{{ $item->mape_tes }}%</td>
                         <td>
                             <strong>{{ $item->metode_terbaik }}</strong>
                         </td>
                         <td>
                            <button class="btn btn-info btn-sm btn-circle" title="Detail" onclick='showDetail(@json($item))'>
                                <i class="fas fa-eye"></i>
                            </button>
                            <form action="{{ route('perbandingan.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus riwayat ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm btn-circle" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                         </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detailModalLabel">Detail Perbandingan</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
         <div class="table-responsive mb-3">
             <table class="table table-bordered text-center">
                 <thead>
                     <tr>
                         <th>Metode</th>
                         <th>MAE</th>
                         <th>MSE</th>
                         <th>MAPE</th>
                     </tr>
                 </thead>
                 <tbody id="detail_table_metrics">
                     <!-- Populated by JS -->
                 </tbody>
             </table>
         </div>
         <div class="chart-area">
             <canvas id="detailChart"></canvas>
         </div>
         <div class="mt-3 text-center">
             <h5>Metode Terbaik: <span class="badge badge-success" id="detail_best_method"></span></h5>
         </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable();
    });

    @if(isset($showResult) && $showResult)
    var ctx = document.getElementById("comparisonChart");
    var comparisonChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($chartLabels),
            datasets: [
                {
                    label: "Aktual",
                    lineTension: 0.3,
                    borderColor: "rgba(78, 115, 223, 1)",
                    pointRadius: 3,
                    pointBackgroundColor: "rgba(78, 115, 223, 1)",
                    data: @json($actualData),
                    fill: false
                },
                {
                    label: "SMA",
                    lineTension: 0.3,
                    borderColor: "rgba(28, 200, 138, 1)", // Green
                    pointRadius: 3,
                    pointBackgroundColor: "rgba(28, 200, 138, 1)",
                    data: @json($smaData),
                    fill: false,
                    borderDash: [5, 5]
                },
                {
                    label: "TES",
                    lineTension: 0.3,
                    borderColor: "rgba(246, 194, 62, 1)", // Yellow
                    pointRadius: 3,
                    pointBackgroundColor: "rgba(246, 194, 62, 1)",
                    data: @json($tesData),
                    fill: false,
                    borderDash: [5, 5]
                }
            ]
        },
        options: {
            maintainAspectRatio: false,
            tooltips: {
                mode: 'index',
                intersect: false
            }
        }
    });
    @endif

    var detailChartInstance = null;

    function showDetail(item) {
        // Pop metrics
        var tbody = $('#detail_table_metrics');
        tbody.empty();
        
        tbody.append(`
            <tr><td>SMA</td><td>${item.mae_sma}</td><td>${item.mse_sma}</td><td>${item.mape_sma}%</td></tr>
            <tr><td>TES</td><td>${item.mae_tes}</td><td>${item.mse_tes}</td><td>${item.mape_tes}%</td></tr>
        `);
        $('#detail_best_method').text(item.metode_terbaik);
        
        // Chart
        var chartData = item.data_perbandingan ? item.data_perbandingan.chart : null;
        var smaData = item.data_perbandingan ? item.data_perbandingan.sma.data : [];
        var tesData = item.data_perbandingan ? item.data_perbandingan.tes.data : [];
        
        if(chartData) {
            if(detailChartInstance) detailChartInstance.destroy();
            
            var ctx = document.getElementById("detailChart");
            detailChartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [
                         {
                            label: "Aktual",
                            borderColor: "rgba(78, 115, 223, 1)",
                            data: chartData.actual,
                            fill: false
                        },
                        {
                            label: "SMA",
                            borderColor: "rgba(28, 200, 138, 1)",
                            data: smaData,
                            fill: false,
                             borderDash: [5, 5]
                        },
                        {
                            label: "TES",
                            borderColor: "rgba(246, 194, 62, 1)",
                            data: tesData,
                            fill: false,
                             borderDash: [5, 5]
                        }
                    ]
                },
                options: {
                    maintainAspectRatio: false,
                    tooltips: { mode: 'index', intersect: false }
                }
            });
        }
        
        $('#detailModal').modal('show');
    }
</script>
@endpush
