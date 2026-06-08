@php
    $role = Auth::user()->role;
    $dashboardRoute = match($role) {
        'director' => 'director.dashboard',
        'team_leader' => 'team-leader.dashboard',
        'asesor' => 'asesor.dashboard',
        default => 'login',
    };

    // Determinar si hoy ya se envió reporte (para asesor)
    $todayReport = null;
    if ($role === 'asesor') {
        $todayReport = \App\Models\DailyReport::where('user_id', Auth::id())
            ->where('report_date', \Carbon\Carbon::today())
            ->first();
    }
    $reportRoute = $todayReport ? 'asesor.report.edit' : 'asesor.report.create';
    $isActiveReport = request()->routeIs('asesor.report.create') || request()->routeIs('asesor.report.edit');

    // Estilos del badge de rol
    $roleBadge = match($role) {
        'director' => ['label' => 'Director', 'class' => 'bg-amber-500/20 text-amber-400 border-amber-500/30'],
        'team_leader' => ['label' => 'Team Líder', 'class' => 'bg-purple-500/20 text-purple-400 border-purple-500/30'],
        'asesor' => ['label' => 'Asesor', 'class' => 'bg-blue-500/20 text-blue-400 border-blue-500/30'],
        default => ['label' => 'Usuario', 'class' => 'bg-gray-500/20 text-gray-400 border-gray-500/30'],
    };
@endphp

