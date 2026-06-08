<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('asesor.dashboard') }}" class="text-sm text-blue-600 hover:underline">← Volver</a>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mt-1">🗺️ Mis Visitas en el Mapa</h2>
                <p class="text-sm text-gray-500 mt-1">Semana del {{ $startOfWeek->format('d/m/Y') }} al {{ $endOfWeek->format('d/m/Y') }}</p>
            </div>
        </div>
    </x-slot>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <style>#visits-map{height:500px;border-radius:1rem;z-index:1;}</style>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Stats --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center shadow-sm">
                    <p class="text-3xl font-black text-blue-600">{{ $totalVisits }}</p>
                    <p class="text-xs text-gray-500 mt-1 uppercase font-bold">Total Visitas</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center shadow-sm">
                    <p class="text-3xl font-black text-green-600">{{ $geoVisits }}</p>
                    <p class="text-xs text-gray-500 mt-1 uppercase font-bold">Con GPS</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center shadow-sm">
                    <p class="text-3xl font-black text-purple-600">{{ $uniqueClients }}</p>
                    <p class="text-xs text-gray-500 mt-1 uppercase font-bold">Clientes</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center shadow-sm">
                    <p class="text-3xl font-black text-amber-600">{{ $reportDays }}</p>
                    <p class="text-xs text-gray-500 mt-1 uppercase font-bold">Días Activos</p>
                </div>
            </div>

            {{-- Mapa --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-3 flex items-center gap-2">
                    <span>🗺️</span> Mapa de Visitas de la Semana
                </h3>
                @if($geoVisits > 0)
                    <div id="visits-map"></div>
                @else
                    <div class="flex flex-col items-center justify-center h-64 bg-gray-50 dark:bg-gray-700/30 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600">
                        <span class="text-5xl mb-3">📍</span>
                        <p class="text-gray-500 font-bold">Sin ubicaciones GPS esta semana</p>
                        <p class="text-xs text-gray-400 mt-1">Captura tu ubicación al registrar visitas para verlas aquí.</p>
                    </div>
                @endif
            </div>

            {{-- Listado de visitas --}}
            @if($allVisitDetails->count() > 0)
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4">📋 Detalle de Visitas de la Semana</h3>
                <div class="space-y-3">
                    @foreach($allVisitDetails as $item)
                    <div class="flex items-start gap-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-700/30 border border-gray-100 dark:border-gray-700">
                        <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center flex-shrink-0">
                            <span class="text-lg">📍</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                <p class="font-bold text-gray-800 dark:text-gray-200 text-sm">{{ $item['client_name'] ?: 'Cliente sin nombre' }}</p>
                                <span class="text-xs text-gray-400 flex-shrink-0">{{ $item['date'] }}</span>
                            </div>
                            @if($item['client_phone'])
                            <p class="text-xs text-gray-500 mt-0.5">📱 {{ $item['client_phone'] }}</p>
                            @endif
                            @if($item['address'])
                            <p class="text-xs text-gray-400 mt-0.5 truncate" title="{{ $item['address'] }}">📌 {{ $item['address'] }}</p>
                            @endif
                            @if($item['latitude'] && $item['longitude'])
                            <span class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 rounded-full text-xs bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 font-medium">
                                ✅ GPS Capturado
                            </span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
    const markers = {!! json_encode($mapMarkers) !!};

    if (document.getElementById('visits-map') && markers.length > 0) {
        const map = L.map('visits-map');
        
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
            const marker = L.marker([m.lat, m.lng]).addTo(map);
            marker.bindPopup(`
                <div style="min-width:180px">
                    <strong>📍 Visita #${i+1}</strong><br>
                    <strong>👤</strong> ${m.client_name || 'Sin nombre'}<br>
                    <strong>📱</strong> ${m.client_phone || '—'}<br>
                    <strong>📅</strong> ${m.date}<br>
                    ${m.address ? '<strong>📌</strong> ' + m.address.substring(0,60) + '...' : ''}
                </div>
            `);
            bounds.push([m.lat, m.lng]);
        });

        if (bounds.length === 1) {
            map.setView(bounds[0], 15);
        } else {
            map.fitBounds(bounds, { padding: [30, 30] });
        }
    }
    </script>
</x-app-layout>
