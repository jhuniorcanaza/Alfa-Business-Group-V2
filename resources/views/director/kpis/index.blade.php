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
                        <div class="p-6 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-200 dark:border-gray-600">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-md font-bold uppercase text-gray-800 dark:text-gray-200">Indicador: {{ $kpi->indicator }}</h4>
                                <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $kpi->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $kpi->is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </div>

                            <form method="POST" action="{{ route('director.kpis.update', $kpi->id) }}" class="space-y-4">
                                @csrf
                                @method('PUT')
                                
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Meta Semanal</label>
                                    <input type="number" name="weekly_goal" value="{{ $kpi->weekly_goal }}" required min="1" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                                    <span class="text-xxs text-gray-400">Total de captaciones con letrero o exclusiva que el asesor debe realizar a la semana.</span>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Umbral Amarillo (%)</label>
                                    <input type="number" name="yellow_threshold_pct" value="{{ $kpi->yellow_threshold_pct }}" required min="0" max="100" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                                    <span class="text-xxs text-gray-400">Porcentaje mínimo de cumplimiento para activar el color amarillo (Ej. 50%).</span>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Umbral Rojo (%)</label>
                                    <input type="number" name="red_threshold_pct" value="{{ $kpi->red_threshold_pct }}" required min="0" max="100" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                                    <span class="text-xxs text-gray-400">Porcentaje por debajo del cual el color del semáforo será rojo (Ej. 49% o menos).</span>
                                </div>

                                <input type="hidden" name="is_active" value="1">

                                <button type="submit" class="w-full py-2.5 rounded-lg text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                                    Actualizar Parámetros
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