<nav x-data="{ open: false }" class="sticky top-0 z-40 bg-[#0f111a] border-b border-gray-800 text-gray-300 print:hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center flex-1">
                <!-- Logo -->
                <div class="shrink-0 flex items-center mr-6">
                    <a href="{{ route($dashboardRoute) }}" class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                            <span class="text-white text-base">☋</span>
                        </div>
                        <span class="font-bold text-white text-sm tracking-wide">Alfa Business</span>
                    </a>
                </div>

                <!-- Horizontal Navigation Links (Desktop) -->
                <div class="hidden sm:flex sm:space-x-4 h-full items-center">
                    @if($role === 'asesor')
                        <!-- Enlaces Asesor -->
                        <a href="{{ route('asesor.dashboard') }}" 
                           class="inline-flex items-center px-3 h-full text-xs font-semibold border-b-2 transition-all {{ request()->routeIs('asesor.dashboard') && !request()->has('anchor') ? 'border-indigo-500 text-white' : 'border-transparent hover:text-white hover:border-gray-700' }}">
                            Dashboard
                        </a>
                        <a href="{{ route($reportRoute) }}" 
                           class="inline-flex items-center px-3 h-full text-xs font-semibold border-b-2 transition-all {{ $isActiveReport ? 'border-indigo-500 text-white' : 'border-transparent hover:text-white hover:border-gray-700' }}">
                            Enviar reporte
                        </a>
                        <a href="{{ route('asesor.dashboard') }}#historial" 
                           class="inline-flex items-center px-3 h-full text-xs font-semibold border-b-2 border-transparent hover:text-white hover:border-gray-700 transition-all">
                            Mi historial
                        </a>
                        <a href="{{ route('asesor.dashboard') }}#ranking" 
                           class="inline-flex items-center px-3 h-full text-xs font-semibold border-b-2 border-transparent hover:text-white hover:border-gray-700 transition-all">
                            Ranking
                        </a>
                        <a href="{{ route('asesor.visits-map') }}" 
                           class="inline-flex items-center px-3 h-full text-xs font-semibold border-b-2 transition-all {{ request()->routeIs('asesor.visits-map') ? 'border-indigo-500 text-white' : 'border-transparent hover:text-white hover:border-gray-700' }}">
                            Mis visitas en mapa
                        </a>
                    @elseif($role === 'team_leader')
                        <!-- Enlaces Team Leader -->
                        <a href="{{ route('team-leader.dashboard') }}" 
                           class="inline-flex items-center px-3 h-full text-xs font-semibold border-b-2 transition-all {{ request()->routeIs('team-leader.dashboard') && !request()->routeIs('team-leader.visits-map') ? 'border-indigo-500 text-white' : 'border-transparent hover:text-white hover:border-gray-700' }}">
                            Dashboard
                        </a>
                    @elseif($role === 'director')
                        <!-- Enlaces Director -->
                        <a href="{{ route('director.dashboard') }}" 
                           class="inline-flex items-center px-3 h-full text-xs font-semibold border-b-2 transition-all {{ request()->routeIs('director.dashboard') ? 'border-indigo-500 text-white' : 'border-transparent hover:text-white hover:border-gray-700' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('director.reports.index') }}" 
                           class="inline-flex items-center px-3 h-full text-xs font-semibold border-b-2 transition-all {{ request()->routeIs('director.reports.index') ? 'border-indigo-500 text-white' : 'border-transparent hover:text-white hover:border-gray-700' }}">
                            Supervisión
                        </a>
                        <a href="{{ route('director.visits-map') }}" 
                           class="inline-flex items-center px-3 h-full text-xs font-semibold border-b-2 transition-all {{ request()->routeIs('director.visits-map') ? 'border-indigo-500 text-white' : 'border-transparent hover:text-white hover:border-gray-700' }}">
                            Mapa de visitas
                        </a>
                        <a href="{{ route('director.users.index') }}" 
                           class="inline-flex items-center px-3 h-full text-xs font-semibold border-b-2 transition-all {{ request()->routeIs('director.users.index') ? 'border-indigo-500 text-white' : 'border-transparent hover:text-white hover:border-gray-700' }}">
                            Usuarios
                        </a>
                        <a href="{{ route('director.offices.index') }}" 
                           class="inline-flex items-center px-3 h-full text-xs font-semibold border-b-2 transition-all {{ request()->routeIs('director.offices.index') ? 'border-indigo-500 text-white' : 'border-transparent hover:text-white hover:border-gray-700' }}">
                            Oficinas
                        </a>
                    @endif
                </div>
            </div>

            <!-- Settings & Profile Card (Desktop) -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-3">
                <!-- Rol badge -->
                <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-bold border {{ $roleBadge['class'] }}">
                    {{ $roleBadge['label'] }}
                </span>

                <!-- User Dropdown Menu -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-1.5 px-3 py-2 border border-transparent text-xs font-bold rounded-lg text-gray-300 hover:text-white hover:bg-white/5 transition-all focus:outline-none">
                            <div>{{ Auth::user()->name }}</div>
                            <svg class="fill-current h-4 w-4 opacity-60" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')" class="text-xs">
                            👤 Mi Perfil
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();" class="text-xs text-red-500 hover:text-red-600">
                                🚪 Cerrar Sesión
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger (Mobile) -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-xl text-gray-400 hover:text-white hover:bg-white/5 focus:outline-none transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu (Mobile Drawer) -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-[#0d0e15] border-t border-gray-800 transition-all duration-300">
        <div class="pt-2 pb-3 space-y-1 px-3">
            @if($role === 'asesor')
                <x-responsive-nav-link :href="route('asesor.dashboard')" :active="request()->routeIs('asesor.dashboard')" class="text-gray-300">
                    📊 Dashboard
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route($reportRoute)" :active="$isActiveReport" class="text-blue-400">
                    📝 Enviar reporte
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('asesor.dashboard') . '#historial'" class="text-gray-300">
                    📋 Mi historial
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('asesor.dashboard') . '#ranking'" class="text-gray-300">
                    🏆 Ranking
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('asesor.visits-map')" :active="request()->routeIs('asesor.visits-map')" class="text-gray-300">
                    🗺️ Mis visitas en mapa
                </x-responsive-nav-link>
            @elseif($role === 'team_leader')
                <x-responsive-nav-link :href="route('team-leader.dashboard')" :active="request()->routeIs('team-leader.dashboard')" class="text-gray-300">
                    📊 Dashboard
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('team-leader.visits-map')" :active="request()->routeIs('team-leader.visits-map')" class="text-emerald-400">
                    🗺️ Mapa de visitas
                </x-responsive-nav-link>
            @elseif($role === 'director')
                <x-responsive-nav-link :href="route('director.dashboard')" :active="request()->routeIs('director.dashboard')" class="text-gray-300">
                    📊 Dashboard
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('director.reports.index')" :active="request()->routeIs('director.reports.index')" class="text-blue-400">
                    📸 Supervisión
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('director.visits-map')" :active="request()->routeIs('director.visits-map')" class="text-emerald-400">
                    🗺️ Mapa de visitas
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('director.users.index')" :active="request()->routeIs('director.users.index')" class="text-gray-300">
                    👥 Usuarios
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('director.offices.index')" :active="request()->routeIs('director.offices.index')" class="text-gray-300">
                    🏢 Oficinas
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-3 border-t border-gray-800">
            <div class="px-4 flex items-center justify-between">
                <div>
                    <div class="font-bold text-sm text-white">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-xs text-gray-500 mt-0.5">{{ Auth::user()->email }}</div>
                </div>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $roleBadge['class'] }}">
                    {{ $roleBadge['label'] }}
                </span>
            </div>

            <div class="mt-3 space-y-1 px-3">
                <x-responsive-nav-link :href="route('profile.edit')" :active="request()->routeIs('profile.*')" class="text-gray-300">
                    👤 Mi Perfil
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();" class="text-red-400">
                        🚪 Cerrar Sesión
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
