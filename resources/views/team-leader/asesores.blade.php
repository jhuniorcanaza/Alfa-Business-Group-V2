<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">
                    👥 Mis Asesores del Equipo
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Listado de integrantes activos y su información de contacto.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-[#11131c] rounded-2xl shadow-xl border border-gray-200 dark:border-gray-800/60 p-6">
                <div class="overflow-x-auto rounded-xl border border-gray-250/50 dark:border-gray-800/60">
                    <table class="w-full text-sm text-left text-gray-550 dark:text-gray-450">
                        <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-800/40 text-gray-700 dark:text-gray-300 font-extrabold tracking-wider border-b border-gray-200/60 dark:border-gray-800/60">
                            <tr>
                                <th scope="col" class="py-4 px-4">Asesor</th>
                                <th scope="col" class="py-4 px-4">Correo Electrónico</th>
                                <th scope="col" class="py-4 px-4">Teléfono</th>
                                <th scope="col" class="py-4 px-4 text-center">Estado de Hoy</th>
                                <th scope="col" class="py-4 px-4 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-150 dark:divide-gray-800/30">
                            @foreach($asesoresData as $a)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20 transition-colors">
                                    <td class="py-4 px-4 font-bold text-gray-900 dark:text-white">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-blue-500 to-indigo-600 flex items-center justify-center text-white text-sm font-black shadow-md shadow-blue-500/20">
                                                {{ strtoupper(substr($a->name, 0, 2)) }}
                                            </div>
                                            <div>
                                                <span class="block">{{ $a->name }}</span>
                                                <span class="block text-[10px] font-normal text-gray-400 dark:text-gray-500">Asesor de Ventas</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 text-gray-600 dark:text-gray-300 font-medium">
                                        {{ $a->email }}
                                    </td>
                                    <td class="py-4 px-4">
                                        @if($a->phone)
                                            <div class="flex items-center gap-2">
                                                <span class="font-semibold text-gray-750 dark:text-gray-300">{{ $a->phone }}</span>
                                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $a->phone) }}" target="_blank" class="p-1 rounded bg-green-500/10 text-green-500 hover:bg-green-500/20 transition-colors" title="Contactar por WhatsApp">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.5-5.739-1.446L0 24zm6.59-4.846c1.6.95 3.188 1.449 4.825 1.451 5.436 0 9.86-4.37 9.864-9.799.002-2.63-1.023-5.101-2.885-6.965C16.528 1.977 14.07 1.95 12.008 1.95c-5.437 0-9.865 4.37-9.869 9.8-.001 1.76.476 3.481 1.381 5.018l-.924 3.38 3.451-.914z"/></svg>
                                                </a>
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400 italic">No registrado</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        @if($a->sent_today)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">
                                                🟢 Reportado
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-500/10 text-rose-500 border border-rose-500/20 animate-pulse">
                                                🔴 Pendiente
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('team-leader.asesor-detail', $a->id) }}" class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold text-blue-600 dark:text-blue-400 bg-blue-500/5 dark:bg-blue-500/10 hover:bg-blue-500/10 dark:hover:bg-blue-500/20 transition-all">
                                                🔍 Ver Historial
                                            </a>
                                        </div>
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
