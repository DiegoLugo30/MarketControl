<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Branch (Sucursal)
 *
 * Representa una sucursal física de la tienda.
 * Cada sucursal tiene su propio inventario de productos.
 */
class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'address',
        'phone',
        'is_main',
        'is_active',
    ];

    protected $casts = [
        'is_main' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Relación: Una sucursal tiene muchos registros de stock
     */
    public function productStocks(): HasMany
    {
        return $this->hasMany(ProductStock::class);
    }

    /**
     * Relación: Una sucursal tiene muchas ventas
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Relación: Una sucursal tiene muchos gastos
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Obtener la sucursal principal
     */
    public static function main(): ?Branch
    {
        return static::where('is_main', true)->first();
    }

    /**
     * Obtener solo sucursales activas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Obtener el stock total de todos los productos en esta sucursal
     */
    public function getTotalStockAttribute(): int
    {
        return $this->productStocks()->sum('stock');
    }

    /**
     * Obtener la cantidad de productos con stock en esta sucursal
     */
    public function getProductsWithStockCountAttribute(): int
    {
        return $this->productStocks()->where('stock', '>', 0)->count();
    }

    /**
     * Verificar si la sucursal tiene stock de un producto específico
     */
    public function hasStockOf(int $productId, int $quantity = 1): bool
    {
        $productStock = $this->productStocks()
            ->where('product_id', $productId)
            ->first();

        return $productStock && $productStock->stock >= $quantity;
    }

    /**
     * Obtener el stock de un producto en esta sucursal
     */
    public function getStockOf(int $productId): int
    {
        $productStock = $this->productStocks()
            ->where('product_id', $productId)
            ->first();

        return $productStock ? $productStock->stock : 0;
    }
}
