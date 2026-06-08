<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use App\Models\KpiConfig;
use App\Models\User;
use App\Models\Team;
use App\Models\Office;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    /**
     * Procesar mensaje del chatbot web.
     */
    public function message(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $user = Auth::user();
        $messageText = $request->input('message');
        $today = Carbon::today();

        // 1. Si el usuario es asesor y quiere registrar su reporte mediante texto/voz
        $reportRegistered = false;
        $reportDetails = "";
        
        if ($user->role === 'asesor') {
            // Verificar si el mensaje contiene patrones de reporte diario
            $keywords = ['reporte', 'registra', 'hice', 'visitas', 'llamadas', 'captaciones', 'letrero', 'exclusiva', 'cierre'];
            $isReportAttempt = false;
            foreach ($keywords as $kw) {
                if (stripos($messageText, $kw) !== false) {
                    $isReportAttempt = true;
                    break;
                }
            }

            // También si el mensaje son solo números separados por comas o espacios (ej: "4, 2, 1, 0, 10, 2")
            if (preg_match('/^\s*\d+[\s,]+\d+[\s,]+\d+[\s,]+\d+[\s,]+\d+[\s,]+\d+\s*$/', $messageText)) {
                $isReportAttempt = true;
            }

            if ($isReportAttempt) {
                // Verificar si ya envió reporte hoy
                $existingReport = DailyReport::where('user_id', $user->id)
                    ->where('report_date', $today)
                    ->first();

                if ($existingReport) {
                    $reportDetails = "⚠️ Ya tienes un reporte registrado para hoy:\n"
                        . "🏠 Visitas: {$existingReport->visits}\n"
                        . "📋 Letrero: {$existingReport->sign_captures}\n"
                        . "📝 Exclusiva: {$existingReport->exclusive_captures}\n"
                        . "🤝 Cierres: {$existingReport->closings}\n"
                        . "📞 Llamadas: {$existingReport->calls_made}\n"
                        . "💻 AlphaX: {$existingReport->properties_in_system}";
                } else {
                    // Intentar extraer los 6 KPIs con Gemini de Google
                    $extractedData = $this->extractKPIsWithGemini($messageText, $user->name);
                    
                    if ($extractedData) {
                        // Guardar el reporte
                        $report = DailyReport::create([
                            'user_id' => $user->id,
                            'report_date' => $today,
                            'visits' => $extractedData['visits'],
                            'sign_captures' => $extractedData['sign_captures'],
                            'exclusive_captures' => $extractedData['exclusive_captures'],
                            'closings' => $extractedData['closings'],
                            'calls_made' => $extractedData['calls_made'],
                            'properties_in_system' => $extractedData['properties_in_system'],
                            'source' => 'web_chatbot',
                        ]);

                        $reportRegistered = true;
                        $reportDetails = "✅ ¡Tu reporte diario ha sido registrado exitosamente en el sistema!\n"
                            . "🏠 Visitas: {$report->visits}\n"
                            . "📋 Letrero: {$report->sign_captures}\n"
                            . "📝 Exclusiva: {$report->exclusive_captures}\n"
                            . "🤝 Cierres: {$report->closings}\n"
                            . "📞 Llamadas: {$report->calls_made}\n"
                            . "💻 AlphaX: {$report->properties_in_system}";
                    }
                }
            }
        }

        // 2. Construir el System Prompt contextualizado según el Rol
        $systemPrompt = $this->buildSystemPrompt($user, $today, $reportRegistered, $reportDetails);

        // 3. Consultar a Google Gemini
        $apiKey = config('services.gemini.api_key');
        
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(20)->post("https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => "INSTRUCCIÓN DEL SISTEMA:\n{$systemPrompt}\n\nMENSAJE DEL USUARIO:\n{$messageText}"]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.65,
                    'maxOutputTokens' => 1000
                ]
            ]);

            if ($response->successful()) {
                $botResponse = $response->json('candidates.0.content.parts.0.text', 'Lo siento, no pude procesar tu mensaje en este momento.');
                
                // Si registramos el reporte y la IA no lo mencionó claramente, agregamos los detalles al final
                if ($reportRegistered && stripos($botResponse, 'registrado') === false) {
                    $botResponse = $reportDetails . "\n\n" . $botResponse;
                }

                return response()->json([
                    'success' => true,
                    'message' => $botResponse,
                ]);
            }

            Log::error('Chatbot Gemini API Error: ' . $response->body());
            
            // Fallback Resiliente Automático Local en caso de error de API
            $localResponse = $this->localResilientResponse($user, $messageText, $response->body());
            return response()->json([
                'success' => true,
                'message' => $localResponse,
            ]);

        } catch (\Exception $e) {
            Log::error('Chatbot Exception: ' . $e->getMessage());
            
            // Fallback Resiliente en caso de excepción de conexión
            $localResponse = $this->localResilientResponse($user, $messageText, $e->getMessage());
            return response()->json([
                'success' => true,
                'message' => $localResponse,
            ]);
        }
    }

    /**
     * Fallback de Inteligencia Artificial Resiliente y Local.
     * Si falla la API por cualquier razón, este motor local de Alfa AI
     * busca y calcula la respuesta exacta del sistema en tiempo real y la formatea de forma elegante.
     */
    private function localResilientResponse(User $user, string $query, string $errorBody): string
    {
        $today = Carbon::today();
        $startOfWeek = $today->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = $today->copy()->endOfWeek(Carbon::SUNDAY);

        $warningHeader = "⚠️ **Nota del Asistente:** He tenido un pequeño retraso al conectar con la red de IA.\n"
            . "🤖 **Alfa AI (Motor Local):** He consultado directamente nuestra base de datos en tiempo real de Alfa Bolivia y aquí tienes tus estadísticas exactas:\n\n";

        $queryLower = mb_strtolower($query);

        // A. CASO: BUSCAR AL MEJOR ASESOR
        if (stripos($queryLower, 'mejor') !== false || stripos($queryLower, 'estrella') !== false || stripos($queryLower, 'ranking') !== false || stripos($queryLower, 'top') !== false || stripos($queryLower, 'primer') !== false) {
            // Obtener todos los asesores y sus captaciones en esta semana
            $asesores = User::where('role', 'asesor')->where('is_active', true)->get();
            $ranking = $asesores->map(function($m) use ($startOfWeek, $endOfWeek) {
                $rep = DailyReport::where('user_id', $m->id)->whereBetween('report_date', [$startOfWeek, $endOfWeek])->get();
                return [
                    'name' => $m->name,
                    'captures' => $rep->sum('sign_captures') + $rep->sum('exclusive_captures'),
                    'visits' => $rep->sum('visits'),
                    'closings' => $rep->sum('closings'),
                ];
            })->sortByDesc('captures')->values();

            $top = $ranking->first();

            if ($top && $top['captures'] > 0) {
                $res = "🏆 **El Asesor Estrella de esta Semana** en Alfa Bolivia es **{$top['name']}** con un total acumulado de **{$top['captures']} captaciones** (Letreros + Exclusivas), además de **{$top['visits']} visitas** y **{$top['closings']} cierres** logrados de Lunes a Domingo. ¡Un rendimiento excepcional! 👏👏🔥\n\n";
                if ($ranking->count() > 1) {
                    $res .= "📊 **Top 3 de la Semana Actual:**\n";
                    for ($i = 0; $i < min(3, $ranking->count()); $i++) {
                        $curr = $ranking[$i];
                        $medals = ['🥇', '🥈', '🥉'];
                        $res .= "{$medals[$i]} **{$curr['name']}** — **{$curr['captures']} captaciones** | Cierres: **{$curr['closings']}**\n";
                    }
                }
                return $warningHeader . $res;
            } else {
                return $warningHeader . "✨ Actualmente no se han registrado captaciones esta semana por parte de ningún asesor. ¡Es una gran oportunidad para ser el primero en inaugurar el marcador de captaciones de esta semana! 💪🏠";
            }
        }

        // B. CASO: CUÁNTAS CAPTACIONES ME FALTAN (ESPECIAL PARA ASESORES)
        if (stripos($queryLower, 'falta') !== false || stripos($queryLower, 'captaciones') !== false || stripos($queryLower, 'meta') !== false || stripos($queryLower, 'semáforo') !== false || stripos($queryLower, 'semaforo') !== false) {
            if ($user->role === 'asesor') {
                $weeklyReports = DailyReport::where('user_id', $user->id)
                    ->whereBetween('report_date', [$startOfWeek, $endOfWeek])
                    ->get();

                $sign = $weeklyReports->sum('sign_captures');
                $excl = $weeklyReports->sum('exclusive_captures');
                $total = $sign + $excl;

                $kpiConfig = KpiConfig::where('indicator', 'captaciones')->where('is_active', true)->first();
                $goal = $kpiConfig ? $kpiConfig->weekly_goal : 10;
                $missing = max(0, $goal - $total);
                $trafficLight = $kpiConfig ? $kpiConfig->getTrafficLightColor($total) : 'green';
                
                $emojiColor = match ($trafficLight) {
                    'green' => '🟢 VERDE (Rendimiento Excelente)',
                    'yellow' => '🟡 AMARILLO (Rendimiento en Progreso)',
                    'red' => '🔴 ROJO (Rendimiento en Alerta - ¡A captar!)',
                    default => '⚪ Sin definir',
                };

                $res = "🎯 **Tus Estadísticas de Captación esta Semana:**\n"
                    . "🏠 Captaciones Acumuladas: **{$total}** (Letreros: {$sign} | Exclusivas: {$excl})\n"
                    . "🏁 Meta Semanal de la Agencia: **{$goal}** captaciones.\n"
                    . "🔥 **Te faltan exactamente:** **{$missing}** captaciones para cumplir tu meta semanal.\n"
                    . "🚦 Tu Semáforo actual de Desempeño: **{$emojiColor}**\n\n"
                    . "¡Sigue adelante! Cada captación te acerca a cerrar tu próximo contrato de éxito. 🚀🏢";

                return $warningHeader . $res;
            }
        }

        // C. CASO: QUIÉN FALTA REPORTAR HOY (ESPECIAL PARA TEAM LEADERS / DIRECTORES)
        if (stripos($queryLower, 'falta reportar') !== false || stripos($queryLower, 'quien no') !== false || stripos($queryLower, 'quienes no') !== false || stripos($queryLower, 'no han reportado') !== false || stripos($queryLower, 'reportaron') !== false) {
            if ($user->role === 'team_leader') {
                $teamMembers = User::where('team_id', $user->team_id)->where('role', 'asesor')->where('is_active', true)->get();
                $reportedTodayUserIds = DailyReport::whereIn('user_id', $teamMembers->pluck('id'))->where('report_date', $today)->pluck('user_id')->toArray();
                $missingReportMembers = $teamMembers->filter(fn($m) => !in_array($m->id, $reportedTodayUserIds))->pluck('name')->toArray();

                if (count($missingReportMembers) > 0) {
                    return $warningHeader . "📋 **Asesores de tu equipo que FALTAN enviar su reporte diario hoy:**\n"
                        . implode("\n", array_map(fn($n) => "❌ **{$n}**", $missingReportMembers)) . "\n\n"
                        . "Te sugiero enviarles un mensaje recordatorio rápido para que envíen sus datos por el bot de WhatsApp. 📲";
                } else {
                    return $warningHeader . "🎉 **¡Excelente noticia!** Todos los asesores activos de tu equipo ya han enviado y registrado su reporte diario de hoy. ¡Felicidades por la gran disciplina y seguimiento! 🤝🏆";
                }
            }
        }

        // D. CASO GENÉRICO DE RESPALDO (MUESTRA UN RESUMEN DE KPIs SELECCIONADOS DEL ROL)
        $res = "He recibido tu consulta: *\"{$query}\"*\n\n";

        if ($user->role === 'asesor') {
            $weeklyReports = DailyReport::where('user_id', $user->id)->whereBetween('report_date', [$startOfWeek, $endOfWeek])->get();
            $res .= "📊 **Resumen de tu Rendimiento de esta Semana:**\n"
                . "🏠 Visitas a Propiedades: **" . $weeklyReports->sum('visits') . "**\n"
                . "📞 Llamadas de Captación: **" . $weeklyReports->sum('calls_made') . "**\n"
                . "📋 Captaciones con Letrero: **" . $weeklyReports->sum('sign_captures') . "**\n"
                . "📝 Captaciones con Exclusiva: **" . $weeklyReports->sum('exclusive_captures') . "**\n"
                . "🤝 Cierres Concretados: **" . $weeklyReports->sum('closings') . "**\n"
                . "💻 Propiedades en AlphaX: **" . $weeklyReports->sum('properties_in_system') . "**\n\n"
                . "*Consejo: Recuerda que puedes consultarme métricas de metas o registrar reportes diarios dictándomelos.*";
        } elseif ($user->role === 'team_leader') {
            $team = Team::find($user->team_id);
            $teamMembersIds = User::where('team_id', $user->team_id)->where('role', 'asesor')->pluck('id');
            $teamReports = DailyReport::whereIn('user_id', $teamMembersIds)->whereBetween('report_date', [$startOfWeek, $endOfWeek])->get();
            $res .= "📊 **Rendimiento Semanal Acumulado de tu Equipo (" . ($team ? $team->name : 'Mi Equipo') . "):**\n"
                . "🏠 Visitas Totales: **" . $teamReports->sum('visits') . "**\n"
                . "📞 Llamadas Totales: **" . $teamReports->sum('calls_made') . "**\n"
                . "🏠 Captaciones Totales: **" . ($teamReports->sum('sign_captures') + $teamReports->sum('exclusive_captures')) . "**\n"
                . "🤝 Cierres de Contrato: **" . $teamReports->sum('closings') . "**\n\n"
                . "*Consejo: Puedes preguntarme quién es el mejor de la semana o quién falta enviar su reporte diario hoy.*";
        } else {
            // Director
            $allWeeklyReports = DailyReport::whereBetween('report_date', [$startOfWeek, $endOfWeek])->get();
            $activeOfficesCount = Office::where('is_active', true)->count();
            $activeAsesoresCount = User::where('role', 'asesor')->where('is_active', true)->count();
            $res .= "📊 **Métricas Consolidadas de Alfa Bolivia esta Semana:**\n"
                . "🏢 Oficinas Operativas: **{$activeOfficesCount}**\n"
                . "👥 Asesores Activos: **{$activeAsesoresCount}**\n"
                . "🏠 Visitas Corporativas: **" . $allWeeklyReports->sum('visits') . "**\n"
                . "📞 Llamadas de Red: **" . $allWeeklyReports->sum('calls_made') . "**\n"
                . "🏠 Captaciones de Red: **" . ($allWeeklyReports->sum('sign_captures') + $allWeeklyReports->sum('exclusive_captures')) . "**\n"
                . "🤝 Cierres de Red: **" . $allWeeklyReports->sum('closings') . "**\n\n"
                . "*Consejo: Puedes preguntarme rankings nacionales, cuál es la oficina líder o estadísticas de alguna sucursal.*";
        }

        return $warningHeader . $res;
    }

    /**
     * Extraer los 6 KPIs del mensaje usando Google Gemini con responseMimeType JSON estructurado obligatorio.
     */
    private function extractKPIsWithGemini(string $message, string $userName): ?array
    {
        $apiKey = config('services.gemini.api_key');

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(15)->post("https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => "Extrae exactamente 6 números enteros del mensaje del asesor.\n"
                            . "Los 6 indicadores son (en este orden): "
                            . "1. Visitas, 2. Letrero, 3. Exclusiva, 4. Cierres, 5. Llamadas, 6. AlphaX.\n"
                            . "Responde ÚNICAMENTE con un JSON válido con estas claves exactas: "
                            . "{\"visits\": N, \"sign_captures\": N, \"exclusive_captures\": N, \"closings\": N, \"calls_made\": N, \"properties_in_system\": N}\n"
                            . "Si algún dato no se menciona en el mensaje, pon 0.\n\n"
                            . "Mensaje a procesar del asesor {$userName}: \"{$message}\""]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.1,
                    'responseMimeType' => 'application/json'
                ]
            ]);

            if ($response->successful()) {
                $content = $response->json('candidates.0.content.parts.0.text', '');
                $content = trim($content);

                $data = json_decode($content, true);

                if ($data && !isset($data['error'])) {
                    $fields = ['visits', 'sign_captures', 'exclusive_captures', 'closings', 'calls_made', 'properties_in_system'];
                    foreach ($fields as $field) {
                        if (!isset($data[$field]) || !is_numeric($data[$field]) || $data[$field] < 0) {
                            $data[$field] = 0;
                        }
                        $data[$field] = (int) $data[$field];
                    }
                    return $data;
                }
            }
            
            // Fallback sintáctico manual básico si el API falla (extracción por regex básica de 6 números)
            return $this->extractKPIsLocally($message);
        } catch (\Exception $e) {
            return $this->extractKPIsLocally($message);
        }
    }

    /**
     * Extracción local por regex básica si la API de voz/reporte no tiene créditos o falla.
     */
    private function extractKPIsLocally(string $message): ?array
    {
        // Buscar patrón simple de 6 números separados por comas, espacios o guiones (ej: "4, 2, 1, 0, 10, 2")
        if (preg_match_all('/\d+/', $message, $matches)) {
            $numbers = $matches[0];
            if (count($numbers) >= 6) {
                return [
                    'visits' => (int) $numbers[0],
                    'sign_captures' => (int) $numbers[1],
                    'exclusive_captures' => (int) $numbers[2],
                    'closings' => (int) $numbers[3],
                    'calls_made' => (int) $numbers[4],
                    'properties_in_system' => (int) $numbers[5],
                ];
            }
        }
        return null;
    }

    /**
     * Construir System Prompt dinámico según el Rol y la data en tiempo real.
     */
    private function buildSystemPrompt(User $user, Carbon $today, bool $reportRegistered, string $reportDetails): string
    {
        $startOfWeek = $today->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = $today->copy()->endOfWeek(Carbon::SUNDAY);

        // Prompt común base
        $prompt = "Eres Alfa AI, el asistente virtual inteligente exclusivo de Alfa Business Group Bolivia.\n"
            . "REGLAS CRÍTICAS DE COMPORTAMIENTO:\n"
            . "1. Sé extremadamente DIRECTO, CORTO y CONCISO. Tus respuestas deben ser breves, de preferencia no mayores a 1 o 2 párrafos cortos, a menos que el usuario pida un detalle específico.\n"
            . "2. NUNCA brindes acumulados, tablas de productividad ni rankings de asesores de forma espontánea si el usuario no te los ha preguntado de manera explícita.\n"
            . "3. Si el usuario te envía un saludo simple (como 'hola', 'buenas', 'buenos días'), limítate a darle una bienvenida muy cordial, breve y profesional (ej. '¡Hola, Director Carlos! Un gusto saludarte hoy. ¿En qué te puedo colaborar?') sin verter ningún tipo de estadística o reporte espontáneo.\n"
            . "4. Hoy es {$today->format('d/m/Y')}. Responde siempre en español con un tono profesional, motivador y elegante. El usuario actual es {$user->name} con el rol de " . ucfirst($user->role) . ".\n\n";

        if ($user->role === 'asesor') {
            // Obtener acumulados de la semana del asesor
            $weeklyReports = DailyReport::where('user_id', $user->id)
                ->whereBetween('report_date', [$startOfWeek, $endOfWeek])
                ->get();

            $weeklyTotals = [
                'visits' => $weeklyReports->sum('visits'),
                'sign_captures' => $weeklyReports->sum('sign_captures'),
                'exclusive_captures' => $weeklyReports->sum('exclusive_captures'),
                'closings' => $weeklyReports->sum('closings'),
                'calls_made' => $weeklyReports->sum('calls_made'),
                'properties_in_system' => $weeklyReports->sum('properties_in_system'),
            ];

            $totalCaptures = $weeklyTotals['sign_captures'] + $weeklyTotals['exclusive_captures'];

            // Meta de captaciones
            $kpiConfig = KpiConfig::where('indicator', 'captaciones')
                ->where('is_active', true)
                ->first();
            $goal = $kpiConfig ? $kpiConfig->weekly_goal : 10;
            $missing = max(0, $goal - $totalCaptures);
            $trafficLight = 'green';
            if ($kpiConfig) {
                $trafficLight = $kpiConfig->getTrafficLightColor($totalCaptures);
            }

            $prompt .= "--- CONTEXTO EN TIEMPO REAL DEL ASESOR ---\n"
                . "- Meta de Captaciones Semanal: {$goal} captaciones totales.\n"
                . "- Tus captaciones acumuladas esta semana: {$totalCaptures} (Letrero: {$weeklyTotals['sign_captures']}, Exclusivas: {$weeklyTotals['exclusive_captures']}).\n"
                . "- Captaciones que te faltan esta semana para cumplir la meta: {$missing}.\n"
                . "- Color actual de tu semáforo de metas: " . uppercase_color($trafficLight) . ".\n"
                . "- Tus otros KPIs acumulados de esta semana:\n"
                . "  * Visitas a propiedades: {$weeklyTotals['visits']}\n"
                . "  * Cierres de contratos: {$weeklyTotals['closings']}\n"
                . "  * Llamadas realizadas: {$weeklyTotals['calls_made']}\n"
                . "  * Propiedades cargadas a AlphaX: {$weeklyTotals['properties_in_system']}\n\n";

            if ($reportRegistered) {
                $prompt .= "ATENCIÓN: Acabas de registrar con éxito tu reporte diario con los siguientes datos:\n{$reportDetails}\n"
                    . "Por favor felicítalo efusivamente y recuérdale cómo quedan sus métricas semanales actualizadas.\n\n";
            } else {
                $prompt .= "Si el asesor te comparte su actividad del día (ej. 'Hoy hice 3 visitas, 2 llamadas...'), el sistema ya procesará los datos automáticamente por detrás. Confírmale que ya lo registraste y muéstrale los números.\n\n";
            }

        } elseif ($user->role === 'team_leader') {
            // Datos del equipo
            $team = Team::find($user->team_id);
            $teamName = $team ? $team->name : 'Sin equipo';
            
            // Asesores del equipo
            $teamMembers = User::where('team_id', $user->team_id)
                ->where('role', 'asesor')
                ->where('is_active', true)
                ->get();

            // Productividad semanal acumulada por asesor
            $asesoresData = $teamMembers->map(function ($member) use ($startOfWeek, $endOfWeek) {
                $reports = DailyReport::where('user_id', $member->id)
                    ->whereBetween('report_date', [$startOfWeek, $endOfWeek])
                    ->get();

                return [
                    'name' => $member->name,
                    'visits' => $reports->sum('visits'),
                    'sign' => $reports->sum('sign_captures'),
                    'exclusive' => $reports->sum('exclusive_captures'),
                    'total_captures' => $reports->sum('sign_captures') + $reports->sum('exclusive_captures'),
                    'closings' => $reports->sum('closings'),
                    'calls' => $reports->sum('calls_made'),
                    'properties' => $reports->sum('properties_in_system'),
                ];
            })->sortByDesc('total_captures')->values();

            // Asesor estrella (top captador)
            $starAsesor = $asesoresData->first();
            $starText = $starAsesor ? "{$starAsesor['name']} con {$starAsesor['total_captures']} captaciones totales" : "Ninguno aún";

            // Quiénes no han reportado hoy
            $reportedTodayUserIds = DailyReport::whereIn('user_id', $teamMembers->pluck('id'))
                ->where('report_date', $today)
                ->pluck('user_id')
                ->toArray();
            
            $missingReportMembers = $teamMembers->filter(function($m) use ($reportedTodayUserIds) {
                return !in_array($m->id, $reportedTodayUserIds);
            })->pluck('name')->toArray();

            $prompt .= "--- CONTEXTO EN TIEMPO REAL DEL TEAM LEADER ---\n"
                . "- Nombre del Equipo: {$teamName}\n"
                . "- Miembros de tu equipo: " . implode(', ', $teamMembers->pluck('name')->toArray()) . "\n"
                . "- Asesor Estrella de la semana (por captaciones): {$starText}.\n"
                . "- Asesores que FALTAN reportar su actividad el día de hoy: " . (count($missingReportMembers) > 0 ? implode(', ', $missingReportMembers) : '¡Ninguno! Todos han reportado hoy. 🎉') . "\n"
                . "- Resumen de la tabla de productividad semanal de tus asesores:\n";
            
            foreach ($asesoresData as $ad) {
                $prompt .= "  * {$ad['name']}: Captaciones: {$ad['total_captures']} (Letrero: {$ad['sign']}, Exclusivas: {$ad['exclusive']}) | Visitas: {$ad['visits']} | Llamadas: {$ad['calls']} | Cierres: {$ad['closings']}\n";
            }
            $prompt .= "\nPuedes responder preguntas sobre quién es el mejor de la semana, quién falta reportar hoy o estadísticas agregadas del equipo.\n\n";

        } elseif ($user->role === 'director') {
            // Datos del Director - Consolidado de Oficinas
            $activeOfficesCount = Office::where('is_active', true)->count();
            $activeAsesoresCount = User::where('role', 'asesor')->where('is_active', true)->count();
            $activeLeadersCount = User::where('role', 'team_leader')->where('is_active', true)->count();

            // Metas de todas las oficinas en la semana actual
            $allWeeklyReports = DailyReport::whereBetween('report_date', [$startOfWeek, $endOfWeek])->get();
            $globalTotals = [
                'visits' => $allWeeklyReports->sum('visits'),
                'sign' => $allWeeklyReports->sum('sign_captures'),
                'exclusive' => $allWeeklyReports->sum('exclusive_captures'),
                'total_captures' => $allWeeklyReports->sum('sign_captures') + $allWeeklyReports->sum('exclusive_captures'),
                'closings' => $allWeeklyReports->sum('closings'),
                'calls' => $allWeeklyReports->sum('calls_made'),
            ];

            // Listado de oficinas y su productividad
            $offices = Office::where('is_active', true)->get();
            $officesSummary = [];
            foreach ($offices as $o) {
                $officeAsesoresIds = User::where('office_id', $o->id)->where('role', 'asesor')->pluck('id');
                $officeReports = DailyReport::whereIn('user_id', $officeAsesoresIds)
                    ->whereBetween('report_date', [$startOfWeek, $endOfWeek])
                    ->get();
                $officesSummary[] = [
                    'name' => $o->name,
                    'city' => $o->city,
                    'captures' => $officeReports->sum('sign_captures') + $officeReports->sum('exclusive_captures'),
                    'visits' => $officeReports->sum('visits'),
                    'closings' => $officeReports->sum('closings'),
                ];
            }

            // Ranking Nacional
            $rankingNacional = User::where('role', 'asesor')
                ->where('is_active', true)
                ->get()
                ->map(function($m) use ($startOfWeek, $endOfWeek) {
                    $rep = DailyReport::where('user_id', $m->id)->whereBetween('report_date', [$startOfWeek, $endOfWeek])->get();
                    return [
                        'name' => $m->name,
                        'captures' => $rep->sum('sign_captures') + $rep->sum('exclusive_captures'),
                        'closings' => $rep->sum('closings'),
                    ];
                })->sortByDesc('captures')->values()->take(5);

            $prompt .= "--- CONTEXTO EN TIEMPO REAL DEL DIRECTOR GENERAL (CORPORATIVO) ---\n"
                . "- Oficinas operativas en Bolivia: {$activeOfficesCount}\n"
                . "- Red de asesores activos a nivel nacional: {$activeAsesoresCount}\n"
                . "- Cantidad de Team Líderes activos: {$activeLeadersCount}\n"
                . "- ACUMULADOS SEMANALES CORPORATIVOS (Toda Bolivia):\n"
                . "  * Captaciones Totales: {$globalTotals['total_captures']} (Letrero: {$globalTotals['sign']}, Exclusivas: {$globalTotals['exclusive']})\n"
                . "  * Visitas a propiedades: {$globalTotals['visits']}\n"
                . "  * Cierres concretados: {$globalTotals['closings']}\n"
                . "  * Llamadas de captación: {$globalTotals['calls']}\n"
                . "- DESEMPEÑO POR OFICINAS ESTA SEMANA:\n";
            
            foreach ($officesSummary as $os) {
                $prompt .= "  * Oficina {$os['name']} ({$os['city']}): Captaciones: {$os['captures']} | Visitas: {$os['visits']} | Cierres: {$os['closings']}\n";
            }

            $prompt .= "\n- TOP 5 ASESORES ESTRELLA A NIVEL NACIONAL:\n";
            foreach ($rankingNacional as $index => $rn) {
                $prompt .= "  " . ($index + 1) . ". {$rn['name']} - {$rn['captures']} captaciones | Cierres: {$rn['closings']}\n";
            }
            $prompt .= "\nPuedes dar análisis estratégicos corporativos de qué oficina va mejor, quién es el mejor del país esta semana, y totales consolidados.\n\n";
        }

        return $prompt;
    }
}

// Función auxiliar de semáforo
if (!function_exists('uppercase_color')) {
    function uppercase_color($color) {
        return match ($color) {
            'green' => '🟢 VERDE (Meta Excelente)',
            'yellow' => '🟡 AMARILLO (Meta en Progreso)',
            'red' => '🔴 ROJO (Meta Crítica - Alerta)',
            default => '⚪ SIN DEFINIR',
        };
    }
}
