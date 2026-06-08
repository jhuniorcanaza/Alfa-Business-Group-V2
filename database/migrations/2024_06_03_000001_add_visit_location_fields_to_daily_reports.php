<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds visit geolocation and client fields to the daily_reports table.
     */
    public function up(): void
    {
        Schema::table('daily_reports', function (Blueprint $table) {
            // Datos de las visitas (JSON array of visit objects)
            $table->json('visits_data')->nullable()->after('visits');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->dropColumn('visits_data');
        });
    }
};
