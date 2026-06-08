<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <a href="{{ route('director.dashboard') }}" class="text-sm text-blue-600 hover:underline">← Volver al Dashboard</a>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mt-1">Administración de Usuarios y Colaboradores</h2>
            </div>
            <div>
                <button onclick="openCreateUserModal()" class="px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 transition-colors shadow-md shadow-blue-600/20 flex items-center gap-1.5">
                    ➕ Registrar Nuevo Colaborador
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Alertas de éxito o error -->
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
                        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">Lista de Colaboradores Registrados</h3>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $users->count() }} Colaboradores en total</p>
                    </div>
                    <div class="relative w-full md:w-80">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">🔍</span>
                        <input type="text" id="userInputSearch" placeholder="Buscar colaborador..." class="w-full pl-9 pr-4 py-2 text-sm bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl focus:ring-blue-500 focus:border-blue-500 text-gray-700 dark:text-gray-200" onkeyup="filterUsersTable()">
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700 text-gray-500 uppercase text-xs">
                                <th class="py-3 px-2">Nombre / Email</th>
                                <th class="py-3 px-2">Rol / Teléfono</th>
                                <th class="py-3 px-2">Oficina / Equipo</th>
                                <th class="py-3 px-2 text-center">Estado</th>
                                <th class="py-3 px-2 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody" class="divide-y divide-gray-100 dark:divide-gray-700/50">
                            @forelse($users as $u)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 text-gray-700 dark:text-gray-300">
                                    <td class="py-3.5 px-2">
                                        <p class="font-bold text-gray-900 dark:text-white">{{ $u->name }}</p>
                                        <p class="text-xs text-gray-400">{{ $u->email }}</p>
                                    </td>
                                    <td class="py-3.5 px-2">
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xxs font-black uppercase tracking-wider {{ $u->role === 'director' ? 'bg-amber-100 text-amber-800' : ($u->role === 'team_leader' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800') }}">
                                            {{ $u->role === 'team_leader' ? 'Team Líder' : ucfirst($u->role) }}
                                        </span>
                                        <p class="text-xs text-gray-400 mt-1 font-semibold">{{ $u->phone ?? 'Sin teléfono registrado' }}</p>
                                    </td>
                                    <td class="py-3.5 px-2">
                                        <p class="text-xs font-bold text-gray-700 dark:text-gray-300">{{ $u->office ? $u->office->name : '⚠️ Sin Oficina' }}</p>
                                        <p class="text-xxs text-gray-400 font-semibold">{{ $u->team ? $u->team->name : '⚠️ Sin Equipo' }}</p>
                                    </td>
                                    <td class="py-3.5 px-2 text-center">
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $u->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $u->is_active ? 'Activo' : 'Desactivado' }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-2 text-right">
                                        <form method="POST" action="{{ route('director.users.update', $u->id) }}" class="flex flex-col gap-1 items-end">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="name" value="{{ $u->name }}">
                                            <input type="hidden" name="email" value="{{ $u->email }}">
                                            <input type="hidden" name="role" value="{{ $u->role }}">
                                            <input type="hidden" name="phone" value="{{ $u->phone }}">
                                            <input type="hidden" name="office_id" value="{{ $u->office_id }}">
                                            <input type="hidden" name="team_id" value="{{ $u->team_id }}">
                                            
                                            <div class="flex items-center gap-1.5">
                                                <button type="button" 
                                                    onclick="openEditUserModal('{{ $u->id }}', '{{ addslashes($u->name) }}', '{{ addslashes($u->email) }}', '{{ addslashes($u->role) }}', '{{ addslashes($u->phone ?? '') }}', '{{ $u->office_id ?? '' }}', '{{ $u->team_id ?? '' }}', '{{ $u->is_active ? 1 : 0 }}')"
                                                    class="p-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-xs font-bold transition-all flex items-center gap-1">
                                                    ✏️ Editar
                                                </button>
                                                
                                                <select name="is_active" onchange="this.form.submit()" class="rounded-lg border-gray-300 dark:border-gray-700 text-xxs py-0.5 dark:bg-gray-950">
                                                    <option value="1" {{ $u->is_active ? 'selected' : '' }}>Activo</option>
                                                    <option value="0" {{ !$u->is_active ? 'selected' : '' }}>Inactivo</option>
                                                </select>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-gray-500">No hay colaboradores registrados. Crea el primero arriba.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- MODAL DE CREACIÓN DE COLABORADOR -->
    <div id="createUserModal" class="fixed inset-0 z-50 overflow-y-auto hidden bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full border border-gray-200 dark:border-gray-700 p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-3">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                    <span>➕</span> Registrar Nuevo Colaborador
                </h3>
                <button onclick="closeCreateUserModal()" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
            </div>
            
            <form method="POST" action="{{ route('director.users.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nombre Completo</label>
                    <input type="text" name="name" required placeholder="Ej. Juan Pérez" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Correo Electrónico</label>
                    <input type="email" name="email" required placeholder="tu@correo.com" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Contraseña</label>
                    <input type="password" name="password" required placeholder="Mínimo 6 caracteres" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Rol de Acceso</label>
                    <select name="role" id="create_user_role" onchange="toggleModalFields('create')" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                        <option value="asesor">Asesor</option>
                        <option value="team_leader">Team Líder</option>
                        <option value="director">Director</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Teléfono</label>
                    <input type="text" name="phone" placeholder="Ej. +591 77000000" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                </div>
                <div id="create_office_container">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Asignar Oficina</label>
                    <select name="office_id" id="create_user_office" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                        <option value="">-- Sin Oficina --</option>
                        @foreach($offices as $office)
                            <option value="{{ $office->id }}">{{ $office->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="create_team_container">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Asignar Equipo</label>
                    <select name="team_id" id="create_user_team" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                        <option value="">-- Sin Equipo --</option>
                        @foreach($teams as $team)
                            <option value="{{ $team->id }}">{{ $team->name }} ({{ $team->office ? $team->office->name : 'N/A' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="pt-3 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2">
                    <button type="button" onclick="closeCreateUserModal()" class="px-4 py-2 text-xs font-bold text-gray-600 dark:text-gray-400 bg-gray-100 hover:bg-gray-250 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-lg">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-md shadow-blue-600/20">
                        💾 Registrar Colaborador
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL DE EDICIÓN FLOTANTE -->
    <div id="editUserModal" class="fixed inset-0 z-50 overflow-y-auto hidden bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full border border-gray-200 dark:border-gray-700 p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-3">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                    <span>👥</span> Editar Colaborador
                </h3>
                <button onclick="closeEditUserModal()" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
            </div>
            
            <form id="editUserForm" method="POST" action="" class="space-y-4">
                @csrf
                @method('PUT')
                
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nombre Completo</label>
                    <input type="text" name="name" id="edit_user_name" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Correo Electrónico</label>
                    <input type="email" name="email" id="edit_user_email" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Contraseña (Dejar en blanco para no cambiarla)</label>
                    <input type="password" name="password" id="edit_user_password" placeholder="Nueva contraseña (mín. 6 chars)" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Rol de Acceso</label>
                    <select name="role" id="edit_user_role" onchange="toggleModalFields('edit')" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                        <option value="asesor">Asesor</option>
                        <option value="team_leader">Team Líder</option>
                        <option value="director">Director</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Teléfono</label>
                    <input type="text" name="phone" id="edit_user_phone" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                </div>

                <div id="edit_office_container">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Asignar Oficina</label>
                    <select name="office_id" id="edit_user_office" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                        <option value="">-- Sin Oficina --</option>
                        @foreach($offices as $office)
                            <option value="{{ $office->id }}">{{ $office->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="edit_team_container">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Asignar Equipo</label>
                    <select name="team_id" id="edit_user_team" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                        <option value="">-- Sin Equipo --</option>
                        @foreach($teams as $team)
                            <option value="{{ $team->id }}">{{ $team->name }} ({{ $team->office ? $team->office->name : 'N/A' }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Estado de Acceso</label>
                    <select name="is_active" id="edit_user_active" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                        <option value="1">Habilitado (Activo)</option>
                        <option value="0">Deshabilitado (Inactivo)</option>
                    </select>
                </div>

                <div class="pt-3 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2">
                    <button type="button" onclick="closeEditUserModal()" class="px-4 py-2 text-xs font-bold text-gray-600 dark:text-gray-400 bg-gray-100 hover:bg-gray-250 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-lg">
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
        // Función para ocultar de forma fluida y dinámica los campos de oficina y equipo para Directores
        function toggleModalFields(prefix) {
            const roleSelect = document.getElementById(prefix === 'create' ? 'create_user_role' : 'edit_user_role');
            const officeContainer = document.getElementById(prefix === 'create' ? 'create_office_container' : 'edit_office_container');
            const teamContainer = document.getElementById(prefix === 'create' ? 'create_team_container' : 'edit_team_container');
            
            const officeSelect = document.getElementById(prefix === 'create' ? 'create_user_office' : 'edit_user_office');
            const teamSelect = document.getElementById(prefix === 'create' ? 'create_user_team' : 'edit_user_team');
            
            if (roleSelect.value === 'director') {
                officeContainer.style.display = 'none';
                teamContainer.style.display = 'none';
                officeSelect.value = '';
                teamSelect.value = '';
            } else {
                officeContainer.style.display = 'block';
                teamContainer.style.display = 'block';
            }
        }

        // Modal de Creación
        function openCreateUserModal() {
            // Resetear por defecto a asesor
            document.getElementById('create_user_role').value = 'asesor';
            toggleModalFields('create');
            document.getElementById('createUserModal').classList.remove('hidden');
            document.getElementById('createUserModal').classList.add('flex');
        }
        function closeCreateUserModal() {
            document.getElementById('createUserModal').classList.add('hidden');
            document.getElementById('createUserModal').classList.remove('flex');
        }

        // Modal de Edición
        function openEditUserModal(id, name, email, role, phone, officeId, teamId, isActive) {
            const form = document.getElementById('editUserForm');
            form.action = `/director/users/${id}`;
            
            document.getElementById('edit_user_name').value = name;
            document.getElementById('edit_user_email').value = email;
            document.getElementById('edit_user_password').value = ''; 
            document.getElementById('edit_user_role').value = role;
            document.getElementById('edit_user_phone').value = phone;
            document.getElementById('edit_user_office').value = officeId;
            document.getElementById('edit_user_team').value = teamId;
            document.getElementById('edit_user_active').value = isActive;
            
            // Evaluar los campos del rol de inmediato al abrir la edición
            toggleModalFields('edit');
            
            document.getElementById('editUserModal').classList.remove('hidden');
            document.getElementById('editUserModal').classList.add('flex');
        }

        function closeEditUserModal() {
            document.getElementById('editUserModal').classList.add('hidden');
            document.getElementById('editUserModal').classList.remove('flex');
        }

        // Búsqueda en Tiempo Real
        function filterUsersTable() {
            const input = document.getElementById("userInputSearch");
            const filter = input.value.toLowerCase();
            const rows = document.querySelectorAll("#usersTableBody tr");

            rows.forEach(row => {
                const nameEmail = row.cells[0]?.textContent || "";
                const rolePhone = row.cells[1]?.textContent || "";
                const officeTeam = row.cells[2]?.textContent || "";
                const status = row.cells[3]?.textContent || "";

                const textMatch = nameEmail.toLowerCase().includes(filter) ||
                                  rolePhone.toLowerCase().includes(filter) ||
                                  officeTeam.toLowerCase().includes(filter) ||
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
