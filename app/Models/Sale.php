<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToBranch;

class Sale extends Model
{
    use BelongsToBranch;

    public $timestamps = false;

    protected $fillable = [
        'branch_id',
        'total',
        'created_at',
        'discount_amount',
        'discount_description',
        'payment_method',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'discount_amount' => 'decimal:2',
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
     * Calcular el subtotal de la venta (antes de descuentos)
     */
    public function calculateSubtotal(): float
    {
        return $this->items->sum('subtotal');
    }

    /**
     * Calcular el total de descuentos de items
     */
    public function calculateItemDiscounts(): float
    {
        return $this->items->sum('item_discount');
    }

    /**
     * Calcular el total de la venta (después de descuentos)
     */
    public function calculateTotal(): float
    {
        $subtotal = $this->calculateSubtotal();
        $itemDiscounts = $this->calculateItemDiscounts();
        return $subtotal - $itemDiscounts - $this->discount_amount;
    }

    /**
     * Obtener el subtotal sin descuentos de items
     */
    public function getSubtotalBeforeItemDiscounts(): float
    {
        return $this->items->sum(function ($item) {
            return $item->is_weighted ? $item->price : ($item->quantity * $item->price);
        });
    }
}
