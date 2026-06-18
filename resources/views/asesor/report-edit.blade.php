<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('asesor.dashboard') }}" class="text-sm text-blue-600 hover:underline">← Volver al Dashboard</a>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mt-1">Actualizar Reporte del Día de Hoy</h2>
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
                <div class="p-4 rounded-xl bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="p-4 rounded-xl bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300">
                    <ul class="list-disc pl-5 text-sm">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                {{-- Encabezado automático --}}
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 mb-6">
                    <h3 class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase mb-2">Datos Automáticos</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                        <div>
                            <span class="text-gray-400 text-xs">Asesor</span>
                            <p class="font-bold text-gray-800 dark:text-gray-200">{{ $user->name }}</p>
                        </div>
                        <div>
                            <span class="text-gray-400 text-xs">Jefe de Team</span>
                            <p class="font-bold text-gray-800 dark:text-gray-200">{{ $teamLeader ? $teamLeader->name : 'Sin asignar' }}</p>
                        </div>
                        <div>
                            <span class="text-gray-400 text-xs">Fecha</span>
                            <p class="font-bold text-gray-800 dark:text-gray-200">{{ $today->format('d/m/Y') }} (Hoy)</p>
                        </div>
                    </div>
                </div>

                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4">✏️ Modifica tus 6 indicadores del día</h3>

                <form method="POST" action="{{ route('asesor.report.update') }}" class="space-y-6" id="reportForm" enctype="multipart/form-data">
                    @csrf

                    {{-- ===== VISITAS REALIZADAS (PREVIAS Y NUEVAS) ===== --}}
                    <div class="space-y-3">
                        <label class="flex items-center gap-2 text-sm font-bold text-gray-700 dark:text-gray-300">
                            <span>🏠</span> Visitas Realizadas
                        </label>

                        {{-- Lista de visitas ya registradas anteriormente (READ-ONLY) --}}
                        @if($report->visits_data && count($report->visits_data) > 0)
                            <div class="space-y-2.5">
                                <h4 class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Visitas Ya Registradas y Guardadas hoy:</h4>
                                @foreach($report->visits_data as $index => $visit)
                                    <div class="bg-gray-50 dark:bg-gray-900/40 border border-gray-150 dark:border-gray-800 rounded-xl p-4 space-y-2 opacity-85">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-bold text-gray-650 dark:text-gray-450">📍 Visita #{{ $index + 1 }} (Registrada)</span>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xxs font-bold bg-gray-200 dark:bg-gray-750 text-gray-550 dark:text-gray-450">
                                                🔒 Guardado
                                            </span>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                                            <div>
                                                <span class="text-xxs text-gray-400 block">Cliente</span>
                                                <span class="font-bold text-gray-700 dark:text-gray-300">{{ $visit['client_name'] ?? '—' }}</span>
                                            </div>
                                            <div>
                                                <span class="text-xxs text-gray-400 block">Celular</span>
                                                <span class="font-bold text-gray-700 dark:text-gray-300">{{ $visit['client_phone'] ?? '—' }}</span>
                                            </div>
                                        </div>
                                        @if(!empty($visit['address']))
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            <span class="text-xxs text-gray-400 block">Dirección de Inmueble</span>
                                            <span>📌 {{ $visit['address'] }}</span>
                                        </div>
                                        @endif
                                        @if(!empty($visit['latitude']) && !empty($visit['longitude']))
                                        <div class="pt-1">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xxs bg-green-50 dark:bg-green-950/20 text-green-700 dark:text-green-400 font-semibold">
                                                ✅ Geolocalización GPS Exitosa
                                            </span>
                                        </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @elseif($report->visits > 0)
                            {{-- Caso de legacy data sin JSON data estructurado --}}
                            <div class="space-y-2">
                                <h4 class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider font-semibold">Visitas Ya Registradas y Guardadas hoy:</h4>
                                @for($i = 1; $i <= $report->visits; $i++)
                                    <div class="bg-gray-50 dark:bg-gray-900/40 border border-gray-150 dark:border-gray-800 rounded-xl p-4 opacity-85 flex items-center justify-between">
                                        <span class="text-xs font-bold text-gray-650 dark:text-gray-450">📍 Visita #{{ $i }} (Registrada sin GPS)</span>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xxs font-bold bg-gray-200 dark:bg-gray-750 text-gray-550 dark:text-gray-450">
                                            🔒 Guardado
                                        </span>
                                    </div>
                                @endfor
                            </div>
                        @endif

                        {{-- Botón para agregar NUEVA visita --}}
                        <div class="pt-2">
                            <button type="button" id="addVisitBtn"
                                class="gps-btn w-full py-3 rounded-xl border-2 border-dashed border-blue-300 dark:border-blue-700 text-blue-600 dark:text-blue-400 font-bold text-sm hover:bg-blue-50 dark:hover:bg-blue-950/20 flex items-center justify-center gap-2">
                                ➕ Registrar Nueva Visita hoy (con ubicación GPS)
                            </button>
                        </div>

                        {{-- Contenedor de las nuevas visitas dinámicas --}}
                        <div id="newVisitsContainer" class="space-y-4"></div>

                        {{-- Indicador y contador de visitas totales --}}
                        <div class="bg-blue-50 dark:bg-blue-950/10 border border-blue-150 dark:border-blue-900/40 rounded-xl p-3 flex items-center justify-between text-sm">
                            <span class="font-bold text-blue-800 dark:text-blue-300">Total acumulado de visitas de hoy:</span>
                            <span class="text-lg font-black text-blue-900 dark:text-blue-200" id="totalVisitsBadge">
                                {{ $report->visits }}
                            </span>
                        </div>
                    </div>

                    @php
                        $images = [];
                        if (!empty($report->sign_image_path)) {
                            $decoded = json_decode($report->sign_image_path, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                $images = $decoded;
                            } else {
                                $images = [$report->sign_image_path];
                            }
                        }
                    @endphp

                    {{-- ===== CAPTACIONES CON LETRERO ===== --}}
                    <div>
                        <label for="sign_captures" class="flex items-center gap-2 text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">
                            <span>📋</span> Captaciones con Letrero
                        </label>
                        <p class="text-xs text-gray-400 mb-1.5">Inmuebles captados hoy donde colocaste letrero físico.</p>
                        <div class="hidden md:block">
                            <input type="number" name="sign_captures" id="sign_captures" required min="0" value="{{ old('sign_captures', $report->sign_captures) }}"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-gray-700 dark:text-gray-300 text-lg font-bold">
                        </div>
                        <div class="flex md:hidden items-center justify-center gap-4 bg-gray-50 dark:bg-gray-900/50 p-2 rounded-xl border border-gray-200 dark:border-gray-800 max-w-xs mx-auto w-full">
                            <button type="button" onclick="dec('sign_captures_m','sign_captures')" class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl flex items-center justify-center text-xl font-bold bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300 active:scale-90 transition-all select-none">➖</button>
                            <input type="text" id="sign_captures_m" readonly value="{{ old('sign_captures', $report->sign_captures) }}" class="w-16 text-center text-2xl font-black bg-transparent border-none focus:ring-0 text-gray-800 dark:text-gray-200">
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
                            <input type="number" name="exclusive_captures" id="exclusive_captures" required min="0" value="{{ old('exclusive_captures', $report->exclusive_captures) }}"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-gray-700 dark:text-gray-300 text-lg font-bold">
                        </div>
                        <div class="flex md:hidden items-center justify-center gap-4 bg-gray-50 dark:bg-gray-900/50 p-2 rounded-xl border border-gray-200 dark:border-gray-800 max-w-xs mx-auto w-full">
                            <button type="button" onclick="dec('exclusive_captures_m','exclusive_captures')" class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl flex items-center justify-center text-xl font-bold bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300 active:scale-90 transition-all select-none">➖</button>
                            <input type="text" id="exclusive_captures_m" readonly value="{{ old('exclusive_captures', $report->exclusive_captures) }}" class="w-16 text-center text-2xl font-black bg-transparent border-none focus:ring-0 text-gray-800 dark:text-gray-200">
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
                            <input type="number" name="closings" id="closings" required min="0" value="{{ old('closings', $report->closings) }}"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-gray-700 dark:text-gray-300 text-lg font-bold">
                        </div>
                        <div class="flex md:hidden items-center justify-center gap-4 bg-gray-50 dark:bg-gray-900/50 p-2 rounded-xl border border-gray-200 dark:border-gray-800 max-w-xs mx-auto w-full">
                            <button type="button" onclick="dec('closings_m','closings')" class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl flex items-center justify-center text-xl font-bold bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300 active:scale-90 transition-all select-none">➖</button>
                            <input type="text" id="closings_m" readonly value="{{ old('closings', $report->closings) }}" class="w-16 text-center text-2xl font-black bg-transparent border-none focus:ring-0 text-gray-800 dark:text-gray-200">
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
                            <input type="number" name="calls_made" id="calls_made" required min="0" value="{{ old('calls_made', $report->calls_made) }}"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-gray-700 dark:text-gray-300 text-lg font-bold">
                        </div>
                        
                        <div class="flex md:hidden items-center justify-center gap-4 bg-gray-50 dark:bg-gray-900/50 p-2 rounded-xl border border-gray-200 dark:border-gray-800 max-w-xs mx-auto w-full">
                            <button type="button" onclick="dec('calls_made_m','calls_made')" class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl flex items-center justify-center text-xl font-bold bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300 active:scale-90 transition-all select-none">➖</button>
                            <input type="text" id="calls_made_m" readonly value="{{ old('calls_made', $report->calls_made) }}" class="w-16 text-center text-2xl font-black bg-transparent border-none focus:ring-0 text-gray-800 dark:text-gray-200">
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
                            <input type="number" name="properties_in_system" id="properties_in_system" required min="0" value="{{ old('properties_in_system', $report->properties_in_system) }}"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-gray-700 dark:text-gray-300 text-lg font-bold">
                        </div>
                        
                        <div class="flex md:hidden items-center justify-center gap-4 bg-gray-50 dark:bg-gray-900/50 p-2 rounded-xl border border-gray-200 dark:border-gray-800 max-w-xs mx-auto w-full">
                            <button type="button" onclick="dec('properties_in_system_m','properties_in_system')" class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl flex items-center justify-center text-xl font-bold bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300 active:scale-90 transition-all select-none">➖</button>
                            <input type="text" id="properties_in_system_m" readonly value="{{ old('properties_in_system', $report->properties_in_system) }}" class="w-16 text-center text-2xl font-black bg-transparent border-none focus:ring-0 text-gray-800 dark:text-gray-200">
                            <button type="button" onclick="inc('properties_in_system_m','properties_in_system')" class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl flex items-center justify-center text-xl font-bold bg-blue-100 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 active:scale-90 transition-all select-none">➕</button>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button type="submit" class="w-full py-3.5 rounded-xl text-white font-bold text-sm bg-amber-600 hover:bg-amber-700 transition-colors shadow-lg shadow-amber-600/20">
                            💾 Guardar Cambios y Actualizar Reporte
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
    // Contadores para móvil
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

    // Lógica del Módulo de Visitas Dinámicas
    const baseVisitsCount = {{ $report->visits }};
    let newVisitCount = 0;
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

    function updateVisitsBadge() {
        const total = baseVisitsCount + newVisitCount;
        document.getElementById('totalVisitsBadge').textContent = total;
    }

    document.getElementById('addVisitBtn').addEventListener('click', () => {
        newVisitCount++;
        const idx = newVisitCount - 1;
        updateVisitsBadge();

        const card = document.createElement('div');
        card.id = 'new-visit-card-' + idx;
        card.className = 'bg-blue-50 dark:bg-blue-950/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4 space-y-3';
        card.innerHTML = `
            <div class="flex items-center justify-between">
                <h4 class="font-bold text-blue-700 dark:text-blue-300 text-sm">📍 Nueva Visita #${newVisitCount}</h4>
                <button type="button" onclick="removeNewVisit(${idx})" class="text-red-500 hover:text-red-700 text-xs font-bold">✕ Eliminar</button>
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

        document.getElementById('newVisitsContainer').appendChild(card);
    });

    window.removeNewVisit = function(idx) {
        const card = document.getElementById('new-visit-card-' + idx);
        if (card) card.remove();
        if (visitMarkers[idx]) { delete visitMarkers[idx]; }
        if (visitMaps[idx]) { visitMaps[idx].remove(); delete visitMaps[idx]; }
        if (originalLocations[idx]) { delete originalLocations[idx]; }
        newVisitCount = document.querySelectorAll('[id^="new-visit-card-"]').length;
        updateVisitsBadge();
    };

    window.updateMarkerPosition = function(idx, lat, lng, isManual, accuracy = null) {
        // Geofencing: rango de 500 metros para evitar ubicaciones falsas
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
                window.updateMarkerPosition(idx, pos.lat, pos.lng, true);
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
    };

    window.captureGPS = function(idx) {
        const btn = document.getElementById('gps-btn-' + idx);
        const status = document.getElementById('gps-status-' + idx);
        const mapEl = document.getElementById('map-' + idx);

        btn.textContent = '⏳ Obteniendo ubicación...';
        btn.disabled = true;
        status.classList.remove('hidden');
        status.textContent = 'Accediendo al GPS del dispositivo...';

        if (!navigator.geolocation) {
            status.textContent = '❌ Tu navegador no soporta geolocalización.';
            btn.textContent = '📡 Capturar Ubicación GPS';
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
                        window.updateMarkerPosition(idx, e.latlng.lat, e.latlng.lng, true);
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
                window.updateMarkerPosition(idx, lat, lng, false, acc);
                btn.disabled = false;
            },
            function(err) {
                status.textContent = '❌ ' + (err.code === 1 ? 'Permiso denegado. Habilita la ubicación.' : 'Error al obtener ubicación.');
                btn.textContent = '📡 Reintentar Captura GPS';
                btn.disabled = false;
            },
            { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
        );
    };

    // Llamadas y sincronizaciones
    document.addEventListener('DOMContentLoaded', function() {
        const callsInput = document.getElementById('calls_made');
        const container  = document.getElementById('phone-fields-container');
        const list       = document.getElementById('phone-inputs-list');
        const existingPhones = @json($existingPhones);

        function updatePhoneFields() {
            const count = parseInt(callsInput.value) || 0;
            list.innerHTML = '';
            if (count > 0) {
                container.classList.remove('hidden');
                for (let i = 1; i <= count; i++) {
                    const val = existingPhones[i - 1] || '';
                    const div = document.createElement('div');
                    div.innerHTML = `<label class="text-xs font-bold text-gray-500 mb-0.5 block">Llamada #${i}</label>
                        <input type="text" name="call_phone_number_list[]" value="${val}" placeholder="Ej. 65763245"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">`;
                    list.appendChild(div);
                }
            } else {
                container.classList.add('hidden');
            }
        }

        callsInput.addEventListener('input', updatePhoneFields);
        callsInput.addEventListener('change', updatePhoneFields);

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

        // Toggle sign image slots dynamically based on count (with existing images loaded)
        const signCapturesInput = document.getElementById('sign_captures');
        const signImageSection = document.getElementById('sign_image_section');
        const signImagesContainer = document.getElementById('sign_images_container');
        const existingImages = @json($images);

        function toggleSignImage() {
            const count = parseInt(signCapturesInput.value) || 0;
            if (count > 0) {
                signImageSection.classList.remove('hidden');
                
                const currentWrappers = signImagesContainer.querySelectorAll('.sign-image-wrapper');
                const currentCount = currentWrappers.length;
                
                if (count > currentCount) {
                    for (let i = currentCount + 1; i <= count; i++) {
                        const div = document.createElement('div');
                        div.className = 'sign-image-wrapper space-y-2 bg-white dark:bg-gray-800 p-2.5 rounded-lg border border-amber-200 dark:border-amber-900/40';
                        
                        const existingPath = existingImages[i - 1];
                        if (existingPath) {
                            div.innerHTML = `
                                <div class="flex items-center gap-3 bg-gray-50 dark:bg-gray-700/50 p-2 rounded-lg border border-amber-100">
                                    <img src="${window.location.origin}/${existingPath}" alt="Letrero #${i}" class="w-12 h-12 object-cover rounded-lg border">
                                    <div>
                                        <p class="text-xs font-bold text-green-700 dark:text-green-400">✅ Letrero #${i} ya subido</p>
                                        <p class="text-[10px] text-gray-400">Para cambiarlo, selecciona una foto abajo.</p>
                                    </div>
                                </div>
                                <input type="file" name="sign_images[${i-1}]" accept="image/*" capture="environment"
                                    class="w-full text-xs text-gray-500 file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-amber-100 file:text-amber-800 hover:file:bg-amber-250 cursor-pointer">
                            `;
                        } else {
                            div.innerHTML = `
                                <label class="block text-xs font-bold text-amber-800 dark:text-amber-300">
                                    Letrero #${i} <span class="text-red-500">*</span>
                                </label>
                                <input type="file" name="sign_images[${i-1}]" required accept="image/*" capture="environment"
                                    class="w-full text-xs text-gray-500 file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-amber-100 file:text-amber-800 hover:file:bg-amber-250 cursor-pointer">
                            `;
                        }
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
