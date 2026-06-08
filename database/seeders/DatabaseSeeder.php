<?php

namespace Database\Seeders;

use App\Models\DailyReport;
use App\Models\KpiConfig;
use App\Models\Office;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ─── 1. Crear Oficinas ──────────────────────────────────
        $equipetrol = Office::create([
            'name' => 'Oficina Equipetrol',
            'address' => 'Av. Equipetrol, 3er anillo',
            'city' => 'Santa Cruz',
            'phone' => '+591 3 333 0001',
        ]);

        $santaCruz = Office::create([
            'name' => 'Oficina Santa Cruz Centro',
            'address' => 'Calle Sucre #123',
            'city' => 'Santa Cruz',
            'phone' => '+591 3 333 0002',
        ]);

        // ─── 2. Crear Director ──────────────────────────────────
        $director = User::create([
            'name' => 'Carlos Mendoza',
            'email' => 'director@alfabusiness.com',
            'password' => 'password',
            'role' => 'director',
            'phone' => '+591 7 000 0001',
            'office_id' => $equipetrol->id,
            'is_active' => true,
        ]);

        // ─── 3. Crear Equipos y Team Líderes ────────────────────
        $teamLeader1 = User::create([
            'name' => 'María López',
            'email' => 'maria@alfabusiness.com',
            'password' => 'password',
            'role' => 'team_leader',
            'phone' => '+591 7 000 0002',
            'office_id' => $equipetrol->id,
            'is_active' => true,
        ]);

        $teamLeader2 = User::create([
            'name' => 'Roberto Flores',
            'email' => 'roberto@alfabusiness.com',
            'password' => 'password',
            'role' => 'team_leader',
            'phone' => '+591 7 000 0003',
            'office_id' => $santaCruz->id,
            'is_active' => true,
        ]);

        $team1 = Team::create([
            'name' => 'Equipo Alfa',
            'office_id' => $equipetrol->id,
            'leader_id' => $teamLeader1->id,
        ]);

        $team2 = Team::create([
            'name' => 'Equipo Beta',
            'office_id' => $santaCruz->id,
            'leader_id' => $teamLeader2->id,
        ]);

        // Asignar team a los líderes
        $teamLeader1->update(['team_id' => $team1->id]);
        $teamLeader2->update(['team_id' => $team2->id]);

        // ─── 4. Crear Asesores ──────────────────────────────────
        $asesores = [
            ['name' => 'Juan Pérez',     'email' => 'juan@alfabusiness.com',     'team' => $team1, 'office' => $equipetrol],
            ['name' => 'Ana García',     'email' => 'ana@alfabusiness.com',      'team' => $team1, 'office' => $equipetrol],
            ['name' => 'Luis Ramírez',   'email' => 'luis@alfabusiness.com',     'team' => $team1, 'office' => $equipetrol],
            ['name' => 'Sofía Martínez', 'email' => 'sofia@alfabusiness.com',    'team' => $team2, 'office' => $santaCruz],
            ['name' => 'Pedro Sánchez',  'email' => 'pedro@alfabusiness.com',    'team' => $team2, 'office' => $santaCruz],
        ];

        $createdAsesores = [];
        foreach ($asesores as $asesor) {
            $createdAsesores[] = User::create([
                'name' => $asesor['name'],
                'email' => $asesor['email'],
                'password' => 'password',
                'role' => 'asesor',
                'phone' => '+591 7 ' . rand(100, 999) . ' ' . rand(1000, 9999),
                'office_id' => $asesor['office']->id,
                'team_id' => $asesor['team']->id,
                'is_active' => true,
            ]);
        }

        // ─── 5. Crear Reportes Diarios de Ejemplo ───────────────
        $today = Carbon::today();
        $monday = $today->copy()->startOfWeek(Carbon::MONDAY);

        foreach ($createdAsesores as $asesor) {
            // Generar reportes para los últimos 5 días laborales
            for ($i = 0; $i < 5; $i++) {
                $date = $monday->copy()->addDays($i);
                if ($date->isAfter($today)) break;

                DailyReport::create([
                    'user_id' => $asesor->id,
                    'report_date' => $date,
                    'visits' => rand(1, 8),
                    'sign_captures' => rand(0, 3),
                    'exclusive_captures' => rand(0, 2),
                    'closings' => rand(0, 1),
                    'calls_made' => $callsMade = rand(3, 6),
                    'call_phone_number' => implode(', ', array_map(fn() => '7' . rand(1000000, 9999999), range(1, $callsMade))),
                    'properties_in_system' => rand(0, 4),
                    'source' => 'web',
                ]);
            }
        }

        // ─── 6. Configurar KPIs ─────────────────────────────────
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
        $this->command->info('✅ Base de datos sembrada exitosamente.');
        $this->command->info('');
        $this->command->info('👤 Usuarios de prueba:');
        $this->command->info('   Director:    director@alfabusiness.com / password');
        $this->command->info('   Team Líder:  maria@alfabusiness.com / password');
        $this->command->info('   Team Líder:  roberto@alfabusiness.com / password');
        $this->command->info('   Asesor:      juan@alfabusiness.com / password');
        $this->command->info('   Asesor:      ana@alfabusiness.com / password');
        $this->command->info('   (y 3 asesores más)');
    }
}
