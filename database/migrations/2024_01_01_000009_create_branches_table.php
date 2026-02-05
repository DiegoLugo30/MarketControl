<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Crea la tabla de sucursales (branches)
     */
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('Código único de la sucursal (ej: SUC001)');
            $table->string('name')->comment('Nombre de la sucursal');
            $table->text('address')->nullable()->comment('Dirección física');
            $table->string('phone', 20)->nullable()->comment('Teléfono de contacto');
            $table->boolean('is_main')->default(false)->comment('¿Es la sucursal principal?');
            $table->boolean('is_active')->default(true)->comment('¿Está activa?');
            $table->timestamps();

            // Índices para búsquedas rápidas
            $table->index('code');
            $table->index('is_main');
            $table->index('is_active');
        });

        // Crear sucursal principal por defecto
        DB::table('branches')->insert([
            'code' => 'MAIN',
            'name' => 'Sucursal Principal',
            'address' => 'Dirección no especificada',
            'phone' => null,
            'is_main' => true,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
