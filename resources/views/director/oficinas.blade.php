<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <a href="{{ route('director.dashboard') }}" class="text-sm font-semibold text-blue-600 dark:text-blue-400 hover:underline">← Volver al Dashboard</a>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mt-1">
                    🏢 Rendimiento por Oficina
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Análisis comparativo del desempeño de cada una de las sucursales de Alfa Business Group
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
                <form method="GET" action="{{ route('director.oficinas') }}" class="flex flex-col lg:flex-row lg:items-end justify-between gap-4">
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
                            <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                                <div class="w-full sm:w-auto">
                                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Desde</label>
                                    <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" class="w-full sm:w-auto rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                                </div>
                                <div class="w-full sm:w-auto">
                                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Hasta</label>
                                    <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" class="w-full sm:w-auto rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
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

            <!-- TABLA DE RENDIMIENTO -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">Comparativa Global</h3>
                        <p class="text-xs text-gray-400">Datos acumulados desde el {{ $startDate->format('d/m/Y') }} hasta el {{ $endDate->format('d/m/Y') }}</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700 text-gray-500 text-xs uppercase tracking-wider">
                                <th class="text-left py-4 px-4 font-semibold">Oficina</th>
                                <th class="text-center py-4 px-3 font-semibold">Asesores Activos</th>
                                <th class="text-center py-4 px-3 font-semibold">🏠 Visitas</th>
                                <th class="text-center py-4 px-3 font-semibold">📋 Letreros</th>
                                <th class="text-center py-4 px-3 font-semibold">📝 Exclusivas</th>
                                <th class="text-center py-4 px-3 font-semibold">🤝 Cierres</th>
                                <th class="text-center py-4 px-3 font-semibold">📞 Llamadas</th>
                                <th class="text-center py-4 px-3 font-semibold">💻 AlphaX</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                            @forelse($offices as $office)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 text-gray-700 dark:text-gray-300 transition-colors">
                                    <td class="py-4 px-4 font-bold text-gray-900 dark:text-gray-100">
                                        {{ $office->name }}
                                    </td>
                                    <td class="py-4 px-3 text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 dark:bg-gray-900/40 dark:text-gray-300 border border-gray-200/50 dark:border-gray-700/50">
                                            👥 {{ $office->total_asesores }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-3 text-center font-bold text-blue-600 dark:text-blue-400">
                                        {{ $office->weekly['visits'] }}
                                    </td>
                                    <td class="py-4 px-3 text-center font-bold text-emerald-600 dark:text-emerald-400">
                                        {{ $office->weekly['sign_captures'] }}
                                    </td>
                                    <td class="py-4 px-3 text-center font-bold text-purple-600 dark:text-purple-400">
                                        {{ $office->weekly['exclusive_captures'] }}
                                    </td>
                                    <td class="py-4 px-3 text-center font-bold text-amber-600 dark:text-amber-400">
                                        {{ $office->weekly['closings'] }}
                                    </td>
                                    <td class="py-4 px-3 text-center text-gray-600 dark:text-gray-400">
                                        {{ $office->weekly['calls_made'] }}
                                    </td>
                                    <td class="py-4 px-3 text-center text-gray-600 dark:text-gray-400">
                                        {{ $office->weekly['properties_in_system'] }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-8 text-center text-gray-400 dark:text-gray-500 italic">
                                        No hay oficinas registradas o activas en este momento.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
