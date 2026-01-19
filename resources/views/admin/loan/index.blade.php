@extends('admin.layouts.app')

@section('css')
    {{-- CSS Tambahan --}}
@endsection

@section('content')
    <div class="card-body p-4" style="background-color: #ffffff; border-bottom: 1px solid #f1f5f9;">
    <h5 class="card-title fw-bolder mb-4" style="color: #1e293b; letter-spacing: -0.025em;">
        Data {{ $title }}
    </h5>
    
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('loan.create') }}" class="btn d-flex align-items-center px-3 py-2 shadow-sm transition-all"
            style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border: none; border-radius: 8px;">
            <i class="ti ti-plus fs-5 me-2"></i>
            <span class="fw-semibold">Tambah Data {{ $title }}</span>
        </a>

        <button type="button" class="btn d-flex align-items-center px-3 py-2 transition-all" 
                data-bs-toggle="modal" data-bs-target="#modalReport"
                style="background-color: #f0f7ff; color: #0d6efd; border: 1px solid #cfe2ff; border-radius: 8px;">
            <i class="ti ti-file-description fs-5 me-2"></i>
            <span class="fw-semibold">Cetak Laporan Bulanan</span>
        </button>
    </div>
</div>

<style>
    /* Efek Hover untuk interaksi yang lebih hidup */
    .transition-all {
        transition: all 0.2s ease-in-out;
    }
    
    .transition-all:hover {
        transform: translateY(-2px);
        filter: brightness(1.1);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
    }

    .transition-all:active {
        transform: translateY(0);
    }
