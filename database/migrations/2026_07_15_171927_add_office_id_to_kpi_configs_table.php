<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kpi_configs', function (Blueprint $table) {
            // Drop unique constraint on indicator
            $table->dropUnique('kpi_configs_indicator_unique');

            // Add office_id column
            $table->foreignId('office_id')
                  ->after('id')
                  ->nullable()
                  ->constrained('offices')
                  ->cascadeOnDelete();

            // Add composite unique constraint
            $table->unique(['indicator', 'office_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kpi_configs', function (Blueprint $table) {
            // Drop composite unique constraint
            $table->dropUnique('kpi_configs_indicator_office_id_unique');

            // Drop foreign key and column
            $table->dropForeign(['office_id']);
            $table->dropColumn('office_id');

            // Restore unique constraint on indicator
            $table->unique('indicator');
        });
    }
};
