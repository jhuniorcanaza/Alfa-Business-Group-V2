<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="bg-amber-500/10 text-amber-500 p-2 rounded-xl text-xl">🏆</span>
                    Ranking del Equipo
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Semana del {{ $startOfWeek->format('d/m/Y') }} al {{ $endOfWeek->format('d/m/Y') }}
                </p>
            </div>
            <div>
                <a href="{{ route('asesor.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-gray-750 dark:text-gray-200 bg-white dark:bg-[#181a26] border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:scale-[1.02] active:scale-[0.98] transition-all shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Volver al Dashboard
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-[#11131c] rounded-2xl shadow-xl border border-gray-200 dark:border-gray-800/60 p-6 transition-all duration-300">
                <div class="flex items-center justify-between mb-5 flex-wrap gap-2">
                    <div>
                        <h3 class="text-base font-extrabold text-gray-900 dark:text-white flex items-center gap-2">
                            🏆 Ranking de Captaciones
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Competencia sana basada en captaciones semanales (Letrero + Exclusiva).</p>
                    </div>
                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-indigo-500/10 text-indigo-450 dark:text-indigo-400 border border-indigo-500/20">
                        {{ $teamAsesores->count() }} Asesores en tu equipo
                    </span>
                </div>

                @if($teamAsesores->count() > 0)
                    <div class="overflow-x-auto rounded-xl border border-gray-200/50 dark:border-gray-800/60">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-[11px] text-gray-700 uppercase bg-gray-50 dark:bg-gray-800/40 dark:text-gray-400 font-extrabold tracking-wider border-b border-gray-200/60 dark:border-gray-800/60">
                                <tr>
                                    <th scope="col" class="py-3.5 px-4 font-bold rounded-l-lg">Posición</th>
                                    <th scope="col" class="py-3.5 px-4 font-bold">Asesor</th>
                                    <th scope="col" class="py-3.5 px-4 font-bold text-center">🏠 Visitas</th>
                                    <th scope="col" class="py-3.5 px-4 font-bold text-center">📋+📝 Captaciones</th>
                                    <th scope="col" class="py-3.5 px-4 font-bold text-center">🤝 Cierres</th>
                                    <th scope="col" class="py-3.5 px-4 font-bold text-center">📞 Llamadas</th>
                                    <th scope="col" class="py-3.5 px-4 font-bold text-center rounded-r-lg">💻 AlphaX</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800/40">
                                @foreach($teamAsesores as $index => $member)
                                    <tr class="{{ $member->id === $user->id ? 'bg-indigo-500/5 dark:bg-indigo-500/10' : '' }} hover:bg-gray-50/50 dark:hover:bg-gray-800/20 transition-colors">
                                        <td class="py-4 px-4 font-medium text-gray-900 dark:text-white">
                                            @if($index === 0)
                                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-yellow-500/20 text-yellow-500 text-xs font-bold">🥇</span>
                                            @elseif($index === 1)
                                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-300/20 text-slate-300 text-xs font-bold">🥈</span>
                                            @elseif($index === 2)
                                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-700/20 text-amber-600 text-xs font-bold">🥉</span>
                                            @else
                                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-500/10 text-gray-400 text-[10px] font-bold">#{{ $index + 1 }}</span>
                                            @endif
                                        </td>
                                        <td class="py-4 px-4 font-bold {{ $member->id === $user->id ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-850 dark:text-gray-200' }}">
                                            <div class="flex items-center gap-2">
                                                {{ $member->name }}
                                                @if($member->id === $user->id)
                                                    <span class="px-2 py-0.5 text-[9px] font-bold rounded bg-indigo-500 text-white uppercase tracking-wider">Tú</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="py-4 px-4 text-center text-gray-700 dark:text-gray-300 font-semibold">{{ $member->visits }}</td>
                                        <td class="py-4 px-4 text-center">
                                            <span class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-bold rounded-full bg-indigo-500/10 text-indigo-450 dark:text-indigo-400 border border-indigo-500/20">
                                                {{ $member->total_captures }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-4 text-center text-gray-700 dark:text-gray-300 font-semibold">{{ $member->closings }}</td>
                                        <td class="py-4 px-4 text-center text-gray-700 dark:text-gray-300 font-semibold">{{ $member->calls_made }}</td>
                                        <td class="py-4 px-4 text-center text-gray-700 dark:text-gray-300 font-semibold">{{ $member->properties_in_system }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12 text-gray-400 bg-gray-50/50 dark:bg-gray-900/10 border border-dashed border-gray-200 dark:border-gray-800/60 rounded-xl">
                        <span class="text-3xl block mb-2">🏆</span>
                        <p class="font-semibold text-sm">Sin datos para el Ranking</p>
                        <p class="text-xs text-gray-500 mt-1">No hay asesores asignados a tu equipo o no se han registrado actividades esta semana.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
