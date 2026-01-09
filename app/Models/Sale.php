<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'total',
        'created_at',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($sale) {
            if (is_null($sale->created_at)) {
                $sale->created_at = now();
            }
        });
    }

    /**
     * Relación con items de venta
     */
    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Calcular el total de la venta
     */
    public function calculateTotal(): float
    {
        return $this->items->sum(function ($item) {
            return $item->quantity * $item->price;
        });
    }
}
