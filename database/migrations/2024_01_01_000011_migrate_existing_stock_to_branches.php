<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Migra el stock existente de la tabla products a product_stocks
     * asociándolo a la sucursal principal
     */
    public function up(): void
    {
        // Obtener la sucursal principal
        $mainBranch = DB::table('branches')->where('is_main', true)->first();

        if (!$mainBranch) {
            throw new \Exception('No se encontró la sucursal principal. Ejecuta la migración de branches primero.');
        }

        // Obtener todos los productos con stock > 0 O productos pesables
        $products = DB::table('products')->get();

        foreach ($products as $product) {
            // Solo migrar stock para productos NO pesables
            // Los productos pesables tendrán stock = 0 (no aplica control de inventario)
            $stockToMigrate = $product->is_weighted ? 0 : $product->stock;

            DB::table('product_stocks')->insert([
                'product_id' => $product->id,
                'branch_id' => $mainBranch->id,
                'stock' => $stockToMigrate,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Log de migración
        $totalProducts = $products->count();
        $totalStock = DB::table('product_stocks')->sum('stock');

        \Log::info("✅ [MIGRACIÓN] Stock migrado exitosamente:", [
            'total_productos' => $totalProducts,
            'stock_total_migrado' => $totalStock,
            'sucursal_destino' => $mainBranch->name,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * Revierte la migración: restaura el stock desde product_stocks a products
     */
    public function down(): void
    {
        // Obtener la sucursal principal
        $mainBranch = DB::table('branches')->where('is_main', true)->first();

        if ($mainBranch) {
            // Restaurar stock desde product_stocks a products
            $productStocks = DB::table('product_stocks')
                ->where('branch_id', $mainBranch->id)
                ->get();

            foreach ($productStocks as $ps) {
                DB::table('products')
                    ->where('id', $ps->product_id)
                    ->update(['stock' => $ps->stock]);
            }

            \Log::info("⏮️ [ROLLBACK] Stock restaurado a tabla products desde sucursal principal");
        }

        // Eliminar todos los registros de product_stocks
        DB::table('product_stocks')->truncate();
    }
};
