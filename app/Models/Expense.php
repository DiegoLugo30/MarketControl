<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'category',
        'description',
        'amount',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Scope para filtrar gastos por mes y año
     */
    public function scopeForMonth($query, $month, $year)
    {
        return $query->whereYear('date', $year)
                     ->whereMonth('date', $month);
    }

    /**
     * Scope para filtrar por categoría
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Obtener el mes y año en formato legible
     */
    public function getMonthYearAttribute()
    {
        return Carbon::parse($this->date)->format('F Y');
    }

    /**
     * Obtener todas las categorías únicas
     */
    public static function getCategories()
    {
        return self::select('category')
                   ->distinct()
                   ->orderBy('category')
                   ->pluck('category')
                   ->toArray();
    }

    /**
     * Obtener el total de gastos por categoría para un mes específico
     */
    public static function getTotalByCategory($month, $year)
    {
        return self::selectRaw('category, SUM(amount) as total')
                   ->forMonth($month, $year)
                   ->groupBy('category')
                   ->orderBy('total', 'desc')
                   ->get();
    }
}
