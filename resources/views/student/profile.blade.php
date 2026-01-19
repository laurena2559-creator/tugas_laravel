@extends('admin.layouts.app')

@section('css')
    {{-- CSS Tambahan --}}
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Selamat Datang, {{ Auth::user()->name }}!</h5>
            <p class="mb-3">Berikut adalah riwayat peminjaman barang Anda.</p>

            <div class="table-responsive">
                <table id="datatable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Barang</th>
                            <th>Tgl Pinjam</th>
                            <th>Tgl Wajib Kembali</th>
                            <th>Tgl Aktual Kembali</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($loans as $loan)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $loan->item->name }}</td>
                                <td>{{ \Carbon\Carbon::parse($loan->loan_date)->format('d-m-Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($loan->return_date)->format('d-m-Y') }}</td>
                                <td>
                                    @if ($loan->actual_return_date)
                                        {{ \Carbon\Carbon::parse($loan->actual_return_date)->format('d-m-Y') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if ($loan->status == 'Dipinjam')
                                        <span class="badge bg-warning">{{ $loan->status }}</span>
                                    @else
                                        <span class="badge bg-success">{{ $loan->status }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
@section('js')
    <script>
        $(document).ready(function() {
            $('#datatable').DataTable();
        });
    </script>
@endsection