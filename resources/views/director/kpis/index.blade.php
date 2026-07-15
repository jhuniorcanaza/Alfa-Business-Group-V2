<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('director.dashboard') }}" class="text-sm text-blue-600 hover:underline">← Volver al Dashboard</a>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mt-1">Configuración de Metas y Parámetros KPIs</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Alerta de éxito -->
            @if(session('success'))
                <div class="p-4 rounded-xl bg-green-50 dark:bg-green-950/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4">🚦 Lógica y Umbrales del Semáforo Semanal</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Defina las metas de captaciones para el equipo comercial y los porcentajes que determinan si el asesor está en verde, amarillo o rojo.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($kpis as $kpi)
                        <div class="p-6 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-250 dark:border-gray-600 space-y-4">
                            <div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-600">
                                <h4 class="text-md font-bold uppercase text-gray-800 dark:text-gray-200 flex items-center gap-2">
                                    @if($kpi->office)
                                        <span>🏢</span> {{ $kpi->office->name }}
                                        <span class="text-xs font-normal text-gray-400 dark:text-gray-400 lowercase">({{ $kpi->office->city }})</span>
                                    @else
                                        <span>🌍</span> Meta General (Por Defecto)
                                    @endif
                                </h4>
                                <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $kpi->is_active ? 'bg-green-100 text-green-800 dark:bg-green-950/30 dark:text-green-400' : 'bg-amber-100 text-amber-800 dark:bg-amber-955/30 dark:text-amber-400' }}">
                                    {{ $kpi->is_active ? 'Activa' : 'Inactiva' }}
                                </span>
                            </div>

                            <form method="POST" action="{{ route('director.kpis.update', $kpi->id) }}" class="space-y-4">
                                @csrf
                                @method('PUT')
                                
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Meta Semanal (Captaciones)</label>
                                    <input type="number" name="weekly_goal" value="{{ $kpi->weekly_goal }}" required min="1" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                                    <span class="text-xxs text-gray-400">Total de captaciones acumuladas (con letrero o exclusiva) requeridas a la semana.</span>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Umbral Amarillo (%)</label>
                                        <input type="number" name="yellow_threshold_pct" value="{{ $kpi->yellow_threshold_pct }}" required min="0" max="100" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                                        <span class="text-xxs text-gray-400">Rendimiento mínimo para color amarillo (ej. 51%).</span>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Umbral Rojo (%)</label>
                                        <input type="number" name="red_threshold_pct" value="{{ $kpi->red_threshold_pct }}" required min="0" max="100" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                                        <span class="text-xxs text-gray-400">Rendimiento por debajo del cual será rojo (ej. 50%).</span>
                                    </div>
                                </div>

                                @if($kpi->office)
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Estado de la Meta de Oficina</label>
                                        <select name="is_active" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                                            <option value="1" {{ $kpi->is_active ? 'selected' : '' }}>Habilitada (Meta Personalizada)</option>
                                            <option value="0" {{ !$kpi->is_active ? 'selected' : '' }}>Deshabilitada (Adopta Meta General)</option>
                                        </select>
                                        <span class="text-xxs text-gray-400">Si se deshabilita, esta oficina usará la Meta General/Por Defecto del sistema.</span>
                                    </div>
                                @else
                                    <input type="hidden" name="is_active" value="1">
                                @endif

                                <button type="submit" class="w-full py-2.5 rounded-lg text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 transition-colors shadow-md shadow-blue-600/10">
                                    💾 Actualizar Parámetros
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
