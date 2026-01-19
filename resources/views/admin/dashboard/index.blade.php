@extends('admin.layouts.app')

{{-- CSS --}}
@section('css')

@endsection

{{-- Konten Utama --}}
@section('content')
<div class="row">
    {{-- Total User --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row align-items-start">
                    <div class="col-8">
                        <span class="text-muted text-uppercase small">Total User</span>
                        <h3 class="fw-bold mt-2 mb-0">{{ $total_siswa }}</h3>
                        <div class="mt-3">
                            <span class="badge bg-info bg-opacity-10 text-info">
                                <i class="ti ti-user-check me-1"></i>Siswa Aktif
                            </span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="d-flex justify-content-end">
                            <div class="text-white bg-gradient-primary rounded-circle p-6 d-flex align-items-center justify-content-center" 
                                 style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);">
                                <i class="ti ti-users fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Total Barang --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row align-items-start">
                    <div class="col-8">
                        <span class="text-muted text-uppercase small">Total Barang</span>
                        <h3 class="fw-bold mt-2 mb-0">{{ $total_item }}</h3>
                        <div class="mt-3">
                            <span class="badge bg-success bg-opacity-10 text-success">
                                <i class="ti ti-check me-1"></i>Tersedia
                            </span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="d-flex justify-content-end">
                            <div class="text-white bg-gradient-success rounded-circle p-6 d-flex align-items-center justify-content-center"
                                 style="background: linear-gradient(135deg, #10b981 0%, #34d399 100%);">
                                <i class="ti ti-box fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Total Pinjaman --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row align-items-start">
                    <div class="col-8">
                        <span class="text-muted text-uppercase small">Total Pinjaman</span>
                        <h3 class="fw-bold mt-2 mb-0">{{ $total_loan }}</h4>
                        <div class="mt-3">
                            <span class="badge bg-warning bg-opacity-10 text-warning">
                                <i class="ti ti-clock me-1"></i>Aktif
                            </span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="d-flex justify-content-end">
                            <div class="text-white bg-gradient-warning rounded-circle p-6 d-flex align-items-center justify-content-center"
                                 style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);">
                                <i class="ti ti-alarm fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

{{-- Daftar Barang dengan gambar dan status stok --}}
@if(isset($items) && $items->count())
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="fw-semibold mb-3">Daftar Barang</h5>

                <div class="row">
                    @foreach($items as $item)
                    <div class="col-6 col-md-4 col-lg-3 mb-4">
                        <div class="card h-100" style="border: 1px solid #e2e8f0;">
                            <div class="position-relative" style="overflow: hidden;">
                                @if ($item->photo)
                                    <img src="{{ asset('photos/' . $item->photo) }}" alt="{{ $item->name }}" class="card-img-top" style="height:160px; object-fit: cover;">
                                @else
                                    <div class="bg-light d-flex align-items-center justify-content-center" style="height:160px;">
                                        <i class="ti ti-photo fs-2" style="color: #94a3b8;"></i>
                                    </div>
                                @endif

                                @php $available = $item->available_stock ?? max(0, ($item->stock ?? 0) - ($item->damaged_count ?? 0)); @endphp
                                @if($available <= 0)
                                    <span class="badge bg-danger position-absolute" style="top:8px; left:8px;">Tidak Tersedia</span>
                                @else
                                    <span class="badge bg-success position-absolute" style="top:8px; left:8px;">Tersedia: {{ $available }}</span>
                                @endif
                            </div>

                            <div class="card-body p-3">
                                <h6 class="card-title mb-1" style="font-weight:600;">{{ $item->name }}</h6>
                                <div class="small text-muted mb-2">{{ $item->unique_code ?? '-' }}</div>
                                @if(($item->damaged_count ?? 0) > 0)
                                    <div><span class="badge bg-warning text-dark">Rusak: {{ $item->damaged_count }}</span></div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

{{-- JavaScript --}}
@section('js')

@endsection