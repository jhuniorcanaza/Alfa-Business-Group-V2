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
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // Dirección del mensaje
            $table->enum('direction', ['incoming', 'outgoing']);

            // Tipo de mensaje
            $table->enum('message_type', ['text', 'audio']);

            // Contenido original (texto del mensaje o URL del audio)
            $table->text('original_content')->nullable();

            // Transcripción del audio (si aplica)
            $table->text('transcription')->nullable();

            // Datos extraídos en formato JSON (los 6 indicadores parseados)
            $table->json('extracted_data')->nullable();

            // Estado del procesamiento
            $table->enum('status', [
                'received',     // Mensaje recibido
                'processing',   // En proceso de extracción
                'confirmed',    // Asesor confirmó los datos
                'rejected',     // Asesor rechazó los datos
            ])->default('received');

            // Reporte diario generado a partir de este mensaje
            $table->foreignId('daily_report_id')
                  ->nullable()
                  ->constrained('daily_reports')
                  ->nullOnDelete();

            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};
