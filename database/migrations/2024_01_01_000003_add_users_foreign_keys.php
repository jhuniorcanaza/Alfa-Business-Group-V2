<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agregar foreign keys a users después de crear offices y teams.
     * Resuelve la dependencia circular: users ↔ offices, users ↔ teams.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('office_id')
                  ->references('id')
                  ->on('offices')
                  ->nullOnDelete();

            $table->foreign('team_id')
                  ->references('id')
                  ->on('teams')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['office_id']);
            $table->dropForeign(['team_id']);
        });
    }
};
