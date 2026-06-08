<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <a href="{{ route('team-leader.dashboard') }}" class="text-sm font-semibold text-blue-600 dark:text-blue-400 hover:underline">← Volver al Dashboard</a>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mt-1">
                    Historial: {{ $asesor->name }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $asesor->email }} · {{ $asesor->phone ?? 'Sin teléfono' }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                    {{ $asesor->team ? $asesor->team->name : 'Sin equipo' }}
                </span>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $asesor->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ $asesor->is_active ? 'Activo' : 'Inactivo' }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Semáforo + Resumen Semanal --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Semáforo --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-3">🚦 Semáforo Semanal</h3>
                    <div class="flex items-center gap-5">
                        @php
                            $colorClasses = match($trafficLight) {
                                'green' => 'bg-green-500 shadow-green-500/40',
                                'yellow' => 'bg-yellow-400 shadow-yellow-400/40',
                                'red' => 'bg-red-500 shadow-red-500/40',
                                default => 'bg-gray-400',
                            };
                            $statusText = match($trafficLight) {
                                'green' => '🟢 Excelente',
                                'yellow' => '🟡 En Progreso',
                                'red' => '🔴 En Alerta',
                                default => '⚪',
                            };
                        @endphp
                        <div class="w-16 h-16 rounded-2xl {{ $colorClasses }} shadow-md flex items-center justify-center text-2xl font-bold text-white">
                            {{ $totalCaptures }}
                        </div>
                        <div>
                            <p class="text-lg font-bold text-gray-800 dark:text-gray-200">{{ $statusText }}</p>
                            <p class="text-sm text-gray-500">{{ $totalCaptures }} / {{ $kpiConfig ? $kpiConfig->weekly_goal : '10' }} captaciones ({{ $percentage }}%)</p>
                        </div>
                    </div>
                    <div class="mt-4 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                        <div class="h-2.5 rounded-full {{ $colorClasses }}" style="width: {{ min($percentage, 100) }}%"></div>
                    </div>
                </div>

                {{-- KPIs semanales --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 lg:col-span-2">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4">📈 Acumulado Semanal</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <div class="p-4 bg-blue-50 dark:bg-blue-900/10 rounded-xl border border-blue-100 dark:border-blue-800/30">
                            <span class="text-gray-500 text-xs uppercase block">Visitas</span>
                            <span class="text-2xl font-black text-blue-600">{{ $weeklyTotals['visits'] }}</span>
                        </div>
                        <div class="p-4 bg-emerald-50 dark:bg-emerald-900/10 rounded-xl border border-emerald-100 dark:border-emerald-800/30">
                            <span class="text-gray-500 text-xs uppercase block">Letrero</span>
                            <span class="text-2xl font-black text-emerald-600">{{ $weeklyTotals['sign_captures'] }}</span>
                        </div>
                        <div class="p-4 bg-purple-50 dark:bg-purple-900/10 rounded-xl border border-purple-100 dark:border-purple-800/30">
                            <span class="text-gray-500 text-xs uppercase block">Exclusivas</span>
                            <span class="text-2xl font-black text-purple-600">{{ $weeklyTotals['exclusive_captures'] }}</span>
                        </div>
                        <div class="p-4 bg-amber-50 dark:bg-amber-900/10 rounded-xl border border-amber-100 dark:border-amber-800/30">
                            <span class="text-gray-500 text-xs uppercase block">Cierres</span>
                            <span class="text-2xl font-black text-amber-600">{{ $weeklyTotals['closings'] }}</span>
                        </div>
                        <div class="p-4 bg-cyan-50 dark:bg-cyan-900/10 rounded-xl border border-cyan-100 dark:border-cyan-800/30">
                            <span class="text-gray-500 text-xs uppercase block">Llamadas</span>
                            <span class="text-2xl font-black text-cyan-600">{{ $weeklyTotals['calls_made'] }}</span>
                        </div>
                        <div class="p-4 bg-rose-50 dark:bg-rose-900/10 rounded-xl border border-rose-100 dark:border-rose-800/30">
                            <span class="text-gray-500 text-xs uppercase block">AlphaX</span>
                            <span class="text-2xl font-black text-rose-600">{{ $weeklyTotals['properties_in_system'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tendencia Semanal (mini bar chart visual) + Reporte de Hoy --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Tendencia diaria de la semana --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-2 flex items-center gap-2">
                        <span>📊</span> Tendencia Semanal de KPIs Diarios
                    </h3>
                    <p class="text-xs text-gray-400 mb-4">Progreso diario de visitas y captaciones acumuladas a lo largo de esta semana.</p>
                    <div id="asesorWeeklyTrendChart" class="w-full"></div>
                </div>

                {{-- Reporte de hoy --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4">📋 Reporte de Hoy — {{ $today->format('d/m/Y') }}</h3>
                    @if($todayReport)
                        <div class="grid grid-cols-2 gap-4">
                            <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg text-center">
                                <span class="block text-xs text-gray-500 uppercase">Visitas</span>
                                <span class="text-xl font-black text-gray-800 dark:text-gray-200">{{ $todayReport->visits }}</span>
                            </div>
                            <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg text-center relative group">
                                <span class="block text-xs text-gray-500 uppercase">Letrero</span>
                                <span class="text-xl font-black text-gray-800 dark:text-gray-200">{{ $todayReport->sign_captures }}</span>
                                @if($todayReport->sign_image_path)
                                    @php
                                        $todayImages = [];
                                        $decoded = json_decode($todayReport->sign_image_path, true);
                                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                            $todayImages = $decoded;
                                        } else {
                                            $todayImages = [$todayReport->sign_image_path];
                                        }
                                    @endphp
                                    @foreach($todayImages as $idx => $img)
                                        <span class="block text-xxs text-amber-600 dark:text-amber-400 font-bold mt-1">
                                            <a href="#" onclick="openLightbox('{{ asset($img) }}'); return false;" class="hover:underline flex items-center justify-center gap-0.5">
                                                📸 Letrero {{ count($todayImages) > 1 ? '#' . ($idx + 1) : '' }}
                                            </a>
                                        </span>
                                    @endforeach
                                @endif
                            </div>
                            <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg text-center">
                                <span class="block text-xs text-gray-500 uppercase">Exclusiva</span>
                                <span class="text-xl font-black text-gray-800 dark:text-gray-200">{{ $todayReport->exclusive_captures }}</span>
                            </div>
                            <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg text-center">
                                <span class="block text-xs text-gray-500 uppercase">Cierres</span>
                                <span class="text-xl font-black text-gray-800 dark:text-gray-200">{{ $todayReport->closings }}</span>
                            </div>
                             <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg text-center relative group">
                                <span class="block text-xs text-gray-500 uppercase">Llamadas</span>
                                <span class="text-xl font-black text-gray-800 dark:text-gray-200">{{ $todayReport->calls_made }}</span>
                                @if($todayReport->call_phone_number)
                                    <span class="block text-xxs text-gray-400 mt-1 truncate" title="{{ $todayReport->call_phone_number }}">
                                        📞 {{ $todayReport->call_phone_number }}
                                    </span>
                                @endif
                            </div>
                            <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg text-center">
                                <span class="block text-xs text-gray-500 uppercase">AlphaX</span>
                                <span class="text-xl font-black text-gray-800 dark:text-gray-200">{{ $todayReport->properties_in_system }}</span>
                            </div>
                        </div>
                        <div class="mt-3 text-xs text-gray-500 flex items-center gap-2">
                            <span>Fuente: {{ $todayReport->source === 'whatsapp' ? '📱 WhatsApp' : '🌐 Web' }}</span>
                            <span>· Enviado: {{ $todayReport->created_at->format('H:i') }} hrs</span>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <span class="text-3xl">⏳</span>
                            <p class="text-gray-500 dark:text-gray-400 mt-2 font-medium">Aún no ha enviado su reporte de hoy.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Registro de Visitas y Geolocalizaciones --}}
            @php
                $reportsWithVisits = $history->filter(function($r) {
                    return !empty($r->visits_data) && is_array($r->visits_data);
                });
                $mapMarkers = [];
            @endphp

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                    <span>📍</span> Registro de Visitas y Ubicaciones GPS
                </h3>

                @if($reportsWithVisits->count() > 0)
                    <div class="space-y-6">
                        {{-- Mini Mapa Leaflet --}}
                        <div id="asesorVisitsMap" class="w-full h-80 rounded-xl border border-gray-200 dark:border-gray-700" style="z-index: 1;"></div>

                        {{-- Tabla de Visitas --}}
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead>
                                    <tr class="border-b border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-405 text-xs uppercase">
                                        <th class="py-3 px-4 font-bold">Fecha</th>
                                        <th class="py-3 px-4 font-bold">Cliente</th>
                                        <th class="py-3 px-4 font-bold">Celular</th>
                                        <th class="py-3 px-4 font-bold">Ubicación / Dirección</th>
                                        <th class="py-3 px-4 font-bold">Acción GPS</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-750">
                                    @foreach($reportsWithVisits as $rep)
                                        @foreach($rep->visits_data as $v)
                                            @php
                                                $lat = $v['latitude'] ?? null;
                                                $lng = $v['longitude'] ?? null;
                                                $hasCoords = !empty($lat) && !empty($lng);
                                                if($hasCoords) {
                                                    $mapMarkers[] = [
                                                        'lat' => (float)$lat,
                                                        'lng' => (float)$lng,
                                                        'client_name' => $v['client_name'] ?? 'Cliente',
                                                        'client_phone' => $v['client_phone'] ?? '',
                                                        'address' => $v['address'] ?? 'Dirección no disponible',
                                                        'date' => $rep->report_date->format('d/m/Y')
                                                    ];
                                                }
                                            @endphp
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 text-gray-700 dark:text-gray-300">
                                                <td class="py-3 px-4 font-semibold">{{ $rep->report_date->format('d/m/Y') }}</td>
                                                <td class="py-3 px-4 font-medium">{{ $v['client_name'] ?? '—' }}</td>
                                                <td class="py-3 px-4">
                                                    @if(!empty($v['client_phone']))
                                                        <a href="tel:{{ $v['client_phone'] }}" class="text-blue-600 dark:text-blue-400 hover:underline">📞 {{ $v['client_phone'] }}</a>
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                                <td class="py-3 px-4 text-xs text-gray-500 dark:text-gray-400 max-w-xs truncate" title="{{ $v['address'] ?? '' }}">
                                                    {{ $v['address'] ?? '—' }}
                                                </td>
                                                <td class="py-3 px-4">
                                                    @if($hasCoords)
                                                        <a href="https://www.google.com/maps/search/?api=1&query={{ $lat }},{{ $lng }}" target="_blank"
                                                           class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xxs font-bold bg-green-50 dark:bg-green-950/30 text-green-700 dark:text-green-400 hover:bg-green-100 transition-colors">
                                                            🗺️ Ver en Google Maps ({{ number_format($lat, 5) }}, {{ number_format($lng, 5) }})
                                                        </a>
                                                    @else
                                                        <span class="text-xs text-gray-400">Sin Coordenadas GPS</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="text-center py-6 text-gray-500 dark:text-gray-400">
                        No se encontraron visitas detalladas de geolocalización registradas para este asesor.
                    </div>
                @endif
            </div>

            {{-- Métricas Históricas + Historial Completo --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Promedios históricos --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-4">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">📊 Métricas Históricas</h3>
                    <div class="border-b border-gray-200 dark:border-gray-700 pb-3">
                        <p class="text-xs text-gray-500">Total Reportes Enviados</p>
                        <p class="text-2xl font-extrabold text-gray-800 dark:text-gray-200">{{ $totalReports }}</p>
                    </div>
                    <div class="border-b border-gray-200 dark:border-gray-700 pb-3">
                        <p class="text-xs text-gray-500">Promedio Visitas/Día</p>
                        <p class="text-2xl font-extrabold text-gray-800 dark:text-gray-200">{{ number_format($avgVisits, 1) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Promedio Captaciones/Día</p>
                        <p class="text-2xl font-extrabold text-gray-800 dark:text-gray-200">{{ number_format($avgCaptures, 1) }}</p>
                    </div>
                </div>

                {{-- Tabla de historial completo --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 md:col-span-2">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4">📅 Historial de Reportes (últimos 60 días)</h3>
                    @if($history->count() > 0)
                        <div class="overflow-x-auto max-h-[500px] overflow-y-auto">
                            <table class="w-full text-sm">
                                <thead class="sticky top-0 bg-white dark:bg-gray-800">
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
                                            <td class="py-2.5 px-2 text-center">
                                                <span>{{ $report->sign_captures }}</span>
                                                @if($report->sign_image_path)
                                                    @php
                                                        $historyImages = [];
                                                        $decoded = json_decode($report->sign_image_path, true);
                                                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                            $historyImages = $decoded;
                                                        } else {
                                                            $historyImages = [$report->sign_image_path];
                                                        }
                                                    @endphp
                                                    @foreach($historyImages as $idx => $img)
                                                        <a href="#" onclick="openLightbox('{{ asset($img) }}'); return false;" class="block text-amber-500 hover:text-amber-700 text-[10px] font-bold mt-0.5" title="Ver Foto de Letrero {{ $idx + 1 }}">
                                                            📸 Foto {{ count($historyImages) > 1 ? '#' . ($idx + 1) : '' }}
                                                        </a>
                                                    @endforeach
                                                @endif
                                            </td>
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
                            No hay reportes registrados para este asesor.
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <!-- Script de Inicialización de ApexCharts para Tendencia Semanal del Asesor -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Datos de tendencia semanal de PHP
            const trendData = @json($weeklyTrend);

            const days = trendData.map(d => `${d.day} (${d.date})`);
            const visits = trendData.map(d => d.visits);
            const captures = trendData.map(d => d.captures);

            const options = {
                chart: {
                    type: 'area',
                    height: 250,
                    toolbar: { show: false },
                    fontFamily: 'Figtree, sans-serif',
                    background: 'transparent'
                },
                theme: {
                    mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light'
                },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.45,
                        opacityTo: 0.05,
                        stops: [0, 100]
                    }
                },
                colors: ['#3b82f6', '#10b981'],
                series: [{
                    name: 'Visitas',
                    data: visits
                }, {
                    name: 'Captaciones',
                    data: captures
                }],
                xaxis: {
                    categories: days,
                    labels: {
                        style: {
                            colors: '#6b7280',
                            fontSize: '11px',
                            fontWeight: 600
                        }
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

            const chart = new ApexCharts(document.querySelector("#asesorWeeklyTrendChart"), options);
            chart.render();
        });
    </script>

    @if(count($mapMarkers) > 0)
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const markers = @json($mapMarkers);
            if (markers.length > 0) {
                const map = L.map('asesorVisitsMap').setView([markers[0].lat, markers[0].lng], 15);
                
                const satellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                    attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS'
                });

                const streets = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                });

                // Por defecto calles, pero con control de capas
                streets.addTo(map);

                const baseMaps = {
                    "🛰️ Vista Satélite": satellite,
                    "🗺️ Vista Calles": streets
                };
                L.control.layers(baseMaps, null, { collapsed: false, position: 'topright' }).addTo(map);

                const bounds = [];
                markers.forEach(m => {
                    const marker = L.marker([m.lat, m.lng]).addTo(map);
                    marker.bindPopup(`
                        <div class="p-1" style="min-width: 140px; font-family: 'Figtree', sans-serif;">
                            <p class="font-black text-gray-800 text-sm">👤 ${m.client_name}</p>
                            <p class="text-xs text-gray-600 mt-0.5">📞 Cel: ${m.client_phone}</p>
                            <p class="text-xs text-blue-600 font-semibold mt-1">📅 ${m.date}</p>
                            <p class="text-xxs text-gray-400 mt-1 truncate" title="${m.address}">${m.address}</p>
                            <a href="https://www.google.com/maps/search/?api=1&query=${m.lat},${m.lng}" target="_blank" 
                               class="inline-block mt-2 text-xs font-bold text-green-600 hover:underline">🗺️ Google Maps</a>
                        </div>
                    `);
                    bounds.push([m.lat, m.lng]);
                });

                if (bounds.length > 1) {
                    map.fitBounds(bounds, { padding: [40, 40] });
                } else if (bounds.length === 1) {
                    map.setView(bounds[0], 16);
                }
            }
        });
    </script>
    @endif

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
