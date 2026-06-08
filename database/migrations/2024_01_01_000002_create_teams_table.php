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
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');                // Nombre del equipo
            $table->foreignId('office_id')
                  ->constrained('offices')
                  ->cascadeOnDelete();             // Si se elimina la oficina, se eliminan sus equipos
            $table->unsignedBigInteger('leader_id') // Team Líder asignado
                  ->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // El foreign key a users se agrega después porque users depende de teams
            $table->index('leader_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
