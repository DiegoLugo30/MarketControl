<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    /**
     * Listar todas las sucursales
     */
    public function index()
    {
        $branches = Branch::orderBy('is_main', 'desc')
            ->orderBy('is_active', 'desc')
            ->orderBy('name')
            ->get();

        return view('branches.index', compact('branches'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        return view('branches.create');
    }

    /**
     * Guardar nueva sucursal
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:branches,code',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'is_main' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        // Si se marca como principal, desmarcar las demás
        if ($request->boolean('is_main')) {
            Branch::where('is_main', true)->update(['is_main' => false]);
        }

        $validated['is_main'] = $request->boolean('is_main');
        $validated['is_active'] = $request->boolean('is_active', true);

        Branch::create($validated);

        return redirect()->route('branches.index')
            ->with('success', 'Sucursal creada exitosamente');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Branch $branch)
    {
        return view('branches.edit', compact('branch'));
    }

    /**
     * Actualizar sucursal
     */
    public function update(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:branches,code,' . $branch->id,
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'is_main' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        // Si se marca como principal, desmarcar las demás
        if ($request->boolean('is_main') && !$branch->is_main) {
            Branch::where('is_main', true)->update(['is_main' => false]);
        }

        $validated['is_main'] = $request->boolean('is_main');
        $validated['is_active'] = $request->boolean('is_active');

        $branch->update($validated);

        return redirect()->route('branches.index')
            ->with('success', 'Sucursal actualizada exitosamente');
    }

    /**
     * Eliminar sucursal
     */
    public function destroy(Branch $branch)
    {
        // Validar que no sea la sucursal principal
        if ($branch->is_main) {
            return redirect()->route('branches.index')
                ->with('error', 'No se puede eliminar la sucursal principal');
        }

        // Validar que no tenga datos críticos
        $hasSales = $branch->sales()->exists();
        $hasExpenses = $branch->expenses()->exists();
        $hasStock = $branch->productStocks()->where('stock', '>', 0)->exists();

        if ($hasSales || $hasExpenses || $hasStock) {
            return redirect()->route('branches.index')
                ->with('error', 'No se puede eliminar la sucursal porque tiene ventas, gastos o stock asociado');
        }

        $branch->delete();

        return redirect()->route('branches.index')
            ->with('success', 'Sucursal eliminada exitosamente');
    }

    /**
     * Cambiar sucursal activa (AJAX)
     */
    public function setActive(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
        ]);

        $branch = Branch::find($validated['branch_id']);

        if (!$branch || !$branch->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Sucursal no encontrada o inactiva',
            ], 400);
        }

        // Establecer sucursal activa en sesión
        session(['active_branch_id' => $branch->id]);

        return response()->json([
            'success' => true,
            'branch' => [
                'id' => $branch->id,
                'name' => $branch->name,
                'code' => $branch->code,
            ],
            'message' => "Cambiado a {$branch->name}",
        ]);
    }
}
