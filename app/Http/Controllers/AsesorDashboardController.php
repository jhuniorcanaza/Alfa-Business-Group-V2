<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use App\Models\KpiConfig;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AsesorDashboardController extends Controller
{
    /**
     * Dashboard principal del Asesor.
     */
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today();
        $startOfWeek = $today->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = $today->copy()->endOfWeek(Carbon::SUNDAY);

        // Reporte de hoy
        $todayReport = DailyReport::where('user_id', $user->id)
            ->where('report_date', $today)
            ->first();

        // Acumulado semanal
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

        // Semáforo de captaciones
        $kpiConfig = KpiConfig::where('indicator', 'captaciones')
            ->where('is_active', true)
            ->first();

        $trafficLight = 'green';
        $percentage = 100;
        if ($kpiConfig && $kpiConfig->weekly_goal > 0) {
            $percentage = round(($totalCaptures / $kpiConfig->weekly_goal) * 100);
            $trafficLight = $kpiConfig->getTrafficLightColor($totalCaptures);
        }

        // Historial de reportes (últimos 30 días)
        $history = DailyReport::where('user_id', $user->id)
            ->orderBy('report_date', 'desc')
            ->limit(30)
            ->get();

        // Ranking de asesores del equipo (solo lectura)
            $teamAsesores = collect();
            if ($user->team_id) {
                $teamMembers = User::where('team_id', $user->team_id)
                    ->where('role', 'asesor')
                    ->where('is_active', true)
                    ->get();

                // Obtener todos los reportes del rango para todos los miembros de una sola vez
                $memberIds = $teamMembers->pluck('id');
                $allTeamReports = DailyReport::whereIn('user_id', $memberIds)
                    ->whereBetween('report_date', [$startOfWeek, $endOfWeek])
                    ->get()
                    ->groupBy('user_id');

                $teamAsesores = $teamMembers->map(function ($member) use ($allTeamReports) {
                    $reports = $allTeamReports->get($member->id, collect());

                    return (object) [
                        'id' => $member->id,
                        'name' => $member->name,
                        'visits' => $reports->sum('visits'),
                        'sign_captures' => $reports->sum('sign_captures'),
                        'exclusive_captures' => $reports->sum('exclusive_captures'),
                        'total_captures' => $reports->sum('sign_captures') + $reports->sum('exclusive_captures'),
                        'closings' => $reports->sum('closings'),
                        'calls_made' => $reports->sum('calls_made'),
                        'properties_in_system' => $reports->sum('properties_in_system'),
                    ];
                })->sortByDesc('total_captures')->values();
            }

        // Team leader name
        $teamLeader = null;
        if ($user->team_id) {
            $teamLeader = User::where('team_id', $user->team_id)
                ->where('role', 'team_leader')
                ->first();
        }

        return view('dashboards.asesor', compact(
            'user', 'todayReport', 'weeklyReports', 'weeklyTotals',
            'totalCaptures', 'trafficLight', 'percentage', 'kpiConfig',
            'history', 'teamAsesores', 'teamLeader', 'today', 'startOfWeek', 'endOfWeek'
        ));
    }

    /**
     * Mapa de visitas del asesor (semana actual).
     */
    public function visitsMap()
    {
        $user = Auth::user();
        $today = Carbon::today();
        $startOfWeek = $today->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek   = $today->copy()->endOfWeek(Carbon::SUNDAY);

        $weeklyReports = DailyReport::where('user_id', $user->id)
            ->whereBetween('report_date', [$startOfWeek, $endOfWeek])
            ->get();

        $totalVisits    = $weeklyReports->sum('visits');
        $reportDays     = $weeklyReports->count();
        $mapMarkers     = [];
        $allVisitDetails = collect();
        $uniqueClientsSet = [];

        foreach ($weeklyReports as $report) {
            if (!$report->visits_data) continue;
            foreach ($report->visits_data as $v) {
                $detail = [
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

        $geoVisits     = count($mapMarkers);
        $uniqueClients = count($uniqueClientsSet);

        return view('asesor.visits-map', compact(
            'startOfWeek', 'endOfWeek', 'totalVisits', 'geoVisits',
            'uniqueClients', 'reportDays', 'mapMarkers', 'allVisitDetails'
        ));
    }
}