</style>

    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body">
            <div class="table-responsive">
                <div class="mb-3 d-flex gap-2">
                    <button id="filterAll" class="btn btn-sm" style="background-color:#f1f5f9;">Tampilkan Semua</button>
                    <button id="filterDueToday" class="btn btn-sm" style="background-color:#fff7ed;">Jatuh Tempo Hari Ini</button>
                    <button id="filterOverdue" class="btn btn-sm" style="background-color:#fee2e2;">Terlambat</button>
                </div>
                <table id="datatable" class="table table-hover" style="width:100%">
                    <thead>
                        <tr style="background-color: #f0fdf4;">
                            <th class="py-3" style="color: #475569; font-weight: 600; border-bottom: 2px solid #a7f3d0;">No
                            </th>
                            <th class="py-3" style="color: #475569; font-weight: 600; border-bottom: 2px solid #a7f3d0;">
                                Nama Peminjam</th>
                            <th class="py-3" style="color: #475569; font-weight: 600; border-bottom: 2px solid #a7f3d0;">
                                Nama Barang</th>
                            <th class="py-3" style="color: #475569; font-weight: 600; border-bottom: 2px solid #a7f3d0;">
                                Tanggal Pinjam</th>
                            <th class="py-3" style="color: #475569; font-weight: 600; border-bottom: 2px solid #a7f3d0;">
                                Tanggal Kembali</th>
                            <th class="py-3" style="color: #475569; font-weight: 600; border-bottom: 2px solid #a7f3d0;">
                                Status</th>
                            <th class="py-3" style="color: #475569; font-weight: 600; border-bottom: 2px solid #a7f3d0;">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($loans as $loan)
                            @php
                                // Determine next return date from items that still have remaining quantity (>0)
                                $nextReturnDate = null;
                                if(isset($loan->items) && $loan->items->count()) {
                                    $dates = $loan->items->filter(function($it){
                                        return (int)($it->pivot->quantity ?? 0) > 0 && !empty($it->pivot->return_date);
                                    })->pluck('pivot.return_date')->filter()->map(function($d){ return \Carbon\Carbon::parse($d); });

                                    if($dates->count()) {
                                        // pick the earliest return_date among remaining items
                                        $nextReturnDate = $dates->min();
                                    }
                                }

                                // fallback to loan->return_date when no per-item date found
                                if(!$nextReturnDate && !empty($loan->return_date)){
                                    $nextReturnDate = \Carbon\Carbon::parse($loan->return_date);
                                }

                                if($nextReturnDate){
                                    $dueToday = ($nextReturnDate->isToday() && $loan->status == 'Dipinjam');
                                    $overdue = ($nextReturnDate->lt(\Carbon\Carbon::today()) && $loan->status == 'Dipinjam');
                                } else {
                                    $dueToday = false;
                                    $overdue = false;
                                }
                            @endphp
                            <tr class="border-bottom loan-row {{ $dueToday ? 'due-today' : '' }} {{ $overdue ? 'overdue' : '' }}" data-return-date="{{ $nextReturnDate ? $nextReturnDate->toDateString() : '' }}" data-status="{{ $loan->status }}">
                                <td class="py-3" style="color: #64748b;">{{ $loop->iteration }}</td>
                                <td class="py-3">
                                    <div style="color: #1e293b; font-weight: 500;">{{ $loan->user->name }}</div>
                                    <small class="text-muted">{{ $loan->user->email ?? '' }}</small>
                                </td>
                                <td class="py-3">
                                    <div style="color: #1e293b; font-weight: 500;">{{ $loan->item->name }}</div>
                                    <small style="color: #64748b; background-color: #f1f5f9; padding: 2px 8px; border-radius: 4px;">{{ $loan->item->unique_code }}</small>
                                </td>
                                <td class="py-3" style="color: #475569;">{{ \Carbon\Carbon::parse($loan->loan_date)->format('d-m-Y') }}</td>
                                <td class="py-3" style="color: #475569;">{{ $nextReturnDate ? $nextReturnDate->format('d-m-Y') : '-' }}
                                    @if($dueToday)
                                        <span style="background-color: #fff7ed; color: #b45309; padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; margin-left:8px;"><i class="ti ti-bell me-1"></i>Jatuh Tempo Hari Ini</span>
                                    @elseif($overdue)
                                        <span style="background-color: #fee2e2; color: #b91c1c; padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; margin-left:8px;"><i class="ti ti-alert-triangle me-1"></i>Terlambat</span>
                                    @endif
                                </td>
                                <td class="py-3">
                                    @if ($loan->status == 'Dipinjam')
                                        <span style="background-color: #fef3c7; color: #d97706; padding: 6px 16px; border-radius: 20px; font-size: 0.875rem; font-weight: 500;"><i class="ti ti-clock me-1"></i>{{ $loan->status }}</span>
                                    @elseif($loan->status == 'Dikembalikan')
                                        <span style="background-color: #dcfce7; color: #16a34a; padding: 6px 16px; border-radius: 20px; font-size: 0.875rem; font-weight: 500;"><i class="ti ti-check me-1"></i>{{ $loan->status }}</span>
                                    @elseif($loan->status == 'Terlambat')
                                        <span style="background-color: #fee2e2; color: #dc2626; padding: 6px 16px; border-radius: 20px; font-size: 0.875rem; font-weight: 500;"><i class="ti ti-alert-triangle me-1"></i>{{ $loan->status }}</span>
                                    @else
                                        <span style="background-color: #f8fafc; color: #64748b; padding: 6px 16px; border-radius: 20px; font-size: 0.875rem; font-weight: 500;">{{ $loan->status }}</span>
                                    @endif
                                </td>
                                <td class="py-3">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('loan.show', $loan->id) }}" class="btn btn-sm me-1" style="background-color: #f0fdf4; color: #166534; border: 1px solid #bbf7d0;"><i class="ti ti-eye"></i></a>
                                        <a href="{{ route('loan.edit', $loan->id) }}" class="btn btn-sm me-1" style="background-color: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;"><i class="ti ti-edit"></i></a>
                                        <form id="deleteForm{{ $loan->id }}" action="{{ route('loan.destroy', $loan->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm" onclick="confirmDelete({{ $loan->id }})" style="background-color: #fef2f2; color: #dc2626; border: 1px solid #fecaca;"><i class="ti ti-trash"></i></button>
                                        </form>
                                    </div>
                                        @if($loan->status == 'Dikembalikan')
                                            <form id="reactivateForm{{ $loan->id }}" action="{{ route('loan.reactivate', $loan->id) }}" method="POST" class="d-inline me-1">
                                                @csrf
                                                @method('PUT')
                                                <button type="button" class="btn btn-sm" onclick="confirmReactivate({{ $loan->id }})" style="background-color: #ecfdf5; color: #065f46; border: 1px solid #bbf7d0;"><i class="ti ti-refresh" title="Aktifkan Kembali"></i></button>
                                            </form>
                                        @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modalReport" tabindex="-1" aria-labelledby="modalReportLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalReportLabel">Pilih Periode Laporan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <form action="{{ route('loan.exportBulanan') }}" method="GET" target="_blank">
                            <div class="mb-3">
                                <label class="form-label">Bulan</label>
                                <select name="bulan" class="form-select" required>
                                    @foreach(range(1, 12) as $m)
                                        <option value="{{ sprintf('%02d', $m) }}" {{ date('m') == $m ? 'selected' : '' }}>
                                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tahun</label>
                                <select name="tahun" class="form-select" required>
                                    @foreach(range(date('Y')-2, date('Y')+2) as $y)
                                        <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="modal-footer p-0">
                                <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">Download PDF</button>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <form action="{{ route('loan.stockMonthly') }}" method="GET">
                            <div class="mb-3">
                                <label class="form-label">Bulan (Kebutuhan Stok)</label>
                                <select name="bulan" class="form-select" required>
                                    @foreach(range(1, 12) as $m)
                                        <option value="{{ sprintf('%02d', $m) }}" {{ date('m') == $m ? 'selected' : '' }}>
                                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tahun</label>
                                <select name="tahun" class="form-select" required>
                                    @foreach(range(date('Y')-2, date('Y')+2) as $y)
                                        <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="modal-footer p-0">
                                <button type="submit" class="btn btn-success">Tampilkan Kebutuhan Stok</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
    <script>
        $(document).ready(function () {
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
                text: "Data peminjaman yang dihapus tidak dapat dikembalikan!",
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

        function confirmReactivate(id) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Aktifkan kembali peminjaman?',
                    text: "Peminjaman akan diubah kembali menjadi 'Dipinjam'. Pastikan stok tersedia untuk periode tersebut.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, aktifkan',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('reactivateForm' + id).submit();
                    }
                });
            } else {
                if (confirm("Aktifkan kembali peminjaman?")) {
                    document.getElementById('reactivateForm' + id).submit();
                }
            }
        }
    
        // Filter buttons behavior
        document.addEventListener('DOMContentLoaded', function () {
            const btnAll = document.getElementById('filterAll');
            const btnDue = document.getElementById('filterDueToday');
            const btnOver = document.getElementById('filterOverdue');

            function setActive(btn) {
                [btnAll, btnDue, btnOver].forEach(b => b.classList.remove('active'));
                if (btn) btn.classList.add('active');
            }

            function showAll() {
                document.querySelectorAll('.loan-row').forEach(r => r.style.display = '');
            }

            function showDueToday() {
                document.querySelectorAll('.loan-row').forEach(r => {
                    if (r.classList.contains('due-today')) r.style.display = '';
                    else r.style.display = 'none';
                });
            }

            function showOverdue() {
                document.querySelectorAll('.loan-row').forEach(r => {
                    if (r.classList.contains('overdue')) r.style.display = '';
                    else r.style.display = 'none';
                });
            }

            if (btnAll) btnAll.addEventListener('click', function (e) { e.preventDefault(); setActive(btnAll); showAll(); });
            if (btnDue) btnDue.addEventListener('click', function (e) { e.preventDefault(); setActive(btnDue); showDueToday(); });
            if (btnOver) btnOver.addEventListener('click', function (e) { e.preventDefault(); setActive(btnOver); showOverdue(); });

            // default: set All active
            setActive(btnAll);
        });
    </script>
@endsection