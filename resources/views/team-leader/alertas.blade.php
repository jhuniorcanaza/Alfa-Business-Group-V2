<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">
                    🚨 Alertas de Monitoreo
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Notificaciones en tiempo real sobre reportes pendientes o irregularidades.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if($missingReports->count() > 0)
                <div class="bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-800/50 rounded-2xl p-6 shadow-xl">
                    <div class="flex items-start gap-4">
                        <span class="text-3xl">🚨</span>
                        <div class="space-y-2 w-full">
                            <h3 class="font-extrabold text-base text-red-800 dark:text-red-250">Asesores sin reporte hoy ({{ $today->format('d/m/Y') }})</h3>
                            <p class="text-sm text-red-700 dark:text-red-300">Los siguientes integrantes de tu equipo aún no han enviado sus indicadores del día:</p>
                            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($missingReports as $asesor)
                                    <div class="flex items-center justify-between p-4 rounded-xl bg-white dark:bg-gray-950/40 border border-red-100 dark:border-red-900/40 shadow-sm">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-full bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 flex items-center justify-center font-bold text-xs">
                                                {{ strtoupper(substr($asesor->name, 0, 2)) }}
                                            </div>
                                            <span class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ $asesor->name }}</span>
                                        </div>
                                        @if($asesor->phone)
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $asesor->phone) }}?text=Hola%20{{ urlencode($asesor->name) }},%20recuerda%20enviar%20tu%20reporte%20diario%20de%20hoy." target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold text-emerald-600 bg-emerald-500/10 hover:bg-emerald-500/20 transition-colors">
                                                💬 Notificar
                                            </a>
                                        @else
                                            <span class="text-[10px] text-gray-400 italic">Sin celular</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-emerald-50 dark:bg-emerald-950/15 border border-emerald-250 dark:border-emerald-900/40 rounded-2xl p-6 shadow-xl flex items-center gap-4">
                    <span class="text-3xl">🎉</span>
                    <div>
                        <h3 class="font-extrabold text-base text-emerald-800 dark:text-emerald-300">¡Todo al día!</h3>
                        <p class="text-sm text-emerald-700 dark:text-emerald-400 mt-0.5">Todos los asesores de tu equipo han reportado exitosamente el día de hoy.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
