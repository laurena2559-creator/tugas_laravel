@extends('admin.layouts.app')

@section('css')
    <style>
        .stock-badge {
            font-size: 0.85rem;
            padding: 6px 10px;
            transition: all 0.3s ease;
            min-width: 100px;
            text-align: center;
        }
        .item-qty.is-invalid {
            border-color: #dc3545;
        }
        .item-row {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
            margin-bottom: 10px;
        }
        .item-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1.5fr auto;
            gap: 10px;
            align-items: start;
        }
    </style>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Tambah Data {{ $title }}</h5>
            <form action="{{ route('loan.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                {{-- Nama Peminjam --}}
                <div class="mb-4">
                    <label for="user_id" class="form-label fw-bold">Nama Peminjam</label>
                    <select class="form-select @error('user_id') is-invalid @enderror" name="user_id" id="user_id">
                        <option value="" disabled selected>Pilih Peminjam</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Tanggal Pinjam Utama --}}
                <div class="mb-4">
                    <label for="loan_date" class="form-label fw-bold">Tanggal Pinjam</label>
                    <input type="date" class="form-control @error('loan_date') is-invalid @enderror"
                        name="loan_date" id="loan_date" value="{{ old('loan_date', date('Y-m-d')) }}">
                    @error('loan_date')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <hr>

                {{-- Daftar Barang --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Daftar Barang & Tanggal Kembali</label>
                    <div id="items_container">
                        <div class="item-row">
                            <div class="item-grid">
                                {{-- Pilih Barang --}}
                                <div>
                                    <select class="form-select item-select" name="items[0][item_id]" required>
                                        <option value="" disabled selected>Pilih Barang</option>
                                        @foreach ($items as $item)
                                            @php
                                                $physical = max(0, ($item->stock ?? 0));
                                                $damaged = max(0, ($item->damaged_count ?? 0));
                                                // Use period-aware availability for today so "static" view
                                                // reflects items currently borrowed
                                                try {
                                                    $today = date('Y-m-d');
                                                    $staticAvailable = method_exists($item, 'availableStockForPeriod') ? $item->availableStockForPeriod($today, $today) : max(0, $physical - $damaged);
                                                } catch (\Exception $e) {
                                                    $staticAvailable = max(0, $physical - $damaged);
                                                }
                                            @endphp
                                            <option value="{{ $item->id }}" 
                                                data-static-available="{{ $staticAvailable }}"
                                                data-physical="{{ $physical }}"
                                                data-damaged="{{ $damaged }}">
                                                {{ $item->name }} ({{ $item->unique_code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="badge bg-secondary stock-badge mt-2">-</span>
                                </div>

                                {{-- Jumlah --}}
                                <div>
                                    <input type="number" name="items[0][quantity]" class="form-control item-qty" 
                                        placeholder="Qty" min="1" value="1" required>
                                </div>

                                {{-- Tanggal Kembali Per Item --}}
                                <div>
                                    <input type="date" name="items[0][return_date]" class="form-control item-return-date" required>
                                    <small class="text-muted" style="font-size: 0.7rem;">Tgl Kembali</small>
                                </div>

                                {{-- Hapus --}}
                                <div>
                                    <button type="button" class="btn btn-danger remove-item">
                                        <i class="ti ti-trash"></i> -
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" id="add_item" class="btn btn-sm btn-outline-primary mt-2">
                        <i class="ti ti-plus"></i> Tambah Item Lain
                    </button>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary px-4">Simpan Peminjaman</button>
                    <a href="{{ route('loan.index') }}" class="btn btn-light ms-2">Kembali</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('js')
    <script>
        (function() {
            const container = document.getElementById('items_container');
            const addBtn = document.getElementById('add_item');
            const form = document.querySelector('form');
            const loanDateInput = document.getElementById('loan_date');

            let index = 1;

            // --- 1. Fungsi Update Stok (GABUNGAN LOGIKA BARU) ---
            async function updateAvailabilityForAllRows() {
                const rows = Array.from(container.querySelectorAll('.item-row'));

                // First: for each row, fetch or assign the availability (date-aware when dates set)
                for (let row of rows) {
                    const sel = row.querySelector('.item-select');
                    const badge = row.querySelector('.stock-badge');
                    const loanDate = loanDateInput.value;
                    const itemReturnDate = row.querySelector('.item-return-date').value;

                    if (!sel || !sel.value) {
                        if (badge) {
                            badge.textContent = '-';
                            badge.className = 'badge bg-secondary stock-badge mt-2';
                        }
                        continue;
                    }

                    const selectedOption = sel.options[sel.selectedIndex];
                    const staticStock = selectedOption ? parseInt(selectedOption.dataset.staticAvailable || 0) : 0;

                    // If both dates available, fetch period-aware availability
                    if (loanDate && itemReturnDate) {
                        try {
                            if (badge) { badge.textContent = 'Checking...'; badge.className = 'badge bg-secondary stock-badge mt-2'; }
                            const url = new URL('{{ route("loan.availability") }}', window.location.origin);
                            url.searchParams.set('item_id', sel.value);
                            url.searchParams.set('loan_date', loanDate);
                            url.searchParams.set('return_date', itemReturnDate);

                            const res = await fetch(url);
                            const data = await res.json();
                            sel.dataset.stock = parseInt(data.available || 0);
                        } catch (err) {
                            // fallback to static stock on error
                            sel.dataset.stock = staticStock;
                            if (badge) { badge.textContent = 'Error Stok'; badge.className = 'badge bg-danger stock-badge mt-2'; }
                            continue;
                        }
                    } else {
                        // no dates -> use static available
                        sel.dataset.stock = staticStock;
                    }
                }

                // Second: aggregate total requested per item and update badges & validation
                const totals = {}; // itemId -> { available, requested, rows: [] }

                rows.forEach(row => {
                    const sel = row.querySelector('.item-select');
                    const qtyInput = row.querySelector('.item-qty');
                    const badge = row.querySelector('.stock-badge');
                    if (!sel || !sel.value) return;
                    const itemId = sel.value;
                    const available = parseInt(sel.dataset.stock || sel.options[sel.selectedIndex]?.dataset?.staticAvailable || 0);
                    const want = parseInt(qtyInput.value || 0);

                    if (!totals[itemId]) totals[itemId] = { available: available, requested: 0, rows: [] };
                    totals[itemId].requested += want;
                    totals[itemId].rows.push({ row, want, badge, sel });
                });

                // Update badges and mark invalid inputs for items exceeding availability
                Object.keys(totals).forEach(itemId => {
                    const info = totals[itemId];
                    const remaining = info.available - info.requested;

                    info.rows.forEach(r => {
                        const { row, want, badge, sel } = r;
                        if (!badge) return;
                        if (remaining < 0) {
                            badge.textContent = `Kekurangan: ${Math.abs(remaining)}`;
                            badge.className = 'badge bg-danger stock-badge mt-2';
                        } else {
                            badge.textContent = `Sisa: ${remaining}`;
                            badge.className = 'badge bg-success stock-badge mt-2';
                        }

                        // mark inputs invalid if total requested exceeds availability
                        const input = row.querySelector('.item-qty');
                        if (info.requested > info.available) {
                            input.classList.add('is-invalid');
                        } else {
                            input.classList.remove('is-invalid');
                        }
                    });
                });

                // For rows with unselected item or items not in totals, ensure badge set
                rows.forEach(row => {
                    const sel = row.querySelector('.item-select');
                    const badge = row.querySelector('.stock-badge');
                    if (!sel || !sel.value) return;
                    if (!totals[sel.value]) {
                        const available = parseInt(sel.dataset.stock || sel.options[sel.selectedIndex]?.dataset?.staticAvailable || 0);
                        if (badge) { badge.textContent = `Sisa: ${available}`; badge.className = 'badge bg-success stock-badge mt-2'; }
                    }
                });
            }

            // --- 2. Fungsi Render Badge ---
            function renderBadge(badge, available) {
                if (available <= 0) {
                    badge.textContent = `Habis (0)`;
                    badge.className = 'badge bg-danger stock-badge mt-2';
                } else {
                    badge.textContent = `Sisa: ${available}`;
                    badge.className = 'badge bg-success stock-badge mt-2';
                }
            }

            // --- 3. Fungsi Validasi Quantity ---
            function validateQty(input) {
                if (!input) return;
                const row = input.closest('.item-row');
                const sel = row.querySelector('.item-select');
                const badge = row.querySelector('.stock-badge');
                
                const available = parseInt(sel.dataset.stock || 0);
                const want = parseInt(input.value || 0);

                if (want > available && sel.value) {
                    input.classList.add('is-invalid');
                    // Jangan timpa teks jika sedang "Checking..."
                    if(badge.textContent !== 'Checking...') {
                        badge.textContent = `Melebihi Stok (${available})`;
                        badge.className = 'badge bg-danger stock-badge mt-2';
                    }
                } else {
                    input.classList.remove('is-invalid');
                }
            }

            // --- 4. Event Listeners ---
            addBtn.addEventListener('click', function() {
                const firstRow = container.querySelector('.item-row');
                const newRow = firstRow.cloneNode(true);
                
                newRow.querySelector('.item-select').name = `items[${index}][item_id]`;
                newRow.querySelector('.item-select').value = "";
                newRow.querySelector('.item-qty').name = `items[${index}][quantity]`;
                newRow.querySelector('.item-qty').value = "1";
                newRow.querySelector('.item-return-date').name = `items[${index}][return_date]`;
                newRow.querySelector('.item-return-date').value = "";
                newRow.querySelector('.stock-badge').textContent = "-";
                newRow.querySelector('.stock-badge').className = "badge bg-secondary stock-badge mt-2";
                
                container.appendChild(newRow);
                index++;
            });

            container.addEventListener('click', function(e) {
                if (e.target.closest('.remove-item')) {
                    if (container.querySelectorAll('.item-row').length > 1) {
                        e.target.closest('.item-row').remove();
                        updateAvailabilityForAllRows();
                    }
                }
            });

            container.addEventListener('change', function(e) {
                if (e.target.classList.contains('item-select') || e.target.classList.contains('item-return-date')) {
                    updateAvailabilityForAllRows();
                }
            });

            container.addEventListener('input', function(e) {
                if (e.target.classList.contains('item-qty')) {
                    validateQty(e.target);
                    clearTimeout(this.delay);
                    this.delay = setTimeout(() => updateAvailabilityForAllRows(), 500);
                }
            });

            loanDateInput.addEventListener('change', () => updateAvailabilityForAllRows());

            form.addEventListener('submit', function(e) {
                const invalidInputs = container.querySelectorAll('.item-qty.is-invalid');
                if (invalidInputs.length > 0) {
                    e.preventDefault();
                    alert('Stok tidak mencukupi atau melebihi ketersediaan pada periode tersebut.');
                }
            });
        })();
    </script>
@endsection