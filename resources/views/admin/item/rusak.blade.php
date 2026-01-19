@extends('admin.layouts.app')

@section('content')
<div class="card" style="border: none; box-shadow: 0 1px 3px 0 rgba(0,0,0,.1),0 1px 2px -1px rgba(0,0,0,.1);">
    <div class="card-body" style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
        <h5 class="card-title fw-semibold mb-4" style="color: #1e293b;">Data Barang Rusak</h5>
        <a href="{{ route('item.index') }}" class="btn mb-4" 
           style="background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;">
           <i class="ti ti-arrow-left me-1"></i>Kembali ke Data Barang
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm mt-4">
    <div class="card-body">
        <div class="table-responsive">
            <table id="datatable-rusak" class="table table-hover" style="width:100%">
                <thead>
                    <tr style="background-color: #fef2f2;">
                        <th class="py-3" style="color: #475569; font-weight: 600; border-bottom: 2px solid #fecaca;">No</th>
                        <th class="py-3" style="color: #475569; font-weight: 600; border-bottom: 2px solid #fecaca;">Nama Barang</th>
                        <th class="py-3" style="color: #475569; font-weight: 600; border-bottom: 2px solid #fecaca;">Foto</th>
                        <th class="py-3" style="color: #475569; font-weight: 600; border-bottom: 2px solid #fecaca;">Kondisi</th>
                        <th class="py-3" style="color: #475569; font-weight: 600; border-bottom: 2px solid #fecaca;">Nomor Seri</th>
                        <th class="py-3" style="color: #475569; font-weight: 600; border-bottom: 2px solid #fecaca;">Stock</th>
                        <th class="py-3" style="color: #475569; font-weight: 600; border-bottom: 2px solid #fecaca;">Penanggung Jawab</th>
                        <th class="py-3" style="color: #475569; font-weight: 600; border-bottom: 2px solid #fecaca;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                    <tr class="border-bottom">
                        <td class="py-3" style="color: #64748b;">{{ $loop->iteration }}</td>
                        <td class="py-3">
                            <div style="color: #1e293b; font-weight: 500;">{{ $item->name }}</div>
                            <small class="text-muted">{{ $item->category->name ?? 'Tidak ada kategori' }}</small>
                        </td>
                        <td class="py-3">
                            @if ($item->photo)
                                <img src="{{ asset('photos/' . $item->photo) }}" 
                                     width="60" height="60" 
                                     style="object-fit:cover; border-radius:8px; border: 1px solid #fee2e2;">
                            @else
                                <div class="d-flex align-items-center justify-content-center" 
                                     style="width: 60px; height: 60px; background-color: #fef2f2; border-radius: 8px;">
                                    <i class="ti ti-photo text-red-300"></i>
                                </div>
                            @endif
                        </td>
                        <td class="py-3">
                            <span style="background-color: #fee2e2; color: #dc2626; padding: 6px 16px; border-radius: 20px; font-size: 0.875rem; font-weight: 500;">
                                <i class="ti ti-alert-circle me-1"></i>Rusak
                            </span>
                        </td>
                        <td class="py-3">
                            <code style="background-color: #fef2f2; color: #dc2626; padding: 4px 8px; border-radius: 4px;">
                                {{ $item->unique_code ?? '-' }}
                            </code>
                        </td>
                        <td class="py-3">
                            <span style="color: #dc2626; font-weight: 600;">{{ $item->damaged_count ?? 0 }}</span>
                        </td>
                        <td class="py-3">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                         style="width: 32px; height: 32px; background-color: #fee2e2;">
                                        <i class="ti ti-user text-red-400"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-2">
                                    <div style="color: #1e293b; font-weight: 500;">{{ $item->user->name ?? '-' }}</div>
                                    @if($item->user)
                                        <small class="text-muted">{{ $item->user->email ?? '' }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="py-3">
                            @if(($item->damaged_count ?? 0) > 0)
                                <form action="{{ route('item.repair', $item->id) }}" method="POST" class="d-inline" id="repairForm{{ $item->id }}">
                                    @csrf
                                    <input type="hidden" name="repair_count" value="{{ $item->damaged_count }}">
                                    <button type="button" class="btn btn-sm" style="background-color: #ecfccb; color: #365314; border: 1px solid #dcfce7;" onclick="confirmRestore({{ $item->id }}, {{ $item->damaged_count }})">
                                        Kembalikan
                                    </button>
                                </form>
                            @else
                                -
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
    $(document).ready(function () {
        $('#datatable-rusak').DataTable({
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data per halaman",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Berikutnya",
                    previous: "Sebelumnya"
                }
            },
            dom: '<"row"<"col-md-6"l><"col-md-6"f>>rt<"row"<"col-md-6"i><"col-md-6"p>>',
            pageLength: 10,
            responsive: true
        });
    });

    function confirmRestore(id, count) {
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
                document.getElementById('repairForm' + id).submit();
            }
        });
    }
</script>
@endsection