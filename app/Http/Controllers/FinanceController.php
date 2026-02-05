<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinanceController extends Controller
{
    /**
     * Dashboard financiero con resumen mensual
     */
    public function index(Request $request)
    {
        // Obtener mes y año actual o del filtro
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        // Obtener filtro de sucursal (solo disponible desde sucursal principal)
        $filterBranchId = $request->get('branch_id', null);

        // Obtener sucursal activa y todas las sucursales
        $activeBranch = \App\Models\Branch::find(session('active_branch_id'));
        $allBranches = \App\Models\Branch::where('is_active', true)->orderBy('name')->get();

        // Variable para indicar si se están mostrando todas las sucursales
        $showingAllBranches = false;
        $viewingBranch = null;

        // Determinar la consulta según el filtro
        if ($filterBranchId === 'all') {
            // Ver todas las sucursales consolidadas
            $showingAllBranches = true;

            $income = Sale::withoutBranchScope()
                         ->whereYear('created_at', $year)
                         ->whereMonth('created_at', $month)
                         ->sum('total');

            $expenses = Expense::withoutBranchScope()
                              ->whereYear('created_at', $year)
                              ->whereMonth('created_at', $month)
                              ->sum('amount');

            $salesCount = Sale::withoutBranchScope()
                             ->whereYear('created_at', $year)
                             ->whereMonth('created_at', $month)
                             ->count();

            $topProducts = $this->getTopProducts($month, $year, null);
            $expensesByCategory = Expense::withoutBranchScope()
                                        ->whereYear('date', $year)
                                        ->whereMonth('date', $month)
                                        ->select('category', DB::raw('SUM(amount) as total'))
                                        ->groupBy('category')
                                        ->orderBy('total', 'desc')
                                        ->get();

        } elseif ($filterBranchId && $activeBranch && $activeBranch->is_main) {
            // Ver sucursal específica (solo desde sucursal principal)
            $viewingBranch = \App\Models\Branch::find($filterBranchId);

            if ($viewingBranch) {
                $income = Sale::withoutBranchScope()
                             ->where('branch_id', $filterBranchId)
                             ->whereYear('created_at', $year)
                             ->whereMonth('created_at', $month)
                             ->sum('total');

                $expenses = Expense::withoutBranchScope()
                                  ->where('branch_id', $filterBranchId)
                                  ->whereYear('created_at', $year)
                                  ->whereMonth('created_at', $month)
                                  ->sum('amount');

                $salesCount = Sale::withoutBranchScope()
                                 ->where('branch_id', $filterBranchId)
                                 ->whereYear('created_at', $year)
                                 ->whereMonth('created_at', $month)
                                 ->count();

                $topProducts = $this->getTopProducts($month, $year, $filterBranchId);
                $expensesByCategory = Expense::withoutBranchScope()
                                            ->where('branch_id', $filterBranchId)
                                            ->whereYear('date', $year)
                                            ->whereMonth('date', $month)
                                            ->select('category', DB::raw('SUM(amount) as total'))
                                            ->groupBy('category')
                                            ->orderBy('total', 'desc')
                                            ->get();
            } else {
                // Sucursal no encontrada, usar datos de sucursal activa
                return redirect()->route('finances.index', ['month' => $month, 'year' => $year]);
            }

        } else {
            // Vista normal - sucursal activa (comportamiento por defecto)
            $viewingBranch = $activeBranch;

            $income = Sale::whereYear('created_at', $year)
                         ->whereMonth('created_at', $month)
                         ->sum('total');

            $expenses = Expense::forMonth($month, $year)->sum('amount');

            $salesCount = Sale::whereYear('created_at', $year)
                             ->whereMonth('created_at', $month)
                             ->count();

            $topProducts = $this->getTopProducts($month, $year);
            $expensesByCategory = Expense::getTotalByCategory($month, $year);
        }

        // Calcular resultado (ingresos - gastos)
        $result = $income - $expenses;

        // Datos para gráfico de barras (ingresos vs gastos)
        $chartData = [
            'income' => $income,
            'expenses' => $expenses,
        ];

        return view('finances.index', compact(
            'month',
            'year',
            'income',
            'expenses',
            'result',
            'salesCount',
            'topProducts',
            'expensesByCategory',
            'chartData',
            'activeBranch',
            'allBranches',
            'filterBranchId',
            'showingAllBranches',
            'viewingBranch'
        ));
    }

    /**
     * Obtener top 5 productos más vendidos del mes
     */
    private function getTopProducts($month, $year, $branchId = null)
    {
        $query = SaleItem::select('product_id', DB::raw('SUM(quantity) as total_quantity'));

        if ($branchId === null) {
            // Sin branch_id específico, usa el scope normal (sucursal activa)
            $query->whereHas('sale', function ($q) use ($month, $year) {
                $q->whereYear('created_at', $year)
                  ->whereMonth('created_at', $month);
            });
        } else {
            // Con branch_id específico o null para todas
            $query->whereHas('sale', function ($q) use ($month, $year, $branchId) {
                $q->withoutBranchScope()
                  ->whereYear('created_at', $year)
                  ->whereMonth('created_at', $month);
                if ($branchId !== 'all') {
                    $q->where('branch_id', $branchId);
                }
            });
        }

        return $query->with('product')
                    ->groupBy('product_id')
                    ->orderBy('total_quantity', 'desc')
                    ->limit(5)
                    ->get();
    }

    /**
     * Listar gastos con filtros
     */
    public function expenses(Request $request)
    {
        $query = Expense::query();

        // Filtrar por mes y año
        if ($request->filled('month') && $request->filled('year')) {
            $query->forMonth($request->month, $request->year);
        }

        // Filtrar por categoría
        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        $expenses = $query->orderBy('date', 'desc')->paginate(20);
        $expenses->appends($request->only(['month', 'year', 'category']));

        // Obtener categorías para el filtro
        $categories = Expense::getCategories();

        return view('finances.expenses', compact('expenses', 'categories'));
    }

    /**
     * Mostrar formulario de crear gasto
     */
    public function createExpense()
    {
        $categories = Expense::getCategories();
        return view('finances.expense-form', compact('categories'));
    }

    /**
     * Guardar nuevo gasto
     */
    public function storeExpense(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'category' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $validated['created_by'] = 'Admin'; // Aquí podrías usar auth()->user()->name si tienes autenticación

        Expense::create($validated);

        return redirect()->route('finances.expenses')
                        ->with('success', 'Gasto registrado exitosamente');
    }

    /**
     * Mostrar formulario de editar gasto
     */
    public function editExpense($id)
    {
        $expense = Expense::findOrFail($id);
        $categories = Expense::getCategories();
        return view('finances.expense-form', compact('expense', 'categories'));
    }

    /**
     * Actualizar gasto
     */
    public function updateExpense(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);

        $validated = $request->validate([
            'date' => 'required|date',
            'category' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $expense->update($validated);

        return redirect()->route('finances.expenses')
                        ->with('success', 'Gasto actualizado exitosamente');
    }

    /**
     * Eliminar gasto
     */
    public function destroyExpense($id)
    {
        $expense = Expense::findOrFail($id);
        $expense->delete();

        return redirect()->route('finances.expenses')
                        ->with('success', 'Gasto eliminado exitosamente');
    }

    /**
     * Exportar reporte mensual - Vista imprimible para PDF
     */
    public function exportReport(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        $filterBranchId = $request->get('branch_id', null);

        $branchName = '';
        $showingAllBranches = false;

        // Determinar qué datos mostrar según el filtro
        if ($filterBranchId === 'all') {
            // Todas las sucursales
            $showingAllBranches = true;
            $branchName = 'Todas las Sucursales';

            $income = Sale::withoutBranchScope()
                         ->whereYear('created_at', $year)
                         ->whereMonth('created_at', $month)
                         ->sum('total');

            $expensesList = Expense::withoutBranchScope()
                                  ->whereYear('date', $year)
                                  ->whereMonth('date', $month)
                                  ->orderBy('date')
                                  ->get();

            $salesCount = Sale::withoutBranchScope()
                             ->whereYear('created_at', $year)
                             ->whereMonth('created_at', $month)
                             ->count();

            $expensesByCategory = Expense::withoutBranchScope()
                                        ->whereYear('date', $year)
                                        ->whereMonth('date', $month)
                                        ->select('category', DB::raw('SUM(amount) as total'))
                                        ->groupBy('category')
                                        ->orderBy('total', 'desc')
                                        ->get();

        } elseif ($filterBranchId) {
            // Sucursal específica
            $branch = \App\Models\Branch::find($filterBranchId);
            $branchName = $branch ? $branch->name : 'Sucursal Activa';

            $income = Sale::withoutBranchScope()
                         ->where('branch_id', $filterBranchId)
                         ->whereYear('created_at', $year)
                         ->whereMonth('created_at', $month)
                         ->sum('total');

            $expensesList = Expense::withoutBranchScope()
                                  ->where('branch_id', $filterBranchId)
                                  ->whereYear('date', $year)
                                  ->whereMonth('date', $month)
                                  ->orderBy('date')
                                  ->get();

            $salesCount = Sale::withoutBranchScope()
                             ->where('branch_id', $filterBranchId)
                             ->whereYear('created_at', $year)
                             ->whereMonth('created_at', $month)
                             ->count();

            $expensesByCategory = Expense::withoutBranchScope()
                                        ->where('branch_id', $filterBranchId)
                                        ->whereYear('date', $year)
                                        ->whereMonth('date', $month)
                                        ->select('category', DB::raw('SUM(amount) as total'))
                                        ->groupBy('category')
                                        ->orderBy('total', 'desc')
                                        ->get();

        } else {
            // Sucursal activa (por defecto)
            $activeBranch = \App\Models\Branch::find(session('active_branch_id'));
            $branchName = $activeBranch ? $activeBranch->name : 'Sucursal Activa';

            $income = Sale::whereYear('created_at', $year)
                         ->whereMonth('created_at', $month)
                         ->sum('total');

            $expensesList = Expense::forMonth($month, $year)
                                  ->orderBy('date')
                                  ->get();

            $salesCount = Sale::whereYear('created_at', $year)
                             ->whereMonth('created_at', $month)
                             ->count();

            $expensesByCategory = Expense::getTotalByCategory($month, $year);
        }

        $totalExpenses = $expensesList->sum('amount');
        $result = $income - $totalExpenses;
        $monthName = Carbon::create($year, $month, 1)->locale('es')->translatedFormat('F Y');

        // Retornar vista imprimible
        return view('finances.report-print', compact(
            'month',
            'year',
            'monthName',
            'income',
            'totalExpenses',
            'result',
            'salesCount',
            'expensesList',
            'expensesByCategory',
            'branchName',
            'showingAllBranches'
        ));
    }

}
