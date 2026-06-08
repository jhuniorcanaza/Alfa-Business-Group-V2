<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">
                    🚦 Semáforos de Rendimiento
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Meta semanal de captaciones: <strong class="text-indigo-600 dark:text-indigo-400">{{ $kpiConfig ? $kpiConfig->weekly_goal : '10' }}</strong>
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-[#11131c] rounded-2xl shadow-xl border border-gray-200 dark:border-gray-800/60 p-6">
                <div class="overflow-x-auto rounded-xl border border-gray-250/50 dark:border-gray-800/60">
                    <table class="w-full text-sm text-left text-gray-550 dark:text-gray-450">
                        <thead class="text-[11px] uppercase bg-gray-50 dark:bg-gray-800/40 text-gray-700 dark:text-gray-300 font-extrabold tracking-wider border-b border-gray-200/60 dark:border-gray-800/60">
                            <tr>
                                <th scope="col" class="py-4 px-4">Asesor</th>
                                <th scope="col" class="py-4 px-2 text-center">🏠 Visitas</th>
                                <th scope="col" class="py-4 px-2 text-center">📋 Letrero</th>
                                <th scope="col" class="py-4 px-2 text-center">📝 Exclusiva</th>
                                <th scope="col" class="py-4 px-2 text-center">🤝 Cierres</th>
                                <th scope="col" class="py-4 px-2 text-center">📞 Llamadas</th>
                                <th scope="col" class="py-4 px-2 text-center">💻 AlphaX</th>
                                <th scope="col" class="py-4 px-3 text-center">🚦 Semáforo</th>
                                <th scope="col" class="py-4 px-4 text-right">Detalle</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-150 dark:divide-gray-800/30">
                            @foreach($asesoresData as $a)
                                @php
                                    $light = 'green';
                                    $pct = 100;
                                    if ($kpiConfig && $kpiConfig->weekly_goal > 0) {
                                        $pct = round(($a->total_captures / $kpiConfig->weekly_goal) * 100);
                                        $light = $kpiConfig->getTrafficLightColor($a->total_captures);
                                    }
                                    $lightBadge = match($light) {
                                        'green' => 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20',
                                        'yellow' => 'bg-yellow-500/10 text-yellow-500 border border-yellow-500/20',
                                        'red' => 'bg-rose-500/10 text-rose-500 border border-rose-500/20',
                                        default => 'bg-gray-100 text-gray-800',
                                    };
                                    $lightEmoji = match($light) {
                                        'green' => '🟢',
                                        'yellow' => '🟡',
                                        'red' => '🔴',
                                        default => '⚪',
                                    };
                                @endphp
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20 transition-colors text-gray-700 dark:text-gray-300">
                                    <td class="py-4 px-4 font-bold text-gray-900 dark:text-white">
                                        {{ $a->name }}
                                    </td>
                                    <td class="py-4 px-2 text-center font-bold">{{ $a->weekly['visits'] }}</td>
                                    <td class="py-4 px-2 text-center font-bold text-gray-650 dark:text-gray-300">{{ $a->weekly['sign_captures'] }}</td>
                                    <td class="py-4 px-2 text-center font-bold text-gray-650 dark:text-gray-300">{{ $a->weekly['exclusive_captures'] }}</td>
                                    <td class="py-4 px-2 text-center font-bold">{{ $a->weekly['closings'] }}</td>
                                    <td class="py-4 px-2 text-center text-gray-500 dark:text-gray-400 font-semibold">{{ $a->weekly['calls_made'] }}</td>
                                    <td class="py-4 px-2 text-center text-gray-500 dark:text-gray-400 font-semibold">{{ $a->weekly['properties_in_system'] }}</td>
                                    <td class="py-4 px-3 text-center">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black {{ $lightBadge }}">
                                            {{ $lightEmoji }} {{ $a->total_captures }}/{{ $kpiConfig ? $kpiConfig->weekly_goal : '10' }} ({{ $pct }}%)
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-right">
                                        <a href="{{ route('team-leader.asesor-detail', $a->id) }}" class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-500/5 dark:bg-indigo-500/10 hover:bg-indigo-500/10 dark:hover:bg-indigo-500/20 transition-colors">
                                            Historial →
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
