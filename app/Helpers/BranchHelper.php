<?php

if (!function_exists('active_branch')) {
    /**
     * Obtener la sucursal activa
     *
     * @return \App\Models\Branch|null
     */
    function active_branch()
    {
        $branchId = session('active_branch_id');

        if (!$branchId) {
            return \App\Models\Branch::main();
        }

        return \App\Models\Branch::find($branchId);
    }
}

if (!function_exists('active_branch_id')) {
    /**
     * Obtener el ID de la sucursal activa
     *
     * @return int|null
     */
    function active_branch_id()
    {
        $branch = active_branch();
        return $branch ? $branch->id : null;
    }
}

if (!function_exists('set_active_branch')) {
    /**
     * Establecer una sucursal como activa
     *
     * @param int $branchId
     * @return bool
     */
    function set_active_branch(int $branchId)
    {
        $branch = \App\Models\Branch::find($branchId);

        if ($branch && $branch->is_active) {
            session(['active_branch_id' => $branchId]);
            return true;
        }

        return false;
    }
}
