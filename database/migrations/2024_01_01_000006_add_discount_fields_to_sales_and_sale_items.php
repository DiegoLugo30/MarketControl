<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Agregar campos de descuento a la tabla sales
        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('discount_amount', 10, 2)->default(0)->after('total');
            $table->string('discount_description')->nullable()->after('discount_amount');
        });

        // Agregar campos de descuento a la tabla sale_items
        Schema::table('sale_items', function (Blueprint $table) {
            $table->decimal('item_discount', 10, 2)->default(0)->after('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['discount_amount', 'discount_description']);
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn('item_discount');
        });
    }
};
