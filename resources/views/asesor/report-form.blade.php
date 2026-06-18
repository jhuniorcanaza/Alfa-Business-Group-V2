<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('asesor.dashboard') }}" class="text-sm text-blue-600 hover:underline">← Volver al Dashboard</a>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mt-1">Enviar Reporte Diario</h2>
            </div>
        </div>
    </x-slot>

    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <style>
        .visit-map { height: 200px; border-radius: 0.75rem; z-index: 1; }
        .gps-btn { transition: all .2s; }
        .gps-btn:active { transform: scale(.95); }
    </style>

    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if(session('error'))
                <div class="p-4 rounded-xl bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="p-4 rounded-xl bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300">
                    <ul class="list-disc pl-5 text-sm">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
                </div>
            @endif

            @if($existingReport)
                <div class="bg-green-50 dark:bg-green-950/20 border border-green-200 dark:border-green-800 rounded-xl p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="text-2xl">✅</span>
                        <div>
                            <h3 class="font-bold text-green-800 dark:text-green-200">Ya enviaste tu reporte de hoy</h3>
                            <p class="text-sm text-green-700 dark:text-green-300">Solo puedes enviar un reporte por día.</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach([['🏠','Visitas',$existingReport->visits],['📋','Letrero',$existingReport->sign_captures],['📝','Exclusiva',$existingReport->exclusive_captures],['🤝','Cierres',$existingReport->closings],['📞','Llamadas',$existingReport->calls_made],['💻','AlphaX',$existingReport->properties_in_system]] as $k)
                        <div class="p-4 bg-white dark:bg-gray-800 rounded-lg text-center">
                            <span class="text-xs text-gray-500 block">{{ $k[0] }} {{ $k[1] }}</span>
                            <span class="text-2xl font-black text-gray-800 dark:text-gray-200">{{ $k[2] }}</span>
                        </div>
                        @endforeach
                    </div>
                    <div class="mt-4 pt-4 border-t border-green-200 dark:border-green-800 flex items-center justify-between flex-wrap gap-3">
                        <p class="text-xs text-green-600 dark:text-green-400">Enviado: {{ $existingReport->created_at->format('H:i') }} hrs</p>
                        <a href="{{ route('asesor.report.edit') }}" class="px-4 py-2 rounded-xl text-sm font-bold text-white bg-amber-600 hover:bg-amber-700 transition-colors shadow-md shadow-amber-600/20">✏️ Actualizar Reporte del Día de Hoy</a>
                    </div>
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 mb-6">
                        <h3 class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase mb-2">Datos Automáticos</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                            <div><span class="text-gray-400 text-xs">Asesor</span><p class="font-bold text-gray-800 dark:text-gray-200">{{ $user->name }}</p></div>
                            <div><span class="text-gray-400 text-xs">Jefe de Team</span><p class="font-bold text-gray-800 dark:text-gray-200">{{ $teamLeader ? $teamLeader->name : 'Sin asignar' }}</p></div>
                            <div><span class="text-gray-400 text-xs">Fecha</span><p class="font-bold text-gray-800 dark:text-gray-200">{{ $today->format('d/m/Y') }}</p></div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('asesor.report.store') }}" class="space-y-6" id="reportForm" enctype="multipart/form-data">
                        @csrf

                        {{-- ===== VISITAS CON GEOLOCALIZACIÓN ===== --}}
                        <div class="space-y-3">
                            <label class="flex items-center gap-2 text-sm font-bold text-gray-700 dark:text-gray-300">
                                <span>🏠</span> Visitas Realizadas
                                <span class="text-red-500">*</span>
                            </label>
                            <p class="text-xs text-gray-400">Registra cada visita con los datos del cliente y su ubicación GPS.</p>

                            {{-- Botón para agregar visita --}}
                            <button type="button" id="addVisitBtn"
                                class="gps-btn w-full py-3 rounded-xl border-2 border-dashed border-blue-300 dark:border-blue-700 text-blue-600 dark:text-blue-400 font-bold text-sm hover:bg-blue-50 dark:hover:bg-blue-950/20 flex items-center justify-center gap-2">
                                📍 Agregar Visita con Ubicación GPS
                            </button>

                            <div id="visitsContainer" class="space-y-4"></div>
                            <input type="hidden" name="visits" id="visits" value="0">
                        </div>

                        {{-- ===== CAPTACIONES CON LETRERO ===== --}}
                        <div>
                            <label for="sign_captures" class="flex items-center gap-2 text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">
                                <span>📋</span> Captaciones con Letrero
                            </label>
                            <p class="text-xs text-gray-400 mb-1.5">Inmuebles captados hoy donde colocaste letrero físico.</p>
                            <div class="hidden md:block">
                                <input type="number" name="sign_captures" id="sign_captures" required min="0" value="{{ old('sign_captures', 0) }}"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-gray-700 dark:text-gray-300 text-lg font-bold">
                            </div>
                            <div class="flex md:hidden items-center justify-center gap-4 bg-gray-50 dark:bg-gray-900/50 p-2 rounded-xl border border-gray-200 dark:border-gray-800 max-w-xs mx-auto w-full">
                                <button type="button" onclick="dec('sign_captures_m','sign_captures')" class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl flex items-center justify-center text-xl font-bold bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300 active:scale-90 transition-all select-none">➖</button>
                                <input type="text" id="sign_captures_m" readonly value="{{ old('sign_captures', 0) }}" class="w-16 text-center text-2xl font-black bg-transparent border-none focus:ring-0 text-gray-800 dark:text-gray-200">
                                <button type="button" onclick="inc('sign_captures_m','sign_captures')" class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl flex items-center justify-center text-xl font-bold bg-blue-100 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 active:scale-90 transition-all select-none">➕</button>
                            </div>
                        </div>

                        {{-- ===== FOTO DE RESPALDO DE LETRERO ===== --}}
                        <div id="sign_image_section" class="p-4 bg-amber-50 dark:bg-amber-950/10 border border-amber-200 dark:border-amber-900/50 rounded-xl space-y-3 hidden">
                            <label class="flex items-center gap-2 text-sm font-bold text-amber-800 dark:text-amber-300">
                                <span>📸</span> Fotos de Respaldo de los Letreros
                            </label>
                            <p class="text-xs text-amber-700 dark:text-amber-400">Es obligatorio subir una foto de respaldo por cada captación con letrero realizada.</p>
                            <div id="sign_images_container" class="space-y-3"></div>
                        </div>

                        {{-- ===== CAPTACIONES CON EXCLUSIVA ===== --}}
                        <div>
                            <label for="exclusive_captures" class="flex items-center gap-2 text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">
                                <span>📝</span> Captaciones con Exclusiva
                            </label>
                            <p class="text-xs text-gray-400 mb-1.5">Inmuebles captados hoy con contrato de exclusiva firmado.</p>
                            <div class="hidden md:block">
                                <input type="number" name="exclusive_captures" id="exclusive_captures" required min="0" value="{{ old('exclusive_captures', 0) }}"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-gray-700 dark:text-gray-300 text-lg font-bold">
                            </div>
                            <div class="flex md:hidden items-center justify-center gap-4 bg-gray-50 dark:bg-gray-900/50 p-2 rounded-xl border border-gray-200 dark:border-gray-800 max-w-xs mx-auto w-full">
                                <button type="button" onclick="dec('exclusive_captures_m','exclusive_captures')" class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl flex items-center justify-center text-xl font-bold bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300 active:scale-90 transition-all select-none">➖</button>
                                <input type="text" id="exclusive_captures_m" readonly value="{{ old('exclusive_captures', 0) }}" class="w-16 text-center text-2xl font-black bg-transparent border-none focus:ring-0 text-gray-800 dark:text-gray-200">
                                <button type="button" onclick="inc('exclusive_captures_m','exclusive_captures')" class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl flex items-center justify-center text-xl font-bold bg-blue-100 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 active:scale-90 transition-all select-none">➕</button>
                            </div>
                        </div>

                        {{-- ===== CIERRES ===== --}}
                        <div>
                            <label for="closings" class="flex items-center gap-2 text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">
                                <span>🤝</span> Cierres (Venta, Anticrético, Alquiler)
                            </label>
                            <p class="text-xs text-gray-400 mb-1.5">Operaciones cerradas hoy: ventas + anticréticos + alquileres.</p>
                            <div class="hidden md:block">
                                <input type="number" name="closings" id="closings" required min="0" value="{{ old('closings', 0) }}"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-gray-700 dark:text-gray-300 text-lg font-bold">
                            </div>
                            <div class="flex md:hidden items-center justify-center gap-4 bg-gray-50 dark:bg-gray-900/50 p-2 rounded-xl border border-gray-200 dark:border-gray-800 max-w-xs mx-auto w-full">
                                <button type="button" onclick="dec('closings_m','closings')" class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl flex items-center justify-center text-xl font-bold bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300 active:scale-90 transition-all select-none">➖</button>
                                <input type="text" id="closings_m" readonly value="{{ old('closings', 0) }}" class="w-16 text-center text-2xl font-black bg-transparent border-none focus:ring-0 text-gray-800 dark:text-gray-200">
                                <button type="button" onclick="inc('closings_m','closings')" class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl flex items-center justify-center text-xl font-bold bg-blue-100 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 active:scale-90 transition-all select-none">➕</button>
                            </div>
                        </div>

                        {{-- ===== LLAMADAS ===== --}}
                        <div>
                            <label for="calls_made" class="flex items-center gap-2 text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">
                                <span>📞</span> Llamadas Realizadas
                            </label>
                            <p class="text-xs text-gray-400 mb-1.5">Total de llamadas efectuadas hoy.</p>
                            <div class="hidden md:block">
                                <input type="number" name="calls_made" id="calls_made" required min="0" value="{{ old('calls_made', 0) }}"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-gray-700 dark:text-gray-300 text-lg font-bold">
                            </div>
                            <div class="flex md:hidden items-center justify-center gap-4 bg-gray-50 dark:bg-gray-900/50 p-2 rounded-xl border border-gray-200 dark:border-gray-800 max-w-xs mx-auto w-full">
                                <button type="button" onclick="dec('calls_made_m','calls_made')" class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl flex items-center justify-center text-xl font-bold bg-gray-250 dark:bg-gray-800 text-gray-700 dark:text-gray-300 active:scale-90 transition-all select-none">➖</button>
                                <input type="text" id="calls_made_m" readonly value="{{ old('calls_made', 0) }}" class="w-16 text-center text-2xl font-black bg-transparent border-none focus:ring-0 text-gray-800 dark:text-gray-200">
                                <button type="button" onclick="inc('calls_made_m','calls_made')" class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl flex items-center justify-center text-xl font-bold bg-blue-100 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 active:scale-90 transition-all select-none">➕</button>
                            </div>
                        </div>
                        <div id="phone-fields-container" class="space-y-3 p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl border border-gray-200 dark:border-gray-800 hidden">
                            <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider">📞 Números Llamados</h4>
                            <div id="phone-inputs-list" class="grid grid-cols-1 sm:grid-cols-2 gap-3"></div>
                        </div>

                        {{-- ===== ALPHAX ===== --}}
                        <div>
                            <label for="properties_in_system" class="flex items-center gap-2 text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">
                                <span>💻</span> Propiedades en Sistema (AlphaX)
                            </label>
                            <p class="text-xs text-gray-400 mb-1.5">Cantidad de propiedades subidas al sistema AlphaX hoy.</p>
                            <div class="hidden md:block">
                                <input type="number" name="properties_in_system" id="properties_in_system" required min="0" value="{{ old('properties_in_system', 0) }}"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-gray-700 dark:text-gray-300 text-lg font-bold">
                            </div>
                            <div class="flex md:hidden items-center justify-center gap-4 bg-gray-50 dark:bg-gray-900/50 p-2 rounded-xl border border-gray-200 dark:border-gray-800 max-w-xs mx-auto w-full">
                                <button type="button" onclick="dec('properties_in_system_m','properties_in_system')" class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl flex items-center justify-center text-xl font-bold bg-gray-250 dark:bg-gray-800 text-gray-700 dark:text-gray-300 active:scale-90 transition-all select-none">➖</button>
                                <input type="text" id="properties_in_system_m" readonly value="{{ old('properties_in_system', 0) }}" class="w-16 text-center text-2xl font-black bg-transparent border-none focus:ring-0 text-gray-800 dark:text-gray-200">
                                <button type="button" onclick="inc('properties_in_system_m','properties_in_system')" class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl flex items-center justify-center text-xl font-bold bg-blue-100 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 active:scale-90 transition-all select-none">➕</button>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                            <button type="submit" class="w-full py-3.5 rounded-xl text-white font-bold text-sm bg-blue-600 hover:bg-blue-700 transition-colors shadow-lg shadow-blue-600/20">
                                📤 Enviar Reporte del Día
                            </button>
                            <p class="text-xs text-gray-400 text-center mt-2">También puedes enviar tu reporte por WhatsApp al +591 69328062</p>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
    // ── Helpers de contador móvil ──────────────────────────────────────────
    function inc(mId, dId) {
        const m = document.getElementById(mId), d = document.getElementById(dId);
        let v = parseInt(d.value)||0; v++;
        d.value = m ? m.value = v : v;
        d.dispatchEvent(new Event('input',{bubbles:true}));
        d.dispatchEvent(new Event('change',{bubbles:true}));
    }
    function dec(mId, dId) {
        const m = document.getElementById(mId), d = document.getElementById(dId);
        let v = parseInt(d.value)||0; if(v>0) v--;
        d.value = m ? m.value = v : v;
        d.dispatchEvent(new Event('input',{bubbles:true}));
        d.dispatchEvent(new Event('change',{bubbles:true}));
    }

    // ── Módulo de Visitas con GPS ──────────────────────────────────────────
    let visitCount = 0;
    const visitMaps = {};
    const visitMarkers = {};
    const originalLocations = {};

    function showErrorModal(message) {
        const existing = document.getElementById('json-alert-modal');
        if (existing) existing.remove();
        
        const modal = document.createElement('div');
        modal.id = 'json-alert-modal';
        modal.className = 'fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-gray-950/70 backdrop-blur-sm transition-opacity duration-300';
        modal.innerHTML = `
            <div class="bg-gray-900 border border-red-500/30 rounded-2xl w-full max-w-sm overflow-hidden shadow-2xl transform scale-100 transition-transform duration-300">
                <div class="p-3.5 bg-gradient-to-r from-red-600 to-amber-600 flex items-center justify-between border-b border-red-900/40">
                    <div class="flex items-center gap-2 text-white font-mono font-bold text-xs uppercase tracking-wider">
                        <span>🚨 ADVERTENCIA</span>
                    </div>
                    <button type="button" onclick="document.getElementById('json-alert-modal').remove()" class="text-white/80 hover:text-white text-lg">✕</button>
                </div>
                <div class="p-6 space-y-4 text-center">
                    <div class="w-12 h-12 rounded-full bg-red-500/10 flex items-center justify-center mx-auto border border-red-500/20 text-red-500 text-xl">
                        ⚠️
                    </div>
                    <p class="text-sm text-gray-200 font-medium leading-relaxed">
                        ${message}
                    </p>
                    <button type="button" onclick="document.getElementById('json-alert-modal').remove()"
                        class="w-full py-2.5 rounded-xl bg-red-500/20 hover:bg-red-500/30 border border-red-500/40 text-red-200 font-bold text-xs uppercase tracking-wider transition-all active:scale-[0.98]">
                        Entendido
                    </button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }

    function getDistanceInMeters(lat1, lon1, lat2, lon2) {
        const R = 6371e3; // Radio de la Tierra en metros
        const phi1 = lat1 * Math.PI / 180;
        const phi2 = lat2 * Math.PI / 180;
        const deltaPhi = (lat2 - lat1) * Math.PI / 180;
        const deltaLambda = (lon2 - lon1) * Math.PI / 180;

        const a = Math.sin(deltaPhi/2) * Math.sin(deltaPhi/2) +
                  Math.cos(phi1) * Math.cos(phi2) *
                  Math.sin(deltaLambda/2) * Math.sin(deltaLambda/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));

        return R * c;
    }

    document.getElementById('addVisitBtn').addEventListener('click', addVisit);

    function addVisit() {
        visitCount++;
        const idx = visitCount - 1;
        document.getElementById('visits').value = visitCount;

        const card = document.createElement('div');
        card.id = 'visit-card-' + idx;
        card.className = 'bg-blue-50 dark:bg-blue-950/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4 space-y-3';
        card.innerHTML = `
            <div class="flex items-center justify-between">
                <h4 class="font-bold text-blue-700 dark:text-blue-300 text-sm">📍 Visita #${visitCount}</h4>
                <button type="button" onclick="removeVisit(${idx})" class="text-red-500 hover:text-red-700 text-xs font-bold">✕ Eliminar</button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-600 dark:text-gray-400 mb-1">👤 Nombre del Cliente <span class="text-red-500">*</span></label>
                    <input type="text" name="visit_client_name[]" required placeholder="Ej. Juan Pérez"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 dark:text-gray-400 mb-1">📱 Celular del Cliente <span class="text-red-500">*</span></label>
                    <input type="text" name="visit_client_phone[]" required placeholder="Ej. 71234567"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-600 dark:text-gray-400 mb-1">📌 Dirección del Inmueble</label>
                <input type="text" name="visit_address[]" id="addr-${idx}" placeholder="Se autocompleta con GPS..."
                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
            </div>
            <input type="hidden" name="visit_latitude[]" id="lat-${idx}">
            <input type="hidden" name="visit_longitude[]" id="lng-${idx}">
            <button type="button" onclick="captureGPS(${idx})" id="gps-btn-${idx}"
                class="gps-btn w-full py-2.5 rounded-xl bg-green-600 hover:bg-green-700 text-white font-bold text-sm flex items-center justify-center gap-2">
                📡 Capturar Ubicación GPS Actual
            </button>
            <div id="map-${idx}" class="visit-map hidden"></div>
            <p id="gps-status-${idx}" class="text-[10px] text-center text-gray-500 dark:text-gray-400 hidden mt-1"></p>
        `;

        document.getElementById('visitsContainer').appendChild(card);
    }

    function removeVisit(idx) {
        const card = document.getElementById('visit-card-' + idx);
        if (card) card.remove();
        if (visitMarkers[idx]) { delete visitMarkers[idx]; }
        if (visitMaps[idx]) { visitMaps[idx].remove(); delete visitMaps[idx]; }
        if (originalLocations[idx]) { delete originalLocations[idx]; }
        // Recontamos los cards visibles
        visitCount = document.querySelectorAll('[id^="visit-card-"]').length;
        document.getElementById('visits').value = visitCount;
    }

    function updateMarkerPosition(idx, lat, lng, isManual, accuracy = null) {
        // Validación de rango de 500 metros (geofencing) para evitar ubicaciones falsas
        if (isManual && originalLocations[idx]) {
            const dist = getDistanceInMeters(originalLocations[idx].lat, originalLocations[idx].lng, lat, lng);
            if (dist > 500) {
                const lastLat = parseFloat(document.getElementById('lat-' + idx).value) || originalLocations[idx].lat;
                const lastLng = parseFloat(document.getElementById('lng-' + idx).value) || originalLocations[idx].lng;

                if (visitMarkers[idx]) {
                    visitMarkers[idx].setLatLng([lastLat, lastLng]);
                }

                showErrorModal("La posición seleccionada supera el límite permitido de 500 metros de su ubicación actual.");

                if (visitMarkers[idx]) {
                    visitMarkers[idx].bindPopup('📍 Ubicación original (límite de 500m excedido)').openPopup();
                }
                return;
            }
        }

        document.getElementById('lat-' + idx).value = lat;
        document.getElementById('lng-' + idx).value = lng;

        const status = document.getElementById('gps-status-' + idx);
        status.textContent = '🔄 Buscando dirección en el mapa...';

        if (visitMarkers[idx]) {
            visitMarkers[idx].setLatLng([lat, lng]);
        } else {
            visitMarkers[idx] = L.marker([lat, lng], { draggable: true }).addTo(visitMaps[idx]);
            
            // Evento al arrastrar el pin
            visitMarkers[idx].on('dragend', function(e) {
                const pos = visitMarkers[idx].getLatLng();
                updateMarkerPosition(idx, pos.lat, pos.lng, true);
            });
        }

        const popupText = isManual 
            ? '📍 Ubicación ajustada manualmente<br>(Arrastra el pin si deseas corregirlo)' 
            : '📍 Ubicación capturada<br>Precisión: ±' + (accuracy || '??') + 'm (Arrastra el pin para corregir)';

        visitMarkers[idx].bindPopup(popupText).openPopup();

        // Geocodificación inversa con Nominatim (OpenStreetMap)
        fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`)
            .then(r => r.json())
            .then(data => {
                const addr = data.display_name || `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                document.getElementById('addr-' + idx).value = addr;
                status.textContent = isManual 
                    ? `✅ Ubicación ajustada manualmente. Puedes arrastrar el marcador en el mapa para mayor precisión.`
                    : `✅ Ubicación capturada · Precisión: ±${accuracy}m. Puedes arrastrar el marcador para corregir.`;
            })
            .catch(() => {
                document.getElementById('addr-' + idx).value = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                status.textContent = isManual 
                    ? `✅ Ubicación ajustada a coords: ${lat.toFixed(5)}, ${lng.toFixed(5)}` 
                    : `✅ Coords: ${lat.toFixed(5)}, ${lng.toFixed(5)} · ±${accuracy}m`;
            });

        const btn = document.getElementById('gps-btn-' + idx);
        if (btn) {
            btn.textContent = '✅ Ubicación Lista (clic para volver a capturar)';
            btn.classList.replace('bg-green-600','bg-emerald-700');
        }
    }

    function captureGPS(idx) {
        const btn = document.getElementById('gps-btn-' + idx);
        const status = document.getElementById('gps-status-' + idx);
        const mapEl = document.getElementById('map-' + idx);

        btn.textContent = '⏳ Obteniendo ubicación...';
        btn.disabled = true;
        status.classList.remove('hidden');
        status.textContent = 'Accediendo al GPS del dispositivo...';

        if (!navigator.geolocation) {
            status.textContent = '❌ Tu navegador no soporta geolocalización.';
            btn.textContent = '📡 Capturar Ubicación GPS Actual';
            btn.disabled = false;
            return;
        }

        navigator.geolocation.getCurrentPosition(
            function(pos) {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                const acc = Math.round(pos.coords.accuracy);

                // Guardar la ubicación original de GPS para geofencing
                originalLocations[idx] = { lat: lat, lng: lng };

                // Mostrar mapa Leaflet con capas satélite y calles
                mapEl.classList.remove('hidden');
                if (!visitMaps[idx]) {
                    visitMaps[idx] = L.map('map-' + idx).setView([lat, lng], 17);
                    
                    const satellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                        attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS'
                    });

                    const streets = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors'
                    });

                    // Cargar satélite por defecto
                    satellite.addTo(visitMaps[idx]);

                    // Control de capas
                    const baseMaps = {
                        "🛰️ Vista Satélite": satellite,
                        "🗺️ Vista Calles": streets
                    };
                    L.control.layers(baseMaps, null, { collapsed: false, position: 'topright' }).addTo(visitMaps[idx]);
                    
                    // Hacer clic en el mapa para ajustar ubicación
                    visitMaps[idx].on('click', function(e) {
                        updateMarkerPosition(idx, e.latlng.lat, e.latlng.lng, true);
                    });
                } else {
                    visitMaps[idx].setView([lat, lng], 17);
                }

                // Asegurar tamaño correcto de Leaflet (evita el bug de mapa gris desfasado)
                setTimeout(() => {
                    if (visitMaps[idx]) {
                        visitMaps[idx].invalidateSize();
                    }
                }, 100);

                // Dibujar y geocodificar marcador
                updateMarkerPosition(idx, lat, lng, false, acc);
                btn.disabled = false;
            },
            function(err) {
                status.textContent = '❌ ' + (err.code === 1 ? 'Permiso denegado. Habilita la ubicación.' : 'Error al obtener ubicación.');
                btn.textContent = '📡 Reintentar Captura GPS';
                btn.disabled = false;
            },
            { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
        );
    }

    // ── Campos de teléfonos de llamadas ───────────────────────────────────
    document.addEventListener('DOMContentLoaded', function() {
        const callsInput = document.getElementById('calls_made');
        const container  = document.getElementById('phone-fields-container');
        const list       = document.getElementById('phone-inputs-list');

        function updatePhoneFields() {
            const count = parseInt(callsInput.value) || 0;
            list.innerHTML = '';
            if (count > 0) {
                container.classList.remove('hidden');
                for (let i = 1; i <= count; i++) {
                    const div = document.createElement('div');
                    div.innerHTML = `<label class="text-xs font-bold text-gray-500 mb-0.5 block">Llamada #${i}</label>
                        <input type="text" name="call_phone_number_list[]" placeholder="Ej. 65763245"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">`;
                    list.appendChild(div);
                }
            } else {
                container.classList.add('hidden');
            }
        }

        callsInput.addEventListener('input', updatePhoneFields);
        callsInput.addEventListener('change', updatePhoneFields);

        // Sync mobile/desktop inputs
        [['sign_captures','sign_captures_m'],['exclusive_captures','exclusive_captures_m'],
         ['closings','closings_m'],['calls_made','calls_made_m'],['properties_in_system','properties_in_system_m']
        ].forEach(([d,m]) => {
            const di = document.getElementById(d), mi = document.getElementById(m);
            if(di && mi) {
                di.addEventListener('input', ()=>{ mi.value = di.value; });
                di.addEventListener('change', ()=>{ mi.value = di.value; });
            }
        });

        updatePhoneFields();

        // Toggle sign image slots dynamically based on count
        const signCapturesInput = document.getElementById('sign_captures');
        const signImageSection = document.getElementById('sign_image_section');
        const signImagesContainer = document.getElementById('sign_images_container');

        function toggleSignImage() {
            const count = parseInt(signCapturesInput.value) || 0;
            if (count > 0) {
                signImageSection.classList.remove('hidden');
                
                const currentWrappers = signImagesContainer.querySelectorAll('.sign-image-wrapper');
                const currentCount = currentWrappers.length;
                
                if (count > currentCount) {
                    for (let i = currentCount + 1; i <= count; i++) {
                        const div = document.createElement('div');
                        div.className = 'sign-image-wrapper space-y-1 bg-white dark:bg-gray-800 p-2.5 rounded-lg border border-amber-200 dark:border-amber-900/40';
                        div.innerHTML = `
                            <label class="block text-xs font-bold text-amber-800 dark:text-amber-300">
                                Letrero #${i} <span class="text-red-500">*</span>
                            </label>
                            <input type="file" name="sign_images[]" required accept="image/*" capture="environment"
                                class="w-full text-xs text-gray-500 file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-amber-100 file:text-amber-800 hover:file:bg-amber-250 cursor-pointer">
                        `;
                        signImagesContainer.appendChild(div);
                    }
                } else if (count < currentCount) {
                    for (let i = currentCount; i > count; i--) {
                        currentWrappers[i - 1].remove();
                    }
                }
            } else {
                signImageSection.classList.add('hidden');
                signImagesContainer.innerHTML = '';
            }
        }

        if (signCapturesInput && signImageSection && signImagesContainer) {
            signCapturesInput.addEventListener('input', toggleSignImage);
            signCapturesInput.addEventListener('change', toggleSignImage);
            
            const signCapturesMobile = document.getElementById('sign_captures_m');
            if (signCapturesMobile) {
                signCapturesMobile.addEventListener('input', () => {
                    signCapturesInput.value = signCapturesMobile.value;
                    toggleSignImage();
                });
                signCapturesMobile.addEventListener('change', () => {
                    signCapturesInput.value = signCapturesMobile.value;
                    toggleSignImage();
                });
            }
            toggleSignImage();
        }
    });
    </script>
</x-app-layout>
