<x-app-layout>
    <div class="space-y-6" x-data="actividadUsuarioDetalle(@js($u))" x-init="init()">

        <div>
            <h1 class="text-2xl font-bold text-white">Actividad por Usuario</h1>
            <p class="text-sm text-slate-300">Visualiza el historial de actividad filtrado por usuario</p>
        </div>

        <div>
            <a href="/actividad/usuarios"
               class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
                ← Volver a usuarios
            </a>
        </div>

        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 shadow">
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-white mb-2">Tipo de Acción</label>
                        <select class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100"
                                x-model="filters.accion"
                                @change="load()">
                            <option value="">Todas las acciones</option>
                            <option value="crear">Crear</option>
                            <option value="editar">Editar</option>
                            <option value="ver">Ver</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-white mb-2">Fecha Inicio</label>
                        <input type="date"
                               class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100"
                               x-model="filters.desde"
                               @change="load()" />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-white mb-2">Fecha Fin</label>
                        <input type="date"
                               class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100"
                               x-model="filters.hasta"
                               @change="load()" />
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 shadow overflow-hidden">
            <div class="p-6 flex items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center text-white font-extrabold text-xl"
                         :class="avatarBg(user.role_name)">
                        <span x-text="initials(user.name)"></span>
                    </div>

                    <div>
                        <div class="text-lg font-extrabold text-white" x-text="user.name"></div>
                        <div class="text-sm text-slate-300">
                            <span x-text="user.role_name ?? 'Usuario'"></span>
                            <span class="text-slate-500"> • </span>
                            <span x-text="user.email ?? ''"></span>
                        </div>
                        <div class="text-xs text-slate-500 mt-1" x-text="user.username ? ('@' + user.username) : ''"></div>
                    </div>
                </div>

                <div class="text-right">
                    <div class="text-2xl font-extrabold text-blue-300" x-text="user.actividades ?? 0"></div>
                    <div class="text-sm text-slate-300">Actividades</div>
                </div>
            </div>

            <div class="border-t border-slate-800">
                <div class="p-0 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-950/60 text-slate-300">
                            <tr>
                                <th class="text-left px-4 py-3">FECHA / HORA</th>
                                <th class="text-left px-4 py-3">ACCIÓN</th>
                                <th class="text-left px-4 py-3">CONTENEDOR</th>
                                <th class="text-left px-4 py-3">DETALLES</th>
                            </tr>
                        </thead>

                        <tbody>
                            <template x-if="loading">
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-slate-200">Cargando actividades…</td>
                                </tr>
                            </template>

                            <template x-if="!loading && logs.length === 0">
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-slate-300">Sin actividad registrada.</td>
                                </tr>
                            </template>

                            <template x-for="row in logs" :key="row.id">
                                <tr class="border-t border-slate-800">
                                    <td class="px-4 py-4 text-slate-200">
                                        <div class="font-semibold" x-text="row.fecha ?? ''"></div>
                                        <div class="text-xs text-slate-500" x-text="row.hora ?? ''"></div>
                                    </td>

                                    <td class="px-4 py-4">
                                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold border"
                                              :class="pillColor(row.accion)">
                                            <span x-text="labelAccion(row.accion)"></span>
                                        </span>
                                    </td>

                                    <td class="px-4 py-4">
                                        <span class="text-blue-300 font-semibold" x-text="row.contenedor ?? '-'"></span>
                                    </td>

                                    <td class="px-4 py-4 text-slate-200">
                                        <div x-text="row.descripcion ?? ''"></div>

                                        <template x-if="row.cambios && row.cambios.length">
                                            <div class="mt-2 text-xs text-slate-400">
                                                <div><span class="font-semibold text-slate-300">Pestaña:</span> <span x-text="row.modulo ?? '-'"></span></div>
                                                <div><span class="font-semibold text-slate-300">Campos:</span> <span x-text="row.cambios.join(', ')"></span></div>
                                            </div>
                                        </template>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <template x-if="errorMsg">
                    <div class="p-4 text-red-200">
                        <span x-text="errorMsg"></span>
                    </div>
                </template>
            </div>
        </div>

    </div>

    <script>
        function actividadUsuarioDetalle(serverUser) {
            return {
                user: serverUser || {},
                logs: [],
                loading: false,
                errorMsg: '',

                filters: { accion: '', desde: '', hasta: '' },

                init() {
                    if (!this.user?.id) {
                        this.errorMsg = 'Usuario inválido.';
                        return;
                    }
                    this.load();
                },

                async load() {
                    this.loading = true;
                    this.errorMsg = '';
                    try {
                        const params = new URLSearchParams();
                        if (this.filters.accion) params.set('accion', this.filters.accion);
                        if (this.filters.desde) params.set('desde', this.filters.desde);
                        if (this.filters.hasta) params.set('hasta', this.filters.hasta);

                        const url = `/actividad/usuarios/${this.user.id}/logs?` + params.toString();
                        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                        if (!res.ok) throw new Error('No se pudo consultar la actividad.');

                        const json = await res.json();
                        this.logs = Array.isArray(json.logs) ? json.logs : [];
                    } catch (e) {
                        this.logs = [];
                        this.errorMsg = e?.message || 'Error al cargar actividad.';
                    } finally {
                        this.loading = false;
                    }
                },

                initials(name) {
                    const s = (name || '').trim().split(/\s+/).slice(0,2).map(x => (x[0]||'').toUpperCase()).join('');
                    return s || 'U';
                },

                avatarBg(role) {
                    role = (role || '').toLowerCase();
                    if (role.includes('admin')) return 'bg-purple-600';
                    if (role.includes('super')) return 'bg-emerald-600';
                    return 'bg-blue-600';
                },

                labelAccion(a) {
                    a = (a || '').toLowerCase();
                    if (a === 'crear') return 'Crear';
                    if (a === 'editar') return 'Editar';
                    if (a === 'ver') return 'Ver';
                    return a ? a : 'Acción';
                },

                pillColor(a) {
                    a = (a || '').toLowerCase();
                    if (a === 'crear') return 'bg-emerald-950/60 text-emerald-200 border-emerald-700/40';
                    if (a === 'editar') return 'bg-amber-950/60 text-amber-200 border-amber-700/40';
                    if (a === 'ver') return 'bg-blue-950/60 text-blue-200 border-blue-700/40';
                    return 'bg-slate-800 text-slate-200 border-slate-700';
                },
            }
        }
    </script>
</x-app-layout>
