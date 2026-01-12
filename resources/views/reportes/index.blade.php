{{-- resources/views/reportes/index.blade.php --}}
<x-app-layout>
    @php
        // viene del controller:
        // $templates = [... default + custom]
        // $filters = ['from','to','clientes','contenedores','template']
        // $results = [...]
        $templates = $templates ?? [];
        $filters = $filters ?? [
            'from' => null,
            'to' => null,
            'clientes' => [],
            'contenedores' => [],
            'template' => 'default:general',
        ];
        $results = $results ?? [];
    @endphp

    <div class="p-6 space-y-6"
         x-data="reportesPage(@js($templates), @js($filters), @js($results))"
         x-init="init()">

        {{-- Header --}}
        <div>
            <div class="text-3xl font-extrabold text-white">Generar Reporte</div>
            <div class="mt-1 text-gray-400 text-sm">Genera reportes personalizados con filtros</div>
        </div>

        {{-- Seleccionar Plantilla --}}
        <div class="rounded-3xl border border-slate-800 bg-slate-900/60 p-6">
            <div class="text-sm text-gray-200 font-semibold mb-4">
                Seleccionar Plantilla <span class="text-red-400">*</span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <template x-for="t in templates" :key="t.key">
                    <button type="button"
                            class="w-full rounded-2xl border border-slate-700 bg-slate-900/40 hover:bg-slate-900/70 px-5 py-4 flex items-center justify-between gap-4"
                            :class="selectedTemplate === t.key ? 'ring-2 ring-blue-600 border-blue-700 bg-blue-600/10' : ''"
                            @click="selectedTemplate = t.key; applyFiltersDebounced()">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-2xl bg-slate-950 border border-slate-800 flex items-center justify-center">
                                <span class="text-lg" x-text="t.icon"></span>
                            </div>
                            <div class="text-white font-extrabold text-sm" x-text="t.label"></div>
                        </div>

                        <div class="w-6 h-6 rounded-full border border-slate-600 flex items-center justify-center"
                             :class="selectedTemplate === t.key ? 'bg-blue-600 border-blue-600' : 'bg-slate-900'">
                            <span x-show="selectedTemplate === t.key" class="text-white text-xs" style="display:none;">✓</span>
                        </div>
                    </button>
                </template>
            </div>
        </div>

        {{-- Filtros Avanzados (Acordeón) --}}
        <div class="rounded-3xl border border-slate-800 bg-slate-900/60 overflow-hidden">
            <button type="button"
                    class="w-full px-6 py-5 flex items-center justify-between hover:bg-slate-900/70"
                    @click="advancedOpen = !advancedOpen">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-slate-950 border border-slate-800 flex items-center justify-center text-blue-400">
                        ⌁
                    </div>
                    <div class="text-white font-extrabold">Filtros Avanzados</div>
                </div>

                <div class="flex items-center gap-3">
                    <span x-show="activeFiltersCount() > 0"
                          class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-600/15 border border-blue-600/20 text-blue-200"
                          style="display:none;"
                          x-text="activeFiltersCount() + ' activos'"></span>

                    <div class="w-10 h-10 rounded-2xl bg-slate-950 border border-slate-800 flex items-center justify-center text-gray-300">
                        <span x-text="advancedOpen ? '▴' : '▾'"></span>
                    </div>
                </div>
            </button>

            <div x-show="advancedOpen"
                 x-transition.opacity.duration.150ms
                 x-transition.scale.origin.top.duration.150ms
                 class="px-6 pb-6"
                 style="display:none;">
                <div class="text-gray-400 text-sm mb-4">
                    Filtra por clientes y contenedores específicos
                </div>

                <div class="space-y-6">
                    {{-- Clientes --}}
                    <div class="space-y-2">
                        <div class="text-white font-semibold text-sm">Filtrar por Clientes</div>

                        {{-- Chips --}}
                        <div class="flex flex-wrap gap-2" x-show="selectedClientes.length > 0" style="display:none;">
                            <template x-for="(c, idx) in selectedClientes" :key="c">
                                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-600 text-white text-xs font-semibold">
                                    <span x-text="c"></span>
                                    <button type="button" class="opacity-90 hover:opacity-100"
                                            @click="removeCliente(idx)">✕</button>
                                </span>
                            </template>
                        </div>

                        {{-- Input + dropdown --}}
                        <div class="relative">
                            <input type="text"
                                   x-model="clienteQuery"
                                   @input="fetchClientesDebounced()"
                                   @focus="fetchClientesDebounced()"
                                   @keydown.enter.prevent="addClienteFromQuery()"
                                   class="w-full px-4 py-4 rounded-2xl bg-slate-800 border border-slate-700 text-white placeholder-gray-500 focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                                   placeholder="Escribe para buscar clientes...">

                            {{-- Dropdown --}}
                            <div x-show="clienteSuggestionsOpen"
                                 @click.outside="clienteSuggestionsOpen=false"
                                 class="absolute z-20 mt-2 w-full rounded-2xl border border-slate-700 bg-slate-950 shadow-xl overflow-hidden"
                                 style="display:none;">
                                <template x-for="s in clienteSuggestions" :key="s">
                                    <button type="button"
                                            class="w-full text-left px-4 py-3 hover:bg-slate-900 text-gray-200"
                                            @click="selectCliente(s)">
                                        <span x-text="s"></span>
                                    </button>
                                </template>

                                <div x-show="clienteSuggestions.length === 0"
                                     class="px-4 py-3 text-gray-400 text-sm"
                                     style="display:none;">
                                    Sin resultados
                                </div>
                            </div>
                        </div>

                        <div class="text-xs text-gray-400">
                            Selecciona de la lista o presiona Enter para agregar manualmente
                        </div>
                    </div>

                    {{-- Contenedores --}}
                    <div class="space-y-2">
                        <div class="text-white font-semibold text-sm">Filtrar por Contenedores</div>

                        {{-- Chips --}}
                        <div class="flex flex-wrap gap-2" x-show="selectedContenedores.length > 0" style="display:none;">
                            <template x-for="(c, idx) in selectedContenedores" :key="c">
                                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-700 text-white text-xs font-semibold border border-slate-600">
                                    <span x-text="c"></span>
                                    <button type="button" class="opacity-90 hover:opacity-100"
                                            @click="removeContenedor(idx)">✕</button>
                                </span>
                            </template>
                        </div>

                        {{-- Input + dropdown --}}
                        <div class="relative">
                            <input type="text"
                                   x-model="contenedorQuery"
                                   @input="fetchContenedoresDebounced()"
                                   @focus="fetchContenedoresDebounced()"
                                   @keydown.enter.prevent="addContenedorFromQuery()"
                                   class="w-full px-4 py-4 rounded-2xl bg-slate-800 border border-slate-700 text-white placeholder-gray-500 focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                                   placeholder="Escribe para buscar contenedores...">

                            {{-- Dropdown --}}
                            <div x-show="contenedorSuggestionsOpen"
                                 @click.outside="contenedorSuggestionsOpen=false"
                                 class="absolute z-20 mt-2 w-full rounded-2xl border border-slate-700 bg-slate-950 shadow-xl overflow-hidden"
                                 style="display:none;">
                                <template x-for="s in contenedorSuggestions" :key="s">
                                    <button type="button"
                                            class="w-full text-left px-4 py-3 hover:bg-slate-900 text-gray-200"
                                            @click="selectContenedor(s)">
                                        <span x-text="s"></span>
                                    </button>
                                </template>

                                <div x-show="contenedorSuggestions.length === 0"
                                     class="px-4 py-3 text-gray-400 text-sm"
                                     style="display:none;">
                                    Sin resultados
                                </div>
                            </div>
                        </div>

                        <div class="text-xs text-gray-400">
                            Selecciona de la lista o presiona Enter para agregar manualmente
                        </div>
                    </div>

                    {{-- Resumen --}}
                    <div class="rounded-2xl border border-slate-800 bg-slate-900/40 p-4">
                        <div class="text-blue-300 text-sm font-semibold mb-2">Resumen de filtros:</div>
                        <div class="flex flex-wrap gap-2 text-xs">
                            <span class="px-3 py-1 rounded-full bg-slate-800 border border-slate-700 text-gray-200"
                                  x-text="selectedClientes.length + ' cliente(s) seleccionado(s)'"></span>
                            <span class="px-3 py-1 rounded-full bg-slate-800 border border-slate-700 text-gray-200"
                                  x-text="selectedContenedores.length + ' contenedor(es) seleccionado(s)'"></span>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- Periodo + Resultados --}}
        <div class="rounded-3xl border border-slate-800 bg-slate-900/60 p-6 space-y-5">

            <div class="text-white font-extrabold">
                Periodo <span class="text-red-400">*</span>
                <span class="text-xs text-red-400 font-semibold">Ambas fechas requeridas</span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                <div>
                    <div class="text-xs text-gray-300 mb-2">Fecha Inicio</div>
                    <input type="date"
                           x-model="fechaInicio"
                           @change="applyFiltersDebounced()"
                           class="w-full px-4 py-4 rounded-2xl bg-slate-800 border border-slate-700 text-white focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                </div>

                <div>
                    <div class="text-xs text-gray-300 mb-2">Fecha Fin</div>
                    <input type="date"
                           x-model="fechaFin"
                           @change="applyFiltersDebounced()"
                           class="w-full px-4 py-4 rounded-2xl bg-slate-800 border border-slate-700 text-white focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                </div>
            </div>

            {{-- barra rango --}}
            <div class="rounded-2xl border border-slate-800 bg-slate-900/40 px-4 py-3 text-sm text-gray-200 flex items-center gap-2">
                <span class="text-blue-400">📅</span>
                <span x-text="rangeLabel()"></span>
            </div>

            {{-- estado: falta fechas --}}
            <div x-show="showNeedDates()"
                 class="rounded-3xl border border-orange-700 bg-orange-600/10 p-10 text-center"
                 style="display:none;">
                <div class="text-5xl mb-3 text-orange-400">🗓️</div>
                <div class="text-orange-300 font-extrabold">Selecciona ambas fechas para buscar</div>
                <div class="text-orange-200/80 text-sm mt-2">
                    Debes elegir tanto la fecha de inicio como la fecha de fin en la sección de Periodo
                </div>
            </div>

            {{-- resultados --}}
            <div x-show="canSearch()" style="display:none;" class="space-y-4">

                <div class="rounded-3xl border border-slate-800 bg-slate-900/40 p-5 flex items-center justify-between gap-4">
                    <div>
                        <div class="text-white font-extrabold text-sm">Resultados encontrados</div>
                        <div class="text-green-400 font-extrabold text-3xl">
                            <span x-text="results.length"></span> contenedores
                        </div>
                    </div>

                    <a :href="exportUrl()"
                       class="px-6 py-3 rounded-2xl bg-green-600 hover:bg-green-700 text-white font-extrabold inline-flex items-center gap-2">
                        ⬇ Exportar a Excel
                    </a>
                </div>

                <div class="rounded-3xl border border-slate-800 bg-slate-900/40 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-900/80 border-b border-slate-800">
                                <tr class="text-left text-gray-300">
                                    <th class="px-5 py-4 text-xs uppercase tracking-wider">Contenedor</th>
                                    <th class="px-5 py-4 text-xs uppercase tracking-wider">Cliente</th>
                                    <th class="px-5 py-4 text-xs uppercase tracking-wider">Fecha Arribo</th>
                                    <th class="px-5 py-4 text-xs uppercase tracking-wider">Fecha Registro</th>
                                    <th class="px-5 py-4 text-xs uppercase tracking-wider">Días transcurridos</th>
                                    <th class="px-5 py-4 text-xs uppercase tracking-wider">Estado</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-800">
                                <template x-for="row in results" :key="row.id">
                                    <tr class="hover:bg-slate-900/60">
                                        <td class="px-5 py-4 text-white font-semibold" x-text="row.numero_contenedor"></td>
                                        <td class="px-5 py-4 text-white" x-text="row.cliente"></td>
                                        <td class="px-5 py-4 text-white" x-text="row.fecha_arribo"></td>
                                        <td class="px-5 py-4 text-white" x-text="row.fecha_registro"></td>
                                        <td class="px-5 py-4 text-white" x-text="row.dias_transcurridos"></td>
                                        <td class="px-5 py-4 text-white" x-text="row.estado"></td>
                                    </tr>
                                </template>

                                <tr x-show="results.length === 0" style="display:none;">
                                    <td colspan="6" class="px-5 py-10 text-center text-gray-400">
                                        No se encontraron contenedores en el periodo seleccionado.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        <script>
            function reportesPage(templatesFromServer, filtersFromServer, initialResults) {
                return {
                    templates: templatesFromServer ?? [],
                    results: initialResults ?? [],

                    // state
                    selectedTemplate: filtersFromServer?.template ?? 'default:general',
                    advancedOpen: false,

                    fechaInicio: filtersFromServer?.from ?? '',
                    fechaFin: filtersFromServer?.to ?? '',

                    // filtros avanzados
                    selectedClientes: filtersFromServer?.clientes ?? [],
                    selectedContenedores: filtersFromServer?.contenedores ?? [],

                    clienteQuery: '',
                    contenedorQuery: '',

                    clienteSuggestions: [],
                    contenedorSuggestions: [],

                    clienteSuggestionsOpen: false,
                    contenedorSuggestionsOpen: false,

                    // timers
                    _tFetchClientes: null,
                    _tFetchConts: null,
                    _tApply: null,

                    init() {
                        // si ya había fechas, el controller ya trae results
                    },

                    // UX helpers
                    canSearch() { return !!this.fechaInicio && !!this.fechaFin; },
                    showNeedDates() { return (!this.fechaInicio || !this.fechaFin); },

                    rangeLabel() {
                        if (!this.fechaInicio && !this.fechaFin) return 'Selecciona un rango de fechas';
                        if (this.fechaInicio && !this.fechaFin) return `${this.fechaInicio} — ...`;
                        if (!this.fechaInicio && this.fechaFin) return `... — ${this.fechaFin}`;
                        return `${this.fechaInicio} — ${this.fechaFin}`;
                    },

                    activeFiltersCount() {
                        return (this.selectedClientes?.length ?? 0) + (this.selectedContenedores?.length ?? 0);
                    },

                    // Autocomplete: clientes
                    fetchClientesDebounced() {
                        clearTimeout(this._tFetchClientes);
                        this._tFetchClientes = setTimeout(() => this.fetchClientes(), 200);
                    },

                    async fetchClientes() {
                        const q = this.clienteQuery.trim();
                        if (q.length < 2) {
                            this.clienteSuggestions = [];
                            this.clienteSuggestionsOpen = false;
                            return;
                        }

                        try {
                            const url = `{{ route('reportes.autocomplete.clientes') }}?q=${encodeURIComponent(q)}`;
                            const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });

                            if (!res.ok) {
                                this.clienteSuggestions = [];
                                this.clienteSuggestionsOpen = false;
                                return;
                            }

                            const data = await res.json();
                            this.clienteSuggestions = (data ?? []).filter(x => !this.selectedClientes.includes(x));
                            this.clienteSuggestionsOpen = true;
                        } catch (e) {
                            this.clienteSuggestions = [];
                            this.clienteSuggestionsOpen = false;
                        }
                    },

                    selectCliente(value) {
                        if (!value) return;
                        if (!this.selectedClientes.includes(value)) this.selectedClientes.push(value);

                        this.clienteQuery = '';
                        this.clienteSuggestions = [];
                        this.clienteSuggestionsOpen = false;

                        this.applyFiltersDebounced();
                    },

                    addClienteFromQuery() {
                        const v = this.clienteQuery.trim();
                        if (!v) return;
                        this.selectCliente(v);
                    },

                    removeCliente(idx) {
                        this.selectedClientes.splice(idx, 1);
                        this.applyFiltersDebounced();
                    },

                    // Autocomplete: contenedores
                    fetchContenedoresDebounced() {
                        clearTimeout(this._tFetchConts);
                        this._tFetchConts = setTimeout(() => this.fetchContenedores(), 200);
                    },

                    async fetchContenedores() {
                        const q = this.contenedorQuery.trim();
                        if (q.length < 2) {
                            this.contenedorSuggestions = [];
                            this.contenedorSuggestionsOpen = false;
                            return;
                        }

                        try {
                            const url = `{{ route('reportes.autocomplete.contenedores') }}?q=${encodeURIComponent(q)}`;
                            const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });

                            if (!res.ok) {
                                this.contenedorSuggestions = [];
                                this.contenedorSuggestionsOpen = false;
                                return;
                            }

                            const data = await res.json();
                            this.contenedorSuggestions = (data ?? []).filter(x => !this.selectedContenedores.includes(x));
                            this.contenedorSuggestionsOpen = true;
                        } catch (e) {
                            this.contenedorSuggestions = [];
                            this.contenedorSuggestionsOpen = false;
                        }
                    },

                    selectContenedor(value) {
                        if (!value) return;
                        if (!this.selectedContenedores.includes(value)) this.selectedContenedores.push(value);

                        this.contenedorQuery = '';
                        this.contenedorSuggestions = [];
                        this.contenedorSuggestionsOpen = false;

                        this.applyFiltersDebounced();
                    },

                    addContenedorFromQuery() {
                        const v = this.contenedorQuery.trim();
                        if (!v) return;
                        this.selectContenedor(v);
                    },

                    removeContenedor(idx) {
                        this.selectedContenedores.splice(idx, 1);
                        this.applyFiltersDebounced();
                    },

                    // Aplicar filtros (recarga con querystring)
                    applyFiltersDebounced() {
                        clearTimeout(this._tApply);
                        this._tApply = setTimeout(() => this.applyFilters(), 250);
                    },

                    applyFilters() {
                        if (!this.canSearch()) return;

                        const params = new URLSearchParams({
                            template: this.selectedTemplate,
                            from: this.fechaInicio,
                            to: this.fechaFin,
                        });

                        (this.selectedClientes ?? []).forEach(c => params.append('clientes[]', c));
                        (this.selectedContenedores ?? []).forEach(x => params.append('contenedores[]', x));

                        window.location.href = `{{ route('reportes.index') }}?${params.toString()}`;
                    },

                    exportUrl() {
                        if (!this.canSearch()) return '#';

                        const params = new URLSearchParams({
                            template: this.selectedTemplate,
                            from: this.fechaInicio,
                            to: this.fechaFin,
                        });

                        (this.selectedClientes ?? []).forEach(c => params.append('clientes[]', c));
                        (this.selectedContenedores ?? []).forEach(x => params.append('contenedores[]', x));

                        return `{{ route('reportes.export') }}?${params.toString()}`;
                    },
                }
            }
        </script>
    </div>
</x-app-layout>
