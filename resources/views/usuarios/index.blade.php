{{-- resources/views/usuarios/index.blade.php --}}
<x-app-layout>
    @php
        $tab = $tab ?? request('tab', 'usuarios');

        $users = $users ?? [];
        $roles = $roles ?? [];

        // Para roles/permiso (si el controller ya los manda)
        $rolesFull = $rolesFull ?? [];
        $permisosByModulo = $permisosByModulo ?? [];
        $routes = [
            // roles
            'rolesStore' => route('roles.store'),
            'rolesUpdate' => url('/roles'), // luego se usa /{id}
            'rolesDestroy' => url('/roles'), // luego se usa /{id}

            // usuarios
            'usersStore' => route('usuarios.store'),
            'usersUpdate' => url('/usuarios'), // luego se usa /{id}
            'usersDestroy' => url('/usuarios'), // luego se usa /{id}
        ];

        $csrf = csrf_token();
    @endphp

    <div class="p-6 space-y-6" x-data="usuariosPage(@js($tab), @js($users), @js($roles), @js($rolesFull), @js($permisosByModulo), @js($routes), @js($csrf))" x-init="init()">

        {{-- Header --}}
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="text-3xl font-extrabold text-white">Gestión de Usuarios y Roles</div>
                <div class="mt-1 text-gray-400 text-sm">
                    <template x-if="activeTab === 'usuarios'">
                        <span x-text="`Total: ${users.length} usuarios registrados`"></span>
                    </template>
                    <template x-if="activeTab === 'roles'">
                        <span x-text="`Total: ${rolesFull.length} roles configurados`"></span>
                    </template>
                </div>
            </div>

            {{-- Botón top-right --}}
            <div class="flex items-center gap-3">
                <template x-if="activeTab === 'usuarios'">
                    <button type="button"
                        class="px-6 py-3 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold inline-flex items-center gap-2"
                        @click="openCreate()">
                        <span class="text-lg">👤</span>
                        <span>Agregar Usuario</span>
                    </button>
                </template>

                <template x-if="activeTab === 'roles'">
                    <button type="button"
                        class="px-6 py-3 rounded-2xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold inline-flex items-center gap-2"
                        @click="openRoleCreate()">
                        <span class="text-lg">🛡️</span>
                        <span>Agregar Rol</span>
                    </button>
                </template>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="border-b border-slate-800">
            <div class="flex flex-wrap gap-6 text-gray-300">
                <a href="{{ route('usuarios.index', ['tab' => 'usuarios']) }}"
                    class="py-4 inline-flex items-center gap-2 font-semibold
                          {{ $tab === 'usuarios' ? 'text-blue-400 border-b-2 border-blue-500' : 'hover:text-white' }}">
                    👥 <span>Usuarios</span>
                </a>

                <a href="{{ route('usuarios.index', ['tab' => 'roles']) }}"
                    class="py-4 inline-flex items-center gap-2 font-semibold
                          {{ $tab === 'roles' ? 'text-purple-300 border-b-2 border-purple-500' : 'hover:text-white' }}">
                    🛡️ <span>Roles y Permisos</span>
                </a>
            </div>
        </div>

        {{-- =========================
             TAB: USUARIOS (CARDS) - SIN BUSCADOR
        ========================== --}}
        <div x-show="activeTab === 'usuarios'" style="display:none;" class="space-y-6">

            {{-- Grid cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                <template x-for="u in users" :key="u.id">
                    <div class="rounded-3xl border border-slate-800 bg-slate-900/60 overflow-hidden">
                        <div class="p-7 flex flex-col items-center text-center">
                            <div class="w-16 h-16 rounded-full flex items-center justify-center text-2xl font-extrabold border"
                                :style="`background: ${roleBg(u)}; border-color: ${roleBorder(u)}`">
                                <span class="text-white">👤</span>
                            </div>

                            <div class="mt-4 text-white font-extrabold" x-text="u.name ?? '—'"></div>
                            <div class="mt-1 text-xs text-gray-400" x-text="u.email ?? '—'"></div>

                            <div class="mt-3">
                                <span
                                    class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-extrabold"
                                    :style="`background:${rolePillBg(u)}; color:${rolePillText(u)}`">
                                    <span x-text="u.role_name ?? 'Sin rol'"></span>
                                </span>
                            </div>

                            <div class="mt-5 flex items-center gap-5 text-gray-300">
                                <button type="button" class="hover:text-white" title="Ver"
                                    @click="openView(u)">👁</button>
                                <button type="button" class="hover:text-white" title="Editar"
                                    @click="openEdit(u)">✏</button>
                                {{-- <button type="button" class="hover:text-white" title="Eliminar"
                                    @click="openDelete(u)">🗑</button> --}}
                            </div>
                        </div>
                    </div>
                </template>

                <div x-show="users.length === 0" style="display:none;"
                    class="col-span-full rounded-3xl border border-slate-800 bg-slate-900/60 p-10 text-center text-gray-400">
                    No hay usuarios para mostrar.
                </div>
            </div>
        </div>

        {{-- =========================
             TAB: ROLES Y PERMISOS (DISEÑO COMPLETO)
        ========================== --}}
        <div x-show="activeTab === 'roles'" style="display:none;" class="space-y-6">

            {{-- Roles list --}}
            <div class="space-y-4">
                <template x-for="r in rolesFull" :key="r.id">
                    <div class="rounded-3xl border border-slate-800 bg-slate-900/60 overflow-hidden">
                        <div class="p-6 flex flex-col gap-4">
                            {{-- top row --}}
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-2xl border border-slate-700 bg-slate-950 flex items-center justify-center"
                                        :style="`box-shadow: inset 0 0 0 2px ${roleColorHex(r)}33`">
                                        <span class="text-white">🛡️</span>
                                    </div>

                                    <div>
                                        <div class="text-white font-extrabold flex items-center gap-3">
                                            <span x-text="r.name"></span>
                                            <span class="text-xs text-gray-400"
                                                x-text="`${r.users_count ?? 0} usuario(s)`"></span>
                                        </div>
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            <template x-for="chip in roleChips(r).slice(0, 10)" :key="chip.key">
                                                <span class="px-3 py-1 rounded-xl text-xs font-extrabold"
                                                    :class="chip.class" x-text="chip.label"></span>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <div class="text-xs text-gray-500" x-show="!!r.protegido">Protegido</div>

                                    <button type="button"
                                        class="w-10 h-10 rounded-2xl bg-slate-950 border border-slate-800 text-gray-300 hover:text-white hover:bg-slate-900"
                                        title="Editar" @click="openRoleEdit(r)">
                                        ✏
                                    </button>

                                    <button type="button"
                                        class="w-10 h-10 rounded-2xl bg-slate-950 border border-slate-800 text-gray-300 hover:text-white hover:bg-slate-900"
                                        title="Eliminar" @click="openRoleDelete(r)">
                                        🗑
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <div x-show="rolesFull.length === 0" style="display:none;"
                    class="rounded-3xl border border-slate-800 bg-slate-900/60 p-10 text-center text-gray-400">
                    No hay roles configurados.
                </div>
            </div>
        </div>

        {{-- =========================
             MODAL: NUEVO USUARIO (MORADO)
        ========================== --}}
        <div x-show="modalCreate" style="display:none;" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" @click="closeAll()"></div>

            <div class="relative w-full max-w-xl rounded-3xl border border-slate-800 bg-slate-950 overflow-hidden"
                @click.stop>
                <div class="p-6 bg-gradient-to-r from-purple-700 to-fuchsia-600 flex items-start justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <div
                            class="w-10 h-10 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center text-white">
                            👤
                        </div>
                        <div>
                            <div class="text-white text-2xl font-extrabold">Nuevo Usuario</div>
                            <div class="text-white/80 text-sm mt-1">Complete la información del usuario</div>
                        </div>
                    </div>

                    <button type="button"
                        class="w-10 h-10 rounded-2xl bg-white/10 border border-white/20 text-white/90 hover:text-white hover:bg-white/15 flex items-center justify-center"
                        @click="closeAll()">
                        ✕
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    <div>
                        <label class="text-sm text-gray-300 font-semibold">Nombre Completo <span
                                class="text-red-400">*</span></label>
                        <input x-model="createForm.name"
                            class="mt-2 w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-800 text-white placeholder-gray-500 focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                            placeholder="Ej: Juan Pérez García">
                    </div>

                    <div>
                        <label class="text-sm text-gray-300 font-semibold">Usuario <span
                                class="text-red-400">*</span></label>
                        <input x-model="createForm.username"
                            class="mt-2 w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-800 text-white placeholder-gray-500 focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                            placeholder="Ej: jperez">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm text-gray-300 font-semibold">Contraseña <span
                                    class="text-red-400">*</span></label>
                            <input type="password" x-model="createForm.password"
                                class="mt-2 w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-800 text-white placeholder-gray-500 focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                placeholder="Ingrese contraseña">
                        </div>

                        <div>
                            <label class="text-sm text-gray-300 font-semibold">Confirmar Contraseña <span
                                    class="text-red-400">*</span></label>
                            <input type="password" x-model="createForm.password_confirmation"
                                class="mt-2 w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-800 text-white placeholder-gray-500 focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                placeholder="Confirme contraseña">
                        </div>
                    </div>

                    <div>
                        <label class="text-sm text-gray-300 font-semibold">Email</label>
                        <input x-model="createForm.email"
                            class="mt-2 w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-800 text-white placeholder-gray-500 focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                            placeholder="correo@dominio.com">
                    </div>

                    <div>
                        <label class="text-sm text-gray-300 font-semibold">Rol <span
                                class="text-red-400">*</span></label>
                        <select x-model="createForm.role_id"
                            class="mt-2 w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-800 text-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            <option value="">Seleccione un rol</option>
                            <template x-for="rr in roles" :key="rr.id">
                                <option :value="rr.id" x-text="rr.name"></option>
                            </template>
                        </select>
                    </div>

                    <input type="hidden" x-model="createForm.is_active">
                </div>

                <div class="p-6 border-t border-slate-800 bg-slate-950 flex items-center justify-end gap-3">
                    <button type="button"
                        class="px-5 py-3 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-semibold border border-slate-800"
                        @click="closeAll()">
                        Cancelar
                    </button>

                    <button type="button"
                        class="px-6 py-3 rounded-2xl bg-gradient-to-r from-purple-600 to-fuchsia-600 hover:from-purple-500 hover:to-fuchsia-500 text-white font-extrabold inline-flex items-center gap-2"
                        @click="submitCreate()">
                        <span>＋</span>
                        <span>Crear Usuario</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- =========================
             MODAL: NUEVO ROL (MORADO + MATRIZ)
        ========================== --}}
        <div x-show="modalRoleCreate" style="display:none;"
            class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" @click="closeAll()"></div>

            <div class="relative w-full max-w-4xl rounded-3xl border border-slate-800 bg-slate-950 overflow-hidden"
                @click.stop>
                {{-- header --}}
                <div
                    class="p-6 bg-gradient-to-r from-purple-700 to-fuchsia-600 flex items-start justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <div
                            class="w-10 h-10 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center text-white">
                            🛡️
                        </div>
                        <div>
                            <div class="text-white text-2xl font-extrabold"
                                x-text="roleMode === 'create' ? 'Nuevo Rol' : 'Editar Rol'"></div>
                            <div class="text-white/80 text-sm mt-1">Configure los permisos del rol</div>
                        </div>
                    </div>

                    <button type="button"
                        class="w-10 h-10 rounded-2xl bg-white/10 border border-white/20 text-white/90 hover:text-white hover:bg-white/15 flex items-center justify-center"
                        @click="closeAll()">
                        ✕
                    </button>
                </div>

                {{-- body --}}
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="text-sm text-gray-300 font-semibold">Nombre del Rol <span
                                    class="text-red-400">*</span></label>
                            <input x-model="roleForm.name"
                                class="mt-2 w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-800 text-white placeholder-gray-500 focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                placeholder="Ej: Supervisor, Operador, Gerente">
                        </div>

                        <div>
                            <label class="text-sm text-gray-300 font-semibold">Color del Rol</label>
                            <select x-model="roleForm.color"
                                class="mt-2 w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-800 text-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                <template x-for="c in roleColors" :key="c.value">
                                    <option :value="c.value" x-text="c.label"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    {{-- Permisos de Contenedores --}}
                    <div class="space-y-3">
                        <div class="text-white font-extrabold text-lg">Permisos de Contenedores</div>

                        {{-- Crear contenedores --}}
                        <label
                            class="flex items-center justify-between gap-3 rounded-2xl border border-slate-800 bg-slate-900/60 p-5 cursor-pointer">
                            <div class="flex items-center gap-3">
                                <input type="checkbox"
                                    class="w-5 h-5 rounded bg-slate-950 border-slate-700 text-purple-600 focus:ring-purple-500"
                                    x-model="roleMatrix.create_contenedores">
                                <div>
                                    <div class="text-white font-extrabold">Crear Contenedores</div>
                                </div>
                            </div>

                            <div
                                class="w-9 h-9 rounded-xl bg-slate-950 border border-slate-800 flex items-center justify-center text-blue-300 font-extrabold">
                                +
                            </div>
                        </label>

                        {{-- Accordion: pestañas de contenedores --}}
                        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 overflow-hidden">
                            <button type="button" class="w-full p-5 flex items-center justify-between gap-4"
                                @click="roleAccordion = !roleAccordion">
                                <div class="flex items-start gap-3">
                                    <div
                                        class="w-10 h-10 rounded-2xl bg-slate-950 border border-slate-800 flex items-center justify-center text-purple-300">
                                        ⬣
                                    </div>
                                    <div class="text-left">
                                        <div class="text-white font-extrabold">Pestañas de Contenedores</div>
                                        <div class="text-gray-400 text-xs mt-1">Permisos de lectura/escritura por
                                            pestaña</div>
                                    </div>
                                </div>

                                <div class="text-gray-300">
                                    <span x-show="!roleAccordion">▾</span>
                                    <span x-show="roleAccordion" style="display:none;">▴</span>
                                </div>
                            </button>

                            <div x-show="roleAccordion" style="display:none;" class="border-t border-slate-800 p-5">
                                {{-- matrix --}}
                                <div class="rounded-2xl border border-slate-800 bg-slate-950/40 overflow-hidden">
                                    <div
                                        class="grid grid-cols-5 gap-2 p-4 border-b border-slate-800 text-xs text-gray-400 font-bold">
                                        <div class="col-span-1"> </div>
                                        <div class="text-center">Ninguno</div>
                                        <div class="text-center">Ver</div>
                                        <div class="text-center">Editar</div>
                                        <div class="text-center">Total</div>
                                    </div>

                                    <template x-for="row in roleTabs" :key="row.key">
                                        <div
                                            class="grid grid-cols-5 gap-2 p-4 border-b border-slate-800 last:border-b-0 items-center">
                                            <div class="col-span-1 flex items-center gap-3 text-white font-semibold">
                                                <span class="text-gray-300" x-text="row.icon"></span>
                                                <span x-text="row.label"></span>
                                            </div>

                                            <template x-for="opt in ['none','ver','editar','total']"
                                                :key="opt">
                                                <button type="button"
                                                    class="h-10 rounded-xl border text-sm font-semibold"
                                                    :class="matrixBtnClass(row.key, opt)"
                                                    @click="setMatrix(row.key, opt)">
                                                    <span
                                                        x-text="opt === 'none' ? '⦸' : (opt === 'ver' ? '👁' : (opt === 'editar' ? '✏' : '✔'))"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Permisos módulos del sistema --}}
                    <div class="space-y-3">
                        <div class="text-white font-extrabold text-lg">Permisos de Módulos del Sistema</div>

                        <template x-for="m in systemModules" :key="m.key">
                            <label
                                class="flex items-center gap-3 rounded-2xl border border-slate-800 bg-slate-900/60 p-5 cursor-pointer">
                                <input type="checkbox"
                                    class="w-5 h-5 rounded bg-slate-950 border-slate-700 text-purple-600 focus:ring-purple-500"
                                    x-model="roleMatrix.system[m.key]">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-2xl bg-slate-950 border border-slate-800 flex items-center justify-center"
                                        :class="m.iconClass">
                                        <span x-text="m.icon"></span>
                                    </div>
                                    <div>
                                        <div class="text-white font-extrabold" x-text="m.label"></div>
                                    </div>
                                </div>
                            </label>
                        </template>
                    </div>
                </div>

                {{-- footer --}}
                <div class="p-6 border-t border-slate-800 bg-slate-950 flex items-center justify-end gap-3">
                    <button type="button"
                        class="px-5 py-3 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-semibold border border-slate-800"
                        @click="closeAll()">
                        Cancelar
                    </button>

                    <button type="button"
                        class="px-6 py-3 rounded-2xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold inline-flex items-center gap-2"
                        @click="submitRole()">
                        <span>🛡️</span>
                        <span x-text="roleMode === 'create' ? 'Crear Rol' : 'Guardar'"></span>
                    </button>
                </div>
            </div>
        </div>

        {{-- =========================
             MODAL: VER / EDITAR / ELIMINAR USUARIO (igual que tu base)
        ========================== --}}
        <div x-show="modalView" style="display:none;"
            class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" @click="closeAll()"></div>

            <div class="relative w-full max-w-xl rounded-3xl border border-slate-800 bg-slate-950 overflow-hidden"
                @click.stop>
                <div class="p-6 border-b border-slate-800 bg-slate-950 flex items-start justify-between gap-4">
                    <div>
                        <div class="text-white text-2xl font-extrabold">Detalle de Usuario</div>
                        <div class="text-gray-400 text-sm mt-1">Información del usuario seleccionado</div>
                    </div>

                    <button type="button"
                        class="w-10 h-10 rounded-2xl bg-slate-900 border border-slate-800 text-gray-300 hover:text-white hover:bg-slate-800 flex items-center justify-center"
                        @click="closeAll()">
                        ✕
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    <div class="rounded-2xl border border-slate-800 bg-slate-900/40 p-4 text-gray-200">
                        <div class="text-sm text-gray-400">Nombre</div>
                        <div class="font-extrabold text-white" x-text="selectedUser?.name ?? '-'"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="rounded-2xl border border-slate-800 bg-slate-900/40 p-4 text-gray-200">
                            <div class="text-sm text-gray-400">Usuario</div>
                            <div class="font-semibold text-white"
                                x-text="selectedUser?.username ? '@'+selectedUser.username : '-'"></div>
                        </div>

                        <div class="rounded-2xl border border-slate-800 bg-slate-900/40 p-4 text-gray-200">
                            <div class="text-sm text-gray-400">Email</div>
                            <div class="font-semibold text-white" x-text="selectedUser?.email ?? '-'"></div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="rounded-2xl border border-slate-800 bg-slate-900/40 p-4 text-gray-200">
                            <div class="text-sm text-gray-400">Rol</div>
                            <div class="font-semibold text-white" x-text="selectedUser?.role_name ?? 'Sin rol'"></div>
                        </div>

                        <div class="rounded-2xl border border-slate-800 bg-slate-900/40 p-4 text-gray-200">
                            <div class="text-sm text-gray-400">Estado</div>
                            <div class="font-semibold"
                                :class="selectedUser?.is_active ? 'text-green-400' : 'text-red-400'"
                                x-text="selectedUser?.is_active ? 'Activo' : 'Inactivo'"></div>
                        </div>
                    </div>
                </div>

                <div class="p-6 border-t border-slate-800 bg-slate-950 flex items-center justify-end">
                    <button type="button"
                        class="px-5 py-3 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-semibold border border-slate-800"
                        @click="closeAll()">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>

        <div x-show="modalEdit" style="display:none;"
            class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" @click="closeAll()"></div>

            <div class="relative w-full max-w-2xl rounded-3xl border border-slate-800 bg-slate-950 overflow-hidden"
                @click.stop>
                <div class="p-6 border-b border-slate-800 bg-slate-950 flex items-start justify-between gap-4">
                    <div>
                        <div class="text-white text-2xl font-extrabold">Editar Usuario</div>
                        <div class="text-gray-400 text-sm mt-1">Actualiza los datos del usuario</div>
                    </div>

                    <button type="button"
                        class="w-10 h-10 rounded-2xl bg-slate-900 border border-slate-800 text-gray-300 hover:text-white hover:bg-slate-800 flex items-center justify-center"
                        @click="closeAll()">
                        ✕
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    <div>
                        <label class="text-sm text-gray-300">Nombre</label>
                        <input x-model="editForm.name"
                            class="mt-2 w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-800 text-white focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm text-gray-300">Usuario</label>
                            <input x-model="editForm.username"
                                class="mt-2 w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-800 text-white focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                        </div>

                        <div>
                            <label class="text-sm text-gray-300">Email</label>
                            <input x-model="editForm.email"
                                class="mt-2 w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-800 text-white focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm text-gray-300">Rol</label>
                            <select x-model="editForm.role_id"
                                class="mt-2 w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-800 text-white focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                                <option value="">Seleccione un rol</option>
                                <template x-for="rr in roles" :key="rr.id">
                                    <option :value="rr.id" x-text="rr.name"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="text-sm text-gray-300">Estado</label>
                            <select x-model="editForm.is_active"
                                class="mt-2 w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-800 text-white focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm text-gray-300">Nueva contraseña (opcional)</label>
                            <input type="password" x-model="editForm.password"
                                class="mt-2 w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-800 text-white focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                        </div>

                        <div>
                            <label class="text-sm text-gray-300">Confirmar contraseña</label>
                            <input type="password" x-model="editForm.password_confirmation"
                                class="mt-2 w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-800 text-white focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                        </div>
                    </div>
                </div>

                <div class="p-6 border-t border-slate-800 bg-slate-950 flex items-center justify-end gap-3">
                    <button type="button"
                        class="px-5 py-3 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-semibold border border-slate-800"
                        @click="closeAll()">
                        Cancelar
                    </button>

                    <button type="button"
                        class="px-6 py-3 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold"
                        @click="submitEdit()">
                        Guardar Cambios
                    </button>
                </div>
            </div>
        </div>

        <div x-show="modalDelete" style="display:none;"
            class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" @click="closeAll()"></div>

            <div class="relative w-full max-w-lg rounded-3xl border border-slate-800 bg-slate-950 overflow-hidden"
                @click.stop>
                <div class="p-6 border-b border-slate-800 bg-slate-950 flex items-start justify-between gap-4">
                    <div>
                        <div class="text-white text-2xl font-extrabold">Eliminar Usuario</div>
                        <div class="text-gray-400 text-sm mt-1">Esta acción no se puede deshacer</div>
                    </div>

                    <button type="button"
                        class="w-10 h-10 rounded-2xl bg-slate-900 border border-slate-800 text-gray-300 hover:text-white hover:bg-slate-800 flex items-center justify-center"
                        @click="closeAll()">
                        ✕
                    </button>
                </div>

                <div class="p-6">
                    <div class="rounded-2xl border border-red-800 bg-red-600/10 p-4 text-red-200">
                        ¿Seguro que quieres eliminar a
                        <span class="font-extrabold text-white" x-text="selectedUser?.name ?? ''"></span>?
                    </div>
                </div>

                <div class="p-6 border-t border-slate-800 bg-slate-950 flex items-center justify-end gap-3">
                    <button type="button"
                        class="px-5 py-3 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-semibold border border-slate-800"
                        @click="closeAll()">
                        Cancelar
                    </button>

                    <button type="button"
                        class="px-6 py-3 rounded-2xl bg-red-600 hover:bg-red-700 text-white font-extrabold"
                        @click="confirmDelete()">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>

        {{-- =========================
             Alpine logic
        ========================== --}}
        <script>
            function usuariosPage(tabFromServer, usersFromServer, rolesFromServer, rolesFullFromServer, permisosByModuloFromServer, routesFromServer, csrfFromServer) {
                return {
                    // tabs
                    activeTab: tabFromServer ?? 'usuarios',

                    // data
                    users: usersFromServer ?? [],
                    roles: rolesFromServer ?? [],
                    rolesFull: rolesFullFromServer ?? [],
                    permisosByModulo: permisosByModuloFromServer ?? {},
                    routes: routesFromServer ?? {},
                    csrf: csrfFromServer ?? '',

                    // users modals
                    modalCreate: false,
                    modalView: false,
                    modalEdit: false,
                    modalDelete: false,
                    selectedUser: null,

                    // roles modals
                    modalRoleCreate: false,
                    modalRoleDelete: false,
                    roleMode: 'create', // create | edit
                    selectedRole: null,

                    // accordion
                    roleAccordion: false,

                    // role colors
                    roleColors: [{
                            value: 'Púrpura',
                            label: 'Púrpura'
                        },
                        {
                            value: 'Azul',
                            label: 'Azul'
                        },
                        {
                            value: 'Verde',
                            label: 'Verde'
                        },
                        {
                            value: 'Rojo',
                            label: 'Rojo'
                        },
                        {
                            value: 'Naranja',
                            label: 'Naranja'
                        },
                        {
                            value: 'Gris',
                            label: 'Gris'
                        },
                    ],

                    // matrix tabs (para el acordeón)
                    roleTabs: [{
                            key: 'registro',
                            icon: '⬣',
                            label: 'Registro'
                        },
                        {
                            key: 'liberacion',
                            icon: '🔓',
                            label: 'Liberación'
                        },
                        {
                            key: 'docs',
                            icon: '✈️',
                            label: 'Envío de Docs'
                        },
                        {
                            key: 'cotizacion',
                            icon: '$',
                            label: 'Cotización Agencia'
                        },
                        {
                            key: 'despacho',
                            icon: '🚚',
                            label: 'Despacho'
                        },
                        {
                            key: 'gastos',
                            icon: '🧾',
                            label: 'Gastos'
                        },
                    ],

                    // módulos del sistema (checks)
                    systemModules: [{
                            key: 'reportes',
                            label: 'Administrar Reportes',
                            icon: '🧾',
                            iconClass: 'text-orange-300'
                        },
                        {
                            key: 'usuarios',
                            label: 'Administrar Usuarios',
                            icon: '👥',
                            iconClass: 'text-purple-300'
                        },
                        {
                            key: 'actividad',
                            label: 'Administrar Actividad',
                            icon: '∿',
                            iconClass: 'text-cyan-300'
                        },
                    ],

                    // forms
                    createForm: {
                        name: '',
                        email: '',
                        username: '',
                        role_id: '',
                        password: '',
                        password_confirmation: '',
                        is_active: 1,
                    },

                    editForm: {
                        name: '',
                        email: '',
                        username: '',
                        role_id: '',
                        password: '',
                        password_confirmation: '',
                        is_active: 1,
                    },

                    roleForm: {
                        id: null,
                        name: '',
                        color: 'Púrpura',
                    },

                    // estado de permisos (solo UI)
                    roleMatrix: {
                        create_contenedores: false,
                        tabs: {
                            registro: 'none',
                            liberacion: 'none',
                            docs: 'none',
                            cotizacion: 'none',
                            despacho: 'none',
                            gastos: 'none',
                        },
                        system: {
                            reportes: false,
                            usuarios: false,
                            actividad: false,
                        },
                    },

                    init() {
                        // nada por ahora
                    },

                    // ====== helper para errores Laravel 422 ======
                    formatLaravelErrors(errors) {
                        try {
                            if (!errors || typeof errors !== 'object') return null;
                            const lines = [];
                            Object.keys(errors).forEach(k => {
                                const arr = errors[k];
                                if (Array.isArray(arr)) arr.forEach(msg => lines.push(`• ${msg}`));
                            });
                            return lines.length ? lines.join('\n') : null;
                        } catch (e) {
                            return null;
                        }
                    },

                    // ====== colors (usuarios cards) ======
                    roleBg(u) {
                        const c = (u?.role_color ?? '').toLowerCase();
                        if (c.includes('púrpura') || c.includes('purpura') || c.includes('purple'))
                            return 'rgba(168,85,247,0.22)';
                        if (c.includes('verde') || c.includes('green')) return 'rgba(34,197,94,0.22)';
                        if (c.includes('azul') || c.includes('blue')) return 'rgba(59,130,246,0.22)';
                        return 'rgba(59,130,246,0.22)';
                    },
                    roleBorder(u) {
                        const c = (u?.role_color ?? '').toLowerCase();
                        if (c.includes('púrpura') || c.includes('purpura') || c.includes('purple'))
                            return 'rgba(168,85,247,0.55)';
                        if (c.includes('verde') || c.includes('green')) return 'rgba(34,197,94,0.55)';
                        if (c.includes('azul') || c.includes('blue')) return 'rgba(59,130,246,0.55)';
                        return 'rgba(59,130,246,0.55)';
                    },
                    rolePillBg(u) {
                        const c = (u?.role_color ?? '').toLowerCase();
                        if (c.includes('púrpura') || c.includes('purpura') || c.includes('purple'))
                            return 'rgba(168,85,247,0.95)';
                        if (c.includes('verde') || c.includes('green')) return 'rgba(34,197,94,0.95)';
                        if (c.includes('azul') || c.includes('blue')) return 'rgba(59,130,246,0.95)';
                        return 'rgba(59,130,246,0.95)';
                    },
                    rolePillText(u) {
                        return '#ffffff';
                    },

                    // ====== roles helper (hex-ish for shadows) ======
                    roleColorHex(r) {
                        const c = (r?.color ?? '').toLowerCase();
                        if (c.includes('púrpura') || c.includes('purpura') || c.includes('purple')) return '#a855f7';
                        if (c.includes('verde') || c.includes('green')) return '#22c55e';
                        if (c.includes('azul') || c.includes('blue')) return '#3b82f6';
                        if (c.includes('rojo') || c.includes('red')) return '#ef4444';
                        if (c.includes('naranja') || c.includes('orange')) return '#f97316';
                        if (c.includes('gris') || c.includes('gray') || c.includes('grey')) return '#94a3b8';
                        return '#a855f7';
                    },

                    roleChips(role) {
                        // Genera chips tipo "Registro: Total" "Docs: Ver" etc.
                        // Si no hay permisos aún, devuelve vacío para no romper.
                        const list = role?.permisos ?? [];
                        if (!Array.isArray(list) || list.length === 0) return [];

                        const mapTipoToLabel = (t) => {
                            const x = (t ?? '').toLowerCase();
                            if (x.includes('crear')) return 'Crear';
                            if (x.includes('ver')) return 'Ver';
                            if (x.includes('editar')) return 'Editar';
                            if (x.includes('eliminar')) return 'Eliminar';
                            return (t ?? 'Permiso');
                        };

                        // Agrupa por modulo y muestra “modulo: tipos...”
                        const grouped = {};
                        list.forEach(p => {
                            const mod = (p.modulo ?? 'General');
                            grouped[mod] = grouped[mod] || new Set();
                            grouped[mod].add(mapTipoToLabel(p.tipo));
                        });

                        return Object.keys(grouped).map(mod => {
                            const types = Array.from(grouped[mod]).join(', ');
                            const label = `${this.prettyModulo(mod)}: ${types}`;
                            return {
                                key: mod,
                                label,
                                class: 'bg-emerald-600/10 text-emerald-200 border border-emerald-600/20'
                            };
                        });
                    },

                    prettyModulo(m) {
                        const x = (m ?? '').toLowerCase();
                        if (x.includes('registro')) return 'Registro';
                        if (x.includes('liber')) return 'Liberación';
                        if (x.includes('docs') || x.includes('envio')) return 'Docs';
                        if (x.includes('cotiz')) return 'Cotización';
                        if (x.includes('desp')) return 'Despacho';
                        if (x.includes('gast')) return 'Gastos';
                        if (x.includes('report')) return 'Reportes';
                        if (x.includes('user') || x.includes('usuario')) return 'Usuarios';
                        if (x.includes('activ')) return 'Actividad';
                        return m;
                    },

                    // ====== matriz UI ======
                    setMatrix(tabKey, option) {
                        this.roleMatrix.tabs[tabKey] = option;
                    },
                    matrixBtnClass(tabKey, option) {
                        const current = this.roleMatrix.tabs[tabKey] ?? 'none';
                        const active = current === option;

                        // estilo como tus capturas: activo gris claro, inactivo oscuro
                        if (active) {
                            return 'bg-slate-300/40 border-slate-200/30 text-white';
                        }
                        return 'bg-slate-900/40 border-slate-700/60 text-gray-300 hover:text-white hover:bg-slate-900/70';
                    },

                    // ====== open/close ======
                    closeAll() {
                        // usuarios
                        this.modalCreate = false;
                        this.modalView = false;
                        this.modalEdit = false;
                        this.modalDelete = false;
                        this.selectedUser = null;

                        // roles
                        this.modalRoleCreate = false;
                        this.modalRoleDelete = false;
                        this.selectedRole = null;
                        this.roleAccordion = false;
                    },

                    // usuarios modals
                    openCreate() {
                        this.createForm = {
                            name: '',
                            email: '',
                            username: '',
                            role_id: '',
                            password: '',
                            password_confirmation: '',
                            is_active: 1,
                        };
                        this.modalCreate = true;
                    },
                    openView(user) {
                        this.selectedUser = user;
                        this.modalView = true;
                    },
                    openEdit(user) {
                        this.selectedUser = user;
                        this.editForm = {
                            name: user.name ?? '',
                            email: user.email ?? '',
                            username: user.username ?? '',
                            role_id: user.role_id ?? '',
                            password: '',
                            password_confirmation: '',
                            is_active: user.is_active ? 1 : 0,
                        };
                        this.modalEdit = true;
                    },
                    openDelete(user) {
                        this.selectedUser = user;
                        this.modalDelete = true;
                    },

                    // roles modals
                    openRoleCreate() {
                        this.roleMode = 'create';
                        this.selectedRole = null;
                        this.roleForm = {
                            id: null,
                            name: '',
                            color: 'Púrpura'
                        };

                        // reset matrix
                        this.roleMatrix = {
                            create_contenedores: false,
                            tabs: {
                                registro: 'none',
                                liberacion: 'none',
                                docs: 'none',
                                cotizacion: 'none',
                                despacho: 'none',
                                gastos: 'none',
                            },
                            system: {
                                reportes: false,
                                usuarios: false,
                                actividad: false
                            },
                        };

                        this.modalRoleCreate = true;
                    },
                    openRoleEdit(role) {
                        this.roleMode = 'edit';
                        this.selectedRole = role;
                        this.roleForm = {
                            id: role.id,
                            name: role.name ?? '',
                            color: role.color ?? 'Púrpura',
                        };

                        // (solo UI) si luego quieres pre-cargar matriz desde permisos reales lo hacemos en la fase funcional
                        this.modalRoleCreate = true;
                    },
                    openRoleDelete(role) {
                        this.selectedRole = role;
                        this.modalRoleDelete = true;
                    },

                    // ============================
                    // USUARIOS (YA FUNCIONAL)
                    // ============================
                    async submitCreate() {
                        try {
                            const payload = {
                                name: (this.createForm.name ?? '').trim(),
                                username: (this.createForm.username ?? '').trim(),
                                email: (this.createForm.email ?? '').trim() || null,
                                role_id: this.createForm.role_id || null,
                                password: this.createForm.password ?? '',
                                password_confirmation: this.createForm.password_confirmation ?? '',
                                is_active: this.createForm.is_active ? 1 : 0,
                            };

                            if (!payload.name || !payload.username || !payload.role_id || !payload.password) {
                                alert('Completa: Nombre, Usuario, Rol y Contraseña.');
                                return;
                            }

                            const res = await fetch(this.routes.usersStore, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': this.csrf,
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                body: JSON.stringify(payload),
                            });

                            let data = {};
                            try {
                                data = await res.json();
                            } catch (e) {
                                data = {};
                            }

                            if (!res.ok || data.ok === false) {
                                if (res.status === 422) {
                                    alert(this.formatLaravelErrors(data.errors) ?? data.message ?? 'Validación fallida.');
                                    return;
                                }
                                alert(data.message ?? `No se pudo crear el usuario. (HTTP ${res.status})`);
                                return;
                            }

                            const saved = data.user;

                            // Insertar en UI
                            this.users.unshift(saved);

                            this.closeAll();
                        } catch (e) {
                            console.error(e);
                            alert('Error inesperado creando usuario.');
                        }
                    },

                    async submitEdit() {
                        try {
                            if (!this.selectedUser?.id) {
                                alert('No hay usuario seleccionado.');
                                return;
                            }

                            const payload = {
                                name: (this.editForm.name ?? '').trim(),
                                username: (this.editForm.username ?? '').trim(),
                                email: (this.editForm.email ?? '').trim() || null,
                                role_id: this.editForm.role_id || null,
                                is_active: this.editForm.is_active ? 1 : 0,
                                password: (this.editForm.password ?? '').trim() || null,
                                password_confirmation: (this.editForm.password_confirmation ?? '').trim() || null,
                            };

                            if (!payload.name || !payload.username || !payload.role_id) {
                                alert('Completa: Nombre, Usuario y Rol.');
                                return;
                            }

                            const url = `${this.routes.usersUpdate}/${this.selectedUser.id}`;

                            const res = await fetch(url, {
                                method: 'PUT',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': this.csrf,
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                body: JSON.stringify(payload),
                            });

                            let data = {};
                            try {
                                data = await res.json();
                            } catch (e) {
                                data = {};
                            }

                            if (!res.ok || data.ok === false) {
                                if (res.status === 422) {
                                    alert(this.formatLaravelErrors(data.errors) ?? data.message ?? 'Validación fallida.');
                                    return;
                                }
                                alert(data.message ?? `No se pudo actualizar el usuario. (HTTP ${res.status})`);
                                return;
                            }

                            const saved = data.user;

                            // Reemplazar en UI
                            const idx = this.users.findIndex(u => u.id === saved.id);
                            if (idx >= 0) this.users.splice(idx, 1, saved);

                            this.closeAll();
                        } catch (e) {
                            console.error(e);
                            alert('Error inesperado actualizando usuario.');
                        }
                    },

                    async confirmDelete() {
                        try {
                            if (!this.selectedUser?.id) {
                                alert('No hay usuario seleccionado.');
                                return;
                            }

                            const url = `${this.routes.usersDestroy}/${this.selectedUser.id}`;

                            const res = await fetch(url, {
                                method: 'DELETE',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': this.csrf,
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                            });

                            let data = {};
                            try {
                                data = await res.json();
                            } catch (e) {
                                data = {};
                            }

                            if (!res.ok || data.ok === false) {
                                alert(data.message ?? `No se pudo eliminar el usuario. (HTTP ${res.status})`);
                                return;
                            }

                            const id = this.selectedUser.id;
                            const idx = this.users.findIndex(u => u.id === id);
                            if (idx >= 0) this.users.splice(idx, 1);

                            this.closeAll();
                        } catch (e) {
                            console.error(e);
                            alert('Error inesperado eliminando usuario.');
                        }
                    },

                    // ============================
                    // ROLES (como ya lo tenías)
                    // ============================
                    async submitRole() {
                        try {
                            // validación mínima
                            const name = (this.roleForm.name ?? '').trim();
                            if (!name) {
                                alert('Escribe el nombre del rol.');
                                return;
                            }

                            const payload = {
                                name: name,
                                color: this.roleForm.color ?? 'Púrpura',
                                matrix: this.roleMatrix, // tal cual tu UI
                            };

                            const isEdit = this.roleMode === 'edit' && this.roleForm.id;
                            const url = isEdit ?
                                `${this.routes.rolesUpdate}/${this.roleForm.id}` :
                                this.routes.rolesStore;

                            const method = isEdit ? 'PUT' : 'POST';

                            const res = await fetch(url, {
                                method,
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': this.csrf,
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                body: JSON.stringify(payload),
                            });

                            const data = await res.json().catch(() => ({}));

                            if (!res.ok || data.ok === false) {
                                alert(data.message ?? 'No se pudo guardar el rol.');
                                return;
                            }

                            // backend devuelve role "full"
                            const saved = data.role;

                            // actualizar rolesFull (cards)
                            const idx = this.rolesFull.findIndex(r => r.id === saved.id);
                            if (idx >= 0) this.rolesFull.splice(idx, 1, saved);
                            else this.rolesFull.unshift(saved);

                            // actualizar roles simples (para selects de usuario)
                            const idx2 = this.roles.findIndex(r => r.id === saved.id);
                            const simple = {
                                id: saved.id,
                                name: saved.name,
                                color: saved.color
                            };
                            if (idx2 >= 0) this.roles.splice(idx2, 1, simple);
                            else this.roles.push(simple);

                            this.closeAll();
                        } catch (e) {
                            console.error(e);
                            alert('Error inesperado guardando rol.');
                        }
                    },
                }
            }
        </script>
    </div>
</x-app-layout>
