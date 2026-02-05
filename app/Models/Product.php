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
        'stock', // Mantener temporalmente para compatibilidad
        'is_weighted',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'price_per_kg' => 'decimal:2',
        'stock' => 'integer', // Mantener temporalmente para compatibilidad
        'is_weighted' => 'boolean',
    ];

    // ========================================
    // RELACIONES
    // ========================================

    /**
     * Relación: Un producto tiene muchos registros de stock (uno por sucursal)
     */
    public function productStocks(): HasMany
    {
        return $this->hasMany(ProductStock::class);
    }

    /**
     * Relación: Un producto tiene muchos items de venta
     */
    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    // ========================================
    // MÉTODOS NUEVOS - MULTI-SUCURSAL
    // ========================================

    /**
     * Validar si hay stock disponible en una sucursal específica
     *
     * @param int $branchId ID de la sucursal
     * @param int $quantity Cantidad requerida
     * @return bool
     */
    public function hasStockInBranch(int $branchId, int $quantity = 1): bool
    {
        // Productos pesables no manejan stock
        if ($this->is_weighted) {
            return true;
        }

        $productStock = $this->productStocks()
            ->where('branch_id', $branchId)
            ->first();

        return $productStock && $productStock->stock >= $quantity;
    }

    /**
     * Obtener el stock disponible en una sucursal específica
     *
     * @param int $branchId ID de la sucursal
     * @return int
     */
    public function getStockInBranch(int $branchId): int
    {
        // Productos pesables siempre retornan 0
        if ($this->is_weighted) {
            return 0;
        }

        $productStock = $this->productStocks()
            ->where('branch_id', $branchId)
            ->first();

        return $productStock ? $productStock->stock : 0;
    }

    /**
     * Decrementar stock en una sucursal específica
     *
     * @param int $branchId ID de la sucursal
     * @param int $quantity Cantidad a decrementar
     * @return bool
     */
    public function decrementStockInBranch(int $branchId, int $quantity): bool
    {
        // Productos pesables no manejan stock
        if ($this->is_weighted) {
            return true;
        }

        $productStock = $this->productStocks()
            ->where('branch_id', $branchId)
            ->first();

        if (!$productStock || !$productStock->hasStock($quantity)) {
            return false;
        }

        return $productStock->decrementStock($quantity);
    }

    /**
     * Incrementar stock en una sucursal específica
     *
     * @param int $branchId ID de la sucursal
     * @param int $quantity Cantidad a incrementar
     * @return void
     */
    public function incrementStockInBranch(int $branchId, int $quantity): void
    {
        // Productos pesables no manejan stock
        if ($this->is_weighted) {
            return;
        }

        $productStock = ProductStock::firstOrCreate(
            [
                'product_id' => $this->id,
                'branch_id' => $branchId,
            ],
            ['stock' => 0]
        );

        $productStock->incrementStock($quantity);
    }

    /**
     * Obtener el stock total en todas las sucursales
     *
     * @return int
     */
    public function getTotalStockAttribute(): int
    {
        if ($this->is_weighted) {
            return 0;
        }

        return $this->productStocks()->sum('stock');
    }

    /**
     * Obtener stock por sucursal (array asociativo)
     *
     * @return array [branch_id => stock]
     */
    public function getStockBySucursal(): array
    {
        if ($this->is_weighted) {
            return [];
        }

        return $this->productStocks()
            ->pluck('stock', 'branch_id')
            ->toArray();
    }

    // ========================================
    // MÉTODOS LEGACY (COMPATIBILIDAD)
    // ========================================

    /**
     * @deprecated Usar hasStockInBranch() con branch_id
     *
     * Validar si hay stock disponible (sucursal principal)
     */
    public function hasStock(int $quantity = 1): bool
    {
        // Si todavía existe el campo stock (antes de ejecutar migración 12)
        if (isset($this->attributes['stock'])) {
            return $this->stock >= $quantity;
        }

        // Usar sucursal principal
        $mainBranch = Branch::main();
        if ($mainBranch) {
            return $this->hasStockInBranch($mainBranch->id, $quantity);
        }

        return false;
    }

    /**
     * @deprecated Usar decrementStockInBranch() con branch_id
     *
     * Decrementar stock (sucursal principal)
     */
    public function decrementStock(int $quantity): void
    {
        // Si todavía existe el campo stock (antes de ejecutar migración 12)
        if (isset($this->attributes['stock'])) {
            $this->decrement('stock', $quantity);
            return;
        }

        // Usar sucursal principal
        $mainBranch = Branch::main();
        if ($mainBranch) {
            $this->decrementStockInBranch($mainBranch->id, $quantity);
        }
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
