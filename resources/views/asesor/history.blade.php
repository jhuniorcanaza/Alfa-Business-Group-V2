<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="bg-indigo-500/10 text-indigo-500 p-2 rounded-xl text-xl">📅</span>
                    Mi Historial de Reportes
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Historial de tus reportes diarios de actividad en los últimos 30 días
                </p>
            </div>
            <div>
                <a href="{{ route('asesor.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-gray-700 dark:text-gray-200 bg-white dark:bg-[#181a26] border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:scale-[1.02] active:scale-[0.98] transition-all shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Volver al Dashboard
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-[#11131c] rounded-2xl shadow-xl border border-gray-200 dark:border-gray-800/60 p-6 transition-all duration-300">
                <div class="mb-5">
                    <h3 class="text-base font-extrabold text-gray-900 dark:text-white">📅 Mis Reportes Anteriores</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Detalle del comportamiento e indicadores registrados por día.</p>
                </div>

                @if($history->count() > 0)
                    <div class="overflow-x-auto rounded-xl border border-gray-200/50 dark:border-gray-800/60">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-[11px] text-gray-700 uppercase bg-gray-50 dark:bg-gray-800/40 dark:text-gray-400 font-extrabold tracking-wider border-b border-gray-200/60 dark:border-gray-800/60">
                                <tr>
                                    <th scope="col" class="py-3.5 px-4 font-bold rounded-l-lg">Fecha</th>
                                    <th scope="col" class="py-3.5 px-4 font-bold text-center">🏠 Visitas</th>
                                    <th scope="col" class="py-3.5 px-4 font-bold text-center">📋 Letrero</th>
                                    <th scope="col" class="py-3.5 px-4 font-bold text-center">📝 Exclusiva</th>
                                    <th scope="col" class="py-3.5 px-4 font-bold text-center">🤝 Cierres</th>
                                    <th scope="col" class="py-3.5 px-4 font-bold text-center">📞 Llamadas</th>
                                    <th scope="col" class="py-3.5 px-4 font-bold text-center rounded-r-lg">💻 AlphaX</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800/40">
                                @foreach($history as $report)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20 transition-colors">
                                        <td class="py-3.5 px-4 font-bold text-gray-900 dark:text-white">
                                            {{ $report->report_date->format('d/m/Y') }}
                                        </td>
                                        <td class="py-3.5 px-4 text-center text-gray-700 dark:text-gray-300 font-semibold">{{ $report->visits }}</td>
                                        <td class="py-3.5 px-4 text-center text-gray-700 dark:text-gray-300 font-semibold">{{ $report->sign_captures }}</td>
                                        <td class="py-3.5 px-4 text-center text-gray-700 dark:text-gray-300 font-semibold">{{ $report->exclusive_captures }}</td>
                                        <td class="py-3.5 px-4 text-center text-gray-700 dark:text-gray-300 font-semibold">{{ $report->closings }}</td>
                                        <td class="py-3.5 px-4 text-center">
                                            <div class="flex flex-col items-center justify-center">
                                                <span class="font-bold text-gray-900 dark:text-white">{{ $report->calls_made }}</span>
                                                @if($report->call_phone_number)
                                                    <span class="block text-[10px] text-gray-400 mt-0.5 max-w-[120px] truncate" title="{{ $report->call_phone_number }}">
                                                        📞 {{ $report->call_phone_number }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4 text-center text-gray-700 dark:text-gray-300 font-semibold">{{ $report->properties_in_system }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12 text-gray-400 bg-gray-50/50 dark:bg-gray-900/10 border border-dashed border-gray-200 dark:border-gray-800/60 rounded-xl">
                        <span class="text-3xl block mb-2">📅</span>
                        <p class="font-semibold text-sm">No tienes reportes anteriores</p>
                        <p class="text-xs text-gray-500 mt-1">¡Registra tu primer reporte del día de hoy para iniciar tu historial!</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
