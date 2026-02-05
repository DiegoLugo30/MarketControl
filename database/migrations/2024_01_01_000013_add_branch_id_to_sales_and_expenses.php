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
     * Agrega branch_id a las tablas sales y expenses
     * para asociar transacciones a sucursales específicas
     */
    public function up(): void
    {
        // Obtener sucursal principal
        $mainBranch = DB::table('branches')->where('is_main', true)->first();

        if (!$mainBranch) {
            throw new \Exception('No se encontró la sucursal principal. Ejecuta las migraciones de branches primero.');
        }

        // Agregar branch_id a sales
        Schema::table('sales', function (Blueprint $table) use ($mainBranch) {
            $table->foreignId('branch_id')
                ->after('id')
                ->default($mainBranch->id)
                ->constrained('branches')
                ->onDelete('restrict')
                ->comment('Sucursal donde se realizó la venta');

            $table->index('branch_id');
        });

        // Migrar ventas existentes a sucursal principal
        DB::table('sales')->update(['branch_id' => $mainBranch->id]);

        // Agregar branch_id a expenses
        Schema::table('expenses', function (Blueprint $table) use ($mainBranch) {
            $table->foreignId('branch_id')
                ->after('id')
                ->default($mainBranch->id)
                ->constrained('branches')
                ->onDelete('restrict')
                ->comment('Sucursal donde se registró el gasto');

            $table->index('branch_id');
        });

        // Migrar gastos existentes a sucursal principal
        DB::table('expenses')->update(['branch_id' => $mainBranch->id]);

        \Log::info("✅ [MIGRACIÓN] branch_id agregado a sales y expenses");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });

        \Log::info("⏮️ [ROLLBACK] branch_id eliminado de sales y expenses");
    }
};
