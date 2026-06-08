<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
            📄 Términos y Condiciones de Uso
        </h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
            Última actualización: {{ date('d/m/Y') }}
        </p>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-200 dark:border-gray-700 p-8 space-y-6 text-gray-700 dark:text-gray-300">
                
                <div class="space-y-2">
                    <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100">1. Aceptación de los Términos</h3>
                    <p class="text-sm leading-relaxed">
                        Al acceder y utilizar el sistema de reporte diario de <strong>Alfa Business Group</strong>, usted acepta estar sujeto a estos Términos y Condiciones de Uso. Si no está de acuerdo con alguno de estos términos, tiene prohibido utilizar o acceder a este sitio.
                    </p>
                </div>

                <div class="space-y-2">
                    <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100">2. Licencia de Uso</h3>
                    <p class="text-sm leading-relaxed">
                        Se otorga permiso para acceder temporalmente a la plataforma web de reportes estrictamente con fines operativos internos de la empresa. Esta es la concesión de una licencia de uso corporativa, no una transferencia de título. Bajo esta licencia, usted no puede:
                    </p>
                    <ul class="list-disc pl-5 text-sm space-y-1">
                        <li>Modificar, copiar o intentar descompilar el software de la plataforma.</li>
                        <li>Utilizar el contenido para fines comerciales externos o exhibición pública no autorizada.</li>
                        <li>Remover firmas de derechos de autor u otras anotaciones de propiedad del código.</li>
                        <li>Transferir el acceso a su cuenta a otra persona que no pertenezca a la corporación Alfa Business Group.</li>
                    </ul>
                </div>

                <div class="space-y-2">
                    <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100">3. Responsabilidad del Usuario</h3>
                    <p class="text-sm leading-relaxed">
                        Como usuario activo (Asesor, Líder de Equipo o Director), usted se compromete a:
                    </p>
                    <ul class="list-disc pl-5 text-sm space-y-1">
                        <li>Proporcionar información verídica y exacta al registrar visitas, letreros, exclusivas, cierres y llamadas del día.</li>
                        <li>No falsificar datos de geolocalización GPS ni subir imágenes de respaldo que no correspondan a captaciones reales.</li>
                        <li>Mantener la confidencialidad de su contraseña y datos de inicio de sesión.</li>
                    </ul>
                </div>

                <div class="space-y-2">
                    <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100">4. Limitación de Responsabilidad</h3>
                    <p class="text-sm leading-relaxed">
                        El sistema de Alfa Business Group se proporciona "tal cual". Alfa Business Group no ofrece garantías, explícitas o implícitas, sobre la disponibilidad ininterrumpida de la plataforma o la exactitud absoluta en condiciones de baja cobertura móvil para capturas de geolocalización GPS.
                    </p>
                </div>

                <div class="space-y-2">
                    <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100">5. Modificaciones de los Términos</h3>
                    <p class="text-sm leading-relaxed">
                        Alfa Business Group puede revisar estos términos de servicio para su sistema en cualquier momento sin previo aviso. Al utilizar este sitio web, usted acepta estar sujeto a la versión vigente en ese momento de estos Términos y Condiciones de Uso.
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
