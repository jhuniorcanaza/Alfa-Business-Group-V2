<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">
                    📈 Acumulado Semanal del Equipo
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Estadísticas acumuladas de la semana: del {{ $startOfWeek->format('d/m/Y') }} al {{ $endOfWeek->format('d/m/Y') }}
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
                @php
                    $teamCards = [
                        ['icon' => '🏠', 'label' => 'Visitas', 'value' => $teamWeekly['visits'], 'color' => 'blue', 'bg' => 'bg-blue-500/5 dark:bg-blue-500/10 text-blue-500 border border-blue-500/10 dark:border-blue-500/20'],
                        ['icon' => '📋', 'label' => 'Capt. Letrero', 'value' => $teamWeekly['sign_captures'], 'color' => 'emerald', 'bg' => 'bg-emerald-500/5 dark:bg-emerald-500/10 text-emerald-500 border border-emerald-500/10 dark:border-emerald-500/20'],
                        ['icon' => '📝', 'label' => 'Capt. Exclusiva', 'value' => $teamWeekly['exclusive_captures'], 'color' => 'purple', 'bg' => 'bg-purple-500/5 dark:bg-purple-500/10 text-purple-500 border border-purple-500/10 dark:border-purple-500/20'],
                        ['icon' => '🤝', 'label' => 'Cierres', 'value' => $teamWeekly['closings'], 'color' => 'amber', 'bg' => 'bg-amber-500/5 dark:bg-amber-500/10 text-amber-500 border border-amber-500/10 dark:border-amber-500/20'],
                        ['icon' => '📞', 'label' => 'Llamadas', 'value' => $teamWeekly['calls_made'], 'color' => 'cyan', 'bg' => 'bg-cyan-500/5 dark:bg-cyan-500/10 text-cyan-500 border border-cyan-500/10 dark:border-cyan-500/20'],
                        ['icon' => '💻', 'label' => 'AlphaX', 'value' => $teamWeekly['properties_in_system'], 'color' => 'rose', 'bg' => 'bg-rose-500/5 dark:bg-rose-500/10 text-rose-500 border border-rose-500/10 dark:border-rose-500/20'],
                    ];
                @endphp
                @foreach($teamCards as $card)
                    <div class="bg-white dark:bg-[#11131c] p-6 rounded-2xl border shadow-xl text-center {{ $card['bg'] }} hover:scale-[1.03] transition-transform duration-300">
                        <span class="text-3xl block mb-2 filter drop-shadow">{{ $card['icon'] }}</span>
                        <span class="text-gray-500 dark:text-gray-400 text-xs font-bold uppercase tracking-wider block mb-1">{{ $card['label'] }}</span>
                        <span class="text-3xl font-black">{{ $card['value'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
