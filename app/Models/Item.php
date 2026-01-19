<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'photo',
        'unique_code',
        'condition',
        'user_id',
        'category_id',
        'stock',           // Total stok keseluruhan
        'damaged_count',   // Jumlah barang rusak
        'notes',           // Catatan tambahan
        'total_stock',
    ];

    protected $appends = [
        'available_stock', // Stok yang tersedia untuk dipinjam
        'damaged_percentage', // Persentase barang rusak
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'stock' => 'integer',
        'damaged_count' => 'integer',
    ];

    // Mendefinisikan relasi "belongsTo" ke model Category
    // public function category()
    // {
    //     return $this->belongsTo(Category::class);
    // }

    public function loan()
    {
        return $this->hasMany(Loan::class);
    }
    
    /**
     * Cek apakah barang bisa dipinjam
     */
    public function isAvailable()
    {
        return $this->available_stock > 0;
    }

    /**
     * Cek apakah barang memiliki kondisi rusak
     */
    public function hasDamagedItems()
    {
        return $this->damaged_count > 0;
    }

    /**
     * Accessor untuk stok yang tersedia (baik)
     */
    public function getAvailableStockAttribute()
    {
        return max(0, $this->stock - $this->damaged_count);
    }

    /**
     * Accessor untuk persentase barang rusak
     */
    public function getDamagedPercentageAttribute()
    {
        if ($this->stock == 0) {
            return 0;
        }
        
        return round(($this->damaged_count / $this->stock) * 100, 2);
    }

    /**
     * Hitung stok yang tersedia untuk rentang tanggal tertentu.
     * Mengurangi jumlah unit yang sedang dipinjam (status 'Dipinjam')
     * dan memperhitungkan barang rusak.
     *
     * @param \Illuminate\Support\Carbon|string $start
     * @param \Illuminate\Support\Carbon|string $end
     * @return int
     */
    public function availableStockForPeriod($start, $end, $excludeLoanId = null)
    {
        $start = \Illuminate\Support\Carbon::parse($start)->startOfDay();
        $end = \Illuminate\Support\Carbon::parse($end)->endOfDay();

        // Total stok fisik (tidak termasuk rusak)
        $physical = max(0, ($this->stock ?? 0) - ($this->damaged_count ?? 0));

        if ($physical <= 0) {
            return 0;
        }

        // Jumlah unit yang sudah dibooking/dipinjam pada rentang tanggal yang tumpang tindih
        // Karena sekarang kita mendukung multi-item peminjaman via pivot table loan_items,
        // hitung jumlah lewat join loan_items -> loans.
        $loanItemsQuery = \Illuminate\Support\Facades\DB::table('loan_items')
            ->join('loans', 'loan_items.loan_id', '=', 'loans.id')
            ->where('loan_items.item_id', $this->id)
            ->where('loans.status', 'Dipinjam')
            ->whereDate('loans.return_date', '>=', $start->toDateString())
            ->whereDate('loans.loan_date', '<=', $end->toDateString());

        if ($excludeLoanId) {
            $loanItemsQuery->where('loans.id', '!=', $excludeLoanId);
        }

        $overlappingQuantity = (int) $loanItemsQuery->sum('loan_items.quantity');

        $available = $physical - $overlappingQuantity;

        return max(0, (int) $available);
    }

    /**
     * Scope untuk barang yang tersedia (baik)
     */
    public function scopeAvailable($query)
    {
        return $query->whereRaw('stock > damaged_count');
    }

    /**
     * Scope untuk barang yang memiliki kerusakan
     */
    public function scopeHasDamage($query)
    {
        return $query->where('damaged_count', '>', 0);
    }

    /**
     * Scope untuk barang yang rusak semua
     */
    public function scopeFullyDamaged($query)
    {
        return $query->whereRaw('stock = damaged_count')->where('stock', '>', 0);
    }

    /**
     * Scope untuk barang berdasarkan kondisi
     */
    public function scopeByCondition($query, $condition)
    {
        return $query->where('condition', $condition);
    }

    /**
     * Update jumlah barang rusak
     */
    public function updateDamagedCount($newDamagedCount)
    {
        if ($newDamagedCount > $this->stock) {
            throw new \Exception('Jumlah barang rusak tidak boleh melebihi total stok');
        }

        $this->damaged_count = $newDamagedCount;
        // Update kondisi barang otomatis (sets attribute but does not save)
        $this->updateConditionAutomatically();
        // Persist changes
        $this->save();

        return $this;
    }

    /**
     * Update kondisi barang otomatis berdasarkan stok rusak
     */
    public function updateConditionAutomatically()
    {
        if ($this->damaged_count == 0) {
            $this->condition = 'Baik';
        } elseif ($this->damaged_count == $this->stock) {
            $this->condition = 'Rusak';
        } else {
            $this->condition = 'Baik'; // Atau "Sebagian Rusak" jika ingin lebih detail
        }
        // NOTE: do NOT call save() here to avoid recursion when used inside model events.
    }

    /**
     * Tambah barang rusak
     */
    public function addDamagedItem($count = 1)
    {
        $newCount = $this->damaged_count + $count;
        
        if ($newCount > $this->stock) {
            throw new \Exception('Tidak bisa menambah barang rusak melebihi total stok');
        }
        
        $this->damaged_count = $newCount;
        $this->updateConditionAutomatically();
        $this->save();

        return $this;
    }

    /**
     * Kurangi barang rusak (barang diperbaiki)
     */
    public function repairItem($count = 1)
    {
        $newCount = $this->damaged_count - $count;
        
        if ($newCount < 0) {
            throw new \Exception('Tidak bisa mengurangi barang rusak kurang dari 0');
        }
        
        $this->damaged_count = $newCount;
        $this->updateConditionAutomatically();
        $this->save();

        return $this;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Event listener untuk memastikan data konsisten
     */
    protected static function boot()
    {
        parent::boot();

        // Validasi sebelum menyimpan
        static::saving(function ($item) {
            if ($item->damaged_count > $item->stock) {
                throw new \Exception('Jumlah barang rusak tidak boleh melebihi total stok');
            }
            
            // Update kondisi otomatis saat menyimpan
            if ($item->isDirty(['stock', 'damaged_count'])) {
                $item->updateConditionAutomatically();
            }
        });
    }
}