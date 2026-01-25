<x-app-layout>
    <div class="space-y-6" x-data="actividadUsuariosIndex()" x-init="init()">

        <div>
            <h1 class="text-2xl font-bold text-white">Actividad por Usuario</h1>
            <p class="text-sm text-slate-300">Visualiza el historial de actividad filtrado por usuario</p>
        </div>

        <template x-if="loading">
            <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 text-slate-200">
                Cargando usuarios…
            </div>
        </template>

        <template x-if="!loading && users.length === 0 && !errorMsg">
            <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 text-slate-300">
                No hay usuarios para mostrar.
            </div>
        </template>

        <template x-if="errorMsg">
            <div class="rounded-2xl border border-red-800 bg-red-950/30 p-6 text-red-200">
                <span x-text="errorMsg"></span>
            </div>
        </template>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" x-show="!loading && users.length > 0">
            <template x-for="u in users" :key="u.id">
                <a :href="`/actividad/usuarios/${u.id}`"
                   class="group rounded-2xl border border-slate-800 bg-slate-900/60 shadow hover:bg-slate-900/80 transition overflow-hidden">

                    <div class="p-8 flex flex-col items-center text-center gap-3">
                        <div class="w-24 h-24 rounded-full flex items-center justify-center text-white font-extrabold text-2xl"
                             :class="avatarBg(u.role_name)">
                            <span x-text="initials(u.name)"></span>
                        </div>

                        <div class="text-lg font-extrabold text-white" x-text="u.name"></div>

                        <template x-if="u.role_name">
                            <div class="px-4 py-1 rounded-full text-xs font-semibold"
                                 :class="rolePill(u.role_name)"
                                 x-text="u.role_name"></div>
                        </template>
                    </div>

                    <div class="border-t border-slate-800 p-6 space-y-3">
                        <div class="flex items-center justify-between text-sm text-slate-300">
                            <span>Actividades</span>
                            <span class="text-blue-300 font-bold" x-text="u.actividades ?? 0"></span>
                        </div>

                        <div class="flex items-center justify-between text-sm text-slate-300">
                            <span>Estado</span>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold"
                                  :class="(u.is_active ?? false)
                                      ? 'bg-emerald-950/60 text-emerald-200 border border-emerald-700/40'
                                      : 'bg-red-950/60 text-red-200 border border-red-700/40'"
                                  x-text="(u.is_active ?? false) ? 'Activo' : 'Inactivo'"></span>
                        </div>
                    </div>

                </a>
            </template>
        </div>

    </div>

    <script>
        function actividadUsuariosIndex() {
            return {
                users: [],
                loading: false,
                errorMsg: '',

                async init() {
                    this.loading = true;
                    this.errorMsg = '';
                    try {
                        const res = await fetch('/actividad/usuarios/data', {
                            headers: { 'Accept': 'application/json' }
                        });

                        if (!res.ok) throw new Error('No se pudo cargar la lista de usuarios.');

                        const json = await res.json();

                        // soporta ambas formas:
                        // { ok:true, users:[...] }  o  { ok:true, items:[...] }
                        this.users = Array.isArray(json.users) ? json.users : (Array.isArray(json.items) ? json.items : []);
                    } catch (e) {
                        this.users = [];
                        this.errorMsg = e?.message || 'Error al cargar usuarios.';
                    } finally {
                        this.loading = false;
                    }
                },

                initials(name) {
                    const s = (name || '').trim()
                        .split(/\s+/)
                        .slice(0, 2)
                        .map(x => (x[0] || '').toUpperCase())
                        .join('');
                    return s || 'U';
                },

                avatarBg(role) {
                    role = (role || '').toLowerCase();
                    if (role.includes('admin')) return 'bg-purple-600';
                    if (role.includes('super')) return 'bg-emerald-600';
                    return 'bg-blue-600';
                },

                rolePill(role) {
                    role = (role || '').toLowerCase();
                    if (role.includes('admin')) return 'bg-purple-950/60 text-purple-200 border border-purple-700/40';
                    if (role.includes('super')) return 'bg-emerald-950/60 text-emerald-200 border border-emerald-700/40';
                    return 'bg-blue-950/60 text-blue-200 border border-blue-700/40';
                },
            }
        }
    </script>
</x-app-layout>
