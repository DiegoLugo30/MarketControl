<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Branch;

/**
 * Middleware: SetActiveBranch
 *
 * Gestiona la sucursal activa del usuario
 * - Lee de sesión o establece la principal por defecto
 * - Hace disponible la sucursal en toda la aplicación
 */
class SetActiveBranch
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Si no hay sucursal activa en sesión, establecer la principal
        if (!session()->has('active_branch_id')) {
            $mainBranch = Branch::main();

            if ($mainBranch) {
                session(['active_branch_id' => $mainBranch->id]);
            } else {
                // Si no hay sucursal principal, usar la primera activa
                $firstBranch = Branch::active()->first();
                if ($firstBranch) {
                    session(['active_branch_id' => $firstBranch->id]);
                }
            }
        }

        // Obtener la sucursal activa
        $activeBranchId = session('active_branch_id');

        if ($activeBranchId) {
            $activeBranch = Branch::find($activeBranchId);

            // Verificar que la sucursal existe y está activa
            if (!$activeBranch || !$activeBranch->is_active) {
                // Si la sucursal no existe o está inactiva, cambiar a la principal
                $mainBranch = Branch::main();
                if ($mainBranch) {
                    session(['active_branch_id' => $mainBranch->id]);
                    $activeBranch = $mainBranch;
                }
            }

            // Compartir sucursal activa con todas las vistas
            if ($activeBranch) {
                view()->share('activeBranch', $activeBranch);

                // También hacerla disponible en el request
                $request->attributes->set('active_branch', $activeBranch);
            }
        }

        return $next($request);
    }
}
