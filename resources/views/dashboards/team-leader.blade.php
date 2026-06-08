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

            {{-- ============================================================ --}}
            {{-- 4. KPIS DIARIOS POR ASESOR (datos de HOY)                   --}}
            {{-- ============================================================ --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4">📋 KPIs del Día — {{ $today->format('d/m/Y') }}</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Datos enviados hoy por cada asesor. Se actualiza en tiempo real conforme reportan.</p>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700 text-gray-500 text-xs uppercase tracking-wider">
                                <th class="text-left py-3 px-3 font-semibold">Asesor</th>
                                <th class="text-center py-3 px-2 font-semibold">Estado</th>
                                <th class="text-center py-3 px-2 font-semibold">Visitas</th>
                                <th class="text-center py-3 px-2 font-semibold">Letrero</th>
                                <th class="text-center py-3 px-2 font-semibold">Exclusiva</th>
                                <th class="text-center py-3 px-2 font-semibold">Cierres</th>
                                <th class="text-center py-3 px-2 font-semibold">Llamadas</th>
                                <th class="text-center py-3 px-2 font-semibold">AlphaX</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                            @foreach($asesoresData as $a)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 text-gray-700 dark:text-gray-300">
                                    <td class="py-3 px-3">
                                        <p class="font-bold text-gray-800 dark:text-gray-100">{{ $a->name }}</p>
                                    </td>
                                    <td class="py-3 px-2 text-center">
                                        @if($a->sent_today)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">✅ Enviado</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">❌ Pendiente</span>
                                        @endif
                                    </td>
                                    @if($a->sent_today)
                                        <td class="py-3 px-2 text-center font-bold">{{ $a->today['visits'] }}</td>
                                        <td class="py-3 px-2 text-center font-bold">
                                            <span>{{ $a->today['sign_captures'] }}</span>
                                            @if(!empty($a->today['sign_image_path']))
                                                @php
                                                    $todayImages = [];
                                                    $decoded = json_decode($a->today['sign_image_path'], true);
                                                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                        $todayImages = $decoded;
                                                    } else {
                                                        $todayImages = [$a->today['sign_image_path']];
                                                    }
                                                @endphp
                                                @foreach($todayImages as $idx => $img)
                                                    <a href="#" onclick="openLightbox('{{ asset($img) }}'); return false;" class="block text-amber-500 hover:text-amber-700 text-[10px] font-bold mt-0.5" title="Ver foto letrero {{ $idx + 1 }}">
                                                        📸 Foto {{ count($todayImages) > 1 ? '#' . ($idx + 1) : '' }}
                                                    </a>
                                                @endforeach
                                            @endif
                                        </td>
                                        <td class="py-3 px-2 text-center font-bold">{{ $a->today['exclusive_captures'] }}</td>
                                        <td class="py-3 px-2 text-center font-bold">{{ $a->today['closings'] }}</td>
                                        <td class="py-3 px-2 text-center font-bold">
                                            <span>{{ $a->today['calls_made'] }}</span>
                                            @if(!empty($a->today['call_phone_number']))
                                                <span class="block text-xxs text-gray-400 font-normal truncate max-w-[100px] mx-auto mt-0.5" title="{{ $a->today['call_phone_number'] }}">
                                                    {{ $a->today['call_phone_number'] }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-2 text-center font-bold">{{ $a->today['properties_in_system'] }}</td>
                                    @else
                                        <td colspan="6" class="py-3 px-2 text-center text-gray-400 dark:text-gray-500 italic text-xs">Sin datos — aún no reportó</td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ============================================================ --}}
            {{-- 5. ACUMULADO SEMANAL POR ASESOR + SEMÁFORO                  --}}
            {{-- ============================================================ --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">👥 Acumulado Semanal por Asesor + Semáforo</h3>
                    <div class="flex items-center gap-4 text-xs text-gray-500">
                        <span>Meta semanal de captaciones: <strong class="text-gray-800 dark:text-gray-200">{{ $kpiConfig ? $kpiConfig->weekly_goal : 'No definida' }}</strong></span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700 text-gray-500 text-xs uppercase tracking-wider">
                                <th class="text-left py-3 px-3 font-semibold">Asesor</th>
                                <th class="text-center py-3 px-2 font-semibold">🏠 Visitas</th>
                                <th class="text-center py-3 px-2 font-semibold">📋 Letrero</th>
                                <th class="text-center py-3 px-2 font-semibold">📝 Exclusiva</th>
                                <th class="text-center py-3 px-2 font-semibold">🤝 Cierres</th>
                                <th class="text-center py-3 px-2 font-semibold">📞 Llamadas</th>
                                <th class="text-center py-3 px-2 font-semibold">💻 AlphaX</th>
                                <th class="text-center py-3 px-3 font-semibold">🚦 Semáforo</th>
                                <th class="text-right py-3 px-3 font-semibold">Detalle</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                            @foreach($asesoresData as $a)
                                @php
                                    $light = 'green';
                                    $pct = 100;
                                    if ($kpiConfig && $kpiConfig->weekly_goal > 0) {
                                        $pct = round(($a->total_captures / $kpiConfig->weekly_goal) * 100);
                                        $light = $kpiConfig->getTrafficLightColor($a->total_captures);
                                    }
                                    $lightBadge = match($light) {
                                        'green' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                        'yellow' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
                                        'red' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                                        default => 'bg-gray-100 text-gray-800',
                                    };
                                    $lightEmoji = match($light) {
                                        'green' => '🟢',
                                        'yellow' => '🟡',
                                        'red' => '🔴',
                                        default => '⚪',
                                    };
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 text-gray-700 dark:text-gray-300">
                                    <td class="py-3.5 px-3">
                                        <p class="font-bold text-gray-800 dark:text-gray-100">{{ $a->name }}</p>
                                        <p class="text-xs text-gray-400">{{ $a->email }}</p>
                                    </td>
                                    <td class="py-3.5 px-2 text-center font-bold text-gray-900 dark:text-gray-100">{{ $a->weekly['visits'] }}</td>
                                    <td class="py-3.5 px-2 text-center font-bold">{{ $a->weekly['sign_captures'] }}</td>
                                    <td class="py-3.5 px-2 text-center font-bold">{{ $a->weekly['exclusive_captures'] }}</td>
                                    <td class="py-3.5 px-2 text-center font-bold text-gray-900 dark:text-gray-100">{{ $a->weekly['closings'] }}</td>
                                    <td class="py-3.5 px-2 text-center">{{ $a->weekly['calls_made'] }}</td>
                                    <td class="py-3.5 px-2 text-center">{{ $a->weekly['properties_in_system'] }}</td>
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-black {{ $lightBadge }}">
                                            {{ $lightEmoji }} {{ $a->total_captures }}/{{ $kpiConfig ? $kpiConfig->weekly_goal : '10' }} ({{ $pct }}%)
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-3 text-right">
                                        <a href="{{ route('team-leader.asesor-detail', $a->id) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-600 dark:bg-blue-900/20 dark:hover:bg-blue-900/40 dark:text-blue-400 font-semibold text-xs transition-colors">
                                            Ver historial →
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ============================================================ --}}
            {{-- 6. RANKING DE ASESORES DESTACADOS (6 rankings, 1 por KPI)   --}}
            {{-- ============================================================ --}}
            <div class="space-y-4">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">🏆 Asesores Destacados de la Semana</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 -mt-2">Ranking por cada uno de los 6 indicadores. Basado en el acumulado semanal.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @php
                        $rankingCards = [
                            ['key' => 'visits', 'icon' => '🏠', 'title' => 'Top Visitas', 'field' => 'visits', 'color' => 'blue'],
                            ['key' => 'sign_captures', 'icon' => '📋', 'title' => 'Top Captaciones Letrero', 'field' => 'sign_captures', 'color' => 'emerald'],
                            ['key' => 'exclusive_captures', 'icon' => '📝', 'title' => 'Top Captaciones Exclusiva', 'field' => 'exclusive_captures', 'color' => 'purple'],
                            ['key' => 'closings', 'icon' => '🤝', 'title' => 'Top Cierres', 'field' => 'closings', 'color' => 'amber'],
                            ['key' => 'calls_made', 'icon' => '📞', 'title' => 'Top Llamadas', 'field' => 'calls_made', 'color' => 'cyan'],
                            ['key' => 'properties_in_system', 'icon' => '💻', 'title' => 'Top AlphaX', 'field' => 'properties_in_system', 'color' => 'rose'],
                        ];
                    @endphp
                    @foreach($rankingCards as $rc)
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                            <div class="flex items-center gap-2 border-b border-gray-100 dark:border-gray-700 pb-2 mb-3">
                                <span class="text-xl">{{ $rc['icon'] }}</span>
                                <h4 class="font-bold text-gray-800 dark:text-gray-200">{{ $rc['title'] }}</h4>
                            </div>
                            <div class="space-y-2">
                                @foreach($rankings[$rc['key']]->take(5) as $index => $a)
                                    <div class="flex items-center justify-between text-sm py-1">
                                        <span class="text-gray-700 dark:text-gray-300">
                                            @if($index === 0)
                                                <span class="text-yellow-500 mr-1">🥇</span>
                                            @elseif($index === 1)
                                                <span class="text-gray-400 mr-1">🥈</span>
                                            @elseif($index === 2)
                                                <span class="text-amber-700 mr-1">🥉</span>
                                            @else
                                                <strong class="text-gray-400 mr-1.5">#{{ $index + 1 }}</strong>
                                            @endif
                                            {{ $a->name }}
                                        </span>
                                        <span class="font-black text-{{ $rc['color'] }}-600 dark:text-{{ $rc['color'] }}-400">{{ $a->weekly[$rc['field']] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
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

    <!-- Modal Lightbox para ver Fotos de Respaldo -->
    <div id="imageLightbox" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm hidden print:hidden" onclick="closeLightbox()">
        <div class="relative max-w-3xl max-h-[85vh] p-4 flex flex-col items-center" onclick="event.stopPropagation()">
            <button onclick="closeLightbox()" class="absolute top-2 right-2 bg-black/60 hover:bg-black/80 text-white rounded-full p-2.5 transition-colors focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            <img id="lightboxImage" src="" alt="Respaldo Letrero" class="max-w-full max-h-[75vh] object-contain rounded-xl border border-gray-700 shadow-2xl">
            <p class="text-white/80 text-sm mt-3 font-semibold text-center bg-black/40 px-4 py-1.5 rounded-full">Respaldo de Letrero</p>
        </div>
    </div>

    <script>
        function openLightbox(url) {
            const lightbox = document.getElementById('imageLightbox');
            const img = document.getElementById('lightboxImage');
            if (lightbox && img) {
                img.src = url;
                lightbox.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
        }
        function closeLightbox() {
            const lightbox = document.getElementById('imageLightbox');
            if (lightbox) {
                lightbox.classList.add('hidden');
                document.body.style.overflow = '';
            }
        }
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeLightbox();
        });
    </script>
</x-app-layout>
