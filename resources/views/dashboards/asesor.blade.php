<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Mi Dashboard</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Bienvenido, <strong>{{ $user->name }}</strong>
                    · Team Líder: {{ $teamLeader ? $teamLeader->name : 'Sin asignar' }}
                    · {{ $today->format('d/m/Y') }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('asesor.visits-map') }}" class="px-4 py-2 rounded-xl text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition-colors shadow-md shadow-emerald-600/20">
                    🗺️ Mis Visitas en Mapa
                </a>
                @if(!$todayReport)
                    <a href="{{ route('asesor.report.create') }}" class="px-4 py-2 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 transition-colors shadow-md shadow-blue-600/20">
                        📤 Enviar Reporte de Hoy
                    </a>
                @else
                    <a href="{{ route('asesor.report.edit') }}" class="px-4 py-2 rounded-xl text-sm font-bold text-white bg-amber-600 hover:bg-amber-700 transition-colors shadow-md shadow-amber-600/20">
                        ✏️ Actualizar Reporte del Día de Hoy
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
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="p-4 rounded-xl bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300">
                    {{ session('error') }}
                </div>
            @endif

            {{-- 1. SEMÁFORO DE CAPTACIONES --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4">🚦 Semáforo de Captaciones — Semana {{ $startOfWeek->format('d/m') }} al {{ $endOfWeek->format('d/m') }}</h3>
                <div class="flex flex-col md:flex-row md:items-center gap-6">
                    <div class="flex-shrink-0">
                        @php
                            $colorClasses = match($trafficLight) {
                                'green' => 'bg-green-500 shadow-green-500/40',
                                'yellow' => 'bg-yellow-400 shadow-yellow-400/40',
                                'red' => 'bg-red-500 shadow-red-500/40',
                                default => 'bg-gray-400',
                            };
                            $statusText = match($trafficLight) {
                                'green' => '🟢 ¡Excelente! Vas por buen camino',
                                'yellow' => '🟡 En Progreso — ¡Tú puedes!',
                                'red' => '🔴 En Alerta — ¡A darle con todo!',
                                default => '⚪',
                            };
                        @endphp
                        <div class="w-20 h-20 rounded-2xl {{ $colorClasses }} shadow-lg flex items-center justify-center">
                            <span class="text-3xl font-black text-white">{{ $totalCaptures }}</span>
                        </div>
                    </div>
                    <div class="flex-1">
                        <p class="text-xl font-bold text-gray-800 dark:text-gray-200">{{ $statusText }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            {{ $totalCaptures }} de {{ $kpiConfig ? $kpiConfig->weekly_goal : '10' }} captaciones esta semana ({{ $percentage }}%)
                        </p>
                        <div class="mt-3 w-full max-w-sm bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                            <div class="h-3 rounded-full {{ $colorClasses }} transition-all duration-500" style="width: {{ min($percentage, 100) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. MIS 6 INDICADORES DE HOY --}}
            <div>
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-3">📊 Mis Indicadores de Hoy</h3>
                @if($todayReport)
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                        @php
                            $todayKpis = [
                                ['label' => 'Visitas', 'value' => $todayReport->visits, 'icon' => '🏠', 'color' => 'blue', 'sub' => null],
                                ['label' => 'Letrero', 'value' => $todayReport->sign_captures, 'icon' => '📋', 'color' => 'emerald', 'sub' => null],
                                ['label' => 'Exclusiva', 'value' => $todayReport->exclusive_captures, 'icon' => '📝', 'color' => 'purple', 'sub' => null],
                                ['label' => 'Cierres', 'value' => $todayReport->closings, 'icon' => '🤝', 'color' => 'amber', 'sub' => null],
                                ['label' => 'Llamadas', 'value' => $todayReport->calls_made, 'icon' => '📞', 'color' => 'cyan', 'sub' => $todayReport->call_phone_number],
                                ['label' => 'AlphaX', 'value' => $todayReport->properties_in_system, 'icon' => '💻', 'color' => 'rose', 'sub' => null],
                            ];
                        @endphp
                        @foreach($todayKpis as $kpi)
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 text-center">
                                <span class="text-2xl">{{ $kpi['icon'] }}</span>
                                <p class="text-2xl font-black text-gray-800 dark:text-gray-200 mt-1">{{ $kpi['value'] }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ $kpi['label'] }}</p>
                                @if($kpi['label'] === 'Llamadas' && $kpi['sub'])
                                    <div class="mt-2 text-xxs text-gray-400 bg-gray-50 dark:bg-gray-900/50 p-1.5 rounded border border-gray-100 dark:border-gray-800 truncate" title="{{ $kpi['sub'] }}">
                                        📞 {{ $kpi['sub'] }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div class="flex items-center justify-between mt-3 flex-wrap gap-2 pt-2 border-t border-gray-100 dark:border-gray-800">
                        <p class="text-xs text-gray-400">
                            Enviado: {{ $todayReport->created_at->format('H:i') }} hrs
                        </p>
                        <a href="{{ route('asesor.report.edit') }}" class="text-xs font-bold text-amber-600 dark:text-amber-400 hover:underline flex items-center gap-1">
                            ✏️ Actualizar Reporte de Hoy
                        </a>
                    </div>
                @else
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-6 text-center">
                        <span class="text-3xl block mb-2">⏳</span>
                        <p class="text-yellow-800 dark:text-yellow-200 font-bold">Aún no enviaste tu reporte de hoy</p>
                        <p class="text-yellow-600 dark:text-yellow-400 text-sm mt-1 mb-4">Registra tu actividad para actualizar tu semáforo.</p>
                        <a href="{{ route('asesor.report.create') }}" class="inline-flex items-center px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                            📤 Enviar Reporte Ahora
                        </a>
                    </div>
                @endif
            </div>

            <!-- GRÁFICO DE COMPORTAMIENTO INDIVIDUAL DEL ASESOR -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Gráfico de Tendencia o Evolución semanal -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 lg:col-span-2">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-2 flex items-center gap-2">
                        <span>📊</span> Actividad Diaria por Indicador (Semana Actual)
                    </h3>
                    <p class="text-xs text-gray-400 mb-4">Comparativa visual de tus visitas, captaciones, cierres y llamadas diarias registradas de Lunes a Viernes.</p>
                    <div id="asesorPerformanceTrendChart" class="w-full"></div>
                </div>

                <!-- Mezcla de mi Productividad Semanal -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-2 flex items-center gap-2">
                        <span>🎯</span> Mezcla de mi Actividad
                    </h3>
                    <p class="text-xs text-gray-400 mb-4">Proporción de tus actividades realizadas en la semana actual.</p>
                    <div id="asesorActivityDonutChart" class="w-full flex justify-center"></div>
                </div>
            </div>

            {{-- 3. ACUMULADO SEMANAL --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4">📈 Mi Acumulado Semanal</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    @php
                        $weeklyCards = [
                            ['label' => 'Visitas', 'value' => $weeklyTotals['visits'], 'icon' => '🏠'],
                            ['label' => 'Letrero', 'value' => $weeklyTotals['sign_captures'], 'icon' => '📋'],
                            ['label' => 'Exclusiva', 'value' => $weeklyTotals['exclusive_captures'], 'icon' => '📝'],
                            ['label' => 'Cierres', 'value' => $weeklyTotals['closings'], 'icon' => '🤝'],
                            ['label' => 'Llamadas', 'value' => $weeklyTotals['calls_made'], 'icon' => '📞'],
                            ['label' => 'AlphaX', 'value' => $weeklyTotals['properties_in_system'], 'icon' => '💻'],
                        ];
                    @endphp
                    @foreach($weeklyCards as $card)
                        <div class="text-center p-4 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                            <span class="text-xl">{{ $card['icon'] }}</span>
                            <p class="text-xl font-black text-gray-800 dark:text-gray-200">{{ $card['value'] }}</p>
                            <p class="text-xs text-gray-500">{{ $card['label'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- 4. RANKING DEL EQUIPO --}}
            @if($teamAsesores->count() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4">🏆 Ranking del Equipo — Semana Actual</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Ranking basado en captaciones totales (Letrero + Exclusiva). No puedes ver los datos de otros asesores, solo la posición.</p>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700 text-gray-500 text-xs uppercase">
                                    <th class="text-left py-3 px-3 font-semibold">#</th>
                                    <th class="text-left py-3 px-2 font-semibold">Asesor</th>
                                    <th class="text-center py-3 px-2 font-semibold">🏠</th>
                                    <th class="text-center py-3 px-2 font-semibold">📋+📝</th>
                                    <th class="text-center py-3 px-2 font-semibold">🤝</th>
                                    <th class="text-center py-3 px-2 font-semibold">📞</th>
                                    <th class="text-center py-3 px-2 font-semibold">💻</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                                @foreach($teamAsesores as $index => $member)
                                    <tr class="{{ $member->id === $user->id ? 'bg-blue-50 dark:bg-blue-900/20' : '' }} hover:bg-gray-50 dark:hover:bg-gray-700/30 text-gray-700 dark:text-gray-300">
                                        <td class="py-3 px-3">
                                            @if($index === 0)
                                                <span class="text-yellow-500">🥇</span>
                                            @elseif($index === 1)
                                                <span class="text-gray-400">🥈</span>
                                            @elseif($index === 2)
                                                <span class="text-amber-700">🥉</span>
                                            @else
                                                <span class="text-gray-400 font-bold">#{{ $index + 1 }}</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-2 font-bold {{ $member->id === $user->id ? 'text-blue-600 dark:text-blue-400' : 'text-gray-800 dark:text-gray-200' }}">
                                            {{ $member->name }}
                                            @if($member->id === $user->id)
                                                <span class="text-xs font-normal text-blue-500">(Tú)</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-2 text-center">{{ $member->visits }}</td>
                                        <td class="py-3 px-2 text-center font-bold">{{ $member->total_captures }}</td>
                                        <td class="py-3 px-2 text-center">{{ $member->closings }}</td>
                                        <td class="py-3 px-2 text-center">{{ $member->calls_made }}</td>
                                        <td class="py-3 px-2 text-center">{{ $member->properties_in_system }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- 5. HISTORIAL DE REPORTES --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4">📅 Mis Reportes Anteriores</h3>
                @if($history->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700 text-gray-500 text-xs uppercase">
                                    <th class="text-left py-3 px-2 font-semibold">Fecha</th>
                                    <th class="text-center py-3 px-2 font-semibold">🏠</th>
                                    <th class="text-center py-3 px-2 font-semibold">📋</th>
                                    <th class="text-center py-3 px-2 font-semibold">📝</th>
                                    <th class="text-center py-3 px-2 font-semibold">🤝</th>
                                    <th class="text-center py-3 px-2 font-semibold">📞</th>
                                    <th class="text-center py-3 px-2 font-semibold">💻</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                                @foreach($history as $report)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 text-gray-700 dark:text-gray-300">
                                        <td class="py-2.5 px-2 font-medium">{{ $report->report_date->format('d/m/Y') }}</td>
                                        <td class="py-2.5 px-2 text-center">{{ $report->visits }}</td>
                                        <td class="py-2.5 px-2 text-center">{{ $report->sign_captures }}</td>
                                        <td class="py-2.5 px-2 text-center">{{ $report->exclusive_captures }}</td>
                                        <td class="py-2.5 px-2 text-center">{{ $report->closings }}</td>
                                        <td class="py-2.5 px-2 text-center">
                                            <span class="font-bold">{{ $report->calls_made }}</span>
                                            @if($report->call_phone_number)
                                                <span class="block text-xxs text-gray-400 truncate max-w-[100px] mx-auto" title="{{ $report->call_phone_number }}">
                                                    {{ $report->call_phone_number }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-2.5 px-2 text-center">{{ $report->properties_in_system }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        No tienes reportes anteriores. ¡Envía tu primer reporte hoy!
                    </div>
                @endif
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
                        columnWidth: '60%',
                        borderRadius: 3,
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
                    borderColor: '#f1f1f1',
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
                            size: '60%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'KPIs',
                                    formatter: function (w) {
                                        return w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                                    },
                                    style: {
                                        color: '#374151',
                                        fontSize: '14px',
                                        fontWeight: 700
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
</x-app-layout>
