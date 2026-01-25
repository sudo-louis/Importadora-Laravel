{{-- resources/views/actividad/contenedores.blade.php --}}

<x-app-layout>
    <div class="space-y-6"
         x-data="actividadContenedores()"
         x-init="init()">

        {{-- Header --}}
        <div>
            <h1 class="text-2xl font-bold text-white">Actividad por Contenedor</h1>
            <p class="text-sm text-slate-300">Busca y visualiza el historial de actividad de uno o varios contenedores</p>
        </div>

        {{-- Card: Buscador --}}
        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 shadow">
            <div class="p-6 space-y-4">
                <div class="text-sm font-semibold text-white">Buscar Contenedores</div>

                {{-- Input + chips --}}
                <div class="relative">
                    <div class="flex flex-wrap items-center gap-2 rounded-xl border border-slate-700 bg-slate-900 px-3 py-2">
                        {{-- chips --}}
                        <template x-for="(item, idx) in selected" :key="item.key">
                            <span class="inline-flex items-center gap-2 rounded-lg border border-blue-700/40 bg-blue-950/60 px-3 py-1 text-sm text-blue-200">
                                <span class="font-semibold" x-text="item.numero"></span>
                                <button type="button"
                                        class="text-blue-200/80 hover:text-white"
                                        title="Quitar"
                                        @click="removeSelected(idx)">
                                    ✕
                                </button>
                            </span>
                        </template>

                        {{-- input --}}
                        <input
                            x-ref="input"
                            type="text"
                            class="flex-1 min-w-[220px] bg-transparent outline-none text-slate-100 placeholder:text-slate-500"
                            placeholder="Escribe el número de contenedor..."
                            x-model="query"
                            @input.debounce.250ms="onType()"
                            @keydown.down.prevent="move(1)"
                            @keydown.up.prevent="move(-1)"
                            @keydown.enter.prevent="enterSelect()"
                            @keydown.escape="closeDropdown()"
                            @focus="openIfHasResults()"
                        />
                    </div>

                    {{-- Dropdown (select) --}}
                    <div x-show="open"
                         x-transition
                         @click.outside="closeDropdown()"
                         class="absolute z-50 mt-2 w-full overflow-hidden rounded-xl border border-slate-700 bg-slate-950 shadow-lg">
                        <div class="max-h-64 overflow-auto">
                            <template x-if="loading">
                                <div class="px-4 py-3 text-sm text-slate-300">Buscando…</div>
                            </template>

                            <template x-if="!loading && results.length === 0">
                                <div class="px-4 py-3 text-sm text-slate-400">Sin resultados</div>
                            </template>

                            <template x-for="(r, i) in results" :key="r.key">
                                <button type="button"
                                        class="w-full text-left px-4 py-3 border-t border-slate-800 hover:bg-slate-900"
                                        :class="highlight === i ? 'bg-slate-900' : ''"
                                        @mouseenter="highlight = i"
                                        @click="selectResult(i)">
                                    <div class="flex items-center justify-between">
                                        <div class="text-sm font-semibold text-white" x-text="r.numero"></div>
                                        <div class="text-xs text-slate-400" x-text="r.cliente ?? ''"></div>
                                    </div>
                                    <div class="text-xs text-slate-500 mt-1" x-text="r.naviera ? ('Naviera: ' + r.naviera) : ''"></div>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Botones --}}
                <div class="flex flex-wrap gap-3">
                    <button type="button"
                            class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500 disabled:opacity-50 disabled:hover:bg-blue-600"
                            :disabled="selected.length === 0 || searching"
                            @click="search()">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M10 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16z"></path>
                            <path d="M21 21l-4.35-4.35"></path>
                        </svg>
                        <span x-text="searching ? 'Buscando…' : 'Buscar Actividad'"></span>
                    </button>

                    <button type="button"
                            class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700"
                            @click="clearAll()">
                        ✕ Limpiar
                    </button>

                    <template x-if="errorMsg">
                        <div class="text-sm text-red-300 flex items-center">
                            <span x-text="errorMsg"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- Filtros (solo cuando ya buscaste o hay resultados) --}}
        <template x-if="hasSearched">
            <div class="rounded-2xl border border-slate-800 bg-slate-900/60 shadow">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-white mb-2">Tipo de Acción</label>
                            <select class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100"
                                    x-model="filters.accion"
                                    @change="applyFilters()">
                                <option value="">Todas las acciones</option>
                                <option value="crear">Crear</option>
                                <option value="editar">Editar</option>
                                <option value="ver">Ver</option>
                                {{-- ✅ Quitado "Eliminar" --}}
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-white mb-2">Fecha Inicio</label>
                            <input type="date"
                                   class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100"
                                   x-model="filters.desde"
                                   @change="applyFilters()" />
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-white mb-2">Fecha Fin</label>
                            <input type="date"
                                   class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100"
                                   x-model="filters.hasta"
                                   @change="applyFilters()" />
                        </div>
                    </div>
                </div>
            </div>
        </template>

        {{-- Resultados --}}
        <template x-if="hasSearched">
            <div class="space-y-4">
                <template x-if="searching">
                    <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 text-slate-200">
                        Cargando resultados…
                    </div>
                </template>

                <template x-if="!searching && contenedores.length === 0">
                    <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 text-slate-300">
                        No se encontraron actividades para los contenedores seleccionados.
                    </div>
                </template>

                <template x-for="c in contenedores" :key="c.key">
                    <div class="rounded-2xl border border-slate-800 bg-slate-900/60 shadow overflow-hidden">
                        {{-- header card --}}
                        <button type="button"
                                class="w-full p-6 flex items-center justify-between hover:bg-slate-900"
                                @click="c.open = !c.open">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-blue-600 flex items-center justify-center text-white font-bold">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4a2 2 0 0 0 1-1.73z"></path>
                                        <path d="M3.3 7l8.7 5 8.7-5"></path>
                                        <path d="M12 22V12"></path>
                                    </svg>
                                </div>

                                <div class="text-left">
                                    <div class="text-lg font-extrabold text-white" x-text="c.numero"></div>
                                    <div class="text-sm text-slate-300">
                                        Cliente: <span class="font-semibold" x-text="c.cliente ?? '-'"></span>
                                    </div>
                                    <div class="text-xs text-slate-500 mt-1" x-text="c.registrado_texto ?? ''"></div>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="text-right">
                                    <div class="text-2xl font-extrabold text-emerald-400" x-text="c.logs.length"></div>
                                    <div class="text-sm text-slate-300">Actividades</div>
                                </div>

                                <svg class="w-5 h-5 text-slate-300 transition"
                                     :class="c.open ? 'rotate-180' : ''"
                                     fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </button>

                        {{-- body --}}
                        <div x-show="c.open" x-collapse class="border-t border-slate-800">
                            <div class="p-6 space-y-6">
                                <template x-if="c.logs.length === 0">
                                    <div class="text-slate-300">Sin actividad registrada.</div>
                                </template>

                                <template x-for="log in c.logs" :key="log.key">
                                    <div class="grid grid-cols-12 gap-4">
                                        {{-- icono --}}
                                        <div class="col-span-12 md:col-span-1 flex md:flex-col items-start md:items-center gap-3">
                                            <div class="w-10 h-10 rounded-full flex items-center justify-center"
                                                 :class="badgeColor(log.accion)">
                                                <span class="text-white text-sm font-bold" x-text="badgeLetter(log.accion)"></span>
                                            </div>
                                            <div class="hidden md:block w-px flex-1 bg-slate-800"></div>
                                        </div>

                                        {{-- contenido --}}
                                        <div class="col-span-12 md:col-span-11">
                                            <div class="flex flex-wrap items-center gap-3">
                                                <span class="px-3 py-1 rounded-full text-xs font-semibold"
                                                      :class="pillColor(log.accion)"
                                                      x-text="labelAccion(log.accion)"></span>

                                                <div class="text-sm text-white font-semibold" x-text="log.user_name ?? 'Usuario'"></div>

                                                <span class="text-xs text-slate-400" x-text="log.role_name ? ('(' + log.role_name + ')') : ''"></span>

                                                <div class="ml-auto text-xs text-slate-400" x-text="log.fecha_hora ?? ''"></div>
                                            </div>

                                            <div class="text-sm text-slate-200 mt-2" x-text="log.descripcion ?? ''"></div>

                                            <template x-if="log.cambios && log.cambios.length">
                                                <div class="mt-3 rounded-xl border border-slate-800 bg-slate-950 p-4 text-sm text-slate-200">
                                                    <div class="font-semibold text-white mb-2">Cambios realizados:</div>
                                                    <div class="text-slate-300">
                                                        <div><span class="font-semibold">Módulo:</span> <span x-text="log.modulo ?? '-'"></span></div>
                                                        <div class="mt-2">
                                                            <span class="font-semibold">Campos modificados:</span>
                                                            <span x-text="log.cambios.join(', ')"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        {{-- Aviso si no existen endpoints --}}
        <template x-if="missingBackend">
            <div class="rounded-2xl border border-yellow-700/40 bg-yellow-950/30 p-6 text-yellow-200">
                <div class="font-semibold">Falta backend para Actividad</div>
                <div class="text-sm mt-2 text-yellow-200/90">
                    Esta vista espera estos endpoints:
                    <div class="mt-2 text-xs font-mono text-yellow-100/90">
                        GET /actividad/contenedores/autocomplete?q=CONT<br>
                        GET /actividad/contenedores/search?contenedores[]=CONT-2024-001&...
                    </div>
                    Si aún no los tienes, dímelo y te paso el Controller + rutas completos.
                </div>
            </div>
        </template>

    </div>

    <script>
        function actividadContenedores() {
            return {
                // endpoints (ajusta si quieres)
                endpoints: {
                    autocomplete: '/actividad/contenedores/autocomplete',
                    search: '/actividad/contenedores/search',
                },

                query: '',
                open: false,
                loading: false,
                searching: false,
                results: [],
                highlight: 0,

                selected: [], // [{key, id?, numero, cliente?, naviera?}]
                hasSearched: false,
                contenedores: [], // [{key, numero, cliente, registrado_texto, open, logs:[]}]
                errorMsg: '',
                missingBackend: false,

                filters: {
                    accion: '',
                    desde: '',
                    hasta: '',
                },

                init() {
                    // nada (pero lo dejamos por si luego quieres hidratar desde querystring)
                },

                async onType() {
                    this.errorMsg = '';
                    const q = (this.query || '').trim();
                    if (q.length < 2) {
                        this.results = [];
                        this.open = false;
                        return;
                    }

                    this.loading = true;
                    this.open = true;
                    this.highlight = 0;

                    try {
                        const url = this.endpoints.autocomplete + '?q=' + encodeURIComponent(q);
                        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });

                        if (!res.ok) {
                            this.results = [];
                            this.open = false;

                            // si no existe ruta, marcamos missing backend
                            if (res.status === 404) this.missingBackend = true;
                            return;
                        }

                        const json = await res.json();

                        // Espera: { ok:true, items:[{id, numero_contenedor, cliente, naviera}] }
                        const items = json.items || json.data || [];
                        this.results = items.map(it => ({
                            key: (it.id ?? it.numero_contenedor ?? it.numero ?? Math.random().toString(36)).toString(),
                            id: it.id ?? null,
                            numero: it.numero_contenedor ?? it.numero ?? '',
                            cliente: it.cliente ?? null,
                            naviera: it.naviera ?? null,
                        }));

                        this.open = this.results.length > 0;
                    } catch (e) {
                        this.results = [];
                        this.open = false;
                        this.errorMsg = 'No se pudo consultar el autocompletado.';
                    } finally {
                        this.loading = false;
                    }
                },

                openIfHasResults() {
                    if (this.results.length > 0) this.open = true;
                },

                closeDropdown() {
                    this.open = false;
                },

                move(dir) {
                    if (!this.open || this.results.length === 0) return;
                    this.highlight += dir;
                    if (this.highlight < 0) this.highlight = this.results.length - 1;
                    if (this.highlight >= this.results.length) this.highlight = 0;
                },

                enterSelect() {
                    if (!this.open || this.results.length === 0) return;
                    this.selectResult(this.highlight);
                },

                selectResult(index) {
                    const r = this.results[index];
                    if (!r) return;

                    const exists = this.selected.some(s => (s.numero || '').toLowerCase() === (r.numero || '').toLowerCase());
                    if (!exists) {
                        this.selected.push({
                            key: r.key,
                            id: r.id,
                            numero: r.numero,
                            cliente: r.cliente,
                            naviera: r.naviera,
                        });
                    }

                    this.query = '';
                    this.results = [];
                    this.open = false;

                    this.$nextTick(() => this.$refs.input?.focus());
                },

                removeSelected(idx) {
                    this.selected.splice(idx, 1);
                },

                clearAll() {
                    this.query = '';
                    this.results = [];
                    this.open = false;
                    this.selected = [];
                    this.hasSearched = false;
                    this.contenedores = [];
                    this.errorMsg = '';
                    this.filters = { accion:'', desde:'', hasta:'' };
                },

                async search() {
                    this.errorMsg = '';
                    this.hasSearched = true;
                    this.searching = true;

                    try {
                        // query string
                        const params = new URLSearchParams();
                        this.selected.forEach(s => params.append('contenedores[]', s.numero));

                        if (this.filters.accion) params.set('accion', this.filters.accion);
                        if (this.filters.desde) params.set('desde', this.filters.desde);
                        if (this.filters.hasta) params.set('hasta', this.filters.hasta);

                        const url = this.endpoints.search + '?' + params.toString();
                        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });

                        if (!res.ok) {
                            if (res.status === 404) this.missingBackend = true;
                            this.contenedores = [];
                            this.errorMsg = 'No se pudo consultar la actividad.';
                            return;
                        }

                        const json = await res.json();

                        // Espera:
                        // { ok:true, contenedores:[{ id, numero_contenedor, cliente, registrado_texto, logs:[...] }] }
                        const rows = json.contenedores || json.data || [];

                        this.contenedores = rows.map(c => ({
                            key: (c.id ?? c.numero_contenedor ?? c.numero ?? Math.random().toString(36)).toString(),
                            numero: c.numero_contenedor ?? c.numero ?? '',
                            cliente: c.cliente ?? null,
                            registrado_texto: c.registrado_texto ?? '',
                            open: true,
                            logs: (c.logs || []).map(l => ({
                                key: (l.id ?? Math.random().toString(36)).toString(),
                                accion: l.accion ?? '',
                                modulo: l.modulo ?? '',
                                descripcion: l.descripcion ?? '',
                                fecha_hora: l.fecha_hora ?? l.created_at ?? '',
                                user_name: l.user_name ?? l.user?.name ?? '',
                                role_name: l.role_name ?? l.user_role ?? '',
                                cambios: l.cambios ?? l.campos_modificados ?? [],
                            })),
                        }));

                    } catch (e) {
                        this.contenedores = [];
                        this.errorMsg = 'Ocurrió un error al buscar la actividad.';
                    } finally {
                        this.searching = false;
                    }
                },

                applyFilters() {
                    // si ya buscaste, relanza búsqueda con filtros (sin obligarte a volver a seleccionar)
                    if (!this.hasSearched) return;
                    if (this.selected.length === 0) return;
                    this.search();
                },

                // UI helpers
                labelAccion(a) {
                    a = (a || '').toLowerCase();
                    if (a === 'crear') return 'Crear';
                    if (a === 'editar') return 'Editar';
                    if (a === 'ver') return 'Ver';
                    // ✅ Quitado "Eliminar"
                    return a ? a : 'Acción';
                },
                badgeLetter(a) {
                    a = (a || '').toLowerCase();
                    if (a === 'crear') return 'C';
                    if (a === 'editar') return 'E';
                    if (a === 'ver') return 'V';
                    // ✅ Quitado "Eliminar"
                    return '•';
                },
                badgeColor(a) {
                    a = (a || '').toLowerCase();
                    if (a === 'crear') return 'bg-emerald-600';
                    if (a === 'editar') return 'bg-amber-600';
                    if (a === 'ver') return 'bg-blue-600';
                    // ✅ Quitado "Eliminar"
                    return 'bg-slate-700';
                },
                pillColor(a) {
                    a = (a || '').toLowerCase();
                    if (a === 'crear') return 'bg-emerald-950/60 text-emerald-200 border border-emerald-700/40';
                    if (a === 'editar') return 'bg-amber-950/60 text-amber-200 border border-amber-700/40';
                    if (a === 'ver') return 'bg-blue-950/60 text-blue-200 border border-blue-700/40';
                    // ✅ Quitado "Eliminar"
                    return 'bg-slate-800 text-slate-200 border border-slate-700';
                },
            }
        }
    </script>
</x-app-layout>
