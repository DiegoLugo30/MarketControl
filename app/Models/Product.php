<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'internal_code',
        'barcode',
        'name',
        'description',
        'price',
        'price_per_kg',
        'stock',
        'is_weighted',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'price_per_kg' => 'decimal:2',
        'stock' => 'integer',
        'is_weighted' => 'boolean',
    ];

    /**
     * Relación con items de venta
     */
    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Validar si hay stock disponible
     */
    public function hasStock(int $quantity = 1): bool
    {
        return $this->stock >= $quantity;
    }

    /**
     * Decrementar stock
     */
    public function decrementStock(int $quantity): void
    {
        $this->decrement('stock', $quantity);
    }

    /**
     * Calcular precio para producto pesable
     */
    public function calculateWeightPrice(float $weight): float
    {
        if (!$this->is_weighted || !$this->price_per_kg) {
            return 0;
        }

        return round($weight * $this->price_per_kg, 2);
    }

    /**
     * Obtener precio a mostrar según tipo de producto
     */
    public function getDisplayPrice(): string
    {
        if ($this->is_weighted) {
            return '$' . number_format($this->price_per_kg, 2) . '/kg';
        }

        return '$' . number_format($this->price, 2);
    }

    /**
     * Verificar si el producto requiere peso
     */
    public function requiresWeight(): bool
    {
        return $this->is_weighted;
    }
}
