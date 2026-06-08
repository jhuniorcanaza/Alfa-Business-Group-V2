<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use App\Models\KpiConfig;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamLeaderDashboardController extends Controller
{
    /**
     * Dashboard principal del Team Líder.
     * Solo lectura: monitoreo de asesores del equipo asignado.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today();

        $filterType = $request->get('filter_type', 'semana');
        
        switch ($filterType) {
            case 'dia':
                $startOfWeek = $today->copy()->startOfDay();
                $endOfWeek = $today->copy()->endOfDay();
                break;
            case 'mes':
                $startOfWeek = $today->copy()->startOfMonth();
                $endOfWeek = $today->copy()->endOfMonth();
                break;
            case 'custom':
                $startOfWeek = Carbon::parse($request->get('start_date', $today->copy()->startOfWeek(Carbon::MONDAY)))->startOfDay();
                $endOfWeek = Carbon::parse($request->get('end_date', $today->copy()->endOfWeek(Carbon::SUNDAY)))->endOfDay();
                break;
            case 'semana':
            default:
                $startOfWeek = $today->copy()->startOfWeek(Carbon::MONDAY);
                $endOfWeek = $today->copy()->endOfWeek(Carbon::SUNDAY);
                break;
        }

        // Asesores activos de mi equipo con sus reportes y estadísticas
        $asesoresData = $this->getTeamAsesoresData($user, $today, $startOfWeek, $endOfWeek);

        // Configuración de semáforo
        $kpiConfig = KpiConfig::where('indicator', 'captaciones')
            ->where('is_active', true)
            ->first();

        // Asesores sin reporte hoy
        $missingReports = $asesoresData->where('sent_today', false);

        // Acumulado semanal del equipo
        $teamWeekly = [
            'visits' => $asesoresData->sum('weekly.visits'),
            'sign_captures' => $asesoresData->sum('weekly.sign_captures'),
            'exclusive_captures' => $asesoresData->sum('weekly.exclusive_captures'),
            'closings' => $asesoresData->sum('weekly.closings'),
            'calls_made' => $asesoresData->sum('weekly.calls_made'),
            'properties_in_system' => $asesoresData->sum('weekly.properties_in_system'),
        ];

        // Rankings por cada uno de los 6 indicadores
        $rankings = [
            'visits' => $asesoresData->sortByDesc('weekly.visits')->values(),
            'sign_captures' => $asesoresData->sortByDesc('weekly.sign_captures')->values(),
            'exclusive_captures' => $asesoresData->sortByDesc('weekly.exclusive_captures')->values(),
            'closings' => $asesoresData->sortByDesc('weekly.closings')->values(),
            'calls_made' => $asesoresData->sortByDesc('weekly.calls_made')->values(),
            'properties_in_system' => $asesoresData->sortByDesc('weekly.properties_in_system')->values(),
        ];

        // Nombre del equipo
        $team = $user->team;

        return view('dashboards.team-leader', compact(
            'user',
            'team',
            'asesoresData',
            'kpiConfig',
            'missingReports',
            'teamWeekly',
            'rankings',
            'today',
            'startOfWeek',
            'endOfWeek',
            'filterType'
        ));
    }

    /**
     * Ver historial completo de un asesor específico.
     * Solo puede ver asesores de su propio equipo.
     */
    public function asesorDetail(User $asesor)
    {
        $user = Auth::user();

        // Seguridad: verificar que el asesor pertenece a mi equipo
        if ($asesor->team_id !== $user->team_id || $asesor->role !== 'asesor') {
            abort(403, 'No tienes permiso para ver este asesor.');
        }

        $today = Carbon::today();
        $startOfWeek = $today->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = $today->copy()->endOfWeek(Carbon::SUNDAY);

        // Reporte de hoy
        $todayReport = DailyReport::where('user_id', $asesor->id)
            ->where('report_date', $today)
            ->first();

        // Historial de reportes (últimos 60 días)
        $history = DailyReport::where('user_id', $asesor->id)
            ->orderBy('report_date', 'desc')
            ->limit(60)
            ->get();

        // Reportes de esta semana para tendencia diaria
        $weeklyReports = DailyReport::where('user_id', $asesor->id)
            ->whereBetween('report_date', [$startOfWeek, $endOfWeek])
            ->orderBy('report_date')
            ->get();

        // Acumulado semanal
        $weeklyTotals = [
            'visits' => $weeklyReports->sum('visits'),
            'sign_captures' => $weeklyReports->sum('sign_captures'),
            'exclusive_captures' => $weeklyReports->sum('exclusive_captures'),
            'closings' => $weeklyReports->sum('closings'),
            'calls_made' => $weeklyReports->sum('calls_made'),
            'properties_in_system' => $weeklyReports->sum('properties_in_system'),
        ];

        $totalCaptures = $weeklyTotals['sign_captures'] + $weeklyTotals['exclusive_captures'];

        // Semáforo
        $kpiConfig = KpiConfig::where('indicator', 'captaciones')
            ->where('is_active', true)
            ->first();

        $trafficLight = 'green';
        $percentage = 100;
        if ($kpiConfig && $kpiConfig->weekly_goal > 0) {
            $percentage = round(($totalCaptures / $kpiConfig->weekly_goal) * 100);
            $trafficLight = $kpiConfig->getTrafficLightColor($totalCaptures);
        }

        // Estadísticas históricas
        $totalReports = DailyReport::where('user_id', $asesor->id)->count();
        $avgVisits = DailyReport::where('user_id', $asesor->id)->avg('visits') ?? 0;
        $avgCaptures = DailyReport::where('user_id', $asesor->id)
            ->selectRaw('AVG(sign_captures + exclusive_captures) as avg_captures')
            ->value('avg_captures') ?? 0;

        // Tendencia diaria de la semana (para mini-gráfico)
        $weekDays = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
        $weeklyTrend = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $startOfWeek->copy()->addDays($i);
            $report = $weeklyReports->firstWhere('report_date', $day);
            $weeklyTrend[] = [
                'day' => $weekDays[$i],
                'date' => $day->format('d/m'),
                'visits' => $report ? $report->visits : 0,
                'captures' => $report ? ($report->sign_captures + $report->exclusive_captures) : 0,
                'has_report' => $report !== null,
            ];
        }

        return view('dashboards.team-leader-asesor-detail', compact(
            'user',
            'asesor',
            'todayReport',
            'history',
            'weeklyTotals',
            'totalCaptures',
            'trafficLight',
            'percentage',
            'kpiConfig',
            'totalReports',
            'avgVisits',
            'avgCaptures',
            'weeklyTrend',
            'today'
        ));
    }

    /**
     * Mapa de visitas del equipo (semana actual).
     */
    public function visitsMap(Request $request)
    {
        $user = Auth::user();
        
        // 1. Manejo de Fechas
        $filterType = $request->get('filter_type', 'semana');
        $startDateInput = $request->get('start_date');
        $endDateInput = $request->get('end_date');

        $today = Carbon::today();
        switch ($filterType) {
            case 'dia':
                $startDate = $today->copy()->startOfDay();
                $endDate = $today->copy()->endOfDay();
                break;
            case 'mes':
                $startDate = $today->copy()->startOfMonth();
                $endDate = $today->copy()->endOfMonth();
                break;
            case 'custom':
                $startDate = $startDateInput ? Carbon::parse($startDateInput)->startOfDay() : $today->copy()->startOfWeek(Carbon::MONDAY);
                $endDate = $endDateInput ? Carbon::parse($endDateInput)->endOfDay() : $today->copy()->endOfWeek(Carbon::SUNDAY);
                break;
            case 'semana':
            default:
                $startDate = $today->copy()->startOfWeek(Carbon::MONDAY);
                $endDate = $today->copy()->endOfWeek(Carbon::SUNDAY);
                break;
        }

        $asesorId = $request->get('user_id');

        // Asesores del equipo para filtrar
        $asesoresForFilter = User::where('team_id', $user->team_id)
            ->where('role', 'asesor')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Consulta de asesores a mostrar en el mapa
        $asesoresQuery = User::where('team_id', $user->team_id)
            ->where('role', 'asesor')
            ->where('is_active', true);

        if ($asesorId) {
            $asesoresQuery->where('id', $asesorId);
        }
        $asesores = $asesoresQuery->get();
        $asesoresCount = $asesores->count();

        // Colores únicos por asesor
        $palette = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#f97316','#14b8a6','#ec4899','#84cc16'];
        $asesorColors = [];
        foreach ($asesores as $i => $a) {
            $asesorColors[$a->name] = $palette[$i % count($palette)];
        }

        $mapMarkers     = [];
        $allVisitDetails = collect();
        $uniqueClientsSet = [];
        $totalVisits = 0;

        foreach ($asesores as $asesor) {
            $reports = DailyReport::where('user_id', $asesor->id)
                ->whereBetween('report_date', [$startDate, $endDate])
                ->get();

            $totalVisits += $reports->sum('visits');

            foreach ($reports as $report) {
                if (!$report->visits_data) continue;
                foreach ($report->visits_data as $v) {
                    $detail = [
                        'asesor_name'  => $asesor->name,
                        'client_name'  => $v['client_name']  ?? '',
                        'client_phone' => $v['client_phone'] ?? '',
                        'address'      => $v['address']      ?? '',
                        'latitude'     => $v['latitude']     ?? null,
                        'longitude'    => $v['longitude']    ?? null,
                        'date'         => $report->report_date->format('d/m/Y'),
                    ];
                    $allVisitDetails->push($detail);
                    if (!empty($v['client_phone'])) $uniqueClientsSet[$v['client_phone']] = true;
                    if (!empty($v['latitude']) && !empty($v['longitude'])) {
                        $mapMarkers[] = array_merge($detail, ['lat' => $v['latitude'], 'lng' => $v['longitude']]);
                    }
                }
            }
        }

        $geoVisits     = count($mapMarkers);
        $uniqueClients = count($uniqueClientsSet);

        return view('team-leader.visits-map', compact(
            'startDate', 'endDate', 'filterType', 'totalVisits', 'geoVisits',
            'uniqueClients', 'asesoresCount', 'mapMarkers',
            'allVisitDetails', 'asesorColors', 'asesoresForFilter', 'asesorId'
        ));
    }

    /**
     * Exportar las visitas en formato CSV (Excel) para el Team Leader.
     */
    public function exportVisitsCsv(Request $request)
    {
        $user = Auth::user();
        
        $startDate = Carbon::parse($request->get('start_date', Carbon::today()->startOfWeek(Carbon::MONDAY)))->startOfDay();
        $endDate = Carbon::parse($request->get('end_date', Carbon::today()->endOfWeek(Carbon::SUNDAY)))->endOfDay();

        // Obtener asesores de mi equipo
        $asesoresIds = User::where('team_id', $user->team_id)
            ->where('role', 'asesor')
            ->pluck('id');

        $reports = DailyReport::with('user')
            ->whereIn('user_id', $asesoresIds)
            ->whereBetween('report_date', [$startDate, $endDate])
            ->orderBy('report_date', 'desc')
            ->get();

        $response = new \Symfony\Component\HttpFoundation\StreamedResponse(function () use ($reports) {
            $handle = fopen('php://output', 'w');
            
            // UTF-8 BOM para que Excel detecte acentos y eñes correctamente
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Encabezados
            fputcsv($handle, [
                'Fecha de Visita', 'Asesor', 'Nombre del Cliente', 'Celular del Cliente', 
                'Dirección / Ubicación', 'Latitud', 'Longitud', 'Enlace Google Maps',
                'Foto Respaldo Letrero',
                'Otros KPIs del día (Letreros | Exclusivas | Cierres | Llamadas)'
            ], ';');

            foreach ($reports as $report) {
                $photoUrls = [];
                if (!empty($report->sign_image_path)) {
                    $decoded = json_decode($report->sign_image_path, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        foreach ($decoded as $path) {
                            $photoUrls[] = url($path);
                        }
                    } else {
                        $photoUrls[] = url($report->sign_image_path);
                    }
                }
                $photoUrl = !empty($photoUrls) ? implode(', ', $photoUrls) : 'N/A';
                
                if ($report->visits_data && is_array($report->visits_data) && count($report->visits_data) > 0) {
                    foreach ($report->visits_data as $v) {
                        $lat = $v['latitude'] ?? '';
                        $lng = $v['longitude'] ?? '';
                        $mapLink = ($lat && $lng) ? "https://www.google.com/maps/search/?api=1&query={$lat},{$lng}" : 'N/A';
                        
                        fputcsv($handle, [
                            $report->report_date->format('Y-m-d'),
                            $report->user->name,
                            $v['client_name'] ?? 'N/A',
                            $v['client_phone'] ?? 'N/A',
                            $v['address'] ?? 'N/A',
                            $lat,
                            $lng,
                            $mapLink,
                            $photoUrl,
                            "Letreros: {$report->sign_captures} | Exclusivas: {$report->exclusive_captures} | Cierres: {$report->closings} | Llamadas: {$report->calls_made}"
                        ], ';');
                    }
                } else {
                    // Si el reporte tiene conteo de visitas pero no datos GPS detallados (históricos)
                    $visitsCount = $report->visits;
                    if ($visitsCount > 0) {
                        for ($i = 1; $i <= $visitsCount; $i++) {
                            fputcsv($handle, [
                                $report->report_date->format('Y-m-d'),
                                $report->user->name,
                                'Cliente Histórico ' . $i,
                                'N/A',
                                'Visita sin coordenadas GPS',
                                '',
                                '',
                                'N/A',
                                $photoUrl,
                                "Letreros: {$report->sign_captures} | Exclusivas: {$report->exclusive_captures} | Cierres: {$report->closings} | Llamadas: {$report->calls_made}"
                            ], ';');
                        }
                    }
                }
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="reporte-visitas-equipo-' . $startDate->format('Ymd') . '-al-' . $endDate->format('Ymd') . '.csv"',
        ]);

        return $response;
    }

    /**
     * Helper to get team advisers with daily/weekly statistics.
     */
    private function getTeamAsesoresData($user, $today, $startOfWeek, $endOfWeek)
    {
        $asesores = User::where('team_id', $user->team_id)
            ->where('role', 'asesor')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $todayReports = DailyReport::whereIn('user_id', $asesores->pluck('id'))
            ->where('report_date', $today)
            ->get()
            ->keyBy('user_id');

        $weeklyReportsAll = DailyReport::whereIn('user_id', $asesores->pluck('id'))
            ->whereBetween('report_date', [$startOfWeek, $endOfWeek])
            ->get()
            ->groupBy('user_id');

        return $asesores->map(function ($asesor) use ($todayReports, $weeklyReportsAll) {
            $todayReport = $todayReports->get($asesor->id);
            $weeklyReports = $weeklyReportsAll->get($asesor->id, collect());

            return (object) [
                'id' => $asesor->id,
                'name' => $asesor->name,
                'email' => $asesor->email,
                'phone' => $asesor->phone,
                'sent_today' => $todayReport !== null,
                'today' => $todayReport ? [
                    'visits' => $todayReport->visits,
                    'sign_captures' => $todayReport->sign_captures,
                    'exclusive_captures' => $todayReport->exclusive_captures,
                    'closings' => $todayReport->closings,
                    'calls_made' => $todayReport->calls_made,
                    'call_phone_number' => $todayReport->call_phone_number,
                    'properties_in_system' => $todayReport->properties_in_system,
                    'source' => $todayReport->source,
                    'created_at' => $todayReport->created_at,
                    'sign_image_path' => $todayReport->sign_image_path,
                ] : null,
                'weekly' => [
                    'visits' => $weeklyReports->sum('visits'),
                    'sign_captures' => $weeklyReports->sum('sign_captures'),
                    'exclusive_captures' => $weeklyReports->sum('exclusive_captures'),
                    'closings' => $weeklyReports->sum('closings'),
                    'calls_made' => $weeklyReports->sum('calls_made'),
                    'properties_in_system' => $weeklyReports->sum('properties_in_system'),
                ],
                'total_captures' => $weeklyReports->sum('sign_captures') + $weeklyReports->sum('exclusive_captures'),
                'reports_count' => $weeklyReports->count(),
            ];
        });
    }

    /**
     * Mis Asesores page.
     */
    public function asesores()
    {
        $user = Auth::user();
        $today = Carbon::today();
        $startOfWeek = $today->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = $today->copy()->endOfWeek(Carbon::SUNDAY);

        $asesoresData = $this->getTeamAsesoresData($user, $today, $startOfWeek, $endOfWeek);

        return view('team-leader.asesores', compact('user', 'asesoresData'));
    }

    /**
     * Semáforos page.
     */
    public function semaforos()
    {
        $user = Auth::user();
        $today = Carbon::today();
        $startOfWeek = $today->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = $today->copy()->endOfWeek(Carbon::SUNDAY);

        $asesoresData = $this->getTeamAsesoresData($user, $today, $startOfWeek, $endOfWeek);

        $kpiConfig = KpiConfig::where('indicator', 'captaciones')
            ->where('is_active', true)
            ->first();

        return view('team-leader.semaforos', compact('user', 'asesoresData', 'kpiConfig'));
    }

    /**
     * Ranking page.
     */
    public function ranking()
    {
        $user = Auth::user();
        $today = Carbon::today();
        $startOfWeek = $today->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = $today->copy()->endOfWeek(Carbon::SUNDAY);

        $asesoresData = $this->getTeamAsesoresData($user, $today, $startOfWeek, $endOfWeek);

        $rankings = [
            'visits' => $asesoresData->sortByDesc('weekly.visits')->values(),
            'sign_captures' => $asesoresData->sortByDesc('weekly.sign_captures')->values(),
            'exclusive_captures' => $asesoresData->sortByDesc('weekly.exclusive_captures')->values(),
            'closings' => $asesoresData->sortByDesc('weekly.closings')->values(),
            'calls_made' => $asesoresData->sortByDesc('weekly.calls_made')->values(),
            'properties_in_system' => $asesoresData->sortByDesc('weekly.properties_in_system')->values(),
        ];

        return view('team-leader.ranking', compact('user', 'rankings', 'startOfWeek', 'endOfWeek'));
    }

    /**
     * Alertas page.
     */
    public function alertas()
    {
        $user = Auth::user();
        $today = Carbon::today();
        $startOfWeek = $today->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = $today->copy()->endOfWeek(Carbon::SUNDAY);

        $asesoresData = $this->getTeamAsesoresData($user, $today, $startOfWeek, $endOfWeek);
        $missingReports = $asesoresData->where('sent_today', false);

        return view('team-leader.alertas', compact('user', 'missingReports', 'today'));
    }

    /**
     * Historial diario (KPIs del día).
     */
    public function historial()
    {
        $user = Auth::user();
        $today = Carbon::today();
        $startOfWeek = $today->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = $today->copy()->endOfWeek(Carbon::SUNDAY);

        $asesoresData = $this->getTeamAsesoresData($user, $today, $startOfWeek, $endOfWeek);

        return view('team-leader.historial', compact('user', 'asesoresData', 'today'));
    }

    /**
     * Acumulado semanal page.
     */
    public function acumulado()
    {
        $user = Auth::user();
        $today = Carbon::today();
        $startOfWeek = $today->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = $today->copy()->endOfWeek(Carbon::SUNDAY);

        $asesoresData = $this->getTeamAsesoresData($user, $today, $startOfWeek, $endOfWeek);

        $teamWeekly = [
            'visits' => $asesoresData->sum('weekly.visits'),
            'sign_captures' => $asesoresData->sum('weekly.sign_captures'),
            'exclusive_captures' => $asesoresData->sum('weekly.exclusive_captures'),
            'closings' => $asesoresData->sum('weekly.closings'),
            'calls_made' => $asesoresData->sum('weekly.calls_made'),
            'properties_in_system' => $asesoresData->sum('weekly.properties_in_system'),
        ];

        return view('team-leader.acumulado', compact('user', 'teamWeekly', 'startOfWeek', 'endOfWeek'));
    }

    public function letreros(Request $request)
    {
        $user = Auth::user();
        
        // 1. Manejo de Fechas
        $filterType = $request->get('filter_type', 'semana');
        $startDateInput = $request->get('start_date');
        $endDateInput = $request->get('end_date');

        $today = Carbon::today();
        switch ($filterType) {
            case 'dia':
                $startDate = $today->copy()->startOfDay();
                $endDate = $today->copy()->endOfDay();
                break;
            case 'mes':
                $startDate = $today->copy()->startOfMonth();
                $endDate = $today->copy()->endOfMonth();
                break;
            case 'custom':
                $startDate = $startDateInput ? Carbon::parse($startDateInput)->startOfDay() : $today->copy()->startOfWeek(Carbon::MONDAY);
                $endDate = $endDateInput ? Carbon::parse($endDateInput)->endOfDay() : $today->copy()->endOfWeek(Carbon::SUNDAY);
                break;
            case 'semana':
            default:
                $startDate = $today->copy()->startOfWeek(Carbon::MONDAY);
                $endDate = $today->copy()->endOfWeek(Carbon::SUNDAY);
                break;
        }

        $asesorId = $request->get('user_id');

        // Asesores del equipo para filtrar
        $asesoresForFilter = User::where('team_id', $user->team_id)
            ->where('role', 'asesor')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Determinar qué asesores consultar
        if ($asesorId) {
            $userIds = [$asesorId];
        } else {
            $userIds = $asesoresForFilter->pluck('id');
        }

        // Obtener reportes con fotos de letrero en el rango y asesores filtrados
        $reportsWithPhotos = DailyReport::whereIn('user_id', $userIds)
            ->whereBetween('report_date', [$startDate, $endDate])
            ->whereNotNull('sign_image_path')
            ->orderBy('report_date', 'desc')
            ->get();

        return view('team-leader.letreros', compact(
            'user', 'reportsWithPhotos', 'startDate', 'endDate', 
            'filterType', 'asesoresForFilter', 'asesorId'
        ));
    }
}

