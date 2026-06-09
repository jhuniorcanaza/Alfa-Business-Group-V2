<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <a href="{{ route('director.dashboard') }}" class="text-sm font-semibold text-blue-600 dark:text-blue-400 hover:underline">← Volver al Dashboard</a>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mt-1">
                    🏆 Rankings de Asesores Globales
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Tablas de clasificación de asesores basadas en el acumulado de cada indicador comercial
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
                <form method="GET" action="{{ route('director.ranking') }}" class="flex flex-col lg:flex-row lg:items-end justify-between gap-4">
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

            <!-- TABLAS DE RANKINGS -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @php
                    $rankingCards = [
                        ['key' => 'visits', 'icon' => '🏠', 'title' => 'Top Visitas', 'color' => 'blue'],
                        ['key' => 'sign_captures', 'icon' => '📋', 'title' => 'Top Letreros', 'color' => 'emerald'],
                        ['key' => 'exclusive_captures', 'icon' => '📝', 'title' => 'Top Exclusivas', 'color' => 'purple'],
                        ['key' => 'closings', 'icon' => '🤝', 'title' => 'Top Cierres', 'color' => 'amber'],
                        ['key' => 'calls_made', 'icon' => '📞', 'title' => 'Top Llamadas', 'color' => 'cyan'],
                        ['key' => 'properties_in_system', 'icon' => '💻', 'title' => 'Top AlphaX', 'color' => 'rose'],
                    ];
                @endphp

                @foreach($rankingCards as $rc)
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5 shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-2.5 border-b border-gray-100 dark:border-gray-700/80 pb-3 mb-4">
                            <span class="text-2xl bg-{{ $rc['color'] }}-50 dark:bg-{{ $rc['color'] }}-900/20 p-1.5 rounded-lg text-{{ $rc['color'] }}-600 dark:text-{{ $rc['color'] }}-400">{{ $rc['icon'] }}</span>
                            <h4 class="font-extrabold text-gray-800 dark:text-gray-200 text-sm uppercase tracking-wide">{{ $rc['title'] }}</h4>
                        </div>
                        <div class="space-y-3">
                            @forelse($rankings[$rc['key']]->take(8) as $index => $a)
                                <div class="flex items-center justify-between text-sm py-1.5 px-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/20 transition-colors">
                                    <span class="text-gray-700 dark:text-gray-300 truncate font-semibold" title="{{ $a->name }}">
                                        @if($index === 0)
                                            <span class="text-yellow-500 mr-1.5 text-base">🥇</span>
                                        @elseif($index === 1)
                                            <span class="text-gray-400 mr-1.5 text-base">🥈</span>
                                        @elseif($index === 2)
                                            <span class="text-amber-700 mr-1.5 text-base">🥉</span>
                                        @else
                                            <strong class="text-gray-400 mr-2">#{{ $index + 1 }}</strong>
                                        @endif
                                        {{ $a->name }}
                                        <span class="block text-xxs font-normal text-gray-400 mt-0.5">{{ $a->office_name }}</span>
                                    </span>
                                    <span class="font-black text-{{ $rc['color'] }}-600 dark:text-{{ $rc['color'] }}-400 text-base">
                                        {{ $rc['key'] === 'visits' ? $a->visits : 
                                           ($rc['key'] === 'sign_captures' ? $a->sign_captures : 
                                           ($rc['key'] === 'exclusive_captures' ? $a->exclusive_captures : 
                                           ($rc['key'] === 'closings' ? $a->closings : 
                                           ($rc['key'] === 'calls_made' ? $a->calls_made : $a->properties_in_system)))) }}
                                    </span>
                                </div>
                            @empty
                                <div class="text-center py-6 text-gray-400 dark:text-gray-500 italic text-xs">
                                    Sin datos para clasificar
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </div>
</x-app-layout>
