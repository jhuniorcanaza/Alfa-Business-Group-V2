<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="bg-indigo-500/10 text-indigo-500 p-2 rounded-xl text-xl">📊</span>
                    Mi Dashboard Asesor
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Bienvenido, <strong class="text-gray-700 dark:text-gray-200">{{ $user->name }}</strong> 
                    · Team Líder: <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $teamLeader ? $teamLeader->name : 'Sin asignar' }}</span> 
                    · {{ $today->format('d/m/Y') }}
                </p>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                <a href="{{ route('asesor.visits-map') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 hover:scale-[1.02] active:scale-[0.98] transition-all shadow-lg shadow-emerald-600/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                    Mis Visitas en Mapa
                </a>
                
                @if($teamAsesores->count() > 0)
                    <a href="{{ route('asesor.ranking') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-gray-700 dark:text-gray-200 bg-white dark:bg-[#181a26] border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:scale-[1.02] active:scale-[0.98] transition-all shadow-sm">
                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                        Ver Ranking
                    </a>
                @endif

                <a href="{{ route('asesor.history') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-gray-700 dark:text-gray-200 bg-white dark:bg-[#181a26] border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:scale-[1.02] active:scale-[0.98] transition-all shadow-sm">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Ver Historial
                </a>

                @if(!$todayReport)
                    <a href="{{ route('asesor.report.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 hover:scale-[1.02] active:scale-[0.98] transition-all shadow-lg shadow-indigo-600/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Enviar Reporte de Hoy
                    </a>
                @else
                    <a href="{{ route('asesor.report.edit') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 hover:scale-[1.02] active:scale-[0.98] transition-all shadow-lg shadow-amber-600/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Actualizar Reporte de Hoy
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Alertas de éxito/error --}}
            @if(session('success'))
                <div class="p-4 rounded-xl bg-green-50 dark:bg-green-950/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 flex items-center gap-2">
                    <span class="text-lg">✅</span>
                    <span class="text-sm font-semibold">{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="p-4 rounded-xl bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 flex items-center gap-2">
                    <span class="text-lg">❌</span>
                    <span class="text-sm font-semibold">{{ session('error') }}</span>
                </div>
            @endif

            {{-- 1. SEMÁFORO DE CAPTACIONES --}}
            <div class="bg-white dark:bg-[#11131c] rounded-2xl shadow-xl border border-gray-200 dark:border-gray-800/60 p-6 transition-all duration-300 hover:shadow-indigo-500/5">
                <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">🚦 Semáforo de Captaciones — Semana {{ $startOfWeek->format('d/m') }} al {{ $endOfWeek->format('d/m') }}</h3>
                <div class="flex flex-col md:flex-row md:items-center gap-6">
                    <div class="flex-shrink-0">
                        @php
                            $colorClasses = match($trafficLight) {
                                'green' => 'bg-emerald-500 shadow-emerald-500/20 text-white',
                                'yellow' => 'bg-amber-400 shadow-amber-400/20 text-gray-950',
                                'red' => 'bg-rose-500 shadow-rose-500/20 text-white',
                                default => 'bg-gray-400 text-white',
                            };
                            $statusText = match($trafficLight) {
                                'green' => '🟢 ¡Excelente! Vas por buen camino',
                                'yellow' => '🟡 En Progreso — ¡Tú puedes!',
                                'red' => '🔴 En Alerta — ¡A darle con todo!',
                                default => '⚪ Sin datos',
                            };
                        @endphp
                        <div class="w-20 h-20 rounded-2xl {{ $colorClasses }} shadow-lg flex flex-col items-center justify-center border border-white/10">
                            <span class="text-3xl font-black">{{ $totalCaptures }}</span>
                            <span class="text-[9px] uppercase tracking-wider font-extrabold opacity-80">Capturas</span>
                        </div>
                    </div>
                    <div class="flex-1">
                        <p class="text-lg font-black text-gray-900 dark:text-white">{{ $statusText }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Llevas <span class="font-bold text-indigo-500">{{ $totalCaptures }}</span> de un objetivo semanal de <span class="font-bold text-gray-700 dark:text-gray-300">{{ $kpiConfig ? $kpiConfig->weekly_goal : '10' }}</span> captaciones ({{ $percentage }}%)
                        </p>
                        <div class="mt-4 w-full max-w-md bg-gray-100 dark:bg-gray-800 rounded-full h-3 overflow-hidden border border-gray-200/40 dark:border-gray-700/50">
                            <div class="h-full rounded-full {{ $colorClasses }} transition-all duration-700 ease-out" style="width: {{ min($percentage, 100) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. MIS 6 INDICADORES DE HOY --}}
            <div class="bg-white dark:bg-[#11131c] rounded-2xl shadow-xl border border-gray-200 dark:border-gray-800/60 p-6">
                <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">📊 Mis Indicadores de Hoy</h3>
                @if($todayReport)
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                        @php
                            $todayKpis = [
                                ['label' => 'Visitas', 'value' => $todayReport->visits, 'icon' => '🏠', 'color' => 'from-blue-500/10 to-indigo-500/10 text-blue-500 border-blue-500/20'],
                                ['label' => 'Letrero', 'value' => $todayReport->sign_captures, 'icon' => '📋', 'color' => 'from-emerald-500/10 to-teal-500/10 text-emerald-500 border-emerald-500/20'],
                                ['label' => 'Exclusiva', 'value' => $todayReport->exclusive_captures, 'icon' => '📝', 'color' => 'from-purple-500/10 to-pink-500/10 text-purple-500 border-purple-500/20'],
                                ['label' => 'Cierres', 'value' => $todayReport->closings, 'icon' => '🤝', 'color' => 'from-amber-500/10 to-orange-500/10 text-amber-500 border-amber-500/20'],
                                ['label' => 'Llamadas', 'value' => $todayReport->calls_made, 'icon' => '📞', 'color' => 'from-cyan-500/10 to-sky-500/10 text-cyan-500 border-cyan-500/20', 'sub' => $todayReport->call_phone_number],
                                ['label' => 'AlphaX', 'value' => $todayReport->properties_in_system, 'icon' => '💻', 'color' => 'from-rose-500/10 to-red-500/10 text-rose-500 border-rose-500/20'],
                            ];
                        @endphp
                        @foreach($todayKpis as $kpi)
                            <div class="bg-gradient-to-br {{ $kpi['color'] }} rounded-xl border p-4 text-center hover:scale-[1.03] transition-all duration-300">
                                <span class="text-3xl filter drop-shadow">{{ $kpi['icon'] }}</span>
                                <p class="text-3xl font-black mt-2 leading-none">{{ $kpi['value'] }}</p>
                                <p class="text-[11px] uppercase tracking-wider font-extrabold opacity-70 mt-1.5">{{ $kpi['label'] }}</p>
                                @if(isset($kpi['sub']) && $kpi['sub'])
                                    <div class="mt-2 text-[10px] bg-white/40 dark:bg-black/25 p-1 rounded font-mono truncate" title="{{ $kpi['sub'] }}">
                                        {{ $kpi['sub'] }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div class="flex items-center justify-between mt-5 pt-4 border-t border-gray-100 dark:border-gray-800/60">
                        <p class="text-xs text-gray-400">
                            Reporte diario recibido a las: <span class="font-bold text-gray-500">{{ $todayReport->created_at->format('H:i') }}</span>
                        </p>
                        <a href="{{ route('asesor.report.edit') }}" class="text-xs font-bold text-amber-500 hover:text-amber-400 transition-colors flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Editar reporte de hoy
                        </a>
                    </div>
                @else
                    <div class="bg-gradient-to-r from-amber-500/5 to-orange-500/5 border border-amber-500/20 rounded-2xl p-8 text-center">
                        <span class="text-4xl block mb-2 filter drop-shadow">⏳</span>
                        <p class="text-amber-800 dark:text-amber-300 font-extrabold text-base">Aún no registraste tu reporte de hoy</p>
                        <p class="text-gray-500 dark:text-gray-400 text-xs mt-1.5 mb-5 max-w-md mx-auto">Es sumamente importante que reportes tu actividad diaria para actualizar tu semáforo de metas y mantener la consistencia en el ranking de tu equipo.</p>
                        <a href="{{ route('asesor.report.create') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 hover:scale-[1.02] active:scale-[0.98] transition-all shadow-lg shadow-indigo-600/20">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Registrar mi reporte de hoy
                        </a>
                    </div>
                @endif
            </div>

            <!-- GRÁFICOS DE RENDIMIENTO -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Gráfico de Tendencia -->
                <div class="bg-white dark:bg-[#11131c] rounded-2xl shadow-xl border border-gray-200 dark:border-gray-800/60 p-6 lg:col-span-2">
                    <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-1">📈 Actividad Diaria por Indicador (Semana Actual)</h3>
                    <p class="text-xs text-gray-400 mb-4">Evolución visual de tus visitas, captaciones, cierres y llamadas diarias.</p>
                    <div id="asesorPerformanceTrendChart" class="w-full"></div>
                </div>

                <!-- Mezcla de Productividad -->
                <div class="bg-white dark:bg-[#11131c] rounded-2xl shadow-xl border border-gray-200 dark:border-gray-800/60 p-6">
                    <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-1">🎯 Mezcla de mi Actividad</h3>
                    <p class="text-xs text-gray-400 mb-4">Proporción general de actividades de la semana.</p>
                    <div id="asesorActivityDonutChart" class="w-full flex justify-center"></div>
                </div>
            </div>

            {{-- 3. ACUMULADO SEMANAL --}}
            <div class="bg-white dark:bg-[#11131c] rounded-2xl shadow-xl border border-gray-200 dark:border-gray-800/60 p-6">
                <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">📈 Mi Acumulado Semanal</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    @php
                        $weeklyCards = [
                            ['label' => 'Visitas', 'value' => $weeklyTotals['visits'], 'icon' => '🏠', 'bg' => 'bg-blue-500/5 dark:bg-blue-500/10 text-blue-500'],
                            ['label' => 'Letrero', 'value' => $weeklyTotals['sign_captures'], 'icon' => '📋', 'bg' => 'bg-emerald-500/5 dark:bg-emerald-500/10 text-emerald-500'],
                            ['label' => 'Exclusiva', 'value' => $weeklyTotals['exclusive_captures'], 'icon' => '📝', 'bg' => 'bg-purple-500/5 dark:bg-purple-500/10 text-purple-500'],
                            ['label' => 'Cierres', 'value' => $weeklyTotals['closings'], 'icon' => '🤝', 'bg' => 'bg-amber-500/5 dark:bg-amber-500/10 text-amber-500'],
                            ['label' => 'Llamadas', 'value' => $weeklyTotals['calls_made'], 'icon' => '📞', 'bg' => 'bg-cyan-500/5 dark:bg-cyan-500/10 text-cyan-500'],
                            ['label' => 'AlphaX', 'value' => $weeklyTotals['properties_in_system'], 'icon' => '💻', 'bg' => 'bg-rose-500/5 dark:bg-rose-500/10 text-rose-500'],
                        ];
                    @endphp
                    @foreach($weeklyCards as $card)
                        <div class="text-center p-4 rounded-xl border border-gray-100 dark:border-gray-800/60 {{ $card['bg'] }} transition-all duration-300">
                            <span class="text-2xl filter drop-shadow">{{ $card['icon'] }}</span>
                            <p class="text-2xl font-black mt-1 leading-none">{{ $card['value'] }}</p>
                            <p class="text-[10px] uppercase tracking-wider font-extrabold opacity-75 mt-1.5">{{ $card['label'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>

    @php
        $asesorTrendChartData = $weeklyReports->map(function($r) {
            return [
                'date' => $r->report_date->format('d/m'),
                'visits' => $r->visits,
                'sign' => $r->sign_captures,
                'exclusive' => $r->exclusive_captures,
                'closings' => $r->closings,
                'calls' => $r->calls_made,
                'properties' => $r->properties_in_system
            ];
        })->sortBy('date')->values();
    @endphp

    <!-- Script de Inicialización de ApexCharts para Desempeño del Asesor -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // 1. Datos semanales del asesor
            const weeklyReports = {!! json_encode($asesorTrendChartData) !!};

            const days = weeklyReports.map(r => r.date);
            const seriesVisits = weeklyReports.map(r => r.visits);
            const seriesSign = weeklyReports.map(r => r.sign);
            const seriesExclusive = weeklyReports.map(r => r.exclusive);
            const seriesClosings = weeklyReports.map(r => r.closings);
            const seriesCalls = weeklyReports.map(r => r.calls);
            const seriesProperties = weeklyReports.map(r => r.properties);

            const optionsTrend = {
                chart: {
                    type: 'bar',
                    height: 250,
                    toolbar: { show: false },
                    fontFamily: 'Figtree, sans-serif',
                    background: 'transparent'
                },
                theme: {
                    mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light'
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '55%',
                        borderRadius: 4,
                    },
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                colors: ['#3b82f6', '#10b981', '#8b5cf6', '#f59e0b', '#06b6d4', '#f43f5e'],
                series: [{
                    name: 'Visitas',
                    data: seriesVisits
                }, {
                    name: 'Letrero',
                    data: seriesSign
                }, {
                    name: 'Exclusivas',
                    data: seriesExclusive
                }, {
                    name: 'Cierres',
                    data: seriesClosings
                }, {
                    name: 'Llamadas',
                    data: seriesCalls
                }, {
                    name: 'AlphaX',
                    data: seriesProperties
                }],
                xaxis: {
                    categories: days.length > 0 ? days : ['Sin datos'],
                    labels: {
                        style: { colors: '#6b7280' }
                    }
                },
                yaxis: {
                    labels: {
                        style: { colors: '#6b7280' }
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'center',
                    labels: { colors: '#6b7280' }
                },
                tooltip: {
                    theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light'
                },
                grid: {
                    borderColor: '#37415120',
                    strokeDashArray: 4
                }
            };

            const chartTrend = new ApexCharts(document.querySelector("#asesorPerformanceTrendChart"), optionsTrend);
            chartTrend.render();

            // 2. Gráfico Donut de Mezcla de Productividad Personal
            const totalVisits = {{ $weeklyTotals['visits'] }};
            const totalSign = {{ $weeklyTotals['sign_captures'] }};
            const totalExclusive = {{ $weeklyTotals['exclusive_captures'] }};
            const totalClosings = {{ $weeklyTotals['closings'] }};
            const totalCalls = {{ $weeklyTotals['calls_made'] }};
            const totalProperties = {{ $weeklyTotals['properties_in_system'] }};

            const optionsDonut = {
                chart: {
                    type: 'donut',
                    height: 250,
                    fontFamily: 'Figtree, sans-serif',
                    background: 'transparent'
                },
                theme: {
                    mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light'
                },
                series: [totalVisits, totalSign, totalExclusive, totalClosings, totalCalls, totalProperties],
                labels: ['Visitas', 'Letrero', 'Exclusivas', 'Cierres', 'Llamadas', 'AlphaX'],
                colors: ['#3b82f6', '#10b981', '#8b5cf6', '#f59e0b', '#06b6d4', '#f43f5e'],
                legend: {
                    position: 'bottom',
                    labels: { colors: '#6b7280' }
                },
                tooltip: {
                    theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light'
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total KPIs',
                                    formatter: function (w) {
                                        return w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                                    },
                                    style: {
                                        color: '#6b7280',
                                        fontSize: '13px',
                                        fontWeight: 600
                                    }
                                }
                            }
                        }
                    }
                }
            };

            const chartDonut = new ApexCharts(document.querySelector("#asesorActivityDonutChart"), optionsDonut);
            chartDonut.render();
        });
    </script>

    <!-- Smooth Scrolling Style directly injected -->
    <style>
        html {
            scroll-behavior: smooth;
        }
    </style>
</x-app-layout>
