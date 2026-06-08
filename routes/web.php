<?php

use App\Http\Controllers\AsesorDashboardController;
use App\Http\Controllers\DirectorDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TeamLeaderDashboardController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Ruta raíz — redirige al login o al dashboard según el rol
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    if (Auth::check()) {
        return match (Auth::user()->role) {
            'director' => redirect()->route('director.dashboard'),
            'team_leader' => redirect()->route('team-leader.dashboard'),
            'asesor' => redirect()->route('asesor.dashboard'),
            default => redirect()->route('login'),
        };
    }
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Dashboard genérico — redirige al dashboard correcto
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    return match (Auth::user()->role) {
        'director' => redirect()->route('director.dashboard'),
        'team_leader' => redirect()->route('team-leader.dashboard'),
        'asesor' => redirect()->route('asesor.dashboard'),
        default => redirect()->route('login'),
    };
})->middleware(['auth'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Rutas del Asesor
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:asesor'])->prefix('asesor')->group(function () {
    Route::get('/dashboard', [AsesorDashboardController::class, 'index'])
        ->name('asesor.dashboard');
    Route::get('/report/create', [\App\Http\Controllers\DailyReportController::class, 'create'])
        ->name('asesor.report.create');
    Route::post('/report', [\App\Http\Controllers\DailyReportController::class, 'store'])
        ->name('asesor.report.store');
    Route::get('/report/edit', [\App\Http\Controllers\DailyReportController::class, 'edit'])
        ->name('asesor.report.edit');
    Route::post('/report/update', [\App\Http\Controllers\DailyReportController::class, 'update'])
        ->name('asesor.report.update');
    Route::get('/visits-map', [AsesorDashboardController::class, 'visitsMap'])
        ->name('asesor.visits-map');
    Route::get('/history', [AsesorDashboardController::class, 'history'])
        ->name('asesor.history');
    Route::get('/ranking', [AsesorDashboardController::class, 'ranking'])
        ->name('asesor.ranking');
});

/*
|--------------------------------------------------------------------------
| Rutas del Team Líder
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:team_leader'])->prefix('team-leader')->group(function () {
    Route::get('/dashboard', [TeamLeaderDashboardController::class, 'index'])
        ->name('team-leader.dashboard');
    Route::get('/asesor/{asesor}', [TeamLeaderDashboardController::class, 'asesorDetail'])
        ->name('team-leader.asesor-detail');
    Route::get('/visits-map', [TeamLeaderDashboardController::class, 'visitsMap'])
        ->name('team-leader.visits-map');
    Route::get('/export-visits', [TeamLeaderDashboardController::class, 'exportVisitsCsv'])
        ->name('team-leader.export-visits');
});

/*
|--------------------------------------------------------------------------
| Rutas del Director
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:director'])->prefix('director')->group(function () {
    Route::get('/dashboard', [DirectorDashboardController::class, 'index'])
        ->name('director.dashboard');
    Route::get('/visits-map', [DirectorDashboardController::class, 'visitsMap'])
        ->name('director.visits-map');
    Route::get('/reports', [DirectorDashboardController::class, 'reportsIndex'])
        ->name('director.reports.index');
    Route::get('/export', [DirectorDashboardController::class, 'exportCsv'])
        ->name('director.export');

    // Gestión de Oficinas
    Route::get('/offices', [\App\Http\Controllers\DirectorAdminController::class, 'officesIndex'])
        ->name('director.offices.index');
    Route::post('/offices', [\App\Http\Controllers\DirectorAdminController::class, 'officesStore'])
        ->name('director.offices.store');
    Route::put('/offices/{office}', [\App\Http\Controllers\DirectorAdminController::class, 'officesUpdate'])
        ->name('director.offices.update');

    // Gestión de Equipos
    Route::get('/teams', [\App\Http\Controllers\DirectorAdminController::class, 'teamsIndex'])
        ->name('director.teams.index');
    Route::post('/teams', [\App\Http\Controllers\DirectorAdminController::class, 'teamsStore'])
        ->name('director.teams.store');
    Route::put('/teams/{team}', [\App\Http\Controllers\DirectorAdminController::class, 'teamsUpdate'])
        ->name('director.teams.update');

    // Gestión de Usuarios
    Route::get('/users', [\App\Http\Controllers\DirectorAdminController::class, 'usersIndex'])
        ->name('director.users.index');
    Route::post('/users', [\App\Http\Controllers\DirectorAdminController::class, 'usersStore'])
        ->name('director.users.store');
    Route::put('/users/{user}', [\App\Http\Controllers\DirectorAdminController::class, 'usersUpdate'])
        ->name('director.users.update');

    // Configuración de KPIs
    Route::get('/kpis', [\App\Http\Controllers\DirectorAdminController::class, 'kpisIndex'])
        ->name('director.kpis.index');
    Route::put('/kpis/{kpi}', [\App\Http\Controllers\DirectorAdminController::class, 'kpisUpdate'])
        ->name('director.kpis.update');
});

/*
|--------------------------------------------------------------------------
| Perfil del usuario (todos los roles)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Chatbot de Inteligencia Artificial (Grok)
    Route::post('/chatbot/message', [\App\Http\Controllers\ChatbotController::class, 'message'])
        ->name('chatbot.message');

    // Política de Privacidad
    Route::get('/privacy-policy', function () {
        return view('privacy-policy');
    })->name('privacy-policy');

    // Términos y Condiciones
    Route::get('/terms', function () {
        return view('terms');
    })->name('terms');

    // Preguntas Frecuentes (FAQ)
    Route::get('/faq', function () {
        return view('faq');
    })->name('faq');

    // Heartbeat para mantener la sesión y el token CSRF vivos
    Route::get('/ping', function () {
        return response()->json(['status' => 'alive']);
    })->name('ping');
});

require __DIR__.'/auth.php';
