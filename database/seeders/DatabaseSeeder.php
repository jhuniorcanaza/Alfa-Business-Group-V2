<?php

namespace Database\Seeders;

use App\Models\KpiConfig;
use App\Models\Office;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ─── 1. Crear Oficina Base Obligatoria ──────────────────
        $oficinaPrincipal = Office::create([
            'name' => 'Oficina Central Alfa Bolivia',
            'address' => 'Santa Cruz, Bolivia',
            'city' => 'Santa Cruz',
            'phone' => '+591 70000000',
        ]);

        // ─── 2. Crear Único Usuario Director ─────────────────────
        $director = User::create([
            'name' => 'Jhunior Director',
            'email' => 'jhunior341@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'director',
            'phone' => '+591 70000000',
            'office_id' => $oficinaPrincipal->id,
            'is_active' => true,
        ]);

        // ─── 3. Configurar KPIs por Defecto (Requeridos por el Sistema) ───
        KpiConfig::create([
            'indicator' => 'captaciones',
            'weekly_goal' => 10,
            'yellow_threshold_pct' => 51,
            'red_threshold_pct' => 50,
            'is_active' => true,
            'created_by' => $director->id,
        ]);

        KpiConfig::create([
            'indicator' => 'visits',
            'weekly_goal' => 20,
            'yellow_threshold_pct' => 51,
            'red_threshold_pct' => 50,
            'is_active' => false,
            'created_by' => $director->id,
        ]);

        KpiConfig::create([
            'indicator' => 'closings',
            'weekly_goal' => 5,
            'yellow_threshold_pct' => 51,
            'red_threshold_pct' => 50,
            'is_active' => false,
            'created_by' => $director->id,
        ]);

        KpiConfig::create([
            'indicator' => 'calls',
            'weekly_goal' => 50,
            'yellow_threshold_pct' => 51,
            'red_threshold_pct' => 50,
            'is_active' => false,
            'created_by' => $director->id,
        ]);

        KpiConfig::create([
            'indicator' => 'properties',
            'weekly_goal' => 15,
            'yellow_threshold_pct' => 51,
            'red_threshold_pct' => 50,
            'is_active' => false,
            'created_by' => $director->id,
        ]);

        $this->command->info('');
        $this->command->info('✅ Base de datos sembrada en limpio.');
        $this->command->info('');
        $this->command->info('👤 Usuario Director Creado:');
        $this->command->info('   Email:      jhunior341@gmail.com');
        $this->command->info('   Contraseña: 12345678');
        $this->command->info('');
    }
}
