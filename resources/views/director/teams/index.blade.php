<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <a href="{{ route('director.dashboard') }}" class="text-sm text-blue-600 hover:underline">← Volver al Dashboard</a>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mt-1">Gestión de Equipos (Teams)</h2>
            </div>
            <div>
                <button onclick="openCreateTeamModal()" class="px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 transition-colors shadow-md shadow-blue-600/20 flex items-center gap-1.5">
                    ➕ Registrar Nuevo Equipo
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Alertas de éxito -->
            @if(session('success'))
                <div class="p-4 rounded-xl bg-green-50 dark:bg-green-950/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="p-4 rounded-xl bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300">
                    <ul class="list-disc pl-5 text-sm">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">Lista de Equipos Registrados</h3>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $teams->count() }} Equipos en total</p>
                    </div>
                    <div class="relative w-full md:w-80">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">🔍</span>
                        <input type="text" id="teamInputSearch" placeholder="Buscar equipo, oficina o líder..." class="w-full pl-9 pr-4 py-2 text-sm bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl focus:ring-blue-500 focus:border-blue-500 text-gray-700 dark:text-gray-200" onkeyup="filterTeamsTable()">
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700 text-gray-500 uppercase text-xs">
                                <th class="py-3 px-2">Equipo</th>
                                <th class="py-3 px-2">Oficina / Ciudad</th>
                                <th class="py-3 px-2">Team Líder</th>
                                <th class="py-3 px-2 text-center">Asesores</th>
                                <th class="py-3 px-2 text-center">Estado</th>
                                <th class="py-3 px-2 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="teamsTableBody" class="divide-y divide-gray-100 dark:divide-gray-700/50">
                            @forelse($teams as $team)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 text-gray-700 dark:text-gray-300">
                                    <td class="py-3.5 px-2">
                                        <p class="font-bold text-gray-900 dark:text-white">{{ $team->name }}</p>
                                    </td>
                                    <td class="py-3.5 px-2">
                                        <p class="font-semibold">{{ $team->office->name ?? 'Sin Oficina' }}</p>
                                        <p class="text-xxs text-gray-400">{{ $team->office->city ?? '' }}</p>
                                    </td>
                                    <td class="py-3.5 px-2">
                                        <p class="font-bold text-blue-600 dark:text-blue-400">{{ $team->leader->name ?? '⚠️ Sin Líder Asignado' }}</p>
                                        <p class="text-xxs text-gray-400">{{ $team->leader->email ?? '' }}</p>
                                    </td>
                                    <td class="py-3.5 px-2 text-center font-black text-gray-900 dark:text-white">
                                        {{ $team->members_count }}
                                    </td>
                                    <td class="py-3.5 px-2 text-center">
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $team->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $team->is_active ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-2 text-right">
                                        <form method="POST" action="{{ route('director.teams.update', $team->id) }}" class="flex flex-col gap-1 items-end">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="name" value="{{ $team->name }}">
                                            <input type="hidden" name="office_id" value="{{ $team->office_id }}">
                                            <input type="hidden" name="leader_id" value="{{ $team->leader_id }}">
                                            
                                            <div class="flex items-center gap-1.5">
                                                <button type="button" 
                                                    onclick="openEditTeamModal('{{ $team->id }}', '{{ addslashes($team->name) }}', '{{ $team->office_id }}', '{{ $team->leader_id }}', '{{ $team->is_active ? 1 : 0 }}')"
                                                    class="p-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-xs font-bold transition-all">
                                                    ✏️ Editar
                                                </button>
                                                
                                                <select name="is_active" onchange="this.form.submit()" class="rounded-lg border-gray-300 dark:border-gray-700 text-xxs py-0.5 dark:bg-gray-950">
                                                    <option value="1" {{ $team->is_active ? 'selected' : '' }}>Habilitado</option>
                                                    <option value="0" {{ !$team->is_active ? 'selected' : '' }}>Deshabilitado</option>
                                                </select>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-6 text-center text-gray-500">No hay equipos registrados todavía. Crea el primero arriba.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- MODAL DE CREACIÓN DE EQUIPO -->
    <div id="createTeamModal" class="fixed inset-0 z-50 overflow-y-auto hidden bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full border border-gray-200 dark:border-gray-700 p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-3">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                    <span>➕</span> Registrar Nuevo Equipo
                </h3>
                <button onclick="closeCreateTeamModal()" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
            </div>
            
            <form method="POST" action="{{ route('director.teams.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nombre del Equipo</label>
                    <input type="text" name="name" required placeholder="Ej. Team Platinum" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Oficina Asignada</label>
                    <select name="office_id" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                        <option value="">-- Selecciona Oficina --</option>
                        @foreach($offices as $office)
                            <option value="{{ $office->id }}">{{ $office->name }} ({{ $office->city }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Team Líder</label>
                    <select name="leader_id" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                        <option value="">-- Sin Líder Inicial --</option>
                        @foreach($leaders as $leader)
                            @php
                                $ledTeam = $leader->ledTeams->where('is_active', true)->first();
                            @endphp
                            @if($ledTeam)
                                <option value="{{ $leader->id }}" disabled class="text-gray-400 bg-gray-100 dark:bg-gray-800 line-through">
                                    🚫 {{ $leader->name }} (Ya asignado al equipo: {{ $ledTeam->name }})
                                </option>
                            @else
                                <option value="{{ $leader->id }}">👤 {{ $leader->name }}</option>
                            @endif
                        @endforeach
                    </select>
                    <p class="text-xxs text-gray-400 mt-1">Solo se listan usuarios con rol 'Team Líder' y que estén activos y disponibles.</p>
                </div>
                <div class="pt-3 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2">
                    <button type="button" onclick="closeCreateTeamModal()" class="px-4 py-2 text-xs font-bold text-gray-600 dark:text-gray-400 bg-gray-100 hover:bg-gray-250 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-lg">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-md shadow-blue-600/20">
                        💾 Registrar Equipo
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL DE EDICIÓN FLOTANTE -->
    <div id="editTeamModal" class="fixed inset-0 z-50 overflow-y-auto hidden bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full border border-gray-200 dark:border-gray-700 p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-3">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                    <span>✏️</span> Editar Equipo
                </h3>
                <button onclick="closeEditTeamModal()" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
            </div>
            
            <form id="editTeamForm" method="POST" action="" class="space-y-4">
                @csrf
                @method('PUT')
                
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nombre del Equipo</label>
                    <input type="text" name="name" id="edit_team_name" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Oficina Asignada</label>
                    <select name="office_id" id="edit_team_office" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                        @foreach($offices as $office)
                            <option value="{{ $office->id }}">{{ $office->name }} ({{ $office->city }})</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Team Líder</label>
                    <select name="leader_id" id="edit_team_leader" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                        <option value="">-- Sin Líder --</option>
                        @foreach($leaders as $leader)
                            @php
                                $ledTeam = $leader->ledTeams->where('is_active', true)->first();
                            @endphp
                            <option value="{{ $leader->id }}" data-led-team-id="{{ $ledTeam ? $ledTeam->id : '' }}">
                                {{ $leader->name }} {!! $ledTeam ? "(Ya lidera a: {$ledTeam->name})" : '' !!}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Estado</label>
                    <select name="is_active" id="edit_team_active" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                        <option value="1">Habilitado</option>
                        <option value="0">Deshabilitado</option>
                    </select>
                </div>

                <div class="pt-3 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2">
                    <button type="button" onclick="closeEditTeamModal()" class="px-4 py-2 text-xs font-bold text-gray-600 dark:text-gray-400 bg-gray-100 hover:bg-gray-250 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-lg">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-md shadow-blue-600/20">
                        💾 Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal de Creación
        function openCreateTeamModal() {
            document.getElementById('createTeamModal').classList.remove('hidden');
            document.getElementById('createTeamModal').classList.add('flex');
        }
        function closeCreateTeamModal() {
            document.getElementById('createTeamModal').classList.add('hidden');
            document.getElementById('createTeamModal').classList.remove('flex');
        }

        // Modal de Edición
        function openEditTeamModal(id, name, officeId, leaderId, isActive) {
            const form = document.getElementById('editTeamForm');
            form.action = `/director/teams/${id}`;
            
            document.getElementById('edit_team_name').value = name;
            document.getElementById('edit_team_office').value = officeId;
            document.getElementById('edit_team_leader').value = leaderId;
            document.getElementById('edit_team_active').value = isActive;
            
            // Habilitar/Deshabilitar líderes dinámicamente según si ya lideran otros equipos activos
            const select = document.getElementById('edit_team_leader');
            Array.from(select.options).forEach(opt => {
                if (opt.value === '') return;
                const ledTeamId = opt.getAttribute('data-led-team-id');
                // Si lidera un equipo y no es el líder actual de este equipo que editamos
                if (ledTeamId && ledTeamId !== id && opt.value !== leaderId) {
                    opt.disabled = true;
                    opt.classList.add('text-gray-400', 'line-through');
                } else {
                    opt.disabled = false;
                    opt.classList.remove('text-gray-400', 'line-through');
                }
            });
            
            document.getElementById('editTeamModal').classList.remove('hidden');
            document.getElementById('editTeamModal').classList.add('flex');
        }

        function closeEditTeamModal() {
            document.getElementById('editTeamModal').classList.add('hidden');
            document.getElementById('editTeamModal').classList.remove('flex');
        }

        // Búsqueda en Tiempo Real de Equipos
        function filterTeamsTable() {
            const input = document.getElementById("teamInputSearch");
            const filter = input.value.toLowerCase();
            const rows = document.querySelectorAll("#teamsTableBody tr");

            rows.forEach(row => {
                const name = row.cells[0]?.textContent || "";
                const officeCity = row.cells[1]?.textContent || "";
                const leader = row.cells[2]?.textContent || "";
                const status = row.cells[4]?.textContent || "";

                const textMatch = name.toLowerCase().includes(filter) ||
                                  officeCity.toLowerCase().includes(filter) ||
                                  leader.toLowerCase().includes(filter) ||
                                  status.toLowerCase().includes(filter);

                if (textMatch) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        }
    </script>
</x-app-layout>
