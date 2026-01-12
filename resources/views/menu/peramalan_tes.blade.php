@extends('layouts.app')

@section('content')

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
  <h1 class="h3 mb-0 text-gray-800">Peramalan TES (Triple Exponential Smoothing)</h1>
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
    <!-- Form Peramalan -->
    <div class="col-xl-12 col-lg-12 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Form Peramalan TES</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('peramalan_tes.process') }}" method="POST">
                    @csrf
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="id_kendaraan">Pilih Kendaraan</label>
                            <select class="form-control" id="id_kendaraan" name="id_kendaraan" required>
                                <option value="">-- Pilih Kendaraan --</option>
                                @foreach($kendaraans as $k)
                                    <option value="{{ $k->id }}">{{ $k->nama_kendaraan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="durasi_prediksi">Jumlah Periode Prediksi</label>
                            <input type="number" class="form-control" id="durasi_prediksi" name="durasi_prediksi" placeholder="Contoh: 12" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="alpha">Nilai Alpha</label>
                            <input type="number" step="0.01" min="0" max="1" class="form-control" id="alpha" name="alpha" placeholder="0 - 1" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="beta">Nilai Beta</label>
                            <input type="number" step="0.01" min="0" max="1" class="form-control" id="beta" name="beta" placeholder="0 - 1" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="gamma">Nilai Gamma</label>
                            <input type="number" step="0.01" min="0" max="1" class="form-control" id="gamma" name="gamma" placeholder="0 - 1" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Proses Peramalan</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Result Card (Placeholder Structure) -->
@if(isset($showResult) && $showResult)
<div class="row" id="result-card">
    <div class="col-xl-12 col-lg-12 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Hasil Prediksi TES</h6>
            </div>
            <div class="card-body">
                <!-- Detailed Table (Top) -->
                 <div class="table-responsive mb-4">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Bulan/Tahun</th>
                                <th>Data Aktual</th>
                                <th>Level (Lt)</th>
                                <th>Trend (Tt)</th>
                                <th>Seasonal (St)</th>
                                <th>Data Prediksi</th>
                                <th>Error</th>
                            </tr>
                        </thead>
                        <tbody>
                             @foreach($resultTable as $row)
                             <tr>
                                 <td>{{ $row['bulan_tahun'] }}</td>
                                 <td>{{ $row['aktual'] }}</td>
                                 <td>{{ $row['level'] ?? '-' }}</td>
                                 <td>{{ $row['trend'] ?? '-' }}</td>
                                 <td>{{ $row['seasonal'] ?? '-' }}</td>
                                 <td>{{ $row['prediksi'] }}</td>
                                 <td>{{ $row['error'] }}</td>
                             </tr>
                             @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="row">
                    <!-- Chart -->
                    <div class="col-lg-8">
                         <div class="chart-area">
                            <canvas id="tesChart"></canvas>
                        </div>
                    </div>
                    <!-- Metrics -->
                    <div class="col-lg-4">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Metric</th>
                                        <th>Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td>MAE</td><td>{{ $mae }}</td></tr>
                                    <tr><td>MSE</td><td>{{ $mse }}</td></tr>
                                    <tr><td>MAPE</td><td>{{ $mape }} %</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <!-- Buttons (Bottom Right) -->
                <div class="d-flex justify-content-end">
                    <form action="{{ route('peramalan_tes.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id_kendaraan" value="{{ $id_kendaraan }}">
                        <input type="hidden" name="durasi_prediksi" value="{{ $durasi }}">
                        <input type="hidden" name="alpha" value="{{ $alpha }}">
                        <input type="hidden" name="beta" value="{{ $beta }}">
                        <input type="hidden" name="gamma" value="{{ $gamma }}">
                        <input type="hidden" name="mae" value="{{ $mae }}">
                        <input type="hidden" name="mse" value="{{ $mse }}">
                        <input type="hidden" name="mape" value="{{ $mape }}">
                        <input type="hidden" name="data_peramalan" value="{{ json_encode($resultTable) }}">
                        <button type="submit" class="btn btn-success mr-2">Simpan Hasil</button>
                    </form>
                    <a href="{{ url('/peramalan-tes') }}" class="btn btn-secondary">Buang</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- History Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Riwayat Peramalan TES</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th>Kendaraan</th>
                        <th>Alpha</th>
                        <th>Beta</th>
                        <th>Gamma</th>
                        <th>MAE</th>
                        <th>MSE</th>
                        <th>MAPE</th>
                        <th width="10%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($riwayat as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->kendaraan->nama_kendaraan ?? '-' }}</td>
                        <td>{{ $item->alfa }}</td> {{-- Model uses alfa --}}
                        <td>{{ $item->beta }}</td>
                        <td>{{ $item->gamma }}</td>
                        <td>{{ $item->mae }}</td>
                        <td>{{ $item->mse }}</td>
                        <td>{{ $item->mape }}%</td>
                        <td>
                            <button class="btn btn-info btn-sm btn-circle" title="Detail" onclick='showDetail(@json($item))'>
                                <i class="fas fa-eye"></i>
                            </button>
                            <form action="{{ route('peramalan_tes.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus riwayat ini?')">
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
                <h5 class="modal-title" id="detailModalLabel">Detail Peramalan TES</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="d-flex flex-wrap mb-3">
                    <div class="mr-4"><strong>Kendaraan:</strong> <span id="detail_kendaraan"></span></div>
                    <div class="mr-4"><strong>Alpha:</strong> <span id="detail_alpha"></span></div>
                    <div class="mr-4"><strong>Beta:</strong> <span id="detail_beta"></span></div>
                    <div class="mr-4"><strong>Gamma:</strong> <span id="detail_gamma"></span></div>
                    <div class="mr-4"><strong>Durasi:</strong> <span id="detail_durasi"></span></div>
                    <div><strong>Metode:</strong> TES</div>
                </div>

                <!-- Detailed Table -->
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-striped" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Bulan/Tahun</th>
                                <th>Data Aktual</th>
                                <th>Level</th>
                                <th>Trend</th>
                                <th>Seasonal</th>
                                <th>Data Prediksi</th>
                                <th>Error</th>
                            </tr>
                        </thead>
                        <tbody id="detail_table_body">
                            <!-- Data populated by JS -->
                        </tbody>
                    </table>
                </div>

                 <div class="row">
                    <!-- Chart -->
                    <div class="col-lg-8">
                         <div class="chart-area">
                            <canvas id="detailChart"></canvas>
                        </div>
                    </div>
                    <!-- Metrics -->
                    <div class="col-lg-4">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Metric</th>
                                        <th>Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td>MAE</td><td id="detail_mae"></td></tr>
                                    <tr><td>MSE</td><td id="detail_mse"></td></tr>
                                    <tr><td>MAPE</td><td id="detail_mape"></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
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

    var detailChartInstance = null;

    function showDetail(item) {
        // Set basic info
        $('#detail_kendaraan').text(item.kendaraan ? item.kendaraan.nama_kendaraan : '-');
        $('#detail_alpha').text(item.alpha);
        $('#detail_beta').text(item.beta);
        $('#detail_gamma').text(item.gamma);
        $('#detail_mae').text(item.mae);
        $('#detail_mse').text(item.mse);
        $('#detail_mape').text(item.mape + '%');
        $('#detail_durasi').text(item.durasi_prediksi);

        // Populate table & Prepare chart arrays
        var tbody = $('#detail_table_body');
        tbody.empty();

        var labels = [];
        var actuals = [];
        var predicteds = [];

        if (item.data_peramalan && item.data_peramalan.length > 0) {
            item.data_peramalan.forEach(function(row) {
                // Assuming similar structure but with extra TES fields
                var tr = `
                    <tr>
                        <td>${row.bulan_tahun || '-'}</td>
                        <td>${row.aktual || '-'}</td>
                        <td>${row.level || '-'}</td>
                        <td>${row.trend || '-'}</td>
                        <td>${row.seasonal || '-'}</td>
                        <td>${row.prediksi || '-'}</td>
                        <td>${row.error || '-'}</td>
                    </tr>
                `;
                tbody.append(tr);

                // Chart data
                labels.push(row.bulan_tahun);
                actuals.push(row.aktual !== '-' ? row.aktual : null);
                predicteds.push(row.prediksi !== '-' ? row.prediksi : null);
            });
        } else {
            tbody.append('<tr><td colspan="7" class="text-center">Tidak ada detail data tersimpan.</td></tr>');
        }

        $('#detailModal').modal('show');

        // Render Chart
        if(detailChartInstance) {
            detailChartInstance.destroy();
        }

        var ctx = document.getElementById("detailChart");
        detailChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: "Aktual",
                    lineTension: 0.3,
                    backgroundColor: "rgba(78, 115, 223, 0.05)",
                    borderColor: "rgba(78, 115, 223, 1)",
                    pointRadius: 3,
                    pointBackgroundColor: "rgba(78, 115, 223, 1)",
                    pointBorderColor: "rgba(78, 115, 223, 1)",
                    pointHoverRadius: 3,
                    pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                    pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                    pointHitRadius: 10,
                    pointBorderWidth: 2,
                    data: actuals,
                },
                {
                    label: "Prediksi",
                    lineTension: 0.3,
                    backgroundColor: "rgba(28, 200, 138, 0.05)",
                    borderColor: "rgba(28, 200, 138, 1)",
                    pointRadius: 3,
                    pointBackgroundColor: "rgba(28, 200, 138, 1)",
                    pointBorderColor: "rgba(28, 200, 138, 1)",
                    pointHoverRadius: 3,
                    pointHoverBackgroundColor: "rgba(28, 200, 138, 1)",
                    pointHoverBorderColor: "rgba(28, 200, 138, 1)",
                    pointHitRadius: 10,
                    pointBorderWidth: 2,
                    data: predicteds,
                }],
            },
            options: {
                maintainAspectRatio: false,
                layout: { padding: { left: 10, right: 25, top: 25, bottom: 0 } },
                scales: {
                    xAxes: [{ time: { unit: 'date' }, gridLines: { display: false, drawBorder: false }, ticks: { maxTicksLimit: 7 } }],
                    yAxes: [{ ticks: { maxTicksLimit: 5, padding: 10 }, gridLines: { color: "rgb(234, 236, 244)", zeroLineColor: "rgb(234, 236, 244)", drawBorder: false, borderDash: [2], zeroLineBorderDash: [2] } }],
                },
                legend: { display: true },
                tooltips: {
                    backgroundColor: "rgb(255,255,255)", bodyFontColor: "#858796", titleMarginBottom: 10, titleFontColor: '#6e707e', titleFontSize: 14, borderColor: '#dddfeb', borderWidth: 1, xPadding: 15, yPadding: 15, displayColors: false, intersect: false, mode: 'index', caretPadding: 10
                }
            }
        });
    }

    @if(isset($showResult) && $showResult)
    // TES Chart (Main Page)
    var ctx = document.getElementById("tesChart");
    var myLineChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                label: "Aktual",
                lineTension: 0.3,
                backgroundColor: "rgba(78, 115, 223, 0.05)",
                borderColor: "rgba(78, 115, 223, 1)",
                pointRadius: 3,
                pointBackgroundColor: "rgba(78, 115, 223, 1)",
                pointBorderColor: "rgba(78, 115, 223, 1)",
                pointHoverRadius: 3,
                pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                pointHitRadius: 10,
                pointBorderWidth: 2,
                data: @json($actualData),
            },
            {
                label: "Prediksi",
                lineTension: 0.3,
                backgroundColor: "rgba(28, 200, 138, 0.05)",
                borderColor: "rgba(28, 200, 138, 1)",
                pointRadius: 3,
                pointBackgroundColor: "rgba(28, 200, 138, 1)",
                pointBorderColor: "rgba(28, 200, 138, 1)",
                pointHoverRadius: 3,
                pointHoverBackgroundColor: "rgba(28, 200, 138, 1)",
                pointHoverBorderColor: "rgba(28, 200, 138, 1)",
                pointHitRadius: 10,
                pointBorderWidth: 2,
                data: @json($predictedData),
            }],
        },
        options: {
            maintainAspectRatio: false,
            layout: {
                padding: {
                    left: 10,
                    right: 25,
                    top: 25,
                    bottom: 0
                }
            },
            scales: {
                xAxes: [{
                    time: {
                        unit: 'date'
                    },
                    gridLines: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        maxTicksLimit: 7
                    }
                }],
                yAxes: [{
                    ticks: {
                        maxTicksLimit: 5,
                        padding: 10,
                        // Include a dollar sign in the ticks
                        callback: function(value, index, values) {
                            return  number_format(value);
                        }
                    },
                    gridLines: {
                        color: "rgb(234, 236, 244)",
                        zeroLineColor: "rgb(234, 236, 244)",
                        drawBorder: false,
                        borderDash: [2],
                        zeroLineBorderDash: [2]
                    }
                }],
            },
            legend: {
                display: true
            },
            tooltips: {
                backgroundColor: "rgb(255,255,255)",
                bodyFontColor: "#858796",
                titleMarginBottom: 10,
                titleFontColor: '#6e707e',
                titleFontSize: 14,
                borderColor: '#dddfeb',
                borderWidth: 1,
                xPadding: 15,
                yPadding: 15,
                displayColors: false,
                intersect: false,
                mode: 'index',
                caretPadding: 10,
                callbacks: {
                    label: function(tooltipItem, chart) {
                        var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
                        return datasetLabel + ': ' + number_format(tooltipItem.yLabel);
                    }
                }
            }
        }
    });
    @endif
</script>
@endpush
