<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
            🔒 Política de Privacidad
        </h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
            Última actualización: {{ date('d/m/Y') }}
        </p>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-200 dark:border-gray-700 p-8 space-y-6 text-gray-700 dark:text-gray-300">
                
                <div class="space-y-2">
                    <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100">1. Introducción</h3>
                    <p class="text-sm leading-relaxed">
                        En <strong>Alfa Business Group</strong>, valoramos y respetamos su privacidad. Esta Política de Privacidad describe cómo recopilamos, utilizamos y protegemos la información personal de nuestros usuarios (incluyendo Directores, Líderes de Equipo y Asesores) al utilizar nuestra plataforma web y sistemas de reporte inmobiliario.
                    </p>
                </div>

                <div class="space-y-2">
                    <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100">2. Información que Recopilamos</h3>
                    <p class="text-sm leading-relaxed">
                        Recopilamos la información estrictamente necesaria para garantizar el correcto funcionamiento del sistema de monitoreo y reportes diarios:
                    </p>
                    <ul class="list-disc pl-5 text-sm space-y-1">
                        <li><strong>Datos de Registro:</strong> Nombre completo, correo electrónico, rol dentro de la empresa, oficina y equipo asignado.</li>
                        <li><strong>Datos de Geolocalización:</strong> Al registrar visitas de clientes, el sistema solicita acceso a las coordenadas GPS (latitud y longitud) del dispositivo para validar la ubicación.</li>
                        <li><strong>Evidencias Fotográficas:</strong> Imágenes de captaciones de letreros subidas de forma voluntaria para respaldar el trabajo diario.</li>
                        <li><strong>Datos de Actividad:</strong> Número de visitas, llamadas realizadas, cierres concretados y propiedades ingresadas al sistema AlphaX.</li>
                    </ul>
                </div>

                <div class="space-y-2">
                    <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100">3. Uso de la Información</h3>
                    <p class="text-sm leading-relaxed">
                        Los datos recopilados se utilizan únicamente para:
                    </p>
                    <ul class="list-disc pl-5 text-sm space-y-1">
                        <li>Visualizar el progreso y KPIs en tiempo real de los asesores en los dashboards respectivos.</li>
                        <li>Permitir que los Directores y Líderes de Equipo supervisen el rendimiento geolocalizado en el mapa corporativo.</li>
                        <li>Generar reportes consolidados y análisis de productividad empresarial.</li>
                        <li>Garantizar la seguridad y auditoría del sistema frente a accesos no autorizados.</li>
                    </ul>
                </div>

                <div class="space-y-2">
                    <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100">4. Almacenamiento y Seguridad de Datos</h3>
                    <p class="text-sm leading-relaxed">
                        Toda la información recolectada se almacena de forma segura en servidores de Alfa Business Group protegidos por firewalls y protocolos de cifrado estándar (SSL/HTTPS). Aplicamos estrictas medidas de seguridad técnica y administrativa para evitar la pérdida, alteración o acceso no autorizado a sus datos.
                    </p>
                </div>

                <div class="space-y-2">
                    <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100">5. Retención de Información</h3>
                    <p class="text-sm leading-relaxed">
                        Conservaremos su información mientras mantenga una cuenta activa en nuestro sistema o sea necesario para cumplir con los fines empresariales descritos en esta política. Las imágenes de respaldo de letreros se eliminan de forma periódica cuando ya no son requeridas para auditorías internas.
                    </p>
                </div>

                <div class="space-y-2">
                    <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100">6. Cambios en esta Política</h3>
                    <p class="text-sm leading-relaxed">
                        Nos reservamos el derecho de modificar esta Política de Privacidad en cualquier momento. Cualquier cambio significativo será notificado en la plataforma con anticipación.
                    </p>
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
