@extends('admin.layouts.app')

@section('content')
<div class="card">
    <div class="card-body">
        <h5 class="card-title">Kebutuhan Stok Bulanan - {{ $bulan }} {{ $tahun }}</h5>
        <p class="text-muted">Periode: {{ $start->format('d-m-Y') }} s.d. {{ $end->format('d-m-Y') }}</p>

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Barang</th>
                        <th>Kode Unik</th>
                        <th>Stok Fisik</th>
                        <th>Rusak</th>
                        <th>Stok Baik</th>
                        <th>Permintaan Bulan</th>
                        <th>Kekurangan (Rekomendasi)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $i => $r)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $r['name'] }}</td>
                            <td>{{ $r['unique_code'] }}</td>
                            <td>{{ $r['stock'] }}</td>
                            <td>{{ $r['damaged'] }}</td>
                            <td>{{ $r['physical'] }}</td>
                            <td>{{ $r['demand'] }}</td>
                            <td>
                                @if($r['shortfall'] > 0)
                                    <span class="text-danger">{{ $r['shortfall'] }}</span>
                                @else
                                    <span class="text-success">0</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <a href="{{ route('loan.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
</div>
@endsection
