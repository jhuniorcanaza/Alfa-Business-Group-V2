<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="Alfa Business Group - Sistema de Gestión Inmobiliaria Pre-CRM">

        <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

        <title>Alfa Business Group — Iniciar Sesión</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body { font-family: 'Inter', sans-serif; }

            .login-bg {
                background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
                position: relative;
                overflow: hidden;
            }

            .login-bg::before {
                content: '';
                position: absolute;
                top: -50%;
                left: -50%;
                width: 200%;
                height: 200%;
                background: radial-gradient(circle at 30% 40%, rgba(59, 130, 246, 0.08) 0%, transparent 50%),
                            radial-gradient(circle at 70% 60%, rgba(168, 85, 247, 0.06) 0%, transparent 50%);
                animation: float 20s ease-in-out infinite;
            }

            @keyframes float {
                0%, 100% { transform: translate(0, 0) rotate(0deg); }
                33% { transform: translate(2%, 1%) rotate(1deg); }
                66% { transform: translate(-1%, 2%) rotate(-1deg); }
            }

            .glass-card {
                background: rgba(255, 255, 255, 0.03);
                backdrop-filter: blur(20px);
                border: 1px solid rgba(255, 255, 255, 0.08);
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6),
                            inset 0 1px 0 rgba(255, 255, 255, 0.05);
            }

            .input-field {
                background: rgba(255, 255, 255, 0.04);
                border: 1px solid rgba(255, 255, 255, 0.1);
                color: #f1f5f9;
                transition: all 0.3s ease;
            }

            .input-field:focus {
                background: rgba(255, 255, 255, 0.07);
                border-color: rgba(59, 130, 246, 0.5);
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
                outline: none;
            }

            .input-field::placeholder {
                color: rgba(148, 163, 184, 0.6);
            }

            .btn-login {
                background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
                transition: all 0.3s ease;
                position: relative;
                overflow: hidden;
            }

            .btn-login:hover {
                background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 100%);
                transform: translateY(-1px);
                box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.4);
            }

            .btn-login:active {
                transform: translateY(0);
            }

            .logo-glow {
                text-shadow: 0 0 40px rgba(59, 130, 246, 0.3);
            }

            .grid-pattern {
                position: absolute;
                inset: 0;
                background-image:
                    linear-gradient(rgba(255, 255, 255, 0.02) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(255, 255, 255, 0.02) 1px, transparent 1px);
                background-size: 60px 60px;
            }

            .fade-in {
                animation: fadeIn 0.6s ease-out;
            }

            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
            }
        </style>
    </head>
    <body class="login-bg min-h-screen flex items-center justify-center p-4">
        <div class="grid-pattern"></div>

        <div class="relative z-10 w-full max-w-md fade-in">
            <!-- Logo y Marca -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center mb-4">
                    <img src="{{ asset('images/logo.png') }}" alt="Alfa Logo" class="w-20 h-20 object-contain rounded-2xl drop-shadow-lg logo-glow">
                </div>
                <h1 class="text-2xl font-bold text-white logo-glow tracking-tight">Alfa Business Group</h1>
                <p class="text-slate-400 text-sm mt-1">Sistema de Gestión Inmobiliaria</p>
            </div>

            <!-- Card de Login -->
            <div class="glass-card rounded-2xl p-8">
                <h2 class="text-lg font-semibold text-white mb-6">Iniciar Sesión</h2>

                <!-- Session Status -->
                @if (session('status'))
                    <div class="mb-4 p-3 rounded-lg bg-green-500/10 border border-green-500/20 text-green-400 text-sm">
                        {{ session('status') }}
                    </div>
                @endif

                <!-- Errores -->
                @if ($errors->any())
                    <div class="mb-4 p-3 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 text-sm">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email -->
                    <div class="mb-5">
                        <label for="email" class="block text-sm font-medium text-slate-300 mb-2">
                            Correo Electrónico
                        </label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="tu@correo.com"
                            class="input-field w-full px-4 py-3 rounded-xl text-sm"
                        >
                    </div>

                    <!-- Password -->
                    <div class="mb-5">
                        <label for="password" class="block text-sm font-medium text-slate-300 mb-2">
                            Contraseña
                        </label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="input-field w-full px-4 py-3 rounded-xl text-sm"
                        >
                    </div>

                    <!-- Remember + Forgot -->
                    <div class="flex items-center justify-between mb-6">
                        <label for="remember_me" class="inline-flex items-center cursor-pointer">
                            <input
                                id="remember_me"
                                type="checkbox"
                                name="remember"
                                class="w-4 h-4 rounded bg-white/5 border-white/10 text-blue-500 focus:ring-blue-500/30 focus:ring-offset-0"
                            >
                            <span class="ms-2 text-sm text-slate-400">Recordarme</span>
                        </label>
                    </div>

                    <!-- Submit -->
                    <button type="submit" id="btn-login" class="btn-login w-full py-3 px-4 rounded-xl text-white font-semibold text-sm tracking-wide">
                        Ingresar
                    </button>
                </form>
            </div>

            <!-- Footer -->
            <p class="text-center text-slate-500 text-xs mt-6">
                © {{ date('Y') }} Alfa Business Group · Santa Cruz, Bolivia
            </p>
        </div>
    </body>
</html>
