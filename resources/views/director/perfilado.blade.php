<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <a href="{{ route('director.dashboard') }}" class="text-sm font-semibold text-blue-600 dark:text-blue-400 hover:underline">← Volver al Dashboard</a>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mt-1">
                    ⭐ Perfilado de Asesores
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Candidatos sugeridos para el rol de Team Leader según métricas de desempeño sostenido
                </p>
            </div>
            <div class="flex items-center gap-3 print:hidden">
                <button onclick="window.print()" class="px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-red-600 hover:bg-red-700 transition-colors shadow-md shadow-red-600/20 flex items-center gap-1.5">
                    📄 Exportar PDF
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- FILTRO DE FECHAS -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 print:hidden">
                <form method="GET" action="{{ route('director.perfilado') }}" class="flex flex-col lg:flex-row lg:items-end justify-between gap-4">
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
                                    <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Hasta</label>
                                    <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
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

            <!-- EXPLICACIÓN DE REGLAS -->
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-2xl p-6 shadow-lg">
                <div class="flex items-start gap-4">
                    <span class="text-3xl">💡</span>
                    <div>
                        <h4 class="font-bold text-lg">¿Cómo funciona el perfilado automático?</h4>
                        <p class="text-sm text-blue-100 mt-1 leading-relaxed">
                            El sistema evalúa el desempeño de todos los asesores activos durante el periodo seleccionado. 
                            Aquellos que demuestren un alto grado de consistencia y liderazgo comercial son sugeridos como candidatos a <strong>Team Leader</strong>.
                        </p>
                        <div class="mt-3 flex flex-wrap gap-2 text-xs">
                            <span class="px-2.5 py-1 bg-white/10 rounded-full font-bold">🤝 Cierres acumulados ≥ 2</span>
                            <span class="px-2.5 py-1 bg-white/10 rounded-full font-bold">📋 Captaciones (Letreros + Exclusivas) ≥ 3</span>
                            <span class="px-2.5 py-1 bg-white/10 rounded-full font-bold">🏠 Visitas realizadas ≥ 5</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CANDIDATOS DETECTADOS -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($candidatosTeamLeader as $index => $cand)
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm hover:shadow-md transition-all relative overflow-hidden flex flex-col justify-between">
                        <!-- Badge de posición -->
                        <div class="absolute top-0 right-0 bg-blue-500 text-white font-black px-4 py-1.5 rounded-bl-xl text-xs uppercase tracking-wider">
                            Candidato #{{ $index + 1 }}
                        </div>

                        <div class="space-y-4">
                            <div>
                                <h3 class="text-lg font-black text-gray-800 dark:text-gray-100 flex items-center gap-1.5">
                                    <span>👤</span> {{ $cand->name }}
                                </h3>
                                <div class="mt-2 flex flex-col gap-1 text-xs text-gray-500 dark:text-gray-400">
                                    <span>🏢 Oficina: <strong>{{ $cand->office_name }}</strong></span>
                                    <span>👥 Equipo actual: <strong>{{ $cand->team_name }}</strong></span>
                                </div>
                            </div>

                            <hr class="border-gray-100 dark:border-gray-700/60">

                            <!-- KPIs alcanzados en el rango -->
                            <div class="grid grid-cols-3 gap-2 text-center">
                                <div class="p-2 bg-blue-50 dark:bg-blue-900/10 rounded-lg">
                                    <span class="text-xxs text-gray-500 dark:text-gray-400 uppercase block font-bold">Cierres</span>
                                    <span class="text-lg font-black text-blue-600 dark:text-blue-400">{{ $cand->closings }}</span>
                                </div>
                                <div class="p-2 bg-emerald-50 dark:bg-emerald-900/10 rounded-lg">
                                    <span class="text-xxs text-gray-500 dark:text-gray-400 uppercase block font-bold">Captaciones</span>
                                    <span class="text-lg font-black text-emerald-600 dark:text-emerald-400">{{ $cand->total_captures }}</span>
                                </div>
                                <div class="p-2 bg-purple-50 dark:bg-purple-900/10 rounded-lg">
                                    <span class="text-xxs text-gray-500 dark:text-gray-400 uppercase block font-bold">Visitas</span>
                                    <span class="text-lg font-black text-purple-600 dark:text-purple-400">{{ $cand->visits }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Info adicional del asesor -->
                        <div class="mt-5 pt-4 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-between text-xs">
                            <span class="text-gray-400">Desempeño Sostenido</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xxs font-semibold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                Apto para ascenso
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-12 text-center text-gray-500 dark:text-gray-400">
                        <span class="text-5xl block mb-3">🔍</span>
                        <h4 class="font-bold text-lg text-gray-800 dark:text-gray-200">Sin candidatos aptos</h4>
                        <p class="text-sm mt-1 max-w-md mx-auto">
                            No se detectaron asesores que cumplan simultáneamente con los criterios mínimos de ascenso en este periodo 
                            (mínimo 2 Cierres, 3 Captaciones y 5 Visitas).
                        </p>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>
