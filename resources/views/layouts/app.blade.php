<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <!-- ApexCharts CDN for Premium Interactive Visualizations -->
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

        <style>
            [x-cloak] { display: none !important; }
        </style>
    </head>
    <body class="font-sans antialiased text-gray-800 dark:text-gray-200">
        @php
            $role = Auth::check() ? Auth::user()->role : null;
            $todayReport = null;
            $reportRoute = 'login';
            $isActiveReport = false;
            if (Auth::check() && $role === 'asesor') {
                $todayReport = \App\Models\DailyReport::where('user_id', Auth::id())
                    ->where('report_date', \Carbon\Carbon::today())
                    ->first();
                $reportRoute = $todayReport ? 'asesor.report.edit' : 'asesor.report.create';
                $isActiveReport = request()->routeIs('asesor.report.create') || request()->routeIs('asesor.report.edit');
            }
        @endphp

        <div class="min-h-screen flex bg-gray-100 dark:bg-[#0d0e15]" x-data="{ sidebarOpen: false }">
            <!-- BACKDROP PARA MÓVIL (Se muestra cuando el sidebar está abierto en pantallas pequeñas) -->
            <div x-cloak x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-black/60 z-40 md:hidden transition-opacity duration-300"></div>

            <!-- SIDEBAR UNIFICADO (DESKTOP & MOBILE) -->
            @if(Auth::check())
                <aside x-cloak :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed md:sticky top-0 left-0 z-50 md:z-auto flex flex-col w-64 bg-[#11131c] border-r border-gray-800/20 h-screen p-5 shrink-0 select-none text-gray-400 transition-transform duration-300 md:translate-x-0 md:flex">
                    <!-- Sidebar Header / Logo -->
                    <div class="flex items-center gap-2.5 mb-8 px-2">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                            <span class="text-white text-base">☋</span>
                        </div>
                        <span class="font-bold text-white text-sm tracking-wide">Alfa Business</span>
                    </div>

                    <!-- Sidebar Scroll Container -->
                    <div class="flex-1 overflow-y-auto space-y-6 pr-1 custom-scrollbar">
                        @if($role === 'asesor')
                            <!-- SECCIÓN GENERAL (ASESOR) -->
                            <div class="space-y-1.5">
                                <span class="block px-3 text-[10px] font-bold text-gray-500 uppercase tracking-widest">General</span>
                                <a href="{{ route('asesor.dashboard') }}" 
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all hover:text-white hover:bg-white/5 {{ request()->routeIs('asesor.dashboard') && !request()->routeIs('asesor.visits-map') ? 'bg-white/10 text-white shadow-sm' : '' }}">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                    Mi Dashboard
                                </a>
                            </div>

                            <!-- SECCIÓN REPORTES (ASESOR) -->
                            <div class="space-y-1.5 pt-2">
                                <span class="block px-3 text-[10px] font-bold text-gray-500 uppercase tracking-widest">Reportes</span>
                                <a href="{{ route($reportRoute) }}" 
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all hover:text-white hover:bg-white/5 {{ $isActiveReport ? 'bg-white/10 text-white shadow-sm' : '' }}">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    {{ $todayReport ? 'Actualizar reporte' : 'Enviar reporte' }}
                                </a>
                                <a href="{{ route('asesor.history') }}" 
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all hover:text-white hover:bg-white/5 {{ request()->routeIs('asesor.history') ? 'bg-white/10 text-white shadow-sm' : '' }}">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Mi historial
                                </a>
                            </div>

                            <!-- SECCIÓN DESEMPEÑO (ASESOR) -->
                            <div class="space-y-1.5 pt-2">
                                <span class="block px-3 text-[10px] font-bold text-gray-500 uppercase tracking-widest">Desempeño</span>
                                <a href="{{ route('asesor.ranking') }}" 
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all hover:text-white hover:bg-white/5 {{ request()->routeIs('asesor.ranking') ? 'bg-white/10 text-white shadow-sm' : '' }}">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                                    Ranking
                                </a>
                            </div>

                            <!-- SECCIÓN EVIDENCIAS (ASESOR) -->
                            <div class="space-y-1.5 pt-2">
                                <span class="block px-3 text-[10px] font-bold text-gray-500 uppercase tracking-widest">Evidencias</span>
                                <a href="{{ route('asesor.visits-map') }}" 
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all hover:text-white hover:bg-white/5 {{ request()->routeIs('asesor.visits-map') ? 'bg-white/10 text-white shadow-sm' : '' }}">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                                    Mis visitas en mapa
                                </a>
                            </div>

                        @elseif($role === 'team_leader')
                            <!-- SECCIÓN MONITOREO (TEAM LEADER) -->
                            <div class="space-y-1.5">
                                <span class="block px-3 text-[10px] font-bold text-gray-500 uppercase tracking-widest">Monitoreo</span>
                                <a href="{{ route('team-leader.dashboard') }}" 
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all hover:text-white hover:bg-white/5 {{ request()->routeIs('team-leader.dashboard') ? 'bg-white/10 text-white shadow-sm' : '' }}">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                    Dashboard equipo
                                </a>
                                <a href="{{ route('team-leader.asesores') }}" 
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all hover:text-white hover:bg-white/5 {{ request()->routeIs('team-leader.asesores') || request()->routeIs('team-leader.asesor-detail') ? 'bg-white/10 text-white shadow-sm' : '' }}">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    Mis asesores
                                </a>
                                <a href="{{ route('team-leader.semaforos') }}" 
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all hover:text-white hover:bg-white/5 {{ request()->routeIs('team-leader.semaforos') ? 'bg-white/10 text-white shadow-sm' : '' }}">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                    Semáforos
                                </a>
                                <a href="{{ route('team-leader.ranking') }}" 
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all hover:text-white hover:bg-white/5 {{ request()->routeIs('team-leader.ranking') ? 'bg-white/10 text-white shadow-sm' : '' }}">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                                    Ranking
                                </a>
                                <a href="{{ route('team-leader.alertas') }}" 
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all hover:text-white hover:bg-white/5 {{ request()->routeIs('team-leader.alertas') ? 'bg-white/10 text-white shadow-sm' : '' }}">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                    Alertas
                                </a>
                            </div>

                            <!-- SECCIÓN REPORTES (TEAM LEADER) -->
                            <div class="space-y-1.5 pt-2">
                                <span class="block px-3 text-[10px] font-bold text-gray-500 uppercase tracking-widest">Reportes</span>
                                <a href="{{ route('team-leader.historial') }}" 
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all hover:text-white hover:bg-white/5 {{ request()->routeIs('team-leader.historial') ? 'bg-white/10 text-white shadow-sm' : '' }}">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Historial diario
                                </a>
                                <a href="{{ route('team-leader.acumulado') }}" 
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all hover:text-white hover:bg-white/5 {{ request()->routeIs('team-leader.acumulado') ? 'bg-white/10 text-white shadow-sm' : '' }}">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    Acumulado semanal
                                </a>
                            </div>

                            <!-- SECCIÓN NUEVO (TEAM LEADER) -->
                            <div class="space-y-1.5 pt-2">
                                <span class="block px-3 text-[10px] font-bold text-gray-500 uppercase tracking-widest">Nuevo</span>
                                <a href="{{ route('team-leader.visits-map') }}" 
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all hover:text-white hover:bg-white/5 {{ request()->routeIs('team-leader.visits-map') ? 'bg-white/10 text-white shadow-sm' : '' }}">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                                    Mapa de visitas
                                </a>
                                <a href="{{ route('team-leader.letreros') }}" 
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all hover:text-white hover:bg-white/5 {{ request()->routeIs('team-leader.letreros') ? 'bg-white/10 text-white shadow-sm' : '' }}">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    Fotos de letreros
                                </a>
                            </div>
                        @elseif($role === 'director')
                            <!-- SECCIÓN GENERAL (DIRECTOR) -->
                            <div class="space-y-1.5">
                                <span class="block px-3 text-[10px] font-bold text-gray-500 uppercase tracking-widest">General</span>
                                <a href="{{ route('director.dashboard') }}" 
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all hover:text-white hover:bg-white/5 {{ request()->routeIs('director.dashboard') ? 'bg-white/10 text-white shadow-sm' : '' }}">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                    Panel directivo
                                </a>
                                <a href="{{ route('director.oficinas') }}" 
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all hover:text-white hover:bg-white/5 {{ request()->routeIs('director.oficinas') ? 'bg-white/10 text-white shadow-sm' : '' }}">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    Por oficina
                                </a>
                            </div>

                            <!-- SECCIÓN ASESORES (DIRECTOR) -->
                            <div class="space-y-1.5 pt-2">
                                <span class="block px-3 text-[10px] font-bold text-gray-500 uppercase tracking-widest">Asesores</span>
                                <a href="{{ route('director.perfilado') }}" 
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all hover:text-white hover:bg-white/5 {{ request()->routeIs('director.perfilado') ? 'bg-white/10 text-white shadow-sm' : '' }}">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    Perfilado asesores
                                </a>
                                <a href="{{ route('director.ranking') }}" 
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all hover:text-white hover:bg-white/5 {{ request()->routeIs('director.ranking') ? 'bg-white/10 text-white shadow-sm' : '' }}">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                                    Ranking global
                                </a>
                            </div>

                            <!-- SECCIÓN EVIDENCIAS (DIRECTOR) -->
                            <div class="space-y-1.5 pt-2">
                                <span class="block px-3 text-[10px] font-bold text-gray-500 uppercase tracking-widest">Evidencias</span>
                                <a href="{{ route('director.visits-map') }}" 
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all hover:text-white hover:bg-white/5 {{ request()->routeIs('director.visits-map') ? 'bg-white/10 text-white shadow-sm' : '' }}">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724v-10.764a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                                    Mapa de visitas
                                </a>
                                <a href="{{ route('director.reports.index') }}" 
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all hover:text-white hover:bg-white/5 {{ request()->routeIs('director.reports.index') ? 'bg-white/10 text-white shadow-sm' : '' }}">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    Fotos de letreros
                                </a>
                            </div>

                            <!-- SECCIÓN ADMINISTRACIÓN (DIRECTOR) -->
                            <div class="space-y-1.5 pt-2">
                                <span class="block px-3 text-[10px] font-bold text-gray-500 uppercase tracking-widest">Administración</span>
                                <a href="{{ route('director.offices.index') }}" 
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all hover:text-white hover:bg-white/5 {{ request()->routeIs('director.offices.index') ? 'bg-white/10 text-white shadow-sm' : '' }}">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    Gestión oficinas
                                </a>
                                <a href="{{ route('director.teams.index') }}" 
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all hover:text-white hover:bg-white/5 {{ request()->routeIs('director.teams.index') ? 'bg-white/10 text-white shadow-sm' : '' }}">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    Gestión equipos
                                </a>
                                <a href="{{ route('director.users.index') }}" 
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all hover:text-white hover:bg-white/5 {{ request()->routeIs('director.users.index') ? 'bg-white/10 text-white shadow-sm' : '' }}">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                    Gestión usuarios
                                </a>
                                <a href="{{ route('director.kpis.index') }}" 
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all hover:text-white hover:bg-white/5 {{ request()->routeIs('director.kpis.index') ? 'bg-white/10 text-white shadow-sm' : '' }}">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    Configurar KPIs
                                </a>
                            </div>

                            <!-- SECCIÓN SISTEMA (DIRECTOR) -->
                            <div class="space-y-1.5 pt-2">
                                <span class="block px-3 text-[10px] font-bold text-gray-500 uppercase tracking-widest">Sistema</span>
                                <a href="{{ route('profile.edit') }}" 
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all hover:text-white hover:bg-white/5 {{ request()->routeIs('profile.edit') ? 'bg-white/10 text-white shadow-sm' : '' }}">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    Configuración
                                </a>
                            </div>
                        @endif
                    </div>
                </aside>
            @endif

            <!-- CONTENEDOR DE CONTENIDO PRINCIPAL -->
            <div class="flex-1 flex flex-col min-w-0">
                @include('layouts.navigation')

                <!-- Page Heading -->
                @isset($header)
                    <header class="bg-white dark:bg-[#11131c] print:hidden">
                        <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <!-- Page Content -->
                <main class="flex-1">
                    {{ $slot }}
                </main>

                <!-- Footer -->
                <footer class="bg-white dark:bg-[#11131c] border-t border-gray-200 dark:border-gray-800/60 py-8 mt-12 print:hidden text-gray-500">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 pb-8 border-b border-gray-200 dark:border-gray-800/60 text-xs">
                            <!-- Enlaces Rápidos -->
                            <div>
                                <h4 class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-3">Enlaces del Sistema</h4>
                                @auth
                                    @php
                                        $dashRoute = match($role) {
                                            'director' => 'director.dashboard',
                                            'team_leader' => 'team-leader.dashboard',
                                            'asesor' => 'asesor.dashboard',
                                            default => 'login',
                                        };
                                    @endphp
                                    <ul class="space-y-2">
                                        <li>
                                            <a href="{{ route($dashRoute) }}" class="hover:text-blue-500 transition-colors">📊 Dashboard Principal</a>
                                        </li>
                                        @if($role === 'asesor')
                                            <li>
                                                <a href="{{ route($reportRoute) }}" class="hover:text-blue-500 transition-colors">📝 {{ $todayReport ? 'Actualizar Reporte' : 'Enviar Reporte' }}</a>
                                            </li>
                                        @endif
                                        <li>
                                            <a href="{{ route('profile.edit') }}" class="hover:text-blue-500 transition-colors">👤 Mi Perfil</a>
                                        </li>
                                    </ul>
                                @endauth
                            </div>

                            <!-- Soporte y Legal -->
                            <div>
                                <h4 class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-3">Soporte y Legalidad</h4>
                                <ul class="space-y-2">
                                    <li>
                                        <a href="{{ route('privacy-policy') }}" class="hover:text-blue-500 transition-colors">🔒 Política de Privacidad</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('terms') }}" class="hover:text-blue-500 transition-colors">📄 Términos y Condiciones</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('faq') }}" class="hover:text-blue-500 transition-colors">❓ Preguntas Frecuentes</a>
                                    </li>
                                </ul>
                            </div>

                            <!-- Soporte Técnico -->
                            <div>
                                <h4 class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-3">Soporte Técnico</h4>
                                <p class="leading-relaxed mb-3">
                                    Si experimentas inconvenientes técnicos o necesitas soporte con el mapa de geolocalización o la carga de imágenes, contacta al administrador.
                                </p>
                                <p>📧 <strong>Email:</strong> soporte@alfabolivia.com</p>
                            </div>
                        </div>

                        <!-- Fila inferior: Derechos de autor -->
                        <div class="flex flex-col sm:flex-row items-center justify-between pt-6 text-[10px]">
                            <p>© {{ date('Y') }} Alfa Business Group. Todos los derechos reservados.</p>
                            <p class="mt-2 sm:mt-0">Sistema de Reporte Diario v2.5 · Optimizado para Bolivia</p>
                        </div>
                    </div>
                </footer>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- WIDGET DE CHATBOT FLOTANTE CON IA (ALFA AI) -->
        <!-- ========================================== -->
        @auth
            <!-- Botón Flotante Circular -->
            <button id="chatbotToggleBtn" onclick="toggleChatbot()" class="fixed bottom-6 right-6 z-50 w-14 h-14 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-600 text-white shadow-2xl hover:scale-110 active:scale-95 transition-all duration-300 flex items-center justify-center cursor-pointer group print:hidden">
                <span class="text-2xl group-hover:rotate-12 transition-transform duration-300">🤖</span>
                <span class="absolute -top-1 -right-1 flex h-4 w-4">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-4 w-4 bg-green-500"></span>
                </span>
            </button>

            <!-- Burbuja de Chat Flotante -->
            <div id="chatbotContainer" class="fixed bottom-24 right-6 w-[380px] h-[520px] max-w-[calc(100vw-2rem)] max-h-[calc(100vh-12rem)] bg-white dark:bg-[#11131c] rounded-3xl shadow-2xl border border-gray-200 dark:border-gray-800 z-50 flex flex-col overflow-hidden hidden transition-all duration-300 transform scale-95 opacity-0 origin-bottom-right print:hidden">
                <!-- Cabecera del Chatbot -->
                <div class="p-4 bg-gradient-to-r from-indigo-500 to-purple-600 text-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center text-xl font-bold">
                            🤖
                        </div>
                        <div>
                            <h4 class="font-bold text-sm tracking-wide">Alfa AI</h4>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                                <span class="text-xs text-indigo-100 font-semibold">En línea · Asistente Inmobiliario</span>
                            </div>
                        </div>
                    </div>
                    <button onclick="toggleChatbot()" class="w-8 h-8 rounded-full hover:bg-white/10 flex items-center justify-center transition-colors text-white/80 hover:text-white">
                        ✕
                    </button>
                </div>

                <!-- Historial de Conversación -->
                <div id="chatbotMessages" class="flex-1 p-4 overflow-y-auto space-y-3 bg-gray-50 dark:bg-gray-900/30">
                    <div class="flex gap-2.5 max-w-[85%]">
                        <div class="w-7 h-7 rounded-full bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center text-sm shrink-0">
                            🤖
                        </div>
                        <div class="p-3 bg-white dark:bg-[#181a26] border border-gray-150 dark:border-gray-800 rounded-2xl rounded-tl-none shadow-sm">
                            <p class="text-xs text-gray-800 dark:text-gray-200 leading-relaxed">
                                ¡Hola <strong>{{ Auth::user()->name }}</strong>! 👋 Soy tu asistente inteligente Alfa AI. 
                            </p>
                            @if($role === 'asesor')
                                <p class="text-xs text-gray-800 dark:text-gray-200 leading-relaxed mt-1.5">
                                    ¿Quieres saber cuántas captaciones te faltan esta semana, o prefieres reportar tu actividad hablándome por nota de voz? 🎙️
                                </p>
                            @elseif($role === 'team_leader')
                                <p class="text-xs text-gray-800 dark:text-gray-200 leading-relaxed mt-1.5">
                                    Puedo decirte quién es el asesor estrella de tu equipo, quién falta reportar hoy o darte estadísticas rápidas.
                                </p>
                            @else
                                <p class="text-xs text-gray-800 dark:text-gray-200 leading-relaxed mt-1.5">
                                    Consúltame el desempeño de las oficinas de Bolivia, rankings nacionales o acumulados corporativos globales.
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Campo de Entrada y Acciones -->
                <div class="p-3 border-t border-gray-200 dark:border-gray-850 bg-white dark:bg-[#11131c] flex items-center gap-2">
                    <button id="chatbotVoiceBtn" onclick="startSpeechRecognition()" class="w-10 h-10 rounded-xl bg-gray-50 dark:bg-gray-800 hover:bg-red-50 dark:hover:bg-red-950/25 text-gray-500 hover:text-red-500 flex items-center justify-center transition-all cursor-pointer border border-gray-200 dark:border-gray-750 active:scale-95 shrink-0" title="Dictar por Voz">
                        <span class="text-lg" id="voiceBtnIcon">🎙️</span>
                    </button>

                    <input type="text" id="chatbotInput" placeholder="Escribe o dicta un mensaje..." class="flex-1 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-800 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500" onkeydown="handleChatbotKey(event)">

                    <button onclick="sendChatbotMessage()" class="w-10 h-10 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white flex items-center justify-center transition-all cursor-pointer border border-indigo-700 shadow-md shadow-indigo-600/20 active:scale-95 shrink-0" title="Enviar Mensaje">
                        <span class="text-lg">➔</span>
                    </button>
                </div>
            </div>

            <script>
                function toggleChatbot() {
                    const container = document.getElementById('chatbotContainer');
                    if (container.classList.contains('hidden')) {
                        container.classList.remove('hidden');
                        setTimeout(() => {
                            container.classList.remove('scale-95', 'opacity-0');
                            container.classList.add('scale-100', 'opacity-100');
                        }, 50);
                    } else {
                        container.classList.remove('scale-100', 'opacity-100');
                        container.classList.add('scale-95', 'opacity-0');
                        setTimeout(() => {
                            container.classList.add('hidden');
                        }, 300);
                    }
                }

                function handleChatbotKey(event) {
                    if (event.key === 'Enter') {
                        sendChatbotMessage();
                    }
                }

                function sendChatbotMessage() {
                    const input = document.getElementById('chatbotInput');
                    const text = input.value.trim();
                    if (!text) return;

                    input.value = '';
                    appendMessage('user', text);

                    const loadingId = appendMessage('bot', '<span class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-indigo-600 animate-bounce"></span><span class="w-1.5 h-1.5 rounded-full bg-indigo-600 animate-bounce" style="animation-delay: 0.2s"></span><span class="w-1.5 h-1.5 rounded-full bg-indigo-600 animate-bounce" style="animation-delay: 0.4s"></span></span>');

                    fetch("{{ route('chatbot.message') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ message: text })
                    })
                    .then(response => response.json())
                    .then(data => {
                        removeMessage(loadingId);
                        if (data.success) {
                            appendMessage('bot', data.message);
                        } else {
                            appendMessage('bot', '❌ Lo siento, hubo un error de conexión con la IA. Por favor, intenta de nuevo.');
                        }
                    })
                    .catch(error => {
                        removeMessage(loadingId);
                        appendMessage('bot', '❌ Error de red: No se pudo conectar con el servidor.');
                    });
                }

                function appendMessage(sender, content) {
                    const messagesContainer = document.getElementById('chatbotMessages');
                    const messageId = 'msg-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
                    
                    const isUser = (sender === 'user');
                    const bubbleClass = isUser 
                        ? 'p-3 bg-indigo-600 text-white rounded-2xl rounded-tr-none shadow-sm text-xs leading-relaxed'
                        : 'p-3 bg-white dark:bg-[#181a26] border border-gray-150 dark:border-gray-800 rounded-2xl rounded-tl-none shadow-sm text-xs text-gray-800 dark:text-gray-200 leading-relaxed';

                    const alignClass = isUser ? 'justify-end' : 'justify-start';
                    const avatarMarkup = isUser 
                        ? '' 
                        : '<div class="w-7 h-7 rounded-full bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center text-sm shrink-0">🤖</div>';

                    const html = `
                        <div id="${messageId}" class="flex gap-2.5 max-w-[85%] ${alignClass} ${isUser ? 'ml-auto' : ''}">
                            ${avatarMarkup}
                            <div class="${bubbleClass}">
                                ${content.replace(/\n/g, '<br>')}
                            </div>
                        </div>
                    `;

                    messagesContainer.insertAdjacentHTML('beforeend', html);
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                    return messageId;
                }

                function removeMessage(id) {
                    const el = document.getElementById(id);
                    if (el) el.remove();
                }

                let recognition;
                let isRecording = false;

                function startSpeechRecognition() {
                    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
                    if (!SpeechRecognition) {
                        alert("⚠️ Tu navegador no soporta el dictado por voz de forma nativa. Te recomiendo usar Google Chrome, Microsoft Edge o Safari.");
                        return;
                    }

                    const voiceBtn = document.getElementById('chatbotVoiceBtn');
                    const voiceIcon = document.getElementById('voiceBtnIcon');

                    if (isRecording) {
                        recognition.stop();
                        return;
                    }

                    recognition = new SpeechRecognition();
                    recognition.lang = 'es-BO';
                    recognition.interimResults = false;
                    recognition.maxAlternatives = 1;

                    recognition.onstart = function() {
                        isRecording = true;
                        voiceBtn.classList.remove('bg-gray-50', 'dark:bg-gray-800');
                        voiceBtn.classList.add('bg-red-500', 'text-white', 'animate-pulse');
                        voiceIcon.textContent = '🛑';
                    };

                    recognition.onend = function() {
                        isRecording = false;
                        voiceBtn.classList.add('bg-gray-50', 'dark:bg-gray-800');
                        voiceBtn.classList.remove('bg-red-500', 'text-white', 'animate-pulse');
                        voiceIcon.textContent = '🎙️';
                    };

                    recognition.onerror = function(event) {
                        console.error("Speech Recognition Error:", event.error);
                    };

                    recognition.onresult = function(event) {
                        const transcript = event.results[0][0].transcript;
                        const input = document.getElementById('chatbotInput');
                        input.value = transcript;
                        
                        setTimeout(() => {
                            sendChatbotMessage();
                        }, 500);
                    };

                    recognition.start();
                }

                setInterval(function() {
                    fetch("{{ route('ping') }}", {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Heartbeat: sesión activa.');
                    })
                    .catch(error => {
                        console.log('Heartbeat: error de conexión.');
                    });
                }, 15 * 60 * 1000);
            </script>
        @endauth

        <style>
            .custom-scrollbar::-webkit-scrollbar {
                width: 4px;
            }
            .custom-scrollbar::-webkit-scrollbar-track {
                background: transparent;
            }
            .custom-scrollbar::-webkit-scrollbar-thumb {
                background: rgba(255, 255, 255, 0.1);
                border-radius: 99px;
            }
            .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                background: rgba(255, 255, 255, 0.2);
            }
        </style>
    </body>
</html>
