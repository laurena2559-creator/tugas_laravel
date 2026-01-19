@extends('admin.layouts.app')

@section('css')
{{-- CSS Tambahan --}}
@endsection

@section('content')
<div class="card" style="border: none; box-shadow: 0 1px 3px 0 rgba(0,0,0,.1),0 1px 2px -1px rgba(0,0,0,.1);">
    <div class="card-body" style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
        <h5 class="card-title fw-semibold mb-4" style="color: #1e293b;">Data {{ $title }}</h5>
        <a href="{{ route('users.create') }}" class="btn mb-4" 
           style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); color: white; border: none;">
           Tambah Data {{ $title }}
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm mt-4">
    <div class="card-body">
        <div class="table-responsive">
            <table id="datatable" class="table table-hover" style="width:100%">
                <thead>
                    <tr style="background-color: #f1f5f9;">
                        <th class="py-3" style="color: #475569; font-weight: 600; border-bottom: 2px solid #cbd5e1;">No</th>
                        <th class="py-3" style="color: #475569; font-weight: 600; border-bottom: 2px solid #cbd5e1;">Nama</th>
                        <th class="py-3" style="color: #475569; font-weight: 600; border-bottom: 2px solid #cbd5e1;">Email</th>
                        <th class="py-3" style="color: #475569; font-weight: 600; border-bottom: 2px solid #cbd5e1;">Role</th>
                        <th class="py-3" style="color: #475569; font-weight: 600; border-bottom: 2px solid #cbd5e1;">Kelas</th>
                        <th class="py-3" style="color: #475569; font-weight: 600; border-bottom: 2px solid #cbd5e1;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $item)
                        <tr class="border-bottom">
                            <td class="py-3" style="color: #64748b;">{{ $loop->iteration }}</td>
                            <td class="py-3" style="color: #1e293b; font-weight: 500;">{{ $item->name }}</td>
                            <td class="py-3" style="color: #475569;">{{ $item->email }}</td>
                            <td class="py-3">
                                @if($item->role == 'Admin')
                                    <span style="background-color: #e0f2fe; color: #0369a1; padding: 4px 12px; border-radius: 20px; font-size: 0.875rem;">
                                        Admin
                                    </span>
                                @elseif($item->role == 'Siswa')
                                    <span style="background-color: #f0fdf4; color: #16a34a; padding: 4px 12px; border-radius: 20px; font-size: 0.875rem;">
                                        Siswa
                                    </span>
                                @else
                                    <span style="background-color: #f8fafc; color: #64748b; padding: 4px 12px; border-radius: 20px; font-size: 0.875rem;">
                                        {{ $item->role }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3" style="color: #475569;">{{ $item->kelas ?? '-' }}</td>
                            <td class="py-3">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('users.edit', $item->id) }}" 
                                       class="btn btn-sm me-1" 
                                       style="background-color: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;">
                                        <i class="ti ti-edit"></i> Ubah
                                    </a>
                                    
                                    <form id="deleteForm{{ $item->id }}" 
                                          action="{{ route('users.destroy', $item->id) }}" 
                                          method="POST" 
                                          class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" 
                                                class="btn btn-sm" 
                                                onclick="confirmDelete({{ $item->id }})"
                                                style="background-color: #fef2f2; color: #dc2626; border: 1px solid #fecaca;">
                                            <i class="ti ti-trash"></i> Hapus
                                        </button>
                                    </form>
                                </div>
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
    $(document).ready( function () {
        $('#datatable').DataTable({
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

    function confirmDelete(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3b82f6',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $('#deleteForm' + id).submit();
            }
        });
    }
</script>
@endsection