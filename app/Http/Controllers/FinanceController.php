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

        // Calcular ingresos del mes (suma de ventas)
        $income = Sale::whereYear('created_at', $year)
                     ->whereMonth('created_at', $month)
                     ->sum('total');

        // Calcular gastos del mes
        $expenses = Expense::forMonth($month, $year)->sum('amount');

        // Calcular resultado (ingresos - gastos)
        $result = $income - $expenses;

        // Cantidad total de ventas del mes
        $salesCount = Sale::whereYear('created_at', $year)
                         ->whereMonth('created_at', $month)
                         ->count();

        // Top 5 productos más vendidos del mes
        $topProducts = $this->getTopProducts($month, $year);

        // Gastos por categoría (para gráfico de torta)
        $expensesByCategory = Expense::getTotalByCategory($month, $year);

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
            'chartData'
        ));
    }

    /**
     * Obtener top 5 productos más vendidos del mes
     */
    private function getTopProducts($month, $year)
    {
        return SaleItem::select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->whereHas('sale', function ($query) use ($month, $year) {
                $query->whereYear('created_at', $year)
                      ->whereMonth('created_at', $month);
            })
            ->with('product')
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

        // Obtener datos del mes
        $income = Sale::whereYear('created_at', $year)
                     ->whereMonth('created_at', $month)
                     ->sum('total');

        $expensesList = Expense::forMonth($month, $year)
                              ->orderBy('date')
                              ->get();

        $totalExpenses = $expensesList->sum('amount');
        $result = $income - $totalExpenses;
        $salesCount = Sale::whereYear('created_at', $year)
                         ->whereMonth('created_at', $month)
                         ->count();

        // Gastos por categoría (para gráfico de torta)
        $expensesByCategory = Expense::getTotalByCategory($month, $year);

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
            'expensesByCategory'
        ));
    }

}
