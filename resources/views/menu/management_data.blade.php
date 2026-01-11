@extends('layouts.app')

@section('content')

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
  <h1 class="h3 mb-0 text-gray-800">Management Data Pemakaian</h1>
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

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  {{ session('error') }}
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
@endif

<div class="row mb-4">

    <!-- Form Input Data -->
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4 h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Input Data Pemakaian</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('input_data.store') }}" method="POST">
                    @csrf
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="id_kendaraan">Kendaraan</label>
                            <select class="form-control" id="id_kendaraan" name="id_kendaraan" required>
                                <option value="">Pilih Kendaraan...</option>
                                @foreach($kendaraans as $k)
                                    <option value="{{ $k->id }}">{{ $k->nama_kendaraan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="bulan">Bulan</label>
                            <select class="form-control" id="bulan" name="bulan" required>
                                <option value="">Pilih Bulan...</option>
                                @foreach(range(1, 12) as $m)
                                    <option value="{{ $m }}">{{ date('F', mktime(0, 0, 0, $m, 10)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="tahun">Tahun</label>
                            <input type="number" class="form-control" id="tahun" name="tahun" min="2000" max="{{ date('Y') + 1 }}" value="{{ date('Y') }}" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="jumlah_transaksi">Jumlah Transaksi</label>
                        <input type="number" class="form-control" id="jumlah_transaksi" name="jumlah_transaksi" min="0" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Pie Chart -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow h-100 mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Transaksi per Kendaraan</h6>
            </div>
            <div class="card-body">
                <div class="chart-pie pt-4 pb-2">
                    <canvas id="myPieChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Data Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Data Pemakaian Kendaraan</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th>Kendaraan</th>
                        <th>Bulan</th>
                        <th>Tahun</th>
                        <th>Jumlah Transaksi</th>
                        <th width="15%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pemakaian as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->kendaraan->nama_kendaraan }}</td>
                        <td>{{ date('F', mktime(0, 0, 0, $item->bulan, 10)) }}</td>
                        <td>{{ $item->tahun }}</td>
                        <td>{{ $item->jumlah_transaksi }}</td>
                        <td>
                            <button class="btn btn-warning btn-sm btn-circle" 
                                onclick="editData({{ $item->id }}, {{ $item->id_kendaraan }}, {{ $item->bulan }}, {{ $item->tahun }}, {{ $item->jumlah_transaksi }})" 
                                title="Edit">
                                <i class="fas fa-pen"></i>
                            </button>
                            <form id="delete-form-{{ $item->id }}" action="{{ route('input_data.destroy', $item->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-danger btn-sm btn-circle" onclick="confirmDelete({{ $item->id }})" title="Hapus">
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

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Data Pemakaian</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="edit_id_kendaraan">Kendaraan</label>
                            <select class="form-control" id="edit_id_kendaraan" name="id_kendaraan" required>
                                @foreach($kendaraans as $k)
                                    <option value="{{ $k->id }}">{{ $k->nama_kendaraan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="edit_bulan">Bulan</label>
                            <select class="form-control" id="edit_bulan" name="bulan" required>
                                @foreach(range(1, 12) as $m)
                                    <option value="{{ $m }}">{{ date('F', mktime(0, 0, 0, $m, 10)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="edit_tahun">Tahun</label>
                            <input type="number" class="form-control" id="edit_tahun" name="tahun" min="2000" max="{{ date('Y') + 1 }}" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_jumlah_transaksi">Jumlah Transaksi</label>
                        <input type="number" class="form-control" id="edit_jumlah_transaksi" name="jumlah_transaksi" min="0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable();

        // Pie Chart
        var ctx = document.getElementById("myPieChart");
        var myPieChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: @json($chartLabels),
                datasets: [{
                    data: @json($chartValues),
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796', '#5a5c69'],
                    hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#dda20a', '#be2617', '#60616f', '#373840'],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }],
            },
            options: {
                maintainAspectRatio: false,
                tooltips: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#858796",
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    caretPadding: 10,
                },
                legend: {
                    display: false
                },
                cutoutPercentage: 80,
            },
        });
    });

    function confirmDelete(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        })
    }

    function editData(id, id_kendaraan, bulan, tahun, jumlah) {
        var url = "{{ route('input_data.update', ':id') }}";
        url = url.replace(':id', id);
        document.getElementById('editForm').action = url;
        
        document.getElementById('edit_id_kendaraan').value = id_kendaraan;
        document.getElementById('edit_bulan').value = bulan;
        document.getElementById('edit_tahun').value = tahun;
        document.getElementById('edit_jumlah_transaksi').value = jumlah;
        
        $('#editModal').modal('show');
    }
</script>
@endpush
