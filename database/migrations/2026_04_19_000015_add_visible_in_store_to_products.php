<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add store visibility flag to products.
     * Defaults to false so no existing product appears in the store until
     * explicitly enabled by the admin.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('visible_in_store')->default(false)->after('is_weighted');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('visible_in_store');
        });
    }
};
