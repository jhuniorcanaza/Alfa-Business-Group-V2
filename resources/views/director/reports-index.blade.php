<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <a href="{{ route('director.dashboard') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">← Volver al Dashboard</a>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mt-1">📸 Supervisión de Reportes y Evidencias</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Historial detallado de visitas, coordenadas GPS y fotos cargadas en campo</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('director.visits-map') }}" class="px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition-colors shadow-md shadow-emerald-600/20">
                    🗺️ Ver Mapa Global
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Leaflet para mini mapas interactivos -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- FILTROS DE BÚSQUEDA -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                <form method="GET" action="{{ route('director.reports.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Filtrar por Asesor</label>
                        <select name="user_id" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                            <option value="">-- Todos los Asesores --</option>
                            @foreach($asesores as $asesor)
                                <option value="{{ $asesor->id }}" {{ $asesorId == $asesor->id ? 'selected' : '' }}>
                                    {{ $asesor->name }} ({{ $asesor->office ? $asesor->office->name : 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Desde</label>
                        <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Hasta</label>
                        <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 py-2.5 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 transition-colors shadow-md shadow-blue-600/20">
                            🔍 Filtrar
                        </button>
                        <a href="{{ route('director.reports.index') }}" class="px-4 py-2.5 rounded-xl text-sm font-bold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-250 dark:hover:bg-gray-600 transition-colors text-center">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- FEED DE REPORTES -->
            @forelse($reports as $report)
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow">
                    <!-- HEADER DEL REPORTE -->
                    <div class="p-5 bg-gray-50 dark:bg-gray-700/30 border-b border-gray-100 dark:border-gray-700/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-300 flex items-center justify-center font-bold">
                                {{ substr($report->user->name, 0, 2) }}
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 dark:text-gray-200">{{ $report->user->name }}</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    🏢 {{ $report->user->office ? $report->user->office->name : 'Sin Oficina' }} · 
                                    👥 {{ $report->user->team ? $report->user->team->name : 'Sin Equipo' }}
                                </p>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-xs font-semibold px-3 py-1 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 rounded-full border border-blue-100 dark:border-blue-900/30">
                                📅 {{ $report->report_date->format('d/m/Y') }}
                            </span>
                            <span class="text-xs font-bold px-3 py-1 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 rounded-full border border-emerald-100 dark:border-emerald-900/30">
                                🏠 {{ $report->visits }} Visitas
                            </span>
                            <span class="text-xs font-bold px-3 py-1 bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 rounded-full border border-amber-100 dark:border-amber-900/30">
                                🤝 {{ $report->closings }} Cierres
                            </span>
                            <span class="text-xs font-bold px-3 py-1 bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-400 rounded-full border border-purple-100 dark:border-purple-900/30">
                                📋 {{ $report->sign_captures }} Letreros
                            </span>
                        </div>
                    </div>

                    <!-- CUERPO DEL REPORTE -->
                    <div class="p-6 grid grid-cols-1 lg:grid-cols-12 gap-6">
                        <!-- COLUMNA 1: DETALLE DE VISITAS -->
                        <div class="lg:col-span-8 space-y-4">
                            <h4 class="text-sm font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                <span>📌</span> Detalle de Visitas Realizadas
                            </h4>
                            @if($report->visits_data && is_array($report->visits_data) && count($report->visits_data) > 0)
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @foreach($report->visits_data as $idx => $v)
                                        <div class="p-4 bg-gray-50 dark:bg-gray-900/40 rounded-xl border border-gray-100 dark:border-gray-800 space-y-2 flex flex-col justify-between">
                                            <div>
                                                <div class="flex items-center justify-between border-b dark:border-gray-800 pb-1.5 mb-2">
                                                    <span class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase">Visita #{{ $idx + 1 }}</span>
                                                    <span class="text-[10px] text-gray-400">Client ID: {{ $idx + 1 }}</span>
                                                </div>
                                                <p class="text-sm font-bold text-gray-800 dark:text-gray-200">👤 {{ $v['client_name'] ?: 'Cliente sin nombre' }}</p>
                                                @if(!empty($v['client_phone']))
                                                    <p class="text-xs text-gray-600 dark:text-gray-400 flex items-center gap-1 mt-1">
                                                        <span>📱 Celular:</span>
                                                        <a href="tel:{{ $v['client_phone'] }}" class="text-blue-500 hover:underline">{{ $v['client_phone'] }}</a>
                                                    </p>
                                                @endif
                                                @if(!empty($v['address']))
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 flex items-start gap-1 mt-1">
                                                        <span class="flex-shrink-0">📌 Dir:</span>
                                                        <span class="line-clamp-2" title="{{ $v['address'] }}">{{ $v['address'] }}</span>
                                                    </p>
                                                @endif
                                            </div>

                                            @if(!empty($v['latitude']) && !empty($v['longitude']))
                                                <div class="pt-3 border-t dark:border-gray-800 flex gap-2">
                                                    <button type="button" 
                                                        onclick="openMiniMapModal('{{ $report->user->name }}', '{{ $v['client_name'] }}', {{ $v['latitude'] }}, {{ $v['longitude'] }})"
                                                        class="flex-1 py-1.5 px-3 rounded-lg text-xs font-bold bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/30 dark:hover:bg-blue-900/50 text-blue-600 dark:text-blue-400 border border-blue-200/50 dark:border-blue-900/40 transition-colors flex items-center justify-center gap-1">
                                                        🗺️ Ver Mapa
                                                    </button>
                                                    <a href="https://www.google.com/maps/search/?api=1&query={{ $v['latitude'] }},{{ $v['longitude'] }}" 
                                                       target="_blank" 
                                                       class="py-1.5 px-3 rounded-lg text-xs font-bold bg-green-50 hover:bg-green-100 dark:bg-green-900/30 dark:hover:bg-green-900/50 text-green-600 dark:text-green-400 border border-green-200/50 dark:border-green-900/40 transition-colors flex items-center justify-center gap-1">
                                                        ↗️ Google
                                                    </a>
                                                </div>
                                            @else
                                                <div class="pt-3 border-t dark:border-gray-800">
                                                    <span class="text-[10px] text-gray-400 dark:text-gray-500 flex items-center gap-1">
                                                        ⚠️ Visita sin coordenadas GPS
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-6 bg-gray-50 dark:bg-gray-900/20 rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-800 text-gray-500 text-sm">
                                    Visitas registradas como conteo simple, sin coordenadas GPS.
                                </div>
                            @endif
                        </div>

                        <!-- COLUMNA 2: FOTOS DE EVIDENCIA -->
                        <div class="lg:col-span-4 space-y-4">
                            <h4 class="text-sm font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                <span>📸</span> Evidencias Cargadas (Letreros)
                            </h4>
                            @php
                                $photoUrls = [];
                                if (!empty($report->sign_image_path)) {
                                    $decoded = json_decode($report->sign_image_path, true);
                                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                        $photoUrls = $decoded;
                                    } else {
                                        $photoUrls = [$report->sign_image_path];
                                    }
                                }
                            @endphp
                            
                            @if(count($photoUrls) > 0)
                                <div class="grid grid-cols-2 gap-3">
                                    @foreach($photoUrls as $index => $path)
                                        <div class="relative group aspect-square rounded-xl overflow-hidden border border-gray-200 dark:border-gray-800 bg-black cursor-pointer shadow-sm hover:shadow-md transition-all"
                                             onclick="openLightbox('{{ asset($path) }}', 'Evidencia #{{ $index + 1 }} - {{ $report->user->name }}')">
                                            <img src="{{ asset($path) }}" alt="Evidencia #{{ $index + 1 }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300 opacity-90 group-hover:opacity-100">
                                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent flex items-end p-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <span class="text-[10px] font-bold text-white tracking-wide uppercase">🔍 Zoom Foto</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="flex flex-col items-center justify-center h-32 bg-gray-50 dark:bg-gray-900/20 rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-800 text-gray-400 dark:text-gray-600 text-center p-4">
                                    <span class="text-2xl mb-1">📷</span>
                                    <p class="text-xs font-bold">Sin imágenes de letreros</p>
                                    <p class="text-[10px] text-gray-400 mt-0.5">El asesor no subió respaldos para este reporte.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
                    <span class="text-5xl mb-4 block">📋</span>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">No se encontraron reportes</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-md mx-auto">Prueba ajustando los filtros de fecha o seleccionando otro asesor de la lista.</p>
                </div>
            @endforelse

            <!-- PAGINADOR -->
            <div class="pt-4">
                {{ $reports->links() }}
            </div>

        </div>
    </div>

    <!-- MODAL DE LIGHTBOX PARA FOTOS -->
    <div id="lightbox-modal" class="fixed inset-0 z-[9999] hidden flex-col items-center justify-center p-4 bg-gray-950/90 backdrop-blur-sm transition-opacity duration-300">
        <button type="button" onclick="closeLightbox()" class="absolute top-4 right-4 text-white hover:text-gray-300 text-3xl font-bold bg-black/40 hover:bg-black/60 w-12 h-12 rounded-full flex items-center justify-center transition-colors">✕</button>
        <div class="max-w-4xl max-h-[80vh] overflow-hidden rounded-xl shadow-2xl flex items-center justify-center">
            <img id="lightbox-img" src="" alt="Zoom" class="max-w-full max-h-[85vh] object-contain rounded-lg">
        </div>
        <p id="lightbox-caption" class="text-white mt-4 font-bold tracking-wide text-sm bg-black/50 py-1.5 px-4 rounded-full"></p>
    </div>

    <!-- MODAL DE MINI MAPA LEAFLET -->
    <div id="minimap-modal" class="fixed inset-0 z-[9999] hidden flex items-center justify-center p-4 bg-gray-950/80 backdrop-blur-sm transition-opacity duration-300">
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl w-full max-w-xl overflow-hidden shadow-2xl transform scale-100 transition-transform duration-300">
            <div class="p-3.5 bg-gradient-to-r from-blue-600 to-indigo-600 flex items-center justify-between">
                <div class="flex flex-col text-white">
                    <span id="map-modal-title" class="font-bold text-sm">📍 Ubicación de la Visita</span>
                    <span id="map-modal-subtitle" class="text-[10px] text-white/80">Cargando detalles de geolocalización...</span>
                </div>
                <button type="button" onclick="closeMiniMapModal()" class="text-white/80 hover:text-white text-lg bg-white/10 hover:bg-white/20 w-8 h-8 rounded-full flex items-center justify-center transition-all">✕</button>
            </div>
            <div class="p-4 space-y-4">
                <!-- Contenedor del mapa Leaflet -->
                <div id="modal-map" class="w-full h-80 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-900"></div>
                
                <div class="flex gap-2">
                    <button type="button" onclick="closeMiniMapModal()"
                        class="flex-1 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 font-bold text-xs uppercase tracking-wider transition-all active:scale-[0.98]">
                        Cerrar Mapa
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPTS MODAL & LEAFLET -->
    <script>
        // Funciones del Lightbox de Fotos
        function openLightbox(src, caption) {
            document.getElementById('lightbox-img').src = src;
            document.getElementById('lightbox-caption').textContent = caption;
            
            const modal = document.getElementById('lightbox-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeLightbox() {
            const modal = document.getElementById('lightbox-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.getElementById('lightbox-img').src = "";
        }

        // Funciones del Mapa Leaflet en Modal
        let modalMap = null;
        let modalMarker = null;

        function openMiniMapModal(asesorName, clientName, lat, lng) {
            document.getElementById('map-modal-title').textContent = "📍 Ubicación: " + clientName;
            document.getElementById('map-modal-subtitle').textContent = "Capturada por Asesor: " + asesorName;

            const modal = document.getElementById('minimap-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            // Inicializar o reposicionar mapa Leaflet
            setTimeout(() => {
                if (!modalMap) {
                    modalMap = L.map('modal-map').setView([lat, lng], 17);
                    
                    const satellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                        attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS'
                    });

                    const streets = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors'
                    });

                    // Satélite por defecto
                    satellite.addTo(modalMap);

                    const baseMaps = {
                        "🛰️ Vista Satélite": satellite,
                        "🗺️ Vista Calles": streets
                    };
                    L.control.layers(baseMaps, null, { collapsed: false, position: 'topright' }).addTo(modalMap);
                } else {
                    modalMap.setView([lat, lng], 17);
                }

                // Asegurar tamaño correcto de renderizado
                modalMap.invalidateSize();

                // Agregar o mover marcador
                if (modalMarker) {
                    modalMarker.setLatLng([lat, lng]);
                } else {
                    modalMarker = L.marker([lat, lng]).addTo(modalMap);
                }
                modalMarker.bindPopup(`<strong>📍 ${clientName}</strong><br>Asesor: ${asesorName}`).openPopup();
            }, 100);
        }

        function closeMiniMapModal() {
            const modal = document.getElementById('minimap-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        // Cerrar modales con escape
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeLightbox();
                closeMiniMapModal();
            }
        });
    </script>

    <style>
        /* Ajustar z-index de los controles Leaflet para que se mantengan dentro del contenedor del modal */
        #modal-map {
            z-index: 10;
        }
    </style>
</x-app-layout>
