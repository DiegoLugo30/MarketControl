<?php

namespace App\Traits;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Trait: BelongsToBranch
 *
 * Aplica filtrado automático por sucursal activa
 * - Agrega scope global para filtrar por branch_id
 * - Establece automáticamente branch_id al crear
 * - Proporciona relación con Branch
 *
 * Uso:
 *   use BelongsToBranch;
 */
trait BelongsToBranch
{
    /**
     * Boot del trait - Registra scope global
     */
    protected static function bootBelongsToBranch()
    {
        // Scope global: filtrar automáticamente por sucursal activa
        static::addGlobalScope('branch', function (Builder $builder) {
            $activeBranchId = session('active_branch_id');

            if ($activeBranchId) {
                $builder->where(static::getBranchColumnName(), $activeBranchId);
            }
        });

        // Al crear un modelo, establecer automáticamente branch_id
        static::creating(function ($model) {
            if (!$model->{static::getBranchColumnName()}) {
                $activeBranchId = session('active_branch_id');

                if ($activeBranchId) {
                    $model->{static::getBranchColumnName()} = $activeBranchId;
                } else {
                    // Fallback: usar sucursal principal
                    $mainBranch = Branch::main();
                    if ($mainBranch) {
                        $model->{static::getBranchColumnName()} = $mainBranch->id;
                    }
                }
            }
        });
    }

    /**
     * Relación: El modelo pertenece a una sucursal
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, static::getBranchColumnName());
    }

    /**
     * Scope: Consultar sin filtro de sucursal
     *
     * Uso: Sale::withoutBranchScope()->get()
     */
    public function scopeWithoutBranchScope(Builder $query)
    {
        return $query->withoutGlobalScope('branch');
    }

    /**
     * Scope: Filtrar por una sucursal específica
     *
     * Uso: Sale::forBranch(2)->get()
     */
    public function scopeForBranch(Builder $query, int $branchId)
    {
        return $query->withoutGlobalScope('branch')
            ->where(static::getBranchColumnName(), $branchId);
    }

    /**
     * Scope: Filtrar por múltiples sucursales
     *
     * Uso: Sale::forBranches([1, 2, 3])->get()
     */
    public function scopeForBranches(Builder $query, array $branchIds)
    {
        return $query->withoutGlobalScope('branch')
            ->whereIn(static::getBranchColumnName(), $branchIds);
    }

    /**
     * Obtener el nombre de la columna branch_id
     * (permite personalización si es necesario)
     */
    protected static function getBranchColumnName(): string
    {
        return 'branch_id';
    }

    /**
     * Verificar si el modelo pertenece a la sucursal activa
     */
    public function belongsToActiveBranch(): bool
    {
        $activeBranchId = session('active_branch_id');
        return $this->{static::getBranchColumnName()} == $activeBranchId;
    }

    /**
     * Verificar si el modelo pertenece a una sucursal específica
     */
    public function belongsToBranchId(int $branchId): bool
    {
        return $this->{static::getBranchColumnName()} == $branchId;
    }
}
