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
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->date('report_date');           // Fecha del reporte (un reporte por día)

            // Los 6 indicadores del asesor
            $table->unsignedInteger('visits')->default(0);                 // Visitas Realizadas
            $table->unsignedInteger('sign_captures')->default(0);          // Captaciones con Letrero
            $table->unsignedInteger('exclusive_captures')->default(0);     // Captaciones con Exclusiva
            $table->unsignedInteger('closings')->default(0);               // Cierres (Venta, anticrético, alquiler)
            $table->unsignedInteger('calls_made')->default(0);             // Llamadas realizadas
            $table->text('call_phone_number')->nullable();                 // Números de teléfono a los que llamó (separados por comas o JSON)
            $table->unsignedInteger('properties_in_system')->default(0);   // Propiedades subidas a AlphaX

            // Fuente del reporte
            $table->enum('source', ['web', 'whatsapp'])->default('web');

            // Notas adicionales
            $table->text('notes')->nullable();

            $table->timestamps();

            // Un asesor solo puede enviar un reporte por día
            $table->unique(['user_id', 'report_date']);

            // Índices para consultas frecuentes
            $table->index('report_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_reports');
    }
};
