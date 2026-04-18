<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add fields required for the "Master Barcode" / custom (non-catalogued) product feature.
     *
     * Changes:
     *  - product_id → nullable  (custom items have no associated product record)
     *  - name       → snapshot of the product name at sale time; required for custom items
     *  - is_custom  → distinguishes manual entries from catalogue products
     *  - unit_type  → 'unit' or 'weight' (replaces implicit inference from is_weighted flag)
     *
     * Backwards-compatible: existing rows keep product_id set and is_custom = false.
     */
    public function up(): void
    {
        // Step 1 – add the new columns
        Schema::table('sale_items', function (Blueprint $table) {
            $table->string('name')->nullable()->after('product_id');
            $table->boolean('is_custom')->default(false)->after('price');
            $table->string('unit_type', 10)->nullable()->after('is_custom');
        });

        // Step 2 – make product_id nullable (drop FK → alter → re-add FK)
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->unsignedBigInteger('product_id')->nullable()->change();
            $table->foreign('product_id')
                  ->references('id')->on('products')
                  ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        // Reverse step 2
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->unsignedBigInteger('product_id')->nullable(false)->change();
            $table->foreign('product_id')
                  ->references('id')->on('products')
                  ->onDelete('restrict');
        });

        // Reverse step 1
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn(['name', 'is_custom', 'unit_type']);
        });
    }
};
