@extends('admin.layouts.app')
@section('css')
        {{-- CSS Tambahan --}}
@endsection

@section('content')
        <div class="card">
                <div class="card-body">
                        <h5 class="card-title fw-semibold mb-4">Ubah Data {{ $title }}</h5>
                        <div class="card">
                                <div class="card-body">
                                        <form action="{{ route('loan.update', $loan->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                {{-- Nama Peminjam --}}
                                                <div class="mb-3">
                                                        <label for="user_id" class="form-label">Nama Peminjam</label>
                                                        <select class="form-select @error('user_id') is-invalid @enderror"
                                name="user_id" id="user_id">
                                                                <option value="" disabled>Pilih Peminjam</option>
                                                                @foreach ($users as $user)
                                                                        <option value="{{ $user->id }}" {{ old('user_id', $loan->user_id) == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        @error('user_id')
                                                                <small class="text-danger">{{ $message }}</small>
                                                        @enderror
                                                    </div>

                        {{-- Daftar Barang (bisa lebih dari satu) --}}
                        <div class="mb-3">
                            <label class="form-label">Daftar Barang</label>
                            <div id="items_container">
                                @php $idx = 0; @endphp
                                @if(old('items'))
                                    @foreach(old('items') as $oldLine)
                                        <div class="item-row d-flex gap-2 align-items-center mb-2">
                                            <select class="form-select item-select" name="items[{{ $idx }}][item_id]">
                                                <option value="" disabled selected>Pilih Barang</option>
                                                @foreach ($items as $item)
                                                    <option value="{{ $item->id }}" {{ isset($oldLine['item_id']) && $oldLine['item_id'] == $item->id ? 'selected' : '' }} data-stock="{{ $item->available_stock ?? max(0, ($item->stock ?? 0) - ($item->damaged_count ?? 0)) }}">{{ $item->name }} - ({{ $item->unique_code }})</option>
                                                @endforeach
                                            </select>
                                            <input type="number" name="items[{{ $idx }}][quantity]" class="form-control item-qty" min="1" value="{{ $oldLine['quantity'] ?? 1 }}" style="max-width:110px;">
                                            <button type="button" class="btn btn-sm btn-danger remove-item">-</button>
                                        </div>
                                        @php $idx++; @endphp
                                    @endforeach
                                @else
                                    @foreach($loan->items as $line)
                                        <div class="item-row d-flex gap-2 align-items-center mb-2">
                                            <select class="form-select item-select" name="items[{{ $idx }}][item_id]">
                                                <option value="" disabled>Pilih Barang</option>
                                                @foreach ($items as $item)
                                                    <option value="{{ $item->id }}" {{ $line->id == $item->id ? 'selected' : '' }} data-stock="{{ $item->available_stock ?? max(0, ($item->stock ?? 0) - ($item->damaged_count ?? 0)) }}">{{ $item->name }} - ({{ $item->unique_code }})</option>
                                                @endforeach
                                            </select>
                                            <input type="number" name="items[{{ $idx }}][quantity]" class="form-control item-qty" min="1" value="{{ $line->pivot->quantity ?? 1 }}" style="max-width:110px;">
                                            <button type="button" class="btn btn-sm btn-danger remove-item">-</button>
                                        </div>
                                        @php $idx++; @endphp
                                    @endforeach
                                @endif
                            </div>
                            <button type="button" id="add_item" class="btn btn-sm btn-secondary mt-2">Tambah Item</button>
                            <small id="available_stock_info" class="text-muted d-block mt-1">Jumlah pinjam tidak boleh melebihi stok tersedia.</small>
                        </div>

                                                {{-- Tanggal Pinjam --}}
                                                <div class="mb-3">
                                                        <label for="loan_date" class="form-label">Tanggal Pinjam</label>
                                                        <input type="date"
                                class="form-control @error('loan_date') is-invalid @enderror" name="loan_date"
                                                                id="loan_date"
                                value="{{ old('loan_date', \Carbon\Carbon::parse($loan->loan_date)->format('Y-m-d')) }}">
                                                        @error('loan_date')
                                                                <small class="text-danger">{{ $message }}</small>
                                                        @enderror
                                                    </div>

                                                {{-- Tanggal Pengembalian --}}
                                                <div class="mb-3">
                                                        <label for="return_date" class="form-label">Tanggal
                                Pengembalian</label>
                                                        <input type="date"
                                class="form-control @error('return_date') is-invalid @enderror" name="return_date"
                                                                id="return_date"
                                value="{{ old('return_date', \Carbon\Carbon::parse($loan->return_date)->format('Y-m-d')) }}">
                                                        @error('return_date')
                                                                <small class="text-danger">{{ $message }}</small>
                                                        @enderror
                                                    </div>

                        <div class="mb-3">
                            <label for="quantity" class="form-label">Jumlah</label>
                            <input type="number" name="quantity" id="quantity" class="form-control" min="1"
                                value="{{ old('quantity', $loan->quantity) }}" required>
                        </div>

                                                {{-- Tombol --}}
                                                <button type="submit" class="btn btn-primary">Simpan</button>
                                                <a href="{{ route('loan.index') }}" class="btn btn-warning">Kembali</a>
                    
                    </form>
                                    </div>
                            </div>
                    </div>
            </div>
@endsection
@section('js')
	{{-- JS Tambahan --}}
    <script>
        (function() {
            const form = document.querySelector('form[action="{{ route('loan.update', $loan->id) }}"]');
            const container = document.getElementById('items_container');
            const addBtn = document.getElementById('add_item');

            let index = container ? container.querySelectorAll('.item-row').length : 0;

            function createRow(i) {
                const div = document.createElement('div');
                div.className = 'item-row d-flex gap-2 align-items-center mb-2';
                div.innerHTML = `
                    <select class="form-select item-select" name="items[${i}][item_id]">
                        <option value="" disabled selected>Pilih Barang</option>
                        @foreach ($items as $item)
                            <option value="{{ $item->id }}" data-stock="{{ $item->available_stock ?? max(0, ($item->stock ?? 0) - ($item->damaged_count ?? 0)) }}">{{ $item->name }} - ({{ $item->unique_code }})</option>
                        @endforeach
                    </select>
                    <input type="number" name="items[${i}][quantity]" class="form-control item-qty" min="1" value="1" style="max-width:110px;">
                    <button type="button" class="btn btn-sm btn-danger remove-item">-</button>
                `;
                return div;
            }

            if (addBtn) addBtn.addEventListener('click', function(e) { e.preventDefault(); const row = createRow(index++); container.appendChild(row); });

            container.addEventListener('click', function(e) { if (e.target && e.target.classList.contains('remove-item')) { const row = e.target.closest('.item-row'); if (row) row.remove(); } });

            // basic submit validation: ensure each row has item selected and qty <= data-stock
            if (form) {
                form.addEventListener('submit', function(e) {
                    const rows = container.querySelectorAll('.item-row');
                    for (let r of rows) {
                        const sel = r.querySelector('.item-select');
                        const qty = r.querySelector('.item-qty');
                        if (!sel.value) { e.preventDefault(); alert('Silakan pilih barang pada setiap baris.'); return false; }
                        const stock = parseInt(sel.dataset?.stock || sel.options[sel.selectedIndex]?.dataset?.stock || '0', 10);
                        const want = parseInt(qty.value || '0', 10);
                        if (want > stock) { e.preventDefault(); alert('Permintaan jumlah (' + want + ') melebihi stok tersedia (' + stock + ').'); return false; }
                    }
                });
            }
        })();
    </script>
@endsection