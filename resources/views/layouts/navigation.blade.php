@php
    $role = Auth::user()->role;
    $roleBadge = match($role) {
        'director' => ['label' => 'Director', 'class' => 'bg-amber-500/20 text-amber-400 border-amber-500/30'],
        'team_leader' => ['label' => 'Team Líder', 'class' => 'bg-purple-500/20 text-purple-400 border-purple-500/30'],
        'asesor' => ['label' => 'Asesor', 'class' => 'bg-blue-500/20 text-blue-400 border-blue-500/30'],
        default => ['label' => 'Usuario', 'class' => 'bg-gray-500/20 text-gray-400 border-gray-500/30'],
    };
@endphp

<nav class="sticky top-0 z-40 bg-[#0f111a] text-gray-300 print:hidden h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8">
    <!-- Left side: Hamburger button (Mobile) -->
    <div class="flex items-center gap-3">
        <button @click="sidebarOpen = !sidebarOpen" class="md:hidden inline-flex items-center justify-center p-2.5 rounded-xl text-gray-400 hover:text-white hover:bg-white/5 focus:outline-none transition active:scale-95">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
        
        <!-- Brand on mobile, Page title on desktop -->
        <span class="md:hidden font-bold text-white text-sm tracking-wide">Alfa Business</span>
        <span class="hidden md:inline text-xs font-semibold text-gray-400 uppercase tracking-widest">
            {{ match($role) {
                'director' => 'Panel Directivo Principal',
                'team_leader' => 'Monitoreo de Equipos',
                'asesor' => 'Gestión de Ventas y Reportes',
                default => 'Sistema'
            } }}
        </span>
    </div>

    <!-- Right side: Dropdown & Role badge -->
    <div class="flex items-center gap-3">
        <!-- Rol badge -->
        <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-bold border {{ $roleBadge['class'] }}">
            {{ $roleBadge['label'] }}
        </span>

        <!-- Dropdown -->
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
</nav>
