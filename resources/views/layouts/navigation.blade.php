<nav x-data="{ open: false }" class="sticky top-0 z-40 backdrop-blur-md bg-white/90 dark:bg-gray-800/90 border-b border-gray-200/50 dark:border-gray-700/50 shadow-sm transition-all duration-300 print:hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    @php
                        $dashboardRoute = match(Auth::user()->role) {
                            'director' => 'director.dashboard',
                            'team_leader' => 'team-leader.dashboard',
                            'asesor' => 'asesor.dashboard',
                            default => 'login',
                        };
                    @endphp
                    <a href="{{ route($dashboardRoute) }}" class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <span class="font-bold text-gray-800 dark:text-gray-200 text-sm">Alfa Business</span>
                    </a>
                </div>
 
                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route($dashboardRoute)" :active="request()->routeIs('*.dashboard')" class="flex items-center gap-1.5 text-sm font-semibold">
                        <span>📊</span> Dashboard
                    </x-nav-link>

                    @if(Auth::user()->role === 'asesor')
                        @php
                            $todayReport = \App\Models\DailyReport::where('user_id', Auth::id())
                                ->where('report_date', \Carbon\Carbon::today())
                                ->first();
                            $reportRoute = $todayReport ? 'asesor.report.edit' : 'asesor.report.create';
                            $isActiveReport = request()->routeIs('asesor.report.create') || request()->routeIs('asesor.report.edit');
                        @endphp
                        <x-nav-link :href="route($reportRoute)" :active="$isActiveReport" class="flex items-center gap-1.5 text-sm font-semibold text-blue-600 dark:text-blue-400">
                            <span>📝</span> {{ $todayReport ? 'Actualizar Reporte' : 'Enviar Reporte' }}
                        </x-nav-link>
                    @endif

                    @if(Auth::user()->role === 'director')
                        <x-nav-link :href="route('director.reports.index')" :active="request()->routeIs('director.reports.index')" class="flex items-center gap-1.5 text-sm font-semibold text-blue-600 dark:text-blue-400">
                            <span>📸</span> Supervisión
                        </x-nav-link>

                        <x-nav-link :href="route('director.visits-map')" :active="request()->routeIs('director.visits-map')" class="flex items-center gap-1.5 text-sm font-semibold text-emerald-600 dark:text-emerald-400">
                            <span>🗺️</span> Mapa de Visitas
                        </x-nav-link>

                        <x-nav-link :href="route('director.users.index')" :active="request()->routeIs('director.users.*')" class="flex items-center gap-1.5 text-sm font-semibold">
                            <span>👥</span> Usuarios
                        </x-nav-link>
                        
                        <x-nav-link :href="route('director.offices.index')" :active="request()->routeIs('director.offices.*')" class="flex items-center gap-1.5 text-sm font-semibold">
                            <span>🏢</span> Oficinas
                        </x-nav-link>
                        
                        <x-nav-link :href="route('director.teams.index')" :active="request()->routeIs('director.teams.*')" class="flex items-center gap-1.5 text-sm font-semibold">
                            <span>👥</span> Equipos
                        </x-nav-link>
                        
                        <x-nav-link :href="route('director.kpis.index')" :active="request()->routeIs('director.kpis.*')" class="flex items-center gap-1.5 text-sm font-semibold">
                            <span>🎯</span> KPIs y Metas
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <!-- Rol badge -->
                @php
                    $roleBadge = match(Auth::user()->role) {
                        'director' => ['label' => 'Director', 'class' => 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200'],
                        'team_leader' => ['label' => 'Team Líder', 'class' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200'],
                        'asesor' => ['label' => 'Asesor', 'class' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'],
                        default => ['label' => 'Usuario', 'class' => 'bg-gray-100 text-gray-800'],
                    };
                @endphp
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $roleBadge['class'] }} mr-3">
                    {{ $roleBadge['label'] }}
                </span>

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            Perfil
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                Cerrar Sesión
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-xl text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-900/50 focus:outline-none transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

        </div>
    </div>

    <!-- Responsive Navigation Menu (Mobile) -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-white/95 dark:bg-gray-800/95 backdrop-blur-md border-t border-gray-200/50 dark:border-gray-700/50 transition-all duration-300">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route($dashboardRoute)" :active="request()->routeIs('*.dashboard')">
                📊 Dashboard
            </x-responsive-nav-link>

            @if(Auth::user()->role === 'asesor')
                @php
                    $todayReport = \App\Models\DailyReport::where('user_id', Auth::id())
                        ->where('report_date', \Carbon\Carbon::today())
                        ->first();
                    $reportRoute = $todayReport ? 'asesor.report.edit' : 'asesor.report.create';
                    $isActiveReport = request()->routeIs('asesor.report.create') || request()->routeIs('asesor.report.edit');
                @endphp
                <x-responsive-nav-link :href="route($reportRoute)" :active="$isActiveReport" class="text-blue-600 dark:text-blue-400">
                    📝 {{ $todayReport ? 'Actualizar Reporte' : 'Enviar Reporte' }}
                </x-responsive-nav-link>
            @endif

            @if(Auth::user()->role === 'director')
                <x-responsive-nav-link :href="route('director.reports.index')" :active="request()->routeIs('director.reports.index')" class="text-blue-600 dark:text-blue-400">
                    📸 Supervisión
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('director.visits-map')" :active="request()->routeIs('director.visits-map')" class="text-emerald-600 dark:text-emerald-400">
                    🗺️ Mapa de Visitas
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('director.users.index')" :active="request()->routeIs('director.users.*')">
                    👥 Usuarios
                </x-responsive-nav-link>
                
                <x-responsive-nav-link :href="route('director.offices.index')" :active="request()->routeIs('director.offices.*')">
                    🏢 Oficinas
                </x-responsive-nav-link>
                
                <x-responsive-nav-link :href="route('director.teams.index')" :active="request()->routeIs('director.teams.*')">
                    👥 Equipos
                </x-responsive-nav-link>
                
                <x-responsive-nav-link :href="route('director.kpis.index')" :active="request()->routeIs('director.kpis.*')">
                    🎯 KPIs y Metas
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-3 border-t border-gray-200 dark:border-gray-700/50">
            <div class="px-4 flex items-center justify-between">
                <div>
                    <div class="font-bold text-sm text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-xs text-gray-500 mt-0.5">{{ Auth::user()->email }}</div>
                </div>
                <!-- Rol badge mobile -->
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $roleBadge['class'] }}">
                    {{ $roleBadge['label'] }}
                </span>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')" :active="request()->routeIs('profile.*')">
                    👤 Mi Perfil
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                        🚪 Cerrar Sesión
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
