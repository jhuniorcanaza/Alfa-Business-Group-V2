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
        Schema::create('kpi_configs', function (Blueprint $table) {
            $table->id();

            // Nombre del indicador: 'captaciones', 'visits', 'closings', 'calls', 'properties'
            $table->string('indicator')->unique();

            // Meta semanal (ej: 10 captaciones por semana)
            $table->unsignedInteger('weekly_goal')->default(0);

            // Umbrales del semáforo (porcentaje)
            $table->unsignedInteger('yellow_threshold_pct')->default(51);  // >= 51% = amarillo
            $table->unsignedInteger('red_threshold_pct')->default(50);     // <= 50% = rojo
            // >= 100% = verde (implícito)

            // ¿Está activo el semáforo para este indicador?
            $table->boolean('is_active')->default(false);

            // Quién configuró
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_configs');
    }
};
