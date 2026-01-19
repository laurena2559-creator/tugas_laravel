<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    protected $fillable = [
        'user_id',
        'item_id',
        'loan_date',
        'return_date',
        'quantity',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Many-to-many relation to items with pivot quantity.
     */
    public function items()
    {
        return $this->belongsToMany(Item::class, 'loan_items')
                    ->withPivot('quantity', 'return_date')
                    ->withTimestamps();
    }
}
