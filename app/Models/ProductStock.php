<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo ProductStock (Stock por Producto y Sucursal)
 *
 * Representa el inventario de un producto específico en una sucursal específica.
 * Cada combinación (producto, sucursal) es única.
 */
class ProductStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'branch_id',
        'stock',
    ];

    protected $casts = [
        'stock' => 'integer',
    ];

    /**
     * Relación: El stock pertenece a un producto
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Relación: El stock pertenece a una sucursal
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Verificar si hay stock suficiente
     */
    public function hasStock(int $quantity = 1): bool
    {
        return $this->stock >= $quantity;
    }

    /**
     * Incrementar stock
     */
    public function incrementStock(int $quantity): void
    {
        $this->increment('stock', $quantity);
    }

    /**
     * Decrementar stock (con validación)
     */
    public function decrementStock(int $quantity): bool
    {
        if (!$this->hasStock($quantity)) {
            return false;
        }

        $this->decrement('stock', $quantity);
        return true;
    }

    /**
     * Establecer stock a un valor específico
     */
    public function setStock(int $newStock): void
    {
        $this->update(['stock' => max(0, $newStock)]);
    }

    /**
     * Transferir stock a otra sucursal
     */
    public function transferTo(int $destinationBranchId, int $quantity): bool
    {
        // Verificar stock disponible
        if (!$this->hasStock($quantity)) {
            return false;
        }

        // Obtener o crear el stock en la sucursal destino
        $destinationStock = static::firstOrCreate(
            [
                'product_id' => $this->product_id,
                'branch_id' => $destinationBranchId,
            ],
            ['stock' => 0]
        );

        // Realizar la transferencia en transacción
        \DB::transaction(function () use ($destinationStock, $quantity) {
            $this->decrementStock($quantity);
            $destinationStock->incrementStock($quantity);
        });

        return true;
    }

    /**
     * Scope: Solo registros con stock disponible
     */
    public function scopeAvailable($query)
    {
        return $query->where('stock', '>', 0);
    }

    /**
     * Scope: Por sucursal
     */
    public function scopeForBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope: Por producto
     */
    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }
}
