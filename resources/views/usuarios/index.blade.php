{{-- resources/views/usuarios/index.blade.php --}}
<x-app-layout>
    @php
        $tab = $tab ?? request('tab', 'usuarios');

        $users = $users ?? [];
        $roles = $roles ?? [];

        // Roles + permisos (para tab roles)
        $rolesFull = $rolesFull ?? [];
        $permisosByModulo = $permisosByModulo ?? [];

        // Flash messages
        $success = session('success');
        $error = session('error');
    @endphp

    <div class="p-6 space-y-6"
         x-data="usuariosPage(@js($tab), @js($users), @js($roles), @js($rolesFull), @js($permisosByModulo))"
         x-init="init()">

        {{-- Header --}}
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="text-3xl font-extrabold text-white">Gestión de Usuarios y Roles</div>
                <div class="mt-1 text-gray-400 text-sm">Administra usuarios, roles y permisos</div>
            </div>

            <div class="flex items-center gap-3">
                <template x-if="activeTab === 'usuarios'">
                    <button type="button"
                            class="px-5 py-3 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold inline-flex items-center gap-2"
                            @click="openCreateUser()">
                        ＋ Agregar Usuario
                    </button>
                </template>

                <template x-if="activeTab === 'roles'">
                    <button type="button"
                            class="px-5 py-3 rounded-2xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold inline-flex items-center gap-2"
                            @click="openCreateRole()">
                        ＋ Agregar Rol
                    </button>
                </template>
            </div>
        </div>

        {{-- Alerts bonitas (flash) --}}
        <div class="space-y-3">
            @if($success)
                <div class="rounded-3xl border border-green-700 bg-green-600/10 px-5 py-4 text-green-200">
                    <div class="font-extrabold">✅ Listo</div>
                    <div class="text-sm mt-1">{{ $success }}</div>
                </div>
            @endif

            @if($error)
                <div class="rounded-3xl border border-red-700 bg-red-600/10 px-5 py-4 text-red-200">
                    <div class="font-extrabold">⚠️ Atención</div>
                    <div class="text-sm mt-1">{{ $error }}</div>
                </div>
            @endif
        </div>

        {{-- Tabs --}}
        <div class="border-b border-slate-800">
            <div class="flex flex-wrap gap-6 text-gray-300">
                <a href="{{ route('usuarios.index', ['tab' => 'usuarios']) }}"
                   class="py-4 inline-flex items-center gap-2 font-semibold
                          {{ $tab === 'usuarios' ? 'text-blue-400 border-b-2 border-blue-500' : 'hover:text-white' }}">
                    👤 <span>Usuarios</span>
                </a>

                <a href="{{ route('usuarios.index', ['tab' => 'roles']) }}"
                   class="py-4 inline-flex items-center gap-2 font-semibold
                          {{ $tab === 'roles' ? 'text-purple-400 border-b-2 border-purple-500' : 'hover:text-white' }}">
                    🛡️ <span>Roles y permisos</span>
                </a>
            </div>
        </div>

        {{-- =========================
             TAB: USUARIOS
        ========================== --}}
        <div x-show="activeTab === 'usuarios'" style="display:none;" class="space-y-6">

            {{-- Toolbar --}}
            <div class="flex flex-col lg:flex-row lg:items-center gap-3 justify-between">
                <div class="flex-1">
                    <div class="relative">
                        <input type="text"
                               x-model="search"
                               class="w-full px-4 py-4 rounded-2xl bg-slate-900 border border-slate-800 text-white placeholder-gray-500 focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                               placeholder="Buscar por nombre, usuario o correo...">
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500">⌕</div>
                    </div>
                </div>
            </div>

            {{-- Tabla --}}
            <div class="rounded-3xl border border-slate-800 bg-slate-900/60 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-900/80 border-b border-slate-800">
                            <tr class="text-left text-gray-300">
                                <th class="px-5 py-4 text-xs uppercase tracking-wider">Usuario</th>
                                <th class="px-5 py-4 text-xs uppercase tracking-wider">Correo</th>
                                <th class="px-5 py-4 text-xs uppercase tracking-wider">Rol</th>
                                <th class="px-5 py-4 text-xs uppercase tracking-wider">Estado</th>
                                <th class="px-5 py-4 text-xs uppercase tracking-wider text-right">Acciones</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-800">
                            <template x-for="u in filteredUsers()" :key="u.id">
                                <tr class="hover:bg-slate-900/60">
                                    <td class="px-5 py-4">
                                        <div class="text-white font-extrabold" x-text="u.name ?? '—'"></div>
                                        <div class="text-xs text-gray-400" x-text="u.username ? '@'+u.username : ''"></div>
                                    </td>

                                    <td class="px-5 py-4 text-gray-200" x-text="u.email ?? '—'"></td>

                                    <td class="px-5 py-4">
                                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold border bg-slate-800/40 border-slate-700 text-gray-200">
                                            <span class="w-2 h-2 rounded-full"
                                                  :style="`background:${u.role_color ?? '#60a5fa'}`"></span>
                                            <span x-text="u.role_name ?? 'Sin rol'"></span>
                                        </span>
                                    </td>

                                    <td class="px-5 py-4">
                                        <span class="px-3 py-1 rounded-full text-xs font-extrabold border"
                                              :class="u.is_active ? 'bg-green-600/10 border-green-600/30 text-green-300' : 'bg-red-600/10 border-red-600/30 text-red-300'"
                                              x-text="u.is_active ? 'Activo' : 'Inactivo'"></span>
                                    </td>

                                    <td class="px-5 py-4 text-right">
                                        <div class="inline-flex items-center gap-2">
                                            <button type="button"
                                                    class="w-10 h-10 rounded-2xl bg-slate-950 border border-slate-800 text-gray-300 hover:text-white hover:bg-slate-900"
                                                    title="Ver"
                                                    @click="openViewUser(u)">
                                                👁
                                            </button>

                                            <button type="button"
                                                    class="w-10 h-10 rounded-2xl bg-slate-950 border border-slate-800 text-gray-300 hover:text-white hover:bg-slate-900"
                                                    title="Editar"
                                                    @click="openEditUser(u)">
                                                ✏
                                            </button>

                                            <button type="button"
                                                    class="w-10 h-10 rounded-2xl bg-slate-950 border border-slate-800 text-gray-300 hover:text-white hover:bg-slate-900"
                                                    title="Eliminar"
                                                    @click="openDeleteUser(u)">
                                                🗑
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>

                            <tr x-show="filteredUsers().length === 0" style="display:none;">
                                <td colspan="5" class="px-5 py-10 text-center text-gray-400">
                                    No hay usuarios para mostrar.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- =========================
             TAB: ROLES
        ========================== --}}
        <div x-show="activeTab === 'roles'" style="display:none;" class="space-y-6">

            <div class="rounded-3xl border border-slate-800 bg-slate-900/60 overflow-hidden">
                <div class="p-6 border-b border-slate-800 bg-slate-900/80 flex items-center justify-between">
                    <div>
                        <div class="text-white font-extrabold text-lg">Roles configurados</div>
                        <div class="text-gray-400 text-sm">Crea roles y asigna permisos por módulo</div>
                    </div>
                </div>

                <div class="p-6 space-y-4">
                    <template x-for="r in rolesFull" :key="r.id">
                        <div class="rounded-2xl border border-slate-800 bg-slate-950/40 p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-2xl border border-slate-800 bg-slate-950 flex items-center justify-center"
                                             :style="`box-shadow: 0 0 0 2px ${r.color ?? '#a855f7'}33 inset`">
                                            <span class="w-3 h-3 rounded-full"
                                                  :style="`background:${r.color ?? '#a855f7'}`"></span>
                                        </div>
                                        <div>
                                            <div class="text-white font-extrabold" x-text="r.name"></div>
                                            <div class="text-gray-400 text-xs" x-text="(r.users_count ?? 0) + ' usuario(s)'"></div>
                                        </div>
                                    </div>

                                    <div class="mt-4 flex flex-wrap gap-2">
                                        <template x-for="p in (r.permisos ?? [])" :key="p.id">
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold border bg-slate-900/50 border-slate-800 text-gray-200">
                                                <span class="text-purple-300" x-text="p.modulo"></span>
                                                <span class="text-gray-400">:</span>
                                                <span x-text="p.tipo"></span>
                                            </span>
                                        </template>

                                        <span x-show="(r.permisos ?? []).length === 0"
                                              style="display:none;"
                                              class="px-3 py-1 rounded-full text-xs font-semibold border bg-slate-900/50 border-slate-800 text-gray-400">
                                            Sin permisos
                                        </span>
                                    </div>
                                </div>

                                <div class="inline-flex items-center gap-2">
                                    <button type="button"
                                            class="w-10 h-10 rounded-2xl bg-slate-950 border border-slate-800 text-gray-300 hover:text-white hover:bg-slate-900"
                                            title="Editar"
                                            @click="openEditRole(r)">
                                        ✏
                                    </button>

                                    <button type="button"
                                            class="w-10 h-10 rounded-2xl bg-slate-950 border border-slate-800 text-gray-300 hover:text-white hover:bg-slate-900"
                                            title="Eliminar"
                                            @click="openDeleteRole(r)">
                                        🗑
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>

                    <div x-show="rolesFull.length === 0" style="display:none;" class="text-gray-400 text-center py-10">
                        No hay roles para mostrar.
                    </div>
                </div>
            </div>
        </div>

        {{-- =========================
             MODAL: CREAR USUARIO
        ========================== --}}
        <div x-show="modalUserCreate" style="display:none;" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" @click="closeAll()"></div>

            <form method="POST" action="{{ route('usuarios.store') }}"
                  class="relative w-full max-w-2xl rounded-3xl border border-slate-800 bg-slate-950 overflow-hidden" @click.stop>
                @csrf

                <div class="p-6 border-b border-slate-800 bg-slate-950 flex items-start justify-between gap-4">
                    <div>
                        <div class="text-white text-2xl font-extrabold">Agregar Usuario</div>
                        <div class="text-gray-400 text-sm mt-1">Crea un nuevo usuario del sistema</div>
                    </div>

                    <button type="button"
                            class="w-10 h-10 rounded-2xl bg-slate-900 border border-slate-800 text-gray-300 hover:text-white hover:bg-slate-800 flex items-center justify-center"
                            @click="closeAll()">✕</button>
                </div>

                <div class="p-6 space-y-4">
                    <div>
                        <label class="text-sm text-gray-300">Nombre</label>
                        <input name="name" required
                               class="mt-2 w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-800 text-white focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                               placeholder="Nombre completo">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm text-gray-300">Usuario</label>
                            <input name="username"
                                   class="mt-2 w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-800 text-white focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                                   placeholder="usuario">
                        </div>

                        <div>
                            <label class="text-sm text-gray-300">Email</label>
                            <input name="email" type="email" required
                                   class="mt-2 w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-800 text-white focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                                   placeholder="correo@dominio.com">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm text-gray-300">Rol</label>
                            <select name="role_id"
                                    class="mt-2 w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-800 text-white focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                                <option value="">Seleccione un rol</option>
                                @foreach($roles as $r)
                                    <option value="{{ $r['id'] }}">{{ $r['name'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="text-sm text-gray-300">Estado</label>
                            <select name="is_active"
                                    class="mt-2 w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-800 text-white focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm text-gray-300">Contraseña</label>
                            <input type="password" name="password" required
                                   class="mt-2 w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-800 text-white focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                        </div>

                        <div>
                            <label class="text-sm text-gray-300">Confirmar contraseña</label>
                            <input type="password" name="password_confirmation" required
                                   class="mt-2 w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-800 text-white focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                        </div>
                    </div>
                </div>

                <div class="p-6 border-t border-slate-800 bg-slate-950 flex items-center justify-end gap-3">
                    <button type="button"
                            class="px-5 py-3 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-semibold border border-slate-800"
                            @click="closeAll()">Cancelar</button>

                    <button type="submit"
                            class="px-6 py-3 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold">
                        Guardar Usuario
                    </button>
                </div>
            </form>
        </div>

        {{-- =========================
             MODAL: CREAR ROL
        ========================== --}}
        <div x-show="modalRoleCreate" style="display:none;" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" @click="closeAll()"></div>

            <div class="relative w-full max-w-3xl rounded-3xl border border-slate-800 bg-slate-950 overflow-hidden"
                 style="max-height: calc(100vh - 2rem);"
                 @click.stop>

                <div class="p-6 border-b border-slate-800 bg-purple-700/90 flex items-start justify-between gap-4">
                    <div>
                        <div class="text-white text-2xl font-extrabold">Nuevo Rol</div>
                        <div class="text-purple-100/90 text-sm mt-1">Configura los permisos del rol</div>
                    </div>

                    <button type="button"
                            class="w-10 h-10 rounded-2xl bg-white/10 border border-white/20 text-white hover:bg-white/20 flex items-center justify-center"
                            @click="closeAll()">✕</button>
                </div>

                <form method="POST" action="{{ route('roles.store') }}" class="flex flex-col">
                    @csrf

                    <div class="p-6 space-y-6 overflow-y-auto" style="max-height: calc(100vh - 2rem - 96px - 90px);">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm text-gray-300 font-semibold">Nombre del Rol *</label>
                                <input name="name" required
                                       class="mt-2 w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-800 text-white focus:ring-2 focus:ring-purple-600 focus:border-purple-600"
                                       placeholder="Ej: Supervisor, Operador, Gerente">
                            </div>

                            <div>
                                <label class="text-sm text-gray-300 font-semibold">Color del Rol</label>
                                <select name="color"
                                        class="mt-2 w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-800 text-white focus:ring-2 focus:ring-purple-600 focus:border-purple-600">
                                    <option value="Púrpura">Púrpura</option>
                                    <option value="Azul">Azul</option>
                                    <option value="Verde">Verde</option>
                                    <option value="Rojo">Rojo</option>
                                    <option value="Gris">Gris</option>
                                </select>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="text-white font-extrabold">Permisos del Rol</div>

                            {{-- OJO: aquí usamos los datos de Alpine (siempre llegan), no dependemos del Blade loop --}}
                            <template x-if="Object.keys(permisosByModulo).length === 0">
                                <div class="rounded-2xl border border-slate-800 bg-slate-900/40 p-6 text-center">
                                    <div class="text-gray-200 font-extrabold">No hay permisos cargados</div>
                                    <div class="text-gray-400 text-sm mt-2">
                                        Verifica que tu tabla <span class="text-gray-200 font-semibold">permisos</span> tenga registros.
                                    </div>
                                </div>
                            </template>

                            <template x-for="(items, modulo) in permisosByModulo" :key="modulo">
                                <div class="rounded-2xl border border-slate-800 bg-slate-900/40 p-4">
                                    <div class="flex items-center justify-between gap-3 mb-3">
                                        <div class="text-white font-semibold" x-text="modulo"></div>
                                        <div class="text-xs text-gray-400" x-text="(items?.length ?? 0) + ' permiso(s)'"></div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                        <template x-for="p in items" :key="p.id">
                                            <label class="flex items-center gap-3 px-4 py-3 rounded-2xl border border-slate-800 bg-slate-950 hover:bg-slate-900 cursor-pointer">
                                                <input type="checkbox" name="permisos[]" :value="p.id"
                                                       class="rounded border-slate-700 bg-slate-900 text-purple-500 focus:ring-purple-600">
                                                <div class="flex flex-col">
                                                    <span class="text-gray-200 text-sm font-semibold" x-text="p.tipo"></span>
                                                    <span class="text-gray-500 text-xs" x-text="p.name"></span>
                                                </div>
                                            </label>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="p-6 border-t border-slate-800 bg-slate-950 flex items-center justify-end gap-3">
                        <button type="button"
                                class="px-5 py-3 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-semibold border border-slate-800"
                                @click="closeAll()">Cancelar</button>

                        <button type="submit"
                                class="px-6 py-3 rounded-2xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold">
                            Crear Rol
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- =========================
             MODAL: CONFIRMAR DELETE ROLE
        ========================== --}}
        <div x-show="modalRoleDelete" style="display:none;" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" @click="closeAll()"></div>

            <form method="POST" :action="roleDeleteAction()"
                  class="relative w-full max-w-lg rounded-3xl border border-slate-800 bg-slate-950 overflow-hidden" @click.stop>
                @csrf
                @method('DELETE')

                <div class="p-6 border-b border-slate-800 bg-slate-950 flex items-start justify-between gap-4">
                    <div>
                        <div class="text-white text-2xl font-extrabold">Eliminar Rol</div>
                        <div class="text-gray-400 text-sm mt-1">Esta acción no se puede deshacer</div>
                    </div>

                    <button type="button"
                            class="w-10 h-10 rounded-2xl bg-slate-900 border border-slate-800 text-gray-300 hover:text-white hover:bg-slate-800 flex items-center justify-center"
                            @click="closeAll()">✕</button>
                </div>

                <div class="p-6">
                    <div class="rounded-2xl border border-red-800 bg-red-600/10 p-4 text-red-200">
                        ¿Seguro que quieres eliminar el rol
                        <span class="font-extrabold text-white" x-text="selectedRole?.name ?? ''"></span>?
                    </div>
                </div>

                <div class="p-6 border-t border-slate-800 bg-slate-950 flex items-center justify-end gap-3">
                    <button type="button"
                            class="px-5 py-3 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-semibold border border-slate-800"
                            @click="closeAll()">Cancelar</button>

                    <button type="submit"
                            class="px-6 py-3 rounded-2xl bg-red-600 hover:bg-red-700 text-white font-extrabold">
                        Eliminar
                    </button>
                </div>
            </form>
        </div>

        {{-- =========================
             Alpine
        ========================== --}}
        <script>
            function usuariosPage(tab, users, roles, rolesFull, permisosByModulo) {
                return {
                    activeTab: tab ?? 'usuarios',
                    users: users ?? [],
                    roles: roles ?? [],
                    rolesFull: rolesFull ?? [],
                    permisosByModulo: permisosByModulo ?? {},

                    search: '',

                    // modals
                    modalUserCreate: false,
                    modalRoleCreate: false,
                    modalRoleDelete: false,

                    selectedRole: null,

                    init() {},

                    filteredUsers() {
                        const q = (this.search ?? '').trim().toLowerCase();
                        if (!q) return this.users;
                        return this.users.filter(u => {
                            const name = (u.name ?? '').toLowerCase();
                            const email = (u.email ?? '').toLowerCase();
                            const username = (u.username ?? '').toLowerCase();
                            const role = (u.role_name ?? '').toLowerCase();
                            return name.includes(q) || email.includes(q) || username.includes(q) || role.includes(q);
                        });
                    },

                    closeAll() {
                        this.modalUserCreate = false;
                        this.modalRoleCreate = false;
                        this.modalRoleDelete = false;
                        this.selectedRole = null;
                    },

                    // Users
                    openCreateUser() { this.modalUserCreate = true; },

                    openViewUser(u) { alert('Aquí puedes re-activar tu modal bonito de Ver (si quieres lo re-integro).'); },
                    openEditUser(u) { alert('Aquí puedes re-activar tu modal bonito de Editar (si quieres lo re-integro).'); },
                    openDeleteUser(u) { alert('Aquí puedes re-activar tu modal bonito de Eliminar (si quieres lo re-integro).'); },

                    // Roles
                    openCreateRole() { this.modalRoleCreate = true; },

                    openEditRole(r) {
                        alert('Si quieres, te agrego el modal completo para editar rol (como crear, pero precargado).');
                    },

                    openDeleteRole(r) {
                        this.selectedRole = r;
                        this.modalRoleDelete = true;
                    },

                    roleDeleteAction() {
                        if (!this.selectedRole?.id) return '#';
                        return `{{ url('/roles') }}/${this.selectedRole.id}`;
                    }
                }
            }
        </script>
    </div>
</x-app-layout>
