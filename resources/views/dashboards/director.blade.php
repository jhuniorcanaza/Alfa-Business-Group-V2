<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
                    Panel Directivo Consolidado
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                    Análisis corporativo global y métricas de desempeño
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3 print:hidden">
                <a href="{{ route('director.reports.index') }}" class="px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 transition-colors shadow-md shadow-blue-600/20 flex items-center gap-1.5">
                    📸 Evidencias y Reportes
                </a>
                <a href="{{ route('director.visits-map') }}" class="px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition-colors shadow-md shadow-emerald-600/20 flex items-center gap-1.5">
                    🗺️ Mapa de Visitas
                </a>
                <a href="{{ route('director.export', ['start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}" class="px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-green-600 hover:bg-green-700 transition-colors shadow-md shadow-green-600/20 flex items-center gap-1.5">
                    📥 Descargar Excel (CSV)
                </a>
                <button onclick="window.print()" class="px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-red-600 hover:bg-red-700 transition-colors shadow-md shadow-red-600/20 flex items-center gap-1.5">
                    📄 Exportar PDF
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- FILTRO DE FECHAS -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 print:hidden">
                <form method="GET" action="{{ route('director.dashboard') }}" class="flex flex-col lg:flex-row lg:items-end justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Rango del Reporte</label>
                            <select name="filter_type" onchange="this.form.submit()" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                                <option value="dia" {{ $filterType === 'dia' ? 'selected' : '' }}>Hoy</option>
                                <option value="semana" {{ $filterType === 'semana' ? 'selected' : '' }}>Esta Semana</option>
                                <option value="mes" {{ $filterType === 'mes' ? 'selected' : '' }}>Este Mes</option>
                                <option value="custom" {{ $filterType === 'custom' ? 'selected' : '' }}>Rango Personalizado</option>
                            </select>
                        </div>

                        @if($filterType === 'custom')
                            <div class="flex items-center gap-2">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Desde</label>
                                    <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Hasta</label>
                                    <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                                </div>
                            </div>
                        @endif
                    </div>
                    <div>
                        <button type="submit" class="w-full lg:w-auto px-5 py-2.5 rounded-lg text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                            🔍 Aplicar Filtros
                        </button>
                    </div>
                </form>
            </div>

            <!-- CARD DE MÉTRICAS GLOBALES -->
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 text-center shadow-sm">
                    <p class="text-3xl font-black text-blue-600 dark:text-blue-400">{{ $totalAsesores }}</p>
                    <p class="text-xs text-gray-500 mt-1 uppercase font-bold tracking-wider">Asesores Activos</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 text-center shadow-sm">
                    <p class="text-3xl font-black text-purple-600 dark:text-purple-400">{{ $totalTeamLeaders }}</p>
                    <p class="text-xs text-gray-500 mt-1 uppercase font-bold tracking-wider">Team Líderes</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 text-center shadow-sm">
                    <p class="text-3xl font-black text-emerald-600 dark:text-emerald-400">{{ $totalOffices }}</p>
                    <p class="text-xs text-gray-500 mt-1 uppercase font-bold tracking-wider">Oficinas</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 text-center shadow-sm">
                    <p class="text-3xl font-black text-green-600 dark:text-green-400">{{ $reportesToday }}</p>
                    <p class="text-xs text-gray-500 mt-1 uppercase font-bold tracking-wider">Reportes en Rango</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 text-center shadow-sm">
                    <p class="text-3xl font-black {{ $asesoresSinReporte > 0 ? 'text-red-500' : 'text-green-600' }}">{{ $asesoresSinReporte }}</p>
                    <p class="text-xs text-gray-500 mt-1 uppercase font-bold tracking-wider">Asesores Inactivos hoy</p>
                </div>
            </div>

            <!-- ACUMULADO GLOBAL -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4">📈 Rendimiento Global en Rango Seleccionado</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    @php
                        $indicators = [
                            ['label' => 'Visitas', 'value' => $weeklyGlobal['visits'], 'icon' => '🏠', 'color' => 'text-blue-600'],
                            ['label' => 'Capt. Letrero', 'value' => $weeklyGlobal['sign_captures'], 'icon' => '📋', 'color' => 'text-emerald-600'],
                            ['label' => 'Capt. Exclusiva', 'value' => $weeklyGlobal['exclusive_captures'], 'icon' => '📝', 'color' => 'text-purple-600'],
                            ['label' => 'Cierres', 'value' => $weeklyGlobal['closings'], 'icon' => '🤝', 'color' => 'text-amber-600'],
                            ['label' => 'Llamadas', 'value' => $weeklyGlobal['calls_made'], 'icon' => '📞', 'color' => 'text-cyan-600'],
                            ['label' => 'Sistemas', 'value' => $weeklyGlobal['properties_in_system'], 'icon' => '💻', 'color' => 'text-rose-600'],
                        ];
                    @endphp
                    @foreach($indicators as $ind)
                        <div class="text-center p-4 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                            <span class="text-2xl">{{ $ind['icon'] }}</span>
                            <p class="text-2xl font-black text-gray-800 dark:text-gray-200 mt-1">{{ $ind['value'] }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $ind['label'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- SECCIÓN GRÁFICOS INTERACTIVOS (APEXCHARTS) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Gráfico Comparativo de Equipos -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 lg:col-span-2">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-2 flex items-center gap-2">
                        <span>📊</span> Distribución de KPIs por Equipo de Trabajo
                    </h3>
                    <p class="text-xs text-gray-400 mb-4">Comparativa visual del desempeño general de captaciones y cierres por cada equipo comercial.</p>
                    <div id="teamsKpiChart" class="w-full"></div>
                </div>

                <!-- Gráfico de Donut de Distribución de KPIs Globales -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-2 flex items-center gap-2">
                        <span>🎯</span> Mezcla de Productividad Global
                    </h3>
                    <p class="text-xs text-gray-400 mb-4">Distribución porcentual de los indicadores clave alcanzados en este periodo.</p>
                    <div id="globalKpisDonutChart" class="w-full flex justify-center"></div>
                </div>
            </div>

        </div>
    </div>

    @php
        $teamsChartData = $teams->map(function($t) {
            return [
                'name' => $t->name . ($t->leader ? ' (' . $t->leader->name . ')' : ' (Sin Líd.)'),
                'visits' => $t->weekly['visits'],
                'sign_captures' => $t->weekly['sign_captures'],
                'exclusive_captures' => $t->weekly['exclusive_captures'],
                'closings' => $t->weekly['closings'],
                'calls_made' => $t->weekly['calls_made'],
                'properties_in_system' => $t->weekly['properties_in_system']
            ];
        });
    @endphp

    <!-- Script de Inicialización de ApexCharts con Datos de PHP -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // 1. Datos para el Gráfico Comparativo de Equipos
            const teamsData = {!! json_encode($teamsChartData) !!};

            const teamCategories = teamsData.map(t => t.name);
            const seriesVisits = teamsData.map(t => t.visits);
            const seriesSign = teamsData.map(t => t.sign_captures);
            const seriesExclusive = teamsData.map(t => t.exclusive_captures);
            const seriesClosings = teamsData.map(t => t.closings);
            const seriesCalls = teamsData.map(t => t.calls_made);
            const seriesProperties = teamsData.map(t => t.properties_in_system);

            const optionsTeams = {
                chart: {
                    type: 'bar',
                    height: 320,
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
                        borderRadius: 6
                    },
                },
                dataLabels: { enabled: false },
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
                    name: 'Capt. Letrero',
                    data: seriesSign
                }, {
                    name: 'Capt. Exclusiva',
                    data: seriesExclusive
                }, {
                    name: 'Cierres',
                    data: seriesClosings
                }, {
                    name: 'Llamadas',
                    data: seriesCalls
                }, {
                    name: 'Sistemas',
                    data: seriesProperties
                }],
                xaxis: {
                    categories: teamCategories,
                    labels: {
                        style: {
                            colors: '#6b7280',
                            fontSize: '11px',
                            fontWeight: 600
                        }
                    }
                },
                yaxis: {
                    title: {
                        text: 'Cantidad Acumulada',
                        style: { color: '#6b7280', fontWeight: 600 }
                    },
                    labels: {
                        style: { colors: '#6b7280' }
                    }
                },
                fill: { opacity: 1 },
                tooltip: {
                    theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
                    y: {
                        formatter: function (val) {
                            return val + " unidades"
                        }
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'center',
                    labels: { colors: '#6b7280' }
                },
                grid: {
                    borderColor: '#f1f1f1',
                    strokeDashArray: 4
                }
            };

            const chartTeams = new ApexCharts(document.querySelector("#teamsKpiChart"), optionsTeams);
            chartTeams.render();


            // 2. Datos para el Gráfico Donut de Mezcla de Productividad Global
            const globalVisits = {{ $weeklyGlobal['visits'] }};
            const globalSign = {{ $weeklyGlobal['sign_captures'] }};
            const globalExclusive = {{ $weeklyGlobal['exclusive_captures'] }};
            const globalClosings = {{ $weeklyGlobal['closings'] }};
            const globalCalls = {{ $weeklyGlobal['calls_made'] }};
            const globalProperties = {{ $weeklyGlobal['properties_in_system'] }};

            const optionsDonut = {
                chart: {
                    type: 'donut',
                    height: 320,
                    fontFamily: 'Figtree, sans-serif',
                    background: 'transparent'
                },
                theme: {
                    mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light'
                },
                series: [globalVisits, globalSign, globalExclusive, globalClosings, globalCalls, globalProperties],
                labels: ['Visitas', 'Capt. Letrero', 'Capt. Exclusiva', 'Cierres', 'Llamadas', 'Sistemas'],
                colors: ['#3b82f6', '#10b981', '#8b5cf6', '#f59e0b', '#06b6d4', '#f43f5e'],
                legend: {
                    position: 'bottom',
                    labels: { colors: '#6b7280' }
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: 280
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }],
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
                                        color: '#374151',
                                        fontSize: '16px',
                                        fontWeight: 700
                                    }
                                }
                            }
                        }
                    }
                }
            };

            const chartDonut = new ApexCharts(document.querySelector("#globalKpisDonutChart"), optionsDonut);
            chartDonut.render();
        });
    </script>
</x-app-layout>
