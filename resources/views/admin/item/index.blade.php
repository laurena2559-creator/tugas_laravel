@extends('admin.layouts.app')

@section('css')
{{-- CSS Tambahan --}}
@endsection

@section('content')
<div class="card">
    <div class="card-body" style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
        <h5 class="card-title fw-semibold mb-4" style="color: #1e293b;">Data {{ $title }}</h5>
        <a href="{{ route('item.create') }}" class="btn mb-4" 
           style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); color: white; border: none;">
           Tambah Data Barang
        </a>
    </div>
</div>

<div class="table-responsive">
    <table id="datatable" class="table table-striped" style="border: 1px solid #e2e8f0;">
        <thead style="background-color: #f1f5f9;">
            <tr>
                <th style="color: #475569; border-bottom: 2px solid #cbd5e1;">No</th>
                <th style="color: #475569; border-bottom: 2px solid #cbd5e1;">Nama Barang</th>
                <th style="color: #475569; border-bottom: 2px solid #cbd5e1;">Foto</th>
                <th style="color: #475569; border-bottom: 2px solid #cbd5e1;">Status</th>
                <th style="color: #475569; border-bottom: 2px solid #cbd5e1;">Nomor Seri</th>
                <th style="color: #475569; border-bottom: 2px solid #cbd5e1;">Penanggung Jawab</th>
                <th style="color: #475569; border-bottom: 2px solid #cbd5e1;">Stock</th>
                <th style="color: #475569; border-bottom: 2px solid #cbd5e1;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $item)
            <tr style="border-bottom: 1px solid #e2e8f0;">
                <td style="color: #64748b;">{{ $loop->iteration }}</td>
                <td style="color: #1e293b; font-weight: 500;">{{ $item->name }}</td>

                <td>
                    @if ($item->photo)
                        <img src="{{ asset('photos/' . $item->photo) }}" 
                             alt="Foto {{ $item->name }}" 
                             width="70" height="70"
                             style="object-fit: cover; border-radius: 6px; border: 1px solid #e2e8f0;">
                    @else
                        -
                    @endif
                </td>

                <td>
                    @if($item->condition == 'Baik')
                        <span style="background-color: #f0fdf4; color: #16a34a; padding: 4px 12px; border-radius: 20px; font-size: 0.875rem;">
                            Baik
                        </span>
                    @elseif($item->condition == 'Rusak')
                        <span style="background-color: #fef2f2; color: #dc2626; padding: 4px 12px; border-radius: 20px; font-size: 0.875rem;">
                            Rusak
                        </span>
                    @else
                        <span style="background-color: #f8fafc; color: #64748b; padding: 4px 12px; border-radius: 20px; font-size: 0.875rem;">
                            {{ $item->condition }}
                        </span>
                    @endif
                    @if(($item->damaged_count ?? 0) > 0)
                        <span title="Jumlah rusak: {{ $item->damaged_count }} unit" style="background-color: #fff7ed; color: #b45309; padding: 4px 8px; border-radius: 12px; font-size: 0.75rem; margin-left:8px; display: inline-block;">
                            <i class="ti ti-alert-triangle me-1"></i>Rusak: {{ $item->damaged_count }}
                        </span>
                    @endif
                </td>
                
                <td style="color: #475569;">
                    <code style="background-color: #f1f5f9; padding: 4px 8px; border-radius: 4px; color: #475569;">
                        {{ $item->unique_code ?? '-' }}
                    </code>
                </td>
                
                <td style="color: #1e293b;">{{ $item->user->name ?? '-' }}</td>

                <td>
                    @php
                        // Hitung ketersediaan untuk hari ini (memperhitungkan peminjaman aktif)
                        $today = \Illuminate\Support\Carbon::today()->toDateString();
                        // availableStockForPeriod mengembalikan stok tersedia untuk rentang tanggal
                        $available = method_exists($item, 'availableStockForPeriod')
                            ? $item->availableStockForPeriod($today, $today)
                            : ($item->available_stock ?? max(0, ($item->stock ?? 0) - ($item->damaged_count ?? 0)));
                    @endphp
                    @if($available > 10)
                        <span style="color: #16a34a; font-weight: 600;">{{ $available }}</span>
                    @elseif($available > 0)
                        <span style="color: #f59e0b; font-weight: 600;">{{ $available }}</span>
                    @else
                        <span style="color: #dc2626; font-weight: 600;">{{ $available }}</span>
                    @endif
                </td>

                <td>
                        {{-- Repair action removed from index view; use item.rusak instead --}}

                    <a href="{{ route('item.edit', $item->id) }}" 
                       class="btn btn-sm" 
                       style="background-color: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;">
                        Ubah
                    </a>

                    <form id="deleteForm{{ $item->id }}" 
                          action="{{ route('item.destroy', $item->id) }}" 
                          method="POST" 
                          class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="button" 
                                class="btn btn-sm" 
                                onclick="confirmDelete({{ $item->id }})"
                                style="background-color: #fef2f2; color: #dc2626; border: 1px solid #fecaca;">
                            Hapus
                        </button>
                    </form>
                </td>
                    @php
                        $physical = max(0, ($item->stock ?? 0) - ($item->damaged_count ?? 0));
                        $available = method_exists($item, 'availableStockForPeriod') ? $item->availableStockForPeriod(
                            \Illuminate\Support\Carbon::today()->toDateString(), \Illuminate\Support\Carbon::today()->toDateString()
                        ) : ($item->available_stock ?? $physical);
                        $reserved = max(0, $physical - $available);
                    @endphp

                    <div style="font-weight:600;">
                        <span style="color: #0f172a;">{{ $available }}</span>
                        <small class="text-muted"> / {{ $physical }} fisik</small>
                    </div>
                    @if($reserved > 0)
                        <div><small class="text-warning">Sedang dipinjam: {{ $reserved }} unit</small></div>
                    @endif
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

@section('js')
<script>
    $(document).ready(function () {
        $('#datatable').DataTable();
    });

    function confirmDelete(id) {
        swal({
            title: "Apakah anda yakin?",
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
        .then((willDelete) => {
            if (willDelete) {
                $('#deleteForm' + id).submit();
            } else {
                swal("Data tidak jadi dihapus!", { icon: "error" });
            }
        });
    }
    
    function confirmRestoreIndex(id, count) {
        if (!count || count <= 0) {
            swal("Tidak ada barang rusak untuk dikembalikan", { icon: "info" });
            return;
        }

        swal({
            title: "Kembalikan barang?",
            text: "Akan mengembalikan semua unit rusak (" + count + " unit) ke stok baik.",
            icon: "warning",
            buttons: true,
            dangerMode: false,
        })
        .then((willReturn) => {
            if (willReturn) {
                document.getElementById('repairFormIndex' + id).submit();
            }
        });
    }
</script>
@endsection