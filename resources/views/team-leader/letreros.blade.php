<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">
                    🖼️ Fotos de Letreros Recibidas
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Evidencia fotográfica de captaciones de letreros: del {{ $startDate->format('d/m/Y') }} al {{ $endDate->format('d/m/Y') }}
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- FILTRO DE BÚSQUEDA DE ASESOR Y FECHAS -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 print:hidden mb-6">
                <form method="GET" action="{{ route('team-leader.letreros') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Rango de Fecha -->
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Rango de Fecha</label>
                            <select name="filter_type" onchange="this.form.submit()" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                                <option value="dia" {{ $filterType === 'dia' ? 'selected' : '' }}>Hoy</option>
                                <option value="semana" {{ $filterType === 'semana' ? 'selected' : '' }}>Esta Semana</option>
                                <option value="mes" {{ $filterType === 'mes' ? 'selected' : '' }}>Este Mes</option>
                                <option value="custom" {{ $filterType === 'custom' ? 'selected' : '' }}>Rango Personalizado</option>
                            </select>
                        </div>

                        <!-- Asesor -->
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Asesor del Equipo</label>
                            <select name="user_id" onchange="this.form.submit()" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                                <option value="">Todos los Asesores</option>
                                @foreach($asesoresForFilter as $a)
                                    <option value="{{ $a->id }}" {{ $asesorId == $a->id ? 'selected' : '' }}>{{ $a->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="flex items-end gap-2">
                            <button type="submit" class="flex-1 px-4 py-2.5 rounded-lg text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 transition-colors text-center">
                                🔍 Filtrar
                            </button>
                            <a href="{{ route('team-leader.letreros') }}" class="px-3 py-2.5 rounded-lg text-sm font-bold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors text-center" title="Limpiar Filtros">
                                🧹
                            </a>
                        </div>
                    </div>

                    @if($filterType === 'custom')
                        <div class="flex flex-col sm:flex-row sm:items-center gap-4 pt-2 border-t border-gray-100 dark:border-gray-700/50">
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
                </form>
            </div>
            @php
                $allPhotos = collect();
                foreach($reportsWithPhotos as $report) {
                    $decoded = json_decode($report->sign_image_path, true);
                    $images = [];
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $images = $decoded;
                    } elseif ($report->sign_image_path) {
                        $images = [$report->sign_image_path];
                    }
                    foreach($images as $img) {
                        $allPhotos->push((object)[
                            'path' => $img,
                            'advisor' => $report->user->name,
                            'date' => $report->report_date->format('d/m/Y'),
                            'qty' => $report->sign_captures
                        ]);
                    }
                }
            @endphp

            @if($allPhotos->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    @foreach($allPhotos as $photo)
                        <div class="bg-white dark:bg-[#11131c] rounded-2xl shadow-xl border border-gray-200 dark:border-gray-800/60 overflow-hidden group hover:scale-[1.02] transition-all duration-300">
                            <div class="relative aspect-[4/3] bg-gray-100 dark:bg-gray-950 overflow-hidden">
                                <img src="{{ asset($photo->path) }}" alt="Respaldo" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 cursor-pointer" onclick="openLightbox('{{ asset($photo->path) }}')">
                                <div class="absolute bottom-2 left-2 bg-black/60 backdrop-blur-sm text-white text-[10px] font-bold px-2.5 py-1 rounded-lg">
                                    📅 {{ $photo->date }}
                                </div>
                            </div>
                            <div class="p-4">
                                <h4 class="font-bold text-gray-850 dark:text-gray-200 text-sm truncate" title="{{ $photo->advisor }}">
                                    👤 {{ $photo->advisor }}
                                </h4>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                    Letreros reportados ese día: <strong class="text-indigo-500">{{ $photo->qty }}</strong>
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-16 text-gray-400 bg-white dark:bg-[#11131c] border border-gray-200 dark:border-gray-800/60 rounded-2xl shadow-xl">
                    <span class="text-5xl block mb-3">🖼️</span>
                    <h3 class="font-bold text-base text-gray-750 dark:text-gray-300">No hay fotos registradas esta semana</h3>
                    <p class="text-xs text-gray-500 mt-1.5">Las fotos que envíen tus asesores como respaldo aparecerán en esta galería.</p>
                </div>
            @endif
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
