<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Agregar columna internal_code como nullable primero
            $table->string('internal_code')->nullable()->after('id');

            // Indicador de producto pesable
            $table->boolean('is_weighted')->default(false)->after('stock');

            // Precio por kilogramo para productos pesables
            $table->decimal('price_per_kg', 10, 2)->nullable()->after('price');

            // Hacer barcode nullable para productos solo con código interno
            $table->string('barcode')->nullable()->change();
        });

        // Generar códigos internos para productos existentes
        DB::statement("
            UPDATE products
            SET internal_code = CASE
                WHEN barcode IS NOT NULL THEN barcode
                ELSE 'PROD' || LPAD(id::text, 4, '0')
            END
            WHERE internal_code IS NULL
        ");

        // Ahora hacer la columna NOT NULL y agregar unique constraint
        Schema::table('products', function (Blueprint $table) {
            $table->string('internal_code')->nullable(false)->change();
            $table->unique('internal_code');

            // Índices para búsquedas rápidas
            $table->index('internal_code');
            $table->index('is_weighted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['internal_code']);
            $table->dropIndex(['is_weighted']);
            $table->dropColumn(['internal_code', 'is_weighted', 'price_per_kg']);
            $table->string('barcode')->nullable(false)->change();
        });
    }
};
