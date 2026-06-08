<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('director.dashboard') }}" class="text-sm text-blue-600 hover:underline">← Volver al Dashboard</a>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mt-1">🗺️ Mapa Global de Visitas</h2>
                <p class="text-sm text-gray-500 mt-1">Periodo del {{ $startDate->format('d/m/Y') }} al {{ $endDate->format('d/m/Y') }}</p>
            </div>
        </div>
    </x-slot>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <style>#global-map{height:550px;border-radius:1rem;z-index:1;}</style>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- FILTRO DE BÚSQUEDA Y ENTIDADES -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 print:hidden">
                <form method="GET" action="{{ route('director.visits-map') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        <!-- Rango de Fecha -->
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Rango de Fecha</label>
                            <select name="filter_type" onchange="this.form.submit()" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                                <option value="dia" {{ $filterType === 'dia' ? 'selected' : '' }}>Hoy</option>
                                <option value="semana" {{ $filterType === 'semana' ? 'selected' : '' }}>Esta Semana</option>
                                <option value="mes" {{ $filterType === 'mes' ? 'selected' : '' }}>Este Mes</option>
                                <option value="custom" {{ $filterType === 'custom' ? 'selected' : '' }}>Rango Personalizado</option>
                            </select>
                        </div>

                        <!-- Oficina -->
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Oficina</label>
                            <select name="office_id" onchange="this.form.submit()" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                                <option value="">Todas las Oficinas</option>
                                @foreach($allOffices as $office)
                                    <option value="{{ $office->id }}" {{ $officeId == $office->id ? 'selected' : '' }}>{{ $office->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Equipo -->
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Equipo</label>
                            <select name="team_id" onchange="this.form.submit()" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                                <option value="">Todos los Equipos</option>
                                @foreach($allTeams as $team)
                                    <option value="{{ $team->id }}" {{ $teamId == $team->id ? 'selected' : '' }}>{{ $team->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Asesor -->
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Asesor</label>
                            <select name="user_id" onchange="this.form.submit()" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                                <option value="">Todos los Asesores</option>
                                @foreach($asesoresForFilter as $a)
                                    <option value="{{ $a->id }}" {{ $asesorId == $a->id ? 'selected' : '' }}>{{ $a->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="flex items-end gap-2">
                            <button type="submit" class="flex-1 px-4 py-2.5 rounded-lg text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 transition-colors text-center">
                                🔍 Filtrar
                            </button>
                            <a href="{{ route('director.visits-map') }}" class="px-3 py-2.5 rounded-lg text-sm font-bold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors text-center" title="Limpiar Filtros">
                                🧹
                            </a>
                        </div>
                    </div>

                    @if($filterType === 'custom')
                        <div class="flex items-center gap-4 pt-2 border-t border-gray-100 dark:border-gray-700/50">
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
                </form>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center shadow-sm">
                    <p class="text-3xl font-black text-blue-600 dark:text-blue-400">{{ $totalVisits }}</p>
                    <p class="text-xs text-gray-500 mt-1 uppercase font-bold">Total Visitas</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center shadow-sm">
                    <p class="text-3xl font-black text-green-600 dark:text-green-400">{{ $geoVisits }}</p>
                    <p class="text-xs text-gray-500 mt-1 uppercase font-bold">Con GPS</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center shadow-sm">
                    <p class="text-3xl font-black text-purple-600 dark:text-purple-400">{{ $asesoresCount }}</p>
                    <p class="text-xs text-gray-500 mt-1 uppercase font-bold">Asesores Activos</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center shadow-sm">
                    <p class="text-3xl font-black text-amber-600 dark:text-amber-400">{{ $uniqueClients }}</p>
                    <p class="text-xs text-gray-500 mt-1 uppercase font-bold">Clientes Visitados</p>
                </div>
            </div>

            {{-- Leyenda de asesores --}}
            @if(count($asesorColors) > 0)
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
                <h3 class="text-sm font-bold text-gray-600 dark:text-gray-400 uppercase mb-3">Leyenda de Asesores</h3>
                <div class="flex flex-wrap gap-3">
                    @foreach($asesorColors as $name => $color)
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-full border text-xs md:text-sm" style="border-color: {{ $color }}20; background-color: {{ $color }}15;">
                        <div class="w-3 h-3 rounded-full" style="background-color: {{ $color }};"></div>
                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ $name }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Mapa --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-3">🗺️ Mapa de Geolocalización de Visitas</h3>
                @if($geoVisits > 0)
                    <div id="global-map"></div>
                @else
                    <div class="flex flex-col items-center justify-center h-64 bg-gray-50 dark:bg-gray-700/30 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600">
                        <span class="text-5xl mb-3">📍</span>
                        <p class="text-gray-500 font-bold">Sin ubicaciones GPS registradas esta semana</p>
                        <p class="text-xs text-gray-400 mt-1">Los asesores deben capturar GPS al registrar visitas.</p>
                    </div>
                @endif
            </div>

            {{-- Tabla de visitas --}}
            @if($allVisitDetails->count() > 0)
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4">📋 Detalle de Todas las Visitas Registradas</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700 text-gray-500 text-xs uppercase">
                                <th class="text-left py-3 px-3">Asesor</th>
                                <th class="text-left py-3 px-2">Cliente</th>
                                <th class="text-left py-3 px-2">Celular</th>
                                <th class="text-left py-3 px-2">Dirección</th>
                                <th class="text-center py-3 px-2">GPS</th>
                                <th class="text-center py-3 px-2">Fecha</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                            @foreach($allVisitDetails as $v)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <td class="py-2.5 px-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color: {{ $asesorColors[$v['asesor_name']] ?? '#6b7280' }};"></div>
                                        <span class="font-medium text-gray-800 dark:text-gray-200">{{ $v['asesor_name'] }}</span>
                                    </div>
                                </td>
                                <td class="py-2.5 px-2 text-gray-700 dark:text-gray-300">{{ $v['client_name'] ?: '—' }}</td>
                                <td class="py-2.5 px-2 text-gray-500">{{ $v['client_phone'] ?: '—' }}</td>
                                <td class="py-2.5 px-2 text-gray-400 max-w-xs truncate" title="{{ $v['address'] }}">{{ $v['address'] ?: '—' }}</td>
                                <td class="py-2.5 px-2 text-center">
                                    @if($v['latitude'] && $v['longitude'])
                                        <span class="text-green-500">✅</span>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-2 text-center text-gray-500">{{ $v['date'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
    const markers = {!! json_encode($mapMarkers) !!};
    const colors = {!! json_encode($asesorColors) !!};

    if (document.getElementById('global-map') && markers.length > 0) {
        const map = L.map('global-map');
        
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
        markers.forEach((m, i) => {
            const color = colors[m.asesor_name] || '#6b7280';
            const icon = L.divIcon({
                html: `<div style="width:14px;height:14px;border-radius:50%;background:${color};border:2px solid white;box-shadow:0 1px 4px rgba(0,0,0,.4)"></div>`,
                className: '', iconSize: [14,14], iconAnchor: [7,7]
            });
            L.marker([m.lat, m.lng], {icon}).addTo(map)
                .bindPopup(`
                    <div style="min-width:200px">
                        <div style="font-weight:bold;margin-bottom:4px">👤 ${m.asesor_name}</div>
                        <strong>Cliente:</strong> ${m.client_name || '—'}<br>
                        <strong>📱</strong> ${m.client_phone || '—'}<br>
                        <strong>📅</strong> ${m.date}<br>
                        ${m.address ? '<strong>📌</strong> ' + m.address.substring(0,70) : ''}
                    </div>
                `);
            bounds.push([m.lat, m.lng]);
        });

        if (bounds.length === 1) map.setView(bounds[0], 15);
        else map.fitBounds(bounds, {padding:[30,30]});
    }
    </script>
</x-app-layout>
