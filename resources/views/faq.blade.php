<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
            ❓ Preguntas Frecuentes (FAQ)
        </h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
            Respuestas a las dudas operativas más comunes en el sistema
        </p>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-200 dark:border-gray-700 p-8 space-y-6 text-gray-700 dark:text-gray-300">
                
                <div class="space-y-4">
                    {{-- Pregunta 1 --}}
                    <div class="p-4 bg-gray-50 dark:bg-gray-700/30 rounded-2xl border border-gray-100 dark:border-gray-700/50">
                        <h4 class="font-bold text-gray-800 dark:text-gray-100 mb-1.5">❓ ¿Cómo reporto visitas que no tienen coordenadas GPS exactas?</h4>
                        <p class="text-xs leading-relaxed text-gray-600 dark:text-gray-400">
                            Para visitas que no pudiste geolocalizar en tiempo real o visitas históricas, puedes ingresar la cantidad total en el campo "Visitas Realizadas". El sistema solo te solicitará los datos GPS de forma detallada para aquellas visitas del día que decidas geolocalizar individualmente.
                        </p>
                    </div>

                    {{-- Pregunta 2 --}}
                    <div class="p-4 bg-gray-50 dark:bg-gray-700/30 rounded-2xl border border-gray-100 dark:border-gray-700/50">
                        <h4 class="font-bold text-gray-800 dark:text-gray-100 mb-1.5">❓ ¿Cuántas fotos de respaldo de letrero debo subir?</h4>
                        <p class="text-xs leading-relaxed text-gray-600 dark:text-gray-400">
                            Debes subir exactamente una (1) foto por cada captación con letrero reportada en el día. El formulario generará automáticamente la cantidad correspondiente de campos de archivos para que asocies una imagen independiente a cada letrero.
                        </p>
                    </div>

                    {{-- Pregunta 3 --}}
                    <div class="p-4 bg-gray-50 dark:bg-gray-700/30 rounded-2xl border border-gray-100 dark:border-gray-700/50">
                        <h4 class="font-bold text-gray-800 dark:text-gray-100 mb-1.5">❓ ¿Hasta qué hora puedo enviar mi reporte del día?</h4>
                        <p class="text-xs leading-relaxed text-gray-600 dark:text-gray-400">
                            El reporte diario debe ser enviado al finalizar tus actividades de la jornada. El sistema te permite registrar un único reporte cada día calendario.
                        </p>
                    </div>

                    {{-- Pregunta 4 --}}
                    <div class="p-4 bg-gray-50 dark:bg-gray-700/30 rounded-2xl border border-gray-100 dark:border-gray-700/50">
                        <h4 class="font-bold text-gray-800 dark:text-gray-100 mb-1.5">❓ ¿Cómo puedo corregir o modificar un reporte ya enviado?</h4>
                        <p class="text-xs leading-relaxed text-gray-600 dark:text-gray-400">
                            Si cometiste un error o completaste más llamadas o visitas posteriormente en el día, ve al Dashboard o al menú superior y presiona <strong>✏️ Actualizar Reporte de Hoy</strong>. Podrás recargar los datos y corregir cualquier información que necesites del día en curso.
                        </p>
                    </div>

                    {{-- Pregunta 5 --}}
                    <div class="p-4 bg-gray-50 dark:bg-gray-700/30 rounded-2xl border border-gray-100 dark:border-gray-700/50">
                        <h4 class="font-bold text-gray-800 dark:text-gray-100 mb-1.5">❓ ¿Quiénes pueden ver mis coordenadas y fotos cargadas?</h4>
                        <p class="text-xs leading-relaxed text-gray-600 dark:text-gray-400">
                            Las ubicaciones exactas y las evidencias fotográficas de los letreros solo son visibles para tu Líder de Equipo (Team Leader) directo y para el Director General mediante el mapa interactivo de supervisión consolidado de la plataforma.
                        </p>
                    </div>
                </div>

                <div class="pt-6 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                    <a href="{{ url()->previous() }}" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-md shadow-blue-600/20 transition-all active:scale-95">
                        Aceptar y Volver
                    </a>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
