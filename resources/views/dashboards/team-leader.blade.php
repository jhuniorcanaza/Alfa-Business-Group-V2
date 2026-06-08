<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
                    Dashboard del Equipo: <span class="text-blue-600 dark:text-blue-400">{{ $team ? $team->name : 'Sin Equipo' }}</span>
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Team Líder: <strong>{{ $user->name }}</strong> · Oficina: {{ $team && $team->office ? $team->office->name : 'N/A' }}
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3 print:hidden">
                <a href="{{ route('team-leader.visits-map') }}" class="px-4 py-2 rounded-xl text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition-colors shadow-md shadow-emerald-600/20">
                    🗺️ Mapa de Visitas
                </a>
                <a href="{{ route('team-leader.export-visits', ['start_date' => $startOfWeek->format('Y-m-d'), 'end_date' => $endOfWeek->format('Y-m-d')]) }}" class="px-4 py-2 rounded-xl text-sm font-bold text-white bg-green-600 hover:bg-green-700 transition-colors shadow-md shadow-green-600/20 flex items-center gap-1">
                    📥 Descargar Excel (CSV)
                </a>
                <button onclick="window.print()" class="px-4 py-2 rounded-xl text-sm font-bold text-white bg-red-650 hover:bg-red-750 transition-colors shadow-md shadow-red-650/20">
                    📄 Exportar PDF
                </button>
                <span class="text-xs text-gray-400 dark:text-gray-500 font-medium">
                    Rango: {{ $startOfWeek->format('d/m/Y') }} al {{ $endOfWeek->format('d/m/Y') }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- FILTRO DE FECHAS (TEAM LEADER) -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 print:hidden">
                <form method="GET" action="{{ route('team-leader.dashboard') }}" class="flex flex-col lg:flex-row lg:items-end justify-between gap-4">
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
                                    <input type="date" name="start_date" value="{{ $startOfWeek->format('Y-m-d') }}" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Hasta</label>
                                    <input type="date" name="end_date" value="{{ $endOfWeek->format('Y-m-d') }}" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
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

            {{-- ============================================================ --}}
            {{-- 1. ALERTAS: ASESORES SIN REPORTE HOY                        --}}
            {{-- ============================================================ --}}
            @if($missingReports->count() > 0)
                <div class="bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-800/50 rounded-xl p-5 shadow-sm">
                    <div class="flex items-start gap-3">
                        <span class="text-2xl">🚨</span>
                        <div class="space-y-1">
                            <h3 class="font-bold text-red-800 dark:text-red-200">Alerta: Asesores sin reporte hoy ({{ $today->format('d/m/Y') }})</h3>
                            <p class="text-sm text-red-700 dark:text-red-300">Los siguientes asesores aún no han enviado sus KPIs:</p>
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach($missingReports as $asesor)
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300">
                                        <span>👤 {{ $asesor->name }}</span>
                                        @if($asesor->phone)
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $asesor->phone) }}?text=Hola%20{{ urlencode($asesor->name) }},%20recuerda%20enviar%20tu%20reporte%20diario." target="_blank" class="ml-1 text-green-600 dark:text-green-400 hover:underline">
                                                (Recordar 💬)
                                            </a>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-green-50 dark:bg-green-950/10 border border-green-200 dark:border-green-800/30 rounded-xl p-4 shadow-sm flex items-center gap-3">
                    <span class="text-xl">🎉</span>
                    <p class="text-green-800 dark:text-green-200 font-medium">¡Excelente! Todos los asesores enviaron su reporte hoy.</p>
                </div>
            @endif

            {{-- ============================================================ --}}
            {{-- 2. RESUMEN RÁPIDO                                           --}}
            {{-- ============================================================ --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 text-center shadow-sm">
                    <p class="text-3xl font-black text-gray-800 dark:text-gray-200">{{ $asesoresData->count() }}</p>
                    <p class="text-xs text-gray-500 mt-1 uppercase font-bold">Asesores en el equipo</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 text-center shadow-sm">
                    <p class="text-3xl font-black text-green-600">{{ $asesoresData->where('sent_today', true)->count() }}</p>
                    <p class="text-xs text-gray-500 mt-1 uppercase font-bold">Reportaron hoy</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 text-center shadow-sm">
                    <p class="text-3xl font-black text-red-500">{{ $missingReports->count() }}</p>
                    <p class="text-xs text-gray-500 mt-1 uppercase font-bold">Sin reporte</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 text-center shadow-sm">
                    <p class="text-3xl font-black text-blue-600">{{ $asesoresData->sum('reports_count') }}</p>
                    <p class="text-xs text-gray-500 mt-1 uppercase font-bold">Reportes esta semana</p>
                </div>
            </div>

            {{-- ============================================================ --}}
            {{-- 3. ACUMULADO SEMANAL DEL EQUIPO                             --}}
            {{-- ============================================================ --}}
            <div class="space-y-3">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">📈 Acumulado Semanal del Equipo</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    @php
                        $teamCards = [
                            ['icon' => '🏠', 'label' => 'Visitas', 'value' => $teamWeekly['visits'], 'color' => 'blue'],
                            ['icon' => '📋', 'label' => 'Capt. Letrero', 'value' => $teamWeekly['sign_captures'], 'color' => 'emerald'],
                            ['icon' => '📝', 'label' => 'Capt. Exclusiva', 'value' => $teamWeekly['exclusive_captures'], 'color' => 'purple'],
                            ['icon' => '🤝', 'label' => 'Cierres', 'value' => $teamWeekly['closings'], 'color' => 'amber'],
                            ['icon' => '📞', 'label' => 'Llamadas', 'value' => $teamWeekly['calls_made'], 'color' => 'cyan'],
                            ['icon' => '💻', 'label' => 'AlphaX', 'value' => $teamWeekly['properties_in_system'], 'color' => 'rose'],
                        ];
                    @endphp
                    @foreach($teamCards as $card)
                        <div class="bg-white dark:bg-gray-800 p-5 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm text-center">
                            <span class="text-2xl block mb-1">{{ $card['icon'] }}</span>
                            <span class="text-gray-500 text-xs font-semibold uppercase block">{{ $card['label'] }}</span>
                            <span class="text-2xl font-black text-{{ $card['color'] }}-600 dark:text-{{ $card['color'] }}-400">{{ $card['value'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- GRÁFICO DE RENDIMIENTO INDIVIDUAL SEMANAL -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-2 flex items-center gap-2">
                    <span>📊</span> Comparativa de Productividad Semanal por Asesor
                </h3>
                <p class="text-xs text-gray-400 mb-4">Total de visitas, llamadas y captaciones (letreros + exclusivas) acumuladas en esta semana por asesor.</p>
                <div id="teamAsesoresKpisChart" class="w-full"></div>
            </div>
        </div>
    </div>

    @php
        $advisorsChartData = $asesoresData->map(function($a) {
            return [
                'name' => $a->name,
                'visits' => $a->weekly['visits'],
                'captures' => $a->total_captures,
                'calls' => $a->weekly['calls_made']
            ];
        });
    @endphp

    <!-- Script de Inicialización de ApexCharts para Monitoreo de Asesores -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Datos semanales acumulados de los asesores del equipo
            const advisorsData = {!! json_encode($advisorsChartData) !!};

            const categories = advisorsData.map(a => a.name);
            const seriesVisits = advisorsData.map(a => a.visits);
            const seriesCaptures = advisorsData.map(a => a.captures);
            const seriesCalls = advisorsData.map(a => a.calls);

            const options = {
                chart: {
                    type: 'bar',
                    height: 350,
                    toolbar: { show: false },
                    fontFamily: 'Figtree, sans-serif',
                    background: 'transparent'
                },
                theme: {
                    mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light'
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        barHeight: '70%',
                        borderRadius: 5,
                        dataLabels: {
                            position: 'end'
                        }
                    }
                },
                colors: ['#3b82f6', '#10b981', '#06b6d4'],
                dataLabels: {
                    enabled: true,
                    style: {
                        fontSize: '11px',
                        colors: ['#fff']
                    }
                },
                stroke: {
                    show: true,
                    width: 1,
                    colors: ['transparent']
                },
                series: [{
                    name: 'Visitas',
                    data: seriesVisits
                }, {
                    name: 'Captaciones Totales',
                    data: seriesCaptures
                }, {
                    name: 'Llamadas Realizadas',
                    data: seriesCalls
                }],
                xaxis: {
                    categories: categories,
                    labels: {
                        style: { colors: '#6b7280' }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: '#6b7280',
                            fontWeight: 600
                        }
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
                    borderColor: '#f1f1f1',
                    strokeDashArray: 4
                }
            };

            const chart = new ApexCharts(document.querySelector("#teamAsesoresKpisChart"), options);
            chart.render();
        });
    </script>
</x-app-layout>
