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
        'quantity',
        'weight',
        'price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'weight' => 'decimal:3',
        'price' => 'decimal:2',
    ];

    /**
     * Relación con venta
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Relación con producto
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calcular subtotal del item
     * Compatible con productos por unidad y por peso
     */
    public function getSubtotalAttribute(): float
    {
        // Si tiene peso, es un producto pesable (el precio ya viene calculado)
        if ($this->weight) {
            return $this->price;
        }

        // Producto por unidad
        return $this->quantity * $this->price;
    }

    /**
     * Verificar si es un item pesable
     */
    public function isWeighted(): bool
    {
        return !is_null($this->weight) && $this->weight > 0;
    }

    /**
     * Obtener texto descriptivo del item
     */
    public function getQuantityText(): string
    {
        if ($this->isWeighted()) {
            return number_format($this->weight, 3) . ' kg';
        }

        return $this->quantity . ' ud.';
    }
}
