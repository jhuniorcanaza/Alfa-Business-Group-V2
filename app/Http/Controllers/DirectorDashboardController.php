<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use App\Models\Office;
use App\Models\Team;
use App\Models\User;
use App\Models\KpiConfig;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DirectorDashboardController extends Controller
{
    private function getDates(Request $request)
    {
        $filterType = $request->get('filter_type', 'semana'); // dia, semana, mes, custom
        $today = Carbon::today();
        $startDate = $today->copy()->startOfWeek(Carbon::MONDAY);
        $endDate = $today->copy()->endOfWeek(Carbon::SUNDAY);

        if ($filterType === 'dia') {
            $startDate = $today->copy()->startOfDay();
            $endDate = $today->copy()->endOfDay();
        } elseif ($filterType === 'mes') {
            $startDate = $today->copy()->startOfMonth();
            $endDate = $today->copy()->endOfMonth();
        } elseif ($filterType === 'custom') {
            $startInput = $request->get('start_date');
            $endInput = $request->get('end_date');
            if ($startInput && $endInput) {
                $startDate = Carbon::parse($startInput)->startOfDay();
                $endDate = Carbon::parse($endInput)->endOfDay();
            }
        }

        return [$startDate, $endDate, $filterType, $today];
    }

    private function getDashboardData($startDate, $endDate)
    {
        $totalAsesores = User::where('role', 'asesor')->where('is_active', true)->count();
        $totalTeamLeaders = User::where('role', 'team_leader')->where('is_active', true)->count();
        $totalOffices = Office::where('is_active', true)->count();

        $reportsCount = DailyReport::whereBetween('report_date', [$startDate, $endDate])->count();
        $asesoresConReporte = DailyReport::whereBetween('report_date', [$startDate, $endDate])
            ->distinct('user_id')
            ->count('user_id');
        $asesoresSinReporte = max(0, $totalAsesores - $asesoresConReporte);

        $rangeReports = DailyReport::whereBetween('report_date', [$startDate, $endDate])->get();
        $reportsByUser = $rangeReports->groupBy('user_id');

        $weeklyGlobal = [
            'visits' => $rangeReports->sum('visits'),
            'sign_captures' => $rangeReports->sum('sign_captures'),
            'exclusive_captures' => $rangeReports->sum('exclusive_captures'),
            'closings' => $rangeReports->sum('closings'),
            'calls_made' => $rangeReports->sum('calls_made'),
            'properties_in_system' => $rangeReports->sum('properties_in_system'),
        ];

        $offices = Office::where('is_active', true)
            ->with(['users' => function ($q) {
                $q->where('role', 'asesor')->where('is_active', true);
            }])
            ->get()
            ->map(function ($office) use ($reportsByUser) {
                $asesorIds = $office->users->pluck('id');
                $officeVisits = 0;
                $officeSign = 0;
                $officeExclusive = 0;
                $officeClosings = 0;
                $officeCalls = 0;
                $officeProperties = 0;

                foreach ($asesorIds as $id) {
                    $reports = $reportsByUser->get($id, collect());
                    $officeVisits += $reports->sum('visits');
                    $officeSign += $reports->sum('sign_captures');
                    $officeExclusive += $reports->sum('exclusive_captures');
                    $officeClosings += $reports->sum('closings');
                    $officeCalls += $reports->sum('calls_made');
                    $officeProperties += $reports->sum('properties_in_system');
                }

                $office->weekly = [
                    'visits' => $officeVisits,
                    'sign_captures' => $officeSign,
                    'exclusive_captures' => $officeExclusive,
                    'closings' => $officeClosings,
                    'calls_made' => $officeCalls,
                    'properties_in_system' => $officeProperties,
                ];
                $office->total_asesores = $asesorIds->count();
                return $office;
            });

        $asesoresList = User::where('role', 'asesor')
            ->where('is_active', true)
            ->with(['office', 'team'])
            ->get();

        $asesoresRanked = $asesoresList->map(function ($asesor) use ($reportsByUser) {
            $reports = $reportsByUser->get($asesor->id, collect());
            $totalCaptures = $reports->sum('sign_captures') + $reports->sum('exclusive_captures');

            return (object) [
                'id' => $asesor->id,
                'name' => $asesor->name,
                'office_name' => $asesor->office ? $asesor->office->name : 'N/A',
                'team_name' => $asesor->team ? $asesor->team->name : 'N/A',
                'visits' => $reports->sum('visits'),
                'sign_captures' => $reports->sum('sign_captures'),
                'exclusive_captures' => $reports->sum('exclusive_captures'),
                'total_captures' => $totalCaptures,
                'closings' => $reports->sum('closings'),
                'calls_made' => $reports->sum('calls_made'),
                'properties_in_system' => $reports->sum('properties_in_system'),
            ];
        });

        $rankings = [
            'visits' => $asesoresRanked->sortByDesc('visits')->values(),
            'sign_captures' => $asesoresRanked->sortByDesc('sign_captures')->values(),
            'exclusive_captures' => $asesoresRanked->sortByDesc('exclusive_captures')->values(),
            'closings' => $asesoresRanked->sortByDesc('closings')->values(),
            'calls_made' => $asesoresRanked->sortByDesc('calls_made')->values(),
            'properties_in_system' => $asesoresRanked->sortByDesc('properties_in_system')->values(),
        ];

        $candidatosTeamLeader = $asesoresRanked->filter(function ($a) {
            return $a->closings >= 2 && $a->total_captures >= 3 && $a->visits >= 5;
        })->sortByDesc('closings')->values();

        return [
            'totalAsesores' => $totalAsesores,
            'totalTeamLeaders' => $totalTeamLeaders,
            'totalOffices' => $totalOffices,
            'reportesToday' => $reportsCount,
            'asesoresSinReporte' => $asesoresSinReporte,
            'weeklyGlobal' => $weeklyGlobal,
            'offices' => $offices,
            'rankings' => $rankings,
            'candidatosTeamLeader' => $candidatosTeamLeader,
        ];
    }

    public function index(Request $request)
    {
        list($startDate, $endDate, $filterType, $today) = $this->getDates($request);
        $data = $this->getDashboardData($startDate, $endDate);

        return view('dashboards.director', array_merge($data, [
            'today' => $today,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'filterType' => $filterType,
        ]));
    }

    public function oficinas(Request $request)
    {
        list($startDate, $endDate, $filterType, $today) = $this->getDates($request);
        $data = $this->getDashboardData($startDate, $endDate);

        return view('director.oficinas', [
            'offices' => $data['offices'],
            'today' => $today,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'filterType' => $filterType,
        ]);
    }

    public function perfilado(Request $request)
    {
        list($startDate, $endDate, $filterType, $today) = $this->getDates($request);
        $data = $this->getDashboardData($startDate, $endDate);

        return view('director.perfilado', [
            'candidatosTeamLeader' => $data['candidatosTeamLeader'],
            'today' => $today,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'filterType' => $filterType,
        ]);
    }

    public function ranking(Request $request)
    {
        list($startDate, $endDate, $filterType, $today) = $this->getDates($request);
        $data = $this->getDashboardData($startDate, $endDate);

        return view('director.ranking', [
            'rankings' => $data['rankings'],
            'today' => $today,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'filterType' => $filterType,
        ]);
    }

    public function exportCsv(Request $request)
    {
        $startDate = Carbon::parse($request->get('start_date', Carbon::today()->startOfWeek()))->startOfDay();
        $endDate = Carbon::parse($request->get('end_date', Carbon::today()->endOfWeek()))->endOfDay();

        $reports = DailyReport::with(['user.office', 'user.team'])
            ->whereBetween('report_date', [$startDate, $endDate])
            ->orderBy('report_date', 'desc')
            ->get();

        $response = new StreamedResponse(function () use ($reports) {
            $handle = fopen('php://output', 'w');
            
            // UTF-8 BOM para que Excel detecte acentos correctamente
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Encabezados detallados para el Director (acceso global)
            fputcsv($handle, [
                'Fecha', 'Asesor', 'Oficina', 'Equipo', 
                'Nombre del Cliente', 'Celular del Cliente', 
                'Dirección / Ubicación', 'Latitud', 'Longitud', 'Enlace Google Maps',
                'Foto Respaldo Letrero',
                'Otros KPIs del día (Letreros | Exclusivas | Cierres | Llamadas | Sistemas)'
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
                            $report->user->office ? $report->user->office->name : 'N/A',
                            $report->user->team ? $report->user->team->name : 'N/A',
                            $v['client_name'] ?? 'N/A',
                            $v['client_phone'] ?? 'N/A',
                            $v['address'] ?? 'N/A',
                            $lat,
                            $lng,
                            $mapLink,
                            $photoUrl,
                            "Letreros: {$report->sign_captures} | Exclusivas: {$report->exclusive_captures} | Cierres: {$report->closings} | Llamadas: {$report->calls_made} | Sistemas: {$report->properties_in_system}"
                        ], ';');
                    }
                } else {
                    // Fallback para reportes sin visitas detalladas
                    $visitsCount = $report->visits;
                    if ($visitsCount > 0) {
                        for ($i = 1; $i <= $visitsCount; $i++) {
                            fputcsv($handle, [
                                $report->report_date->format('Y-m-d'),
                                $report->user->name,
                                $report->user->office ? $report->user->office->name : 'N/A',
                                $report->user->team ? $report->user->team->name : 'N/A',
                                'Cliente Histórico ' . $i,
                                'N/A',
                                'Visita sin coordenadas GPS',
                                '',
                                '',
                                'N/A',
                                $photoUrl,
                                "Letreros: {$report->sign_captures} | Exclusivas: {$report->exclusive_captures} | Cierres: {$report->closings} | Llamadas: {$report->calls_made} | Sistemas: {$report->properties_in_system}"
                            ], ';');
                        }
                    } else {
                        // Reportes con 0 visitas (otros KPIs de hoy)
                        fputcsv($handle, [
                            $report->report_date->format('Y-m-d'),
                            $report->user->name,
                            $report->user->office ? $report->user->office->name : 'N/A',
                            $report->user->team ? $report->user->team->name : 'N/A',
                            'N/A (Sin visitas)',
                            'N/A',
                            'N/A',
                            '',
                            '',
                            'N/A',
                            $photoUrl,
                            "Letreros: {$report->sign_captures} | Exclusivas: {$report->exclusive_captures} | Cierres: {$report->closings} | Llamadas: {$report->calls_made} | Sistemas: {$report->properties_in_system}"
                        ], ';');
                    }
                }
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="alfa-kpi-report-' . $startDate->format('Ymd') . '-to-' . $endDate->format('Ymd') . '.csv"',
        ]);

        return $response;
    }

    /**
     * Mostrar mapa de visitas global con geolocalización para el Director.
     */
    public function visitsMap(Request $request)
    {
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

        // 2. Filtros de Entidades
        $officeId = $request->get('office_id');
        $teamId = $request->get('team_id');
        $asesorId = $request->get('user_id');

        // Obtener todos los datos para los dropdowns de filtros
        $allOffices = Office::orderBy('name')->get();
        $allTeams = Team::orderBy('name')->get();
        
        $allAsesoresQuery = User::where('role', 'asesor')->where('is_active', true);
        if ($officeId) {
            $allAsesoresQuery->where('office_id', $officeId);
        }
        if ($teamId) {
            $allAsesoresQuery->where('team_id', $teamId);
        }
        $asesoresForFilter = $allAsesoresQuery->orderBy('name')->get();

        // 3. Obtener asesores filtrados para consultar visitas
        $asesoresQuery = User::where('role', 'asesor')->where('is_active', true);
        if ($officeId) {
            $asesoresQuery->where('office_id', $officeId);
        }
        if ($teamId) {
            $asesoresQuery->where('team_id', $teamId);
        }
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

        return view('director.visits-map', compact(
            'startDate', 'endDate', 'filterType', 'totalVisits', 'geoVisits',
            'uniqueClients', 'asesoresCount', 'mapMarkers',
            'allVisitDetails', 'asesorColors',
            'allOffices', 'allTeams', 'asesoresForFilter',
            'officeId', 'teamId', 'asesorId'
        ));
    }

    /**
     * Vista de supervisión de reportes, fotos y ubicaciones para el Director.
     */
    public function reportsIndex(Request $request)
    {
        $asesorId = $request->get('user_id');
        $startDateInput = $request->get('start_date');
        $endDateInput = $request->get('end_date');

        $today = Carbon::today();
        $startDate = $startDateInput ? Carbon::parse($startDateInput)->startOfDay() : $today->copy()->startOfWeek(Carbon::MONDAY);
        $endDate = $endDateInput ? Carbon::parse($endDateInput)->endOfDay() : $today->copy()->endOfWeek(Carbon::SUNDAY);

        // Todos los asesores para el filtro
        $asesores = User::where('role', 'asesor')->where('is_active', true)->orderBy('name')->get();

        // Consulta
        $query = DailyReport::with(['user.office', 'user.team'])
            ->whereBetween('report_date', [$startDate, $endDate]);

        if ($asesorId) {
            $query->where('user_id', $asesorId);
        }

        // Paginado de 10 reportes para evitar sobrecarga del servidor
        $reports = $query->orderBy('report_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('director.reports-index', compact('reports', 'asesores', 'asesorId', 'startDate', 'endDate'));
    }
}
