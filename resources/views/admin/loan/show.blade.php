@extends('admin.layouts.app')

@section('css')
    <style>
        .item-card {
            border-bottom: 1px solid #eee;
            padding-bottom: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .item-card:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        .info-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #777;
            display: block;
            margin-bottom: 2px;
        }
    </style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        {{-- KOLOM KIRI: DETAIL PEMINJAMAN --}}
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title fw-semibold mb-4 text-primary">Detail Transaksi</h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <tbody>
                                <tr>
                                    <th width="40%">Nama Peminjam</th>
                                    <td>: {{ $loan->user->name }}</td>
                                </tr>
                                <tr>
                                    <th>Tanggal Pinjam</th>
                                    <td>: {{ \Carbon\Carbon::parse($loan->loan_date)->format('d F Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Wajib Kembali</th>
                                    <td>: <span class="text-danger fw-bold">{{ \Carbon\Carbon::parse($loan->return_date)->format('d F Y') }}</span></td>
                                </tr>
                                <tr>
                                    <th>Aktual Kembali</th>
                                    <td>: 
                                        @if ($loan->actual_return_date)
                                            <span class="text-success">{{ \Carbon\Carbon::parse($loan->actual_return_date)->format('d F Y') }}</span>
                                        @else
                                            <span class="text-muted small italic">Belum kembali</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>: 
                                        @if ($loan->status == 'Dipinjam')
                                            <span class="badge bg-warning px-3">Sedang Dipinjam</span>
                                        @else
                                            <span class="badge bg-success px-3">Selesai</span>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: DETAIL BARANG --}}
        <div class="col-md-7">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title fw-semibold mb-4 text-primary">Daftar Barang & Pengembalian</h5>

                    @if($loan->items && $loan->items->count())
                        <form action="{{ route('loan.return', $loan->id) }}" method="POST" id="returnForm">
                            @csrf
                            @method('PUT')
                            
                            @foreach($loan->items as $line)
                                <div class="d-flex gap-3 align-items-start item-card">
                                    {{-- Thumbnail Barang --}}
                                    @if($line->photo)
                                        <img src="{{ asset('photos/' . $line->photo) }}" class="rounded border" style="width:100px; height:100px; object-fit:cover;">
                                    @else
                                        <div class="rounded bg-light d-flex align-items-center justify-content-center border" style="width:100px; height:100px;">
                                            <i class="ti ti-photo text-muted fs-6"></i>
                                        </div>
                                    @endif

                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="fw-bold mb-1">{{ $line->name }}</h6>
                                            <span class="badge bg-info">Qty: {{ $line->pivot->quantity }}</span>
                                        </div>
                                        <p class="text-muted small mb-2">
                                            Code: {{ $line->unique_code }} | Kondisi: {{ $line->condition }}
                                        </p>

                                        <div class="row g-2 align-items-end mt-2">
                                            {{-- Info Tanggal Kembali (Pivot) --}}
                                            <div class="col-md-4">
                                                <label class="info-label text-info">Tgl Pengembalian</label>
                                                <div class="fw-semibold small">
                                                    @if(!empty($line->pivot->return_date))
                                                        <i class="ti ti-calendar-check text-success"></i> 
                                                        {{ \Carbon\Carbon::parse($line->pivot->return_date)->format('d M Y') }}
                                                    @else
                                                        <span class="text-muted italic small">- Belum Ada -</span>
                                                    @endif
                                                </div>
                                            </div>

                                            {{-- Input Jumlah --}}
                                            @if($loan->status == 'Dipinjam')
                                            <div class="col-md-4">
                                                <label class="info-label">Jumlah Kembali</label>
                                                <input type="number" name="returned_items[{{ $line->id }}]" 
                                                       id="returned_{{ $line->id }}" 
                                                       class="form-control form-control-sm border-primary" 
                                                       min="0" max="{{ $line->pivot->quantity }}" 
                                                       value="0">
                                            </div>

                                            {{-- Tombol Aksi Cepat --}}
                                            <div class="col-md-4">
                                                <button type="button" class="btn btn-sm btn-outline-success w-100" 
                                                        onclick="returnSingle({{ $line->id }}, {{ $line->pivot->quantity }}, '{{ $line->name }}')">
                                                    <i class="ti ti-check"></i> Kembalikan Semua
                                                </button>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </form>
                    @else
                        <div class="alert alert-light text-center">Tidak ada item dalam transaksi ini.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- BARIS TOMBOL NAVIGASI --}}
    <div class="card mt-3 shadow-sm border-0">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center">
                <a href="{{ route('loan.index') }}" class="btn btn-outline-secondary px-4">
                    <i class="ti ti-arrow-left me-1"></i> Kembali ke Daftar
                </a>

                @if ($loan->status == 'Dipinjam')
                    <button type="submit" form="returnForm" class="btn btn-primary px-5 shadow">
                        <i class="ti ti-device-floppy me-1"></i> Simpan Perubahan
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
    <script>
        function returnSingle(itemId, qty, itemName) {
            const input = document.getElementById('returned_' + itemId);
            if (!input) return;

            input.value = qty;
            
            if (confirm(`Apakah Anda yakin ingin mengembalikan semua (${qty} unit) barang "${itemName}"?`)) {
                document.getElementById('returnForm').submit();
            }
        }
    </script>
@endsection