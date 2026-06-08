<?php

namespace Database\Seeders;

use App\Models\DailyReport;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MockReportsSeeder extends Seeder
{
    /**
     * Run the database seeds to populate rich realistic report data.
     */
    public function run(): void
    {
        // Obtener todos los asesores activos
        $asesores = User::where('role', 'asesor')->get();

        if ($asesores->isEmpty()) {
            $this->command->warn('⚠️ No se encontraron asesores en la base de datos para generar reportes.');
            return;
        }

        $this->command->info('🚀 Iniciando simulación de datos de reportes para ' . $asesores->count() . ' asesores...');

        // Vamos a limpiar reportes anteriores para evitar duplicados en fechas coincidentes
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DailyReport::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $today = Carbon::today();
        $startDate = $today->copy()->subDays(30);

        // Elegir 2 asesores estrella al azar para asegurar que cumplan el perfilado de Team Leaders
        $asesoresEstrella = $asesores->random(min(2, $asesores->count()));
        $estrellaIds = $asesoresEstrella->pluck('id')->toArray();

        $insertedCount = 0;

        foreach ($asesores as $asesor) {
            $esEstrella = in_array($asesor->id, $estrellaIds);

            // Generar reportes diarios para los últimos 30 días
            for ($dayOffset = 0; $dayOffset <= 30; $dayOffset++) {
                $date = $startDate->copy()->addDays($dayOffset);

                // No reportar fines de semana para mayor realismo (la mayoría descansa sábados y domingos)
                if ($date->isWeekend()) {
                    // 15% de probabilidad de tener alguna actividad de emergencia en fin de semana
                    if (rand(1, 100) > 15) {
                        continue;
                    }
                }

                // Definir rangos realistas basados en si es asesor estrella o regular
                if ($esEstrella) {
                    $visits = rand(2, 6);
                    $signCaptures = rand(0, 3);
                    $exclusiveCaptures = rand(0, 2);
                    // Los estrella cierran más seguido (15% de probabilidad diaria)
                    $closings = (rand(1, 100) <= 15) ? 1 : 0;
                    $callsMade = rand(8, 20);
                    $propertiesInSystem = rand(1, 4);
                } else {
                    $visits = rand(0, 4);
                    $signCaptures = rand(0, 2);
                    $exclusiveCaptures = rand(0, 1);
                    // Los regulares cierran ocasionalmente (3% de probabilidad diaria)
                    $closings = (rand(1, 100) <= 3) ? 1 : 0;
                    $callsMade = rand(3, 12);
                    $propertiesInSystem = rand(0, 2);
                }

                // Generar números de teléfono ficticios para las llamadas hechas
                $callsNumbers = [];
                if ($callsMade > 0) {
                    for ($c = 0; $c < $callsMade; $c++) {
                        $callsNumbers[] = '7' . rand(1000000, 9999999);
                    }
                }

                DailyReport::create([
                    'user_id' => $asesor->id,
                    'report_date' => $date,
                    'visits' => $visits,
                    'sign_captures' => $signCaptures,
                    'exclusive_captures' => $exclusiveCaptures,
                    'closings' => $closings,
                    'calls_made' => $callsMade,
                    'call_phone_number' => implode(', ', $callsNumbers),
                    'properties_in_system' => $propertiesInSystem,
                    'source' => rand(1, 100) > 40 ? 'whatsapp' : 'web',
                    'notes' => rand(1, 100) > 80 ? 'Seguimiento constante al cliente.' : null,
                ]);

                $insertedCount++;
            }
        }

        $this->command->info("✅ ¡Éxito! Se han inyectado {$insertedCount} reportes diarios realistas para todos tus asesores.");
    }
}
