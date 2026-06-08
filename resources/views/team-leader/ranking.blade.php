<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">
                    🏆 Ranking de Asesores Destacados
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Semana Actual: del {{ $startOfWeek->format('d/m/Y') }} al {{ $endOfWeek->format('d/m/Y') }}
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @php
                    $rankingCards = [
                        ['key' => 'visits', 'icon' => '🏠', 'title' => 'Top Visitas', 'field' => 'visits', 'color' => 'blue', 'bg' => 'from-blue-500/5 to-indigo-500/5'],
                        ['key' => 'sign_captures', 'icon' => '📋', 'title' => 'Top Captaciones Letrero', 'field' => 'sign_captures', 'color' => 'emerald', 'bg' => 'from-emerald-500/5 to-teal-500/5'],
                        ['key' => 'exclusive_captures', 'icon' => '📝', 'title' => 'Top Captaciones Exclusiva', 'field' => 'exclusive_captures', 'color' => 'purple', 'bg' => 'from-purple-500/5 to-pink-500/5'],
                        ['key' => 'closings', 'icon' => '🤝', 'title' => 'Top Cierres', 'field' => 'closings', 'color' => 'amber', 'bg' => 'from-amber-500/5 to-orange-500/5'],
                        ['key' => 'calls_made', 'icon' => '📞', 'title' => 'Top Llamadas', 'field' => 'calls_made', 'color' => 'cyan', 'bg' => 'from-cyan-500/5 to-sky-500/5'],
                        ['key' => 'properties_in_system', 'icon' => '💻', 'title' => 'Top AlphaX', 'field' => 'properties_in_system', 'color' => 'rose', 'bg' => 'from-rose-500/5 to-red-500/5'],
                    ];
                @endphp
                @foreach($rankingCards as $rc)
                    <div class="bg-white dark:bg-[#11131c] rounded-2xl shadow-xl border border-gray-200 dark:border-gray-800/60 p-6 hover:scale-[1.01] transition-transform duration-350">
                        <div class="flex items-center gap-2.5 border-b border-gray-100 dark:border-gray-800/60 pb-3 mb-4">
                            <span class="text-2xl">{{ $rc['icon'] }}</span>
                            <h4 class="font-extrabold text-gray-800 dark:text-gray-100 tracking-tight">{{ $rc['title'] }}</h4>
                        </div>
                        <div class="space-y-3">
                            @forelse($rankings[$rc['key']]->take(5) as $index => $a)
                                <div class="flex items-center justify-between text-sm py-2 px-3 rounded-xl bg-gray-50/50 dark:bg-gray-800/20 border border-gray-100/50 dark:border-gray-800/40">
                                    <span class="text-gray-700 dark:text-gray-300 font-bold flex items-center gap-1.5">
                                        @if($index === 0)
                                            <span class="text-lg">🥇</span>
                                        @elseif($index === 1)
                                            <span class="text-lg">🥈</span>
                                        @elseif($index === 2)
                                            <span class="text-lg">🥉</span>
                                        @else
                                            <strong class="text-gray-400 dark:text-gray-500 text-xs w-5 text-center">#{{ $index + 1 }}</strong>
                                        @endif
                                        {{ $a->name }}
                                    </span>
                                    <span class="font-black text-base text-{{ $rc['color'] }}-600 dark:text-{{ $rc['color'] }}-450">{{ $a->weekly[$rc['field']] }}</span>
                                </div>
                            @empty
                                <div class="text-center py-6 text-xs text-gray-400">
                                    No hay datos registrados aún.
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
