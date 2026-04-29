<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('order_code')->nullable()->after('payment_method');
            $table->string('customer_name')->nullable()->after('order_code');
            $table->string('customer_email')->nullable()->after('customer_name');
            $table->text('order_comment')->nullable()->after('customer_email');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['order_code', 'customer_name', 'customer_email', 'order_comment']);
        });
    }
};
