@extends('layouts.app')

@section('content')

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
  <h1 class="h3 mb-0 text-gray-800">Management Kendaraan</h1>
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

<div class="row mb-4">

    <!-- Form Tambah Kendaraan -->
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4 h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Tambah Kendaraan</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('management_kendaraan.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="nama_kendaraan">Nama Kendaraan</label>
                        <input type="text" class="form-control" id="nama_kendaraan" name="nama_kendaraan" placeholder="Masukkan nama kendaraan..." required>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Card Jumlah Kendaraan -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center h-100">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Kendaraan</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalKendaraan }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-car fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Data Kendaraan</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th>Nama Kendaraan</th>
                        <th>Tanggal Dibuat</th>
                        <th width="15%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kendaraans as $index => $kendaraan)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $kendaraan->nama_kendaraan }}</td>
                        <td>{{ $kendaraan->created_at->format('d M Y') }}</td>
                        <td>
                            <button class="btn btn-warning btn-sm btn-circle" 
                                onclick="editKendaraan({{ $kendaraan->id }}, '{{ $kendaraan->nama_kendaraan }}')" 
                                title="Edit">
                                <i class="fas fa-pen"></i>
                            </button>
                            <form id="delete-form-{{ $kendaraan->id }}" action="{{ route('management_kendaraan.destroy', $kendaraan->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-danger btn-sm btn-circle" onclick="confirmDelete({{ $kendaraan->id }})" title="Hapus">
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
                <h5 class="modal-title" id="editModalLabel">Edit Kendaraan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_nama_kendaraan">Nama Kendaraan</label>
                        <input type="text" class="form-control" id="edit_nama_kendaraan" name="nama_kendaraan" required>
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


@push('scripts')
<script>
    // Initialize DataTable when DOM is ready
    $(document).ready(function() {
        $('#dataTable').DataTable();
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

    function editKendaraan(id, nama) {
        // Set form action dynamically
        var url = "{{ route('management_kendaraan.update', ':id') }}";
        url = url.replace(':id', id);
        document.getElementById('editForm').action = url;
        
        // Populate input
        document.getElementById('edit_nama_kendaraan').value = nama;
        
        // Show modal
        $('#editModal').modal('show');
    }
</script>
@endpush

@endsection