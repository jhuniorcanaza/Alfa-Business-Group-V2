<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Se ejecuta después de crear users y teams para resolver
     * la dependencia circular: teams.leader_id → users.id
     */
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->foreign('leader_id')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropForeign(['leader_id']);
        });
    }
};
