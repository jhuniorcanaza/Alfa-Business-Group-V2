<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">
                    📅 Historial de KPIs del Día
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Resumen detallado de la actividad reportada hoy: {{ $today->format('d/m/Y') }}
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
                                <th scope="col" class="py-4 px-2 text-center">Estado</th>
                                <th scope="col" class="py-4 px-2 text-center">🏠 Visitas</th>
                                <th scope="col" class="py-4 px-2 text-center">📋 Letrero</th>
                                <th scope="col" class="py-4 px-2 text-center">📝 Exclusiva</th>
                                <th scope="col" class="py-4 px-2 text-center">🤝 Cierres</th>
                                <th scope="col" class="py-4 px-2 text-center">📞 Llamadas</th>
                                <th scope="col" class="py-4 px-2 text-center">💻 AlphaX</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-150 dark:divide-gray-800/30">
                            @foreach($asesoresData as $a)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20 transition-colors text-gray-700 dark:text-gray-300">
                                    <td class="py-4 px-4 font-bold text-gray-900 dark:text-white">
                                        {{ $a->name }}
                                    </td>
                                    <td class="py-4 px-2 text-center">
                                        @if($a->sent_today)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-green-500/10 text-green-600 border border-green-500/20">✅ Enviado</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-500/10 text-rose-500 border border-rose-500/20">❌ Pendiente</span>
                                        @endif
                                    </td>
                                    @if($a->sent_today)
                                        <td class="py-4 px-2 text-center font-bold text-gray-900 dark:text-white">{{ $a->today['visits'] }}</td>
                                        <td class="py-4 px-2 text-center font-bold">
                                            <span>{{ $a->today['sign_captures'] }}</span>
                                            @if(!empty($a->today['sign_image_path']))
                                                @php
                                                    $todayImages = [];
                                                    $decoded = json_decode($a->today['sign_image_path'], true);
                                                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                        $todayImages = $decoded;
                                                    } else {
                                                        $todayImages = [$a->today['sign_image_path']];
                                                    }
                                                @endphp
                                                @foreach($todayImages as $idx => $img)
                                                    <a href="#" onclick="openLightbox('{{ asset($img) }}'); return false;" class="block text-amber-500 hover:text-amber-700 text-[10px] font-extrabold mt-0.5" title="Ver foto letrero {{ $idx + 1 }}">
                                                        📸 Foto {{ count($todayImages) > 1 ? '#' . ($idx + 1) : '' }}
                                                    </a>
                                                @endforeach
                                            @endif
                                        </td>
                                        <td class="py-4 px-2 text-center font-bold text-gray-900 dark:text-white">{{ $a->today['exclusive_captures'] }}</td>
                                        <td class="py-4 px-2 text-center font-bold text-gray-900 dark:text-white">{{ $a->today['closings'] }}</td>
                                        <td class="py-4 px-2 text-center font-bold">
                                            <span>{{ $a->today['calls_made'] }}</span>
                                            @if(!empty($a->today['call_phone_number']))
                                                <span class="block text-[10px] text-gray-400 dark:text-gray-500 font-normal truncate max-w-[100px] mx-auto mt-0.5" title="{{ $a->today['call_phone_number'] }}">
                                                    📞 {{ $a->today['call_phone_number'] }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-4 px-2 text-center font-bold text-gray-900 dark:text-white">{{ $a->today['properties_in_system'] }}</td>
                                    @else
                                        <td colspan="6" class="py-4 px-2 text-center text-gray-400 dark:text-gray-500 italic text-xs">Sin datos — aún no reportó</td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Lightbox para ver Fotos de Respaldo -->
    <div id="imageLightbox" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm hidden print:hidden" onclick="closeLightbox()">
        <div class="relative max-w-3xl max-h-[85vh] p-4 flex flex-col items-center" onclick="event.stopPropagation()">
            <button onclick="closeLightbox()" class="absolute top-2 right-2 bg-black/60 hover:bg-black/80 text-white rounded-full p-2.5 transition-colors focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            <img id="lightboxImage" src="" alt="Respaldo Letrero" class="max-w-full max-h-[75vh] object-contain rounded-xl border border-gray-700 shadow-2xl">
            <p class="text-white/80 text-sm mt-3 font-semibold text-center bg-black/40 px-4 py-1.5 rounded-full">Respaldo de Letrero</p>
        </div>
    </div>

    <script>
        function openLightbox(url) {
            const lightbox = document.getElementById('imageLightbox');
            const img = document.getElementById('lightboxImage');
            if (lightbox && img) {
                img.src = url;
                lightbox.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
        }
        function closeLightbox() {
            const lightbox = document.getElementById('imageLightbox');
            if (lightbox) {
                lightbox.classList.add('hidden');
                document.body.style.overflow = '';
            }
        }
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeLightbox();
        });
    </script>
</x-app-layout>
