<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'sale_id',
        'product_id',
        'name',
        'quantity',
        'weight',
        'price',
        'item_discount',
        'is_custom',
        'unit_type',
    ];

    protected $casts = [
        'quantity'      => 'integer',
        'weight'        => 'decimal:3',
        'price'         => 'decimal:2',
        'item_discount' => 'decimal:2',
        'is_custom'     => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // -------------------------------------------------------------------------
    // Accessors / helpers
    // -------------------------------------------------------------------------

    /**
     * The human-readable name for this item.
     *
     * Priority:
     *   1. Snapshot stored at sale time (name column) – set for all new records.
     *   2. Live product relation – fallback for legacy records created before the
     *      snapshot column existed.
     *   3. Hard fallback when the product has since been deleted.
     */
    public function getDisplayName(): string
    {
        return $this->name
            ?? $this->product?->name
            ?? 'Producto eliminado';
    }

    /**
     * Subtotal before item-level discount.
     * For weighted items the `price` column already stores the total (weight × rate).
     */
    public function getSubtotalAttribute(): float
    {
        if ($this->weight) {
            return (float) $this->price;
        }

        return $this->quantity * (float) $this->price;
    }

    /**
     * Final total after item-level discount.
     */
    public function getTotalWithDiscountAttribute(): float
    {
        return $this->subtotal - ((float) ($this->item_discount ?? 0));
    }

    /**
     * True when this item was sold by weight rather than by unit.
     */
    public function isWeighted(): bool
    {
        return !is_null($this->weight) && $this->weight > 0;
    }

    /**
     * Human-readable quantity/weight string.
     */
    public function getQuantityText(): string
    {
        if ($this->isWeighted()) {
            return number_format($this->weight, 3) . ' kg';
        }

        return $this->quantity . ' ud.';
    }
}
