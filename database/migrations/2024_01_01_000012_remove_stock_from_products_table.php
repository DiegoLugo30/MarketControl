<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * MIGRACIÓN OPCIONAL: Elimina el campo 'stock' de la tabla products
     * ya que ahora el stock se gestiona en product_stocks
     *
     * IMPORTANTE: Solo ejecutar después de verificar que la migración
     * de stock funcionó correctamente y el sistema está estable.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Eliminar campo stock
            $table->dropColumn('stock');
        });

        \Log::info("✅ [MIGRACIÓN] Campo 'stock' eliminado de tabla products");
    }

    /**
     * Reverse the migrations.
     *
     * Restaura el campo 'stock' en la tabla products
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Restaurar campo stock con valor por defecto 0
            $table->integer('stock')->default(0)->after('price_per_kg');
        });

        // Restaurar valores de stock desde product_stocks (sucursal principal)
        $mainBranch = DB::table('branches')->where('is_main', true)->first();

        if ($mainBranch) {
            $productStocks = DB::table('product_stocks')
                ->where('branch_id', $mainBranch->id)
                ->get();

            foreach ($productStocks as $ps) {
                DB::table('products')
                    ->where('id', $ps->product_id)
                    ->update(['stock' => $ps->stock]);
            }
        }

        \Log::info("⏮️ [ROLLBACK] Campo 'stock' restaurado en tabla products");
    }
};
