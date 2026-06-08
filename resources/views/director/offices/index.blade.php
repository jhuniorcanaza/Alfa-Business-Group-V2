<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <a href="{{ route('director.dashboard') }}" class="text-sm text-blue-600 hover:underline">← Volver al Dashboard</a>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mt-1">Gestión de Oficinas y Agencias</h2>
            </div>
            <div>
                <button onclick="openCreateOfficeModal()" class="px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 transition-colors shadow-md shadow-blue-600/20 flex items-center gap-1.5">
                    ➕ Registrar Nueva Oficina
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
                        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">Lista de Oficinas Registradas</h3>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $offices->count() }} Oficinas en total</p>
                    </div>
                    <div class="relative w-full md:w-80">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">🔍</span>
                        <input type="text" id="officeInputSearch" placeholder="Buscar oficina o ciudad..." class="w-full pl-9 pr-4 py-2 text-sm bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl focus:ring-blue-500 focus:border-blue-500 text-gray-700 dark:text-gray-200" onkeyup="filterOfficesTable()">
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700 text-gray-500 uppercase text-xs">
                                <th class="py-3 px-2">Oficina / Ciudad</th>
                                <th class="py-3 px-2">Dirección / Teléfono</th>
                                <th class="py-3 px-2 text-center">Asesores</th>
                                <th class="py-3 px-2 text-center">Estado</th>
                                <th class="py-3 px-2 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="officesTableBody" class="divide-y divide-gray-100 dark:divide-gray-700/50">
                            @forelse($offices as $office)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 text-gray-700 dark:text-gray-300">
                                    <td class="py-3.5 px-2">
                                        <p class="font-bold text-gray-900 dark:text-white">{{ $office->name }}</p>
                                        <p class="text-xs text-gray-400">{{ $office->city }}</p>
                                    </td>
                                    <td class="py-3.5 px-2">
                                        <p class="text-xs font-semibold">{{ $office->address ?? 'Sin dirección' }}</p>
                                        <p class="text-xxs text-gray-400">{{ $office->phone ?? 'Sin teléfono corporativo' }}</p>
                                    </td>
                                    <td class="py-3.5 px-2 text-center font-bold text-gray-900 dark:text-white">{{ $office->users_count }}</td>
                                    <td class="py-3.5 px-2 text-center">
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $office->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $office->is_active ? 'Activa' : 'Inactiva' }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-2 text-right">
                                        <form method="POST" action="{{ route('director.offices.update', $office->id) }}" class="flex flex-col gap-1 items-end">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="name" value="{{ $office->name }}">
                                            <input type="hidden" name="city" value="{{ $office->city }}">
                                            <input type="hidden" name="address" value="{{ $office->address }}">
                                            <input type="hidden" name="phone" value="{{ $office->phone }}">
                                            
                                            <div class="flex items-center gap-1.5">
                                                <button type="button" 
                                                    onclick="openEditOfficeModal('{{ $office->id }}', '{{ addslashes($office->name) }}', '{{ addslashes($office->city) }}', '{{ addslashes($office->address ?? '') }}', '{{ addslashes($office->phone ?? '') }}', '{{ $office->is_active ? 1 : 0 }}')"
                                                    class="p-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-xs font-bold transition-all">
                                                    ✏️ Editar
                                                </button>
                                                
                                                <select name="is_active" onchange="this.form.submit()" class="rounded-lg border-gray-300 dark:border-gray-700 text-xxs py-0.5 dark:bg-gray-950">
                                                    <option value="1" {{ $office->is_active ? 'selected' : '' }}>Habilitada</option>
                                                    <option value="0" {{ !$office->is_active ? 'selected' : '' }}>Deshabilitada</option>
                                                </select>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-gray-500">No hay oficinas registradas. Crea la primera arriba.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- MODAL DE CREACIÓN DE OFICINA -->
    <div id="createOfficeModal" class="fixed inset-0 z-50 overflow-y-auto hidden bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full border border-gray-200 dark:border-gray-700 p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-3">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                    <span>➕</span> Registrar Nueva Oficina
                </h3>
                <button onclick="closeCreateOfficeModal()" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
            </div>
            
            <form method="POST" action="{{ route('director.offices.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nombre de la Oficina</label>
                    <input type="text" name="name" required placeholder="Ej. Equipetrol" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Ciudad</label>
                    <input type="text" name="city" required placeholder="Ej. Santa Cruz" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Dirección</label>
                    <input type="text" name="address" placeholder="Ej. Av. San Martín #123" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Teléfono Corporativo</label>
                    <input type="text" name="phone" placeholder="Ej. +591 77000000" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                </div>
                <div class="pt-3 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2">
                    <button type="button" onclick="closeCreateOfficeModal()" class="px-4 py-2 text-xs font-bold text-gray-600 dark:text-gray-400 bg-gray-100 hover:bg-gray-250 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-lg">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-md shadow-blue-600/20">
                        💾 Registrar Oficina
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL DE EDICIÓN FLOTANTE -->
    <div id="editOfficeModal" class="fixed inset-0 z-50 overflow-y-auto hidden bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full border border-gray-200 dark:border-gray-700 p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-3">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                    <span>🏢</span> Editar Oficina
                </h3>
                <button onclick="closeEditOfficeModal()" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
            </div>
            
            <form id="editOfficeForm" method="POST" action="" class="space-y-4">
                @csrf
                @method('PUT')
                
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nombre de la Oficina</label>
                    <input type="text" name="name" id="edit_office_name" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Ciudad</label>
                    <input type="text" name="city" id="edit_office_city" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Dirección</label>
                    <input type="text" name="address" id="edit_office_address" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Teléfono Corporativo</label>
                    <input type="text" name="phone" id="edit_office_phone" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Estado</label>
                    <select name="is_active" id="edit_office_active" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                        <option value="1">Habilitada</option>
                        <option value="0">Deshabilitada</option>
                    </select>
                </div>

                <div class="pt-3 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2">
                    <button type="button" onclick="closeEditOfficeModal()" class="px-4 py-2 text-xs font-bold text-gray-600 dark:text-gray-400 bg-gray-100 hover:bg-gray-250 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-lg">
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
        function openCreateOfficeModal() {
            document.getElementById('createOfficeModal').classList.remove('hidden');
            document.getElementById('createOfficeModal').classList.add('flex');
        }
        function closeCreateOfficeModal() {
            document.getElementById('createOfficeModal').classList.add('hidden');
            document.getElementById('createOfficeModal').classList.remove('flex');
        }

        // Modal de Edición
        function openEditOfficeModal(id, name, city, address, phone, isActive) {
            const form = document.getElementById('editOfficeForm');
            form.action = `/director/offices/${id}`;
            
            document.getElementById('edit_office_name').value = name;
            document.getElementById('edit_office_city').value = city;
            document.getElementById('edit_office_address').value = address;
            document.getElementById('edit_office_phone').value = phone;
            document.getElementById('edit_office_active').value = isActive;
            
            document.getElementById('editOfficeModal').classList.remove('hidden');
            document.getElementById('editOfficeModal').classList.add('flex');
        }

        function closeEditOfficeModal() {
            document.getElementById('editOfficeModal').classList.add('hidden');
            document.getElementById('editOfficeModal').classList.remove('flex');
        }

        // Búsqueda en Tiempo Real de Oficinas
        function filterOfficesTable() {
            const input = document.getElementById("officeInputSearch");
            const filter = input.value.toLowerCase();
            const rows = document.querySelectorAll("#officesTableBody tr");

            rows.forEach(row => {
                const nameCity = row.cells[0]?.textContent || "";
                const addressPhone = row.cells[1]?.textContent || "";
                const status = row.cells[3]?.textContent || "";

                const textMatch = nameCity.toLowerCase().includes(filter) ||
                                  addressPhone.toLowerCase().includes(filter) ||
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
