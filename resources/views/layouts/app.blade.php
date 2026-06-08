<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <!-- ApexCharts CDN for Premium Interactive Visualizations -->
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen flex flex-col bg-gray-100 dark:bg-gray-900">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="flex-1">
                {{ $slot }}
            </main>

            <!-- Footer (Pie de página completo) -->
            <footer class="bg-white dark:bg-gray-800 border-t border-gray-250 dark:border-gray-700 py-10 mt-16 print:hidden">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 pb-8 border-b border-gray-150 dark:border-gray-700/50">
                        <!-- Columna 1: Enlaces Rápidos -->
                        <div>
                            <h4 class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-4">Enlaces del Sistema</h4>
                            @auth
                                @php
                                    $role = Auth::user()->role;
                                    $dashRoute = match($role) {
                                        'director' => 'director.dashboard',
                                        'team_leader' => 'team-leader.dashboard',
                                        'asesor' => 'asesor.dashboard',
                                        default => 'login',
                                    };
                                @endphp
                                <ul class="space-y-2.5 text-xs">
                                    <li>
                                        <a href="{{ route($dashRoute) }}" class="text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">📊 Dashboard Principal</a>
                                    </li>
                                    @if($role === 'asesor')
                                        @php
                                            $todayReport = \App\Models\DailyReport::where('user_id', Auth::id())
                                                ->where('report_date', \Carbon\Carbon::today())
                                                ->first();
                                            $reportRoute = $todayReport ? 'asesor.report.edit' : 'asesor.report.create';
                                        @endphp
                                        <li>
                                            <a href="{{ route($reportRoute) }}" class="text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">📝 {{ $todayReport ? 'Actualizar Reporte' : 'Enviar Reporte' }}</a>
                                        </li>
                                    @endif
                                    <li>
                                        <a href="{{ route('profile.edit') }}" class="text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">👤 Mi Perfil de Usuario</a>
                                    </li>
                                </ul>
                            @endauth
                        </div>

                        <!-- Columna 2: Soporte y Legal -->
                        <div>
                            <h4 class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-4">Soporte y Legalidad</h4>
                            <ul class="space-y-2.5 text-xs">
                                @auth
                                    <li>
                                        <a href="{{ route('privacy-policy') }}" class="text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">🔒 Política de Privacidad</a>
                                    </li>
                                @endauth
                                <li>
                                    <a href="{{ route('terms') }}" class="text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">📄 Términos y Condiciones de Uso</a>
                                </li>
                                <li>
                                    <a href="{{ route('faq') }}" class="text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">❓ Preguntas Frecuentes</a>
                                </li>
                            </ul>
                        </div>

                        <!-- Columna 3: Información de Soporte -->
                        <div>
                            <h4 class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-4">Soporte Técnico</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed mb-3">
                                Si experimentas inconvenientes técnicos o necesitas soporte con el mapa de geolocalización o la carga de imágenes, contacta al administrador.
                            </p>
                            <div class="text-xs text-gray-600 dark:text-gray-400 space-y-1">
                                <p>📧 <strong>Email:</strong> soporte@alfabolivia.com</p>
                            </div>
                        </div>
                    </div>

                    <!-- Fila inferior: Derechos de autor -->
                    <div class="flex flex-col sm:flex-row items-center justify-between pt-6 text-xs text-gray-500 dark:text-gray-400">
                        <p>© {{ date('Y') }} Alfa Business Group. Todos los derechos reservados.</p>
                        <p class="mt-2 sm:mt-0 text-[10px] text-gray-400 dark:text-gray-500">Sistema de Reporte Diario v2.4 · Optimizado para Bolivia</p>
                    </div>
                </div>
            </footer>
        </div>

        @auth
            <!-- Menú de Navegación Inferior Mobile (Solo se muestra en pantallas móviles) -->
            <div class="block sm:hidden fixed bottom-4 left-4 right-4 z-50 print:hidden">
                <div class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-lg rounded-2xl shadow-2xl border border-gray-200/50 dark:border-gray-700/50 py-2.5 px-3 flex items-center justify-around gap-1">
                    @php
                        $role = Auth::user()->role;
                        $dashRoute = match($role) {
                            'director' => 'director.dashboard',
                            'team_leader' => 'team-leader.dashboard',
                            'asesor' => 'asesor.dashboard',
                            default => 'login',
                        };
                    @endphp

                    <!-- Enlace Dashboard -->
                    <a href="{{ route($dashRoute) }}" class="flex flex-col items-center gap-0.5 text-center transition-all active:scale-95 flex-1 {{ request()->routeIs('*.dashboard') ? 'text-blue-600 dark:text-blue-400 font-black' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
                        <span class="text-xl">📊</span>
                        <span class="text-[10px] tracking-tight">Dashboard</span>
                    </a>

                    <!-- Enlace Acción Principal (Reportar para Asesor, o Gestión para Director) -->
                    @if($role === 'asesor')
                        @php
                            $todayReport = \App\Models\DailyReport::where('user_id', Auth::id())
                                ->where('report_date', \Carbon\Carbon::today())
                                ->first();
                            $reportRoute = $todayReport ? 'asesor.report.edit' : 'asesor.report.create';
                            $isActiveReport = request()->routeIs('asesor.report.create') || request()->routeIs('asesor.report.edit');
                        @endphp
                        <a href="{{ route($reportRoute) }}" class="flex flex-col items-center gap-0.5 text-center transition-all active:scale-95 flex-1 {{ $isActiveReport ? 'text-blue-600 dark:text-blue-400 font-black' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
                            <span class="text-xl">📝</span>
                            <span class="text-[10px] tracking-tight">{{ $todayReport ? 'Editar' : 'Reportar' }}</span>
                        </a>
                    @elseif($role === 'director')
                        <a href="{{ route('director.users.index') }}" class="flex flex-col items-center gap-0.5 text-center transition-all active:scale-95 flex-1 {{ request()->routeIs('director.users.*') ? 'text-blue-600 dark:text-blue-400 font-black' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
                            <span class="text-xl">👥</span>
                            <span class="text-[10px] tracking-tight">Usuarios</span>
                        </a>
                    @endif

                    <!-- Enlace Mi Perfil -->
                    <a href="{{ route('profile.edit') }}" class="flex flex-col items-center gap-0.5 text-center transition-all active:scale-95 flex-1 {{ request()->routeIs('profile.edit') ? 'text-blue-600 dark:text-blue-400 font-black' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
                        <span class="text-xl">👤</span>
                        <span class="text-[10px] tracking-tight">Perfil</span>
                    </a>

                    <!-- Botón Cerrar Sesión Rápido -->
                    <form method="POST" action="{{ route('logout') }}" class="flex-1 flex justify-center">
                        @csrf
                        <button type="submit" class="flex flex-col items-center gap-0.5 text-center transition-all active:scale-95 text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                            <span class="text-xl">🚪</span>
                            <span class="text-[10px] tracking-tight">Salir</span>
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Padding inferior para que el menú flotante no tape el contenido de las páginas en móvil -->
            <div class="block sm:hidden h-24 print:hidden"></div>
        @endauth

        <!-- ========================================== -->
        <!-- WIDGET DE CHATBOT FLOTANTE CON IA (ALFA AI) -->
        <!-- ========================================== -->
        @auth
            <!-- Botón Flotante Circular -->
            <button id="chatbotToggleBtn" onclick="toggleChatbot()" class="fixed bottom-24 right-6 sm:bottom-6 sm:right-6 z-50 w-14 h-14 rounded-full bg-gradient-to-tr from-blue-600 to-indigo-500 text-white shadow-2xl hover:scale-110 active:scale-95 transition-all duration-300 flex items-center justify-center cursor-pointer group print:hidden">
                <span class="text-2xl group-hover:rotate-12 transition-transform duration-300">🤖</span>
                <span class="absolute -top-1 -right-1 flex h-4 w-4">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-4 w-4 bg-green-500"></span>
                </span>
            </button>

            <!-- Burbuja de Chat Flotante -->
            <div id="chatbotContainer" class="fixed bottom-40 right-6 sm:bottom-24 sm:right-6 w-[380px] h-[520px] max-w-[calc(100vw-2rem)] max-h-[calc(100vh-12rem)] bg-white dark:bg-gray-800 rounded-3xl shadow-2xl border border-gray-200 dark:border-gray-700 z-50 flex flex-col overflow-hidden hidden transition-all duration-300 transform scale-95 opacity-0 origin-bottom-right print:hidden">
                <!-- Cabecera del Chatbot -->
                <div class="p-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center text-xl font-bold">
                            🤖
                        </div>
                        <div>
                            <h4 class="font-bold text-sm tracking-wide">Alfa AI</h4>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                                <span class="text-xs text-blue-100 font-semibold">En línea · Asistente Inmobiliario</span>
                            </div>
                        </div>
                    </div>
                    <button onclick="toggleChatbot()" class="w-8 h-8 rounded-full hover:bg-white/10 flex items-center justify-center transition-colors text-white/80 hover:text-white">
                        ✕
                    </button>
                </div>

                <!-- Historial de Conversación -->
                <div id="chatbotMessages" class="flex-1 p-4 overflow-y-auto space-y-3 bg-gray-50 dark:bg-gray-900/30">
                    <!-- Mensaje de bienvenida inicial -->
                    <div class="flex gap-2.5 max-w-[85%]">
                        <div class="w-7 h-7 rounded-full bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center text-sm shrink-0">
                            🤖
                        </div>
                        <div class="p-3 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl rounded-tl-none shadow-sm">
                            <p class="text-xs text-gray-800 dark:text-gray-200 leading-relaxed">
                                ¡Hola <strong>{{ Auth::user()->name }}</strong>! 👋 Soy tu asistente inteligente Alfa AI. 
                            </p>
                            @if(Auth::user()->role === 'asesor')
                                <p class="text-xs text-gray-800 dark:text-gray-200 leading-relaxed mt-1.5">
                                    ¿Quieres saber cuántas captaciones te faltan esta semana, o prefieres reportar tu actividad hablándome por nota de voz? 🎙️
                                </p>
                            @elseif(Auth::user()->role === 'team_leader')
                                <p class="text-xs text-gray-800 dark:text-gray-200 leading-relaxed mt-1.5">
                                    Puedo decirte quién es el asesor estrella de tu equipo, quién falta reportar hoy o darte estadísticas rápidas.
                                </p>
                            @else
                                <p class="text-xs text-gray-800 dark:text-gray-200 leading-relaxed mt-1.5">
                                    Consúltame el desempeño de las oficinas de Bolivia, rankings nacionales o acumulados corporativos globales.
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Campo de Entrada y Acciones -->
                <div class="p-3 border-t border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 flex items-center gap-2">
                    <!-- Botón de Micrófono para Dictar Nota de Voz -->
                    <button id="chatbotVoiceBtn" onclick="startSpeechRecognition()" class="w-10 h-10 rounded-xl bg-gray-50 dark:bg-gray-700/50 hover:bg-red-50 dark:hover:bg-red-950/20 text-gray-500 hover:text-red-500 flex items-center justify-center transition-all cursor-pointer border border-gray-200 dark:border-gray-700 active:scale-95 shrink-0" title="Dictar por Voz (Nota de voz)">
                        <span class="text-lg" id="voiceBtnIcon">🎙️</span>
                    </button>

                    <!-- Input de Texto -->
                    <input type="text" id="chatbotInput" placeholder="Escribe o dicta un mensaje..." class="flex-1 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" onkeydown="handleChatbotKey(event)">

                    <!-- Botón de Enviar -->
                    <button onclick="sendChatbotMessage()" class="w-10 h-10 rounded-xl bg-blue-600 hover:bg-blue-700 text-white flex items-center justify-center transition-all cursor-pointer border border-blue-700 shadow-md shadow-blue-600/20 active:scale-95 shrink-0" title="Enviar Mensaje">
                        <span class="text-lg">➔</span>
                    </button>
                </div>
            </div>

            <!-- Estilos y Scripts del Chatbot -->
            <script>
                // 1. Mostrar/Ocultar Chatbot
                function toggleChatbot() {
                    const container = document.getElementById('chatbotContainer');
                    if (container.classList.contains('hidden')) {
                        container.classList.remove('hidden');
                        setTimeout(() => {
                            container.classList.remove('scale-95', 'opacity-0');
                            container.classList.add('scale-100', 'opacity-100');
                        }, 50);
                    } else {
                        container.classList.remove('scale-100', 'opacity-100');
                        container.classList.add('scale-95', 'opacity-0');
                        setTimeout(() => {
                            container.classList.add('hidden');
                        }, 300);
                    }
                }

                // 2. Controlar la tecla Enter
                function handleChatbotKey(event) {
                    if (event.key === 'Enter') {
                        sendChatbotMessage();
                    }
                }

                // 3. Enviar mensaje a través de Fetch API
                function sendChatbotMessage() {
                    const input = document.getElementById('chatbotInput');
                    const text = input.value.trim();
                    if (!text) return;

                    input.value = '';
                    appendMessage('user', text);

                    // Indicador de carga del bot
                    const loadingId = appendMessage('bot', '<span class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-bounce"></span><span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-bounce" style="animation-delay: 0.2s"></span><span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-bounce" style="animation-delay: 0.4s"></span></span>');

                    fetch("{{ route('chatbot.message') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ message: text })
                    })
                    .then(response => response.json())
                    .then(data => {
                        removeMessage(loadingId);
                        if (data.success) {
                            appendMessage('bot', data.message);
                        } else {
                            appendMessage('bot', '❌ Lo siento, hubo un error de conexión con la IA. Por favor, intenta de nuevo.');
                        }
                    })
                    .catch(error => {
                        removeMessage(loadingId);
                        appendMessage('bot', '❌ Error de red: No se pudo conectar con el servidor.');
                    });
                }

                // 4. Agregar mensaje al historial visual
                function appendMessage(sender, content) {
                    const messagesContainer = document.getElementById('chatbotMessages');
                    const messageId = 'msg-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
                    
                    const isUser = (sender === 'user');
                    const bubbleClass = isUser 
                        ? 'p-3 bg-blue-600 text-white rounded-2xl rounded-tr-none shadow-sm text-xs leading-relaxed'
                        : 'p-3 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl rounded-tl-none shadow-sm text-xs text-gray-800 dark:text-gray-200 leading-relaxed';

                    const alignClass = isUser ? 'justify-end' : 'justify-start';
                    const avatarMarkup = isUser 
                        ? '' 
                        : '<div class="w-7 h-7 rounded-full bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center text-sm shrink-0">🤖</div>';

                    const html = `
                        <div id="${messageId}" class="flex gap-2.5 max-w-[85%] ${alignClass} ${isUser ? 'ml-auto' : ''}">
                            ${avatarMarkup}
                            <div class="${bubbleClass}">
                                ${content.replace(/\n/g, '<br>')}
                            </div>
                        </div>
                    `;

                    messagesContainer.insertAdjacentHTML('beforeend', html);
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                    return messageId;
                }

                function removeMessage(id) {
                    const el = document.getElementById(id);
                    if (el) el.remove();
                }

                // 5. Integración de Notas de Voz (Web Speech API)
                let recognition;
                let isRecording = false;

                function startSpeechRecognition() {
                    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
                    if (!SpeechRecognition) {
                        alert("⚠️ Tu navegador no soporta el dictado por voz de forma nativa. Te recomiendo usar Google Chrome, Microsoft Edge o Safari.");
                        return;
                    }

                    const voiceBtn = document.getElementById('chatbotVoiceBtn');
                    const voiceIcon = document.getElementById('voiceBtnIcon');

                    if (isRecording) {
                        recognition.stop();
                        return;
                    }

                    recognition = new SpeechRecognition();
                    recognition.lang = 'es-BO'; // Español de Bolivia
                    recognition.interimResults = false;
                    recognition.maxAlternatives = 1;

                    recognition.onstart = function() {
                        isRecording = true;
                        voiceBtn.classList.remove('bg-gray-50', 'dark:bg-gray-700/50');
                        voiceBtn.classList.add('bg-red-500', 'text-white', 'animate-pulse');
                        voiceIcon.textContent = '🛑';
                    };

                    recognition.onend = function() {
                        isRecording = false;
                        voiceBtn.classList.add('bg-gray-50', 'dark:bg-gray-700/50');
                        voiceBtn.classList.remove('bg-red-500', 'text-white', 'animate-pulse');
                        voiceIcon.textContent = '🎙️';
                    };

                    recognition.onerror = function(event) {
                        console.error("Speech Recognition Error:", event.error);
                    };

                    recognition.onresult = function(event) {
                        const transcript = event.results[0][0].transcript;
                        const input = document.getElementById('chatbotInput');
                        input.value = transcript;
                        
                        // Enviar automáticamente el mensaje de voz dictado
                        setTimeout(() => {
                            sendChatbotMessage();
                        }, 500);
                    };

                    recognition.start();
                }

                // 6. Heartbeat para mantener la sesión y el token CSRF siempre activos de forma indefinida
                setInterval(function() {
                    fetch("{{ route('ping') }}", {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Heartbeat: sesión activa.');
                    })
                    .catch(error => {
                        console.log('Heartbeat: error de conexión.');
                    });
                }, 15 * 60 * 1000); // Cada 15 minutos para evitar que la sesión o el token expiren
            </script>
        @endauth
    </body>
</html>
