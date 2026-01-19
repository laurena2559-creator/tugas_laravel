@extends('admin.layouts.app')
@section('css')
{{-- CSS Tambahan --}}
@endsection

@section('content')
<div class="card" style="border: none; box-shadow: 0 1px 3px 0 rgba(0,0,0,.1),0 1px 2px -1px rgba(0,0,0,.1);">
    <div class="card-body" style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
        <h5 class="card-title fw-semibold mb-4" style="color: #1e293b;">Ubah Data {{ $title }}</h5>
        <div class="card" style="border: 1px solid #e2e8f0;">
            <div class="card-body" style="background-color: #ffffff;">
                <form action="{{ route('item.update', $item->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    {{-- Nama --}}
                    <div class="mb-4">
                        <label for="name" class="form-label" style="color: #475569; font-weight: 500;">Nama Barang</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               name="name" id="name" placeholder="Nama Item" 
                               value="{{ old('name', $item->name) }}"
                               style="border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px; color: #1e293b;">
                        @error('name')
                        <small class="text-danger" style="color: #dc2626;">{{ $message }}</small>
                        @enderror
                    </div>
                    
                    {{-- Total Stok --}}
                    <div class="mb-4">
                        <label for="stock" class="form-label" style="color: #475569; font-weight: 500;">Jumlah Total Stok</label>
                        <input type="number" class="form-control @error('stock') is-invalid @enderror" 
                               name="stock" id="stock" 
                               value="{{ old('stock', $item->stock) }}" 
                               min="1" required
                               style="border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px; color: #1e293b;">
                        <small class="text-muted" style="color: #64748b; font-size: 0.875rem;">
                            Total keseluruhan barang (baik + rusak)
                        </small>
                        @error('stock')
                        <small class="text-danger" style="color: #dc2626;">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Jumlah Rusak --}}
                    <div class="mb-4">
                        <label for="damaged_count" class="form-label" style="color: #475569; font-weight: 500;">Jumlah Barang Rusak</label>
                        <input type="number" class="form-control @error('damaged_count') is-invalid @enderror" 
                               name="damaged_count" id="damaged_count" 
                               value="{{ old('damaged_count', $item->damaged_count ?? 0) }}" 
                               min="0" max="{{ $item->stock }}"
                               style="border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px; color: #1e293b;">
                        <div class="mt-2">
                            <small class="text-muted" style="color: #64748b; font-size: 0.875rem;">
                                <strong>Rincian Stok:</strong><br>
                                • Total Stok: <span class="fw-semibold">{{ $item->stock }}</span><br>
                                • Barang Rusak: <span class="fw-semibold text-orange">{{ $item->damaged_count ?? 0 }}</span><br>
                                • Stok Tersedia: <span class="fw-semibold text-success">{{ $item->stock - ($item->damaged_count ?? 0) }}</span>
                            </small>
                        </div>
                        @error('damaged_count')
                        <small class="text-danger" style="color: #dc2626;">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Status / Kondisi --}}
                    <div class="mb-4">
                        <label for="condition" class="form-label" style="color: #475569; font-weight: 500;">Status Barang</label>
                        <select class="form-select @error('condition') is-invalid @enderror" 
                                name="condition" id="condition"
                                style="border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px; color: #1e293b;">
                            <option value="" disabled {{ old('condition', $item->condition) == null ? 'selected' : '' }}>Pilih Status</option>
                            <option value="Baik" {{ old('condition', $item->condition) == 'Baik' ? 'selected' : '' }}>Baik</option>
                            <option value="Rusak" {{ old('condition', $item->condition) == 'Rusak' ? 'selected' : '' }}>Rusak</option>
                        </select>
                        @error('condition')
                        <small class="text-danger" style="color: #dc2626;">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Foto --}}
                    <div class="mb-4">
                        <label for="photo" class="form-label" style="color: #475569; font-weight: 500;">Ganti Foto (Opsional)</label>
                        @if ($item->photo)
                            <div class="mb-3">
                                <p style="color: #64748b; margin-bottom: 8px; font-size: 0.875rem;">Foto saat ini:</p>
                                <img src="{{ asset('photos/' . $item->photo) }}" 
                                     alt="Foto Item" 
                                     width="120" 
                                     style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 4px;">
                            </div>
                        @endif
                        <input type="file" class="form-control @error('photo') is-invalid @enderror" 
                               name="photo" id="photo"
                               style="border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px;">
                        <small class="text-muted" style="color: #64748b; font-size: 0.875rem;">
                            Biarkan kosong jika tidak ingin mengubah foto
                        </small>
                        @error('photo')
                            <small class="text-danger" style="color: #dc2626;">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Kode Unik --}}
                    <div class="mb-4">
                        <label for="unique_code" class="form-label" style="color: #475569; font-weight: 500;">Nomor Seri</label>
                        <input type="text" class="form-control @error('unique_code') is-invalid @enderror" 
                               name="unique_code" id="unique_code" placeholder="Contoh: MM-001" 
                               value="{{ old('unique_code', $item->unique_code) }}"
                               style="border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px; color: #1e293b;">
                        @error('unique_code')
                        <small class="text-danger" style="color: #dc2626;">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Penanggung Jawab --}}
                    <div class="mb-4">
                        <label for="user_id" class="form-label" style="color: #475569; font-weight: 500;">Penanggung Jawab</label>
                        <select class="form-select @error('user_id') is-invalid @enderror" 
                                name="user_id" id="user_id"
                                style="border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px; color: #1e293b;">
                            <option value="" disabled {{ old('user_id', $item->user_id) == null ? 'selected' : '' }}>Pilih</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" 
                                    {{ old('user_id', $item->user_id) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <small class="text-danger" style="color: #dc2626;">{{ $message }}</small>
                        @enderror
                    </div>
                    
                    {{-- Tombol --}}
                    <div class="d-flex gap-2">
                        <button type="submit" 
                                class="btn" 
                                style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); 
                                       color: white; 
                                       border: none;
                                       padding: 10px 24px;
                                       border-radius: 6px;
                                       font-weight: 500;">
                            Update Data
                        </button>
                        <a href="{{ route('item.index') }}" 
                           class="btn" 
                           style="background-color: #f1f5f9; 
                                  color: #475569; 
                                  border: 1px solid #cbd5e1;
                                  padding: 10px 24px;
                                  border-radius: 6px;
                                  font-weight: 500;">
                            Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    // Validasi agar jumlah rusak tidak melebihi total stok
    document.addEventListener('DOMContentLoaded', function() {
        const stockInput = document.getElementById('stock');
        const damagedInput = document.getElementById('damaged_count');
        
        // Fungsi untuk update max damaged_count
        function updateDamagedMax() {
            const stock = parseInt(stockInput.value) || 0;
            damagedInput.setAttribute('max', stock);
            
            // Jika damaged_count melebihi stock baru, sesuaikan
            const currentDamaged = parseInt(damagedInput.value) || 0;
            if (currentDamaged > stock) {
                damagedInput.value = stock;
                updateStokInfo();
            }
        }
        
        // Fungsi untuk update info stok
        function updateStokInfo() {
            const stock = parseInt(stockInput.value) || 0;
            const damaged = parseInt(damagedInput.value) || 0;
            const available = stock - damaged;
            
            // Update tampilan info stok
            const stockInfo = document.querySelector('.text-muted .fw-semibold:nth-child(2)');
            const damagedInfo = document.querySelector('.text-muted .text-orange');
            const availableInfo = document.querySelector('.text-muted .text-success');
            
            if (stockInfo && damagedInfo && availableInfo) {
                stockInfo.textContent = stock;
                damagedInfo.textContent = damaged;
                availableInfo.textContent = available;
            }
        }
        
        // Event listeners
        if (stockInput) {
            stockInput.addEventListener('input', function() {
                updateDamagedMax();
                updateStokInfo();
            });
            
            stockInput.addEventListener('change', function() {
                updateDamagedMax();
                updateStokInfo();
            });
        }
        
        if (damagedInput) {
            damagedInput.addEventListener('input', function() {
                const stock = parseInt(stockInput.value) || 0;
                const damaged = parseInt(this.value) || 0;
                
                if (damaged > stock) {
                    alert('Jumlah barang rusak tidak boleh melebihi total stok!');
                    this.value = stock;
                }
                updateStokInfo();
            });
            
            damagedInput.addEventListener('change', function() {
                updateStokInfo();
            });
        }
        
        // Inisialisasi awal
        updateDamagedMax();
        updateStokInfo();
    });
</script>

<style>
    .text-orange {
        color: #f97316 !important;
    }
    .text-success {
        color: #22c55e !important;
    }
</style>
@endsection