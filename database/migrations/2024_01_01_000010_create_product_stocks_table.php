<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Crea la tabla de stock por producto y sucursal
     */
    public function up(): void
    {
        Schema::create('product_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                ->constrained('products')
                ->onDelete('cascade')
                ->comment('Producto al que pertenece este stock');

            $table->foreignId('branch_id')
                ->constrained('branches')
                ->onDelete('cascade')
                ->comment('Sucursal donde está este stock');

            $table->integer('stock')
                ->default(0)
                ->comment('Cantidad disponible en esta sucursal');

            $table->timestamps();

            // Restricción única: un producto solo puede tener un registro de stock por sucursal
            $table->unique(['product_id', 'branch_id'], 'unique_product_branch');

            // Índices para búsquedas rápidas
            $table->index('product_id');
            $table->index('branch_id');
            $table->index('stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_stocks');
    }
};
