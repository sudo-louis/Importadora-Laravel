{{-- resources/views/plantillas/index.blade.php --}}
<x-app-layout>
    @php
        $defaultTemplates = [
            [
                'title' => 'Reporte Financiero',
                'icon'  => '💲',
                'desc'  => 'Incluye: Cotizaciones de agencia, todos los gastos (flete, maniobras, almacenaje, demoras, reparaciones), y totales calculados',
                'tags'  => ['Cotización','Gastos'],
            ],
            [
                'title' => 'Reporte General',
                'icon'  => '🧾',
                'desc'  => 'Incluye: Datos básicos del contenedor, cliente, mercancía, fechas de arribo, naviera, estado, y datos de auditoría (quién y cuándo registró)',
                'tags'  => ['Registro'],
            ],
            [
                'title' => 'Reporte de Trazabilidad',
                'icon'  => '🕒',
                'desc'  => 'Incluye: Todas las fechas clave (liberación, envío de docs, cotización, despacho, pago, modulación), tiempos de proceso, y seguimiento completo del contenedor',
                'tags'  => ['Liberación','Envío Docs','Despacho'],
            ],
        ];

        $sections = [
            'registro' => [
                'label' => 'Registro',
                'icon'  => '📄',
                'fields'=> [
                    ['key'=>'numero_contenedor','label'=>'Número de Contenedor'],
                    ['key'=>'cliente','label'=>'Cliente'],
                    ['key'=>'fecha_arribo','label'=>'Fecha de Arribo'],
                    ['key'=>'proveedor','label'=>'Proveedor'],
                    ['key'=>'mercancia_recibida','label'=>'Mercancía Recibida'],
                    ['key'=>'naviera','label'=>'Naviera'],
                    ['key'=>'booking','label'=>'Booking'],
                    ['key'=>'peso','label'=>'Peso'],
                    ['key'=>'registrado_por','label'=>'Registrado por'],
                    ['key'=>'fecha_registro','label'=>'Fecha de Registro'],
                ],
            ],
            'liberacion' => [
                'label' => 'Liberación',
                'icon'  => '🔓',
                'fields'=> [
                    ['key'=>'dias_libres','label'=>'Días Libres'],
                    ['key'=>'revalidacion','label'=>'Revalidación'],
                    ['key'=>'fecha_revalidacion','label'=>'Fecha Revalidación'],
                    ['key'=>'costo_liberacion','label'=>'Costo Liberación'],
                    ['key'=>'fecha_liberacion','label'=>'Fecha Liberación'],
                    ['key'=>'garantia','label'=>'Garantía'],
                    ['key'=>'fecha_garantia','label'=>'Fecha Garantía'],
                    ['key'=>'devolucion_garantia','label'=>'Devolución Garantía'],
                    ['key'=>'costos_demora','label'=>'Costos Demora'],
                    ['key'=>'fecha_demora','label'=>'Fecha Demora'],
                    ['key'=>'flete_maritimo','label'=>'Flete Marítimo'],
                    ['key'=>'fecha_flete','label'=>'Fecha Flete'],
                    ['key'=>'gastos_liberacion','label'=>'Gastos (Liberación)'],
                ],
            ],
            'docs' => [
                'label' => 'Envío de Docs',
                'icon'  => '✈️',
                'fields'=> [
                    ['key'=>'docs_enviados','label'=>'Documentos Enviados'],
                    ['key'=>'fecha_envio','label'=>'Fecha de Envío'],
                ],
            ],
            'cotizacion' => [
                'label' => 'Cotización',
                'icon'  => '💲',
                'fields'=> [
                    ['key'=>'fecha_pago','label'=>'Fecha de Pago'],
                    ['key'=>'impuestos','label'=>'Impuestos'],
                    ['key'=>'honorarios','label'=>'Honorarios'],
                    ['key'=>'maniobras','label'=>'Maniobras'],
                    ['key'=>'almacenaje','label'=>'Almacenaje'],
                    ['key'=>'total','label'=>'Total Cotización'],
                ],
            ],
            'despacho' => [
                'label' => 'Despacho',
                'icon'  => '🚚',
                'fields'=> [
                    ['key'=>'numero_pedimento','label'=>'No. Pedimento'],
                    ['key'=>'clave_pedimento','label'=>'Clave de Pedimento'],
                    ['key'=>'importador','label'=>'Importador'],
                    ['key'=>'tipo_carga','label'=>'Tipo de Carga'],
                    ['key'=>'fecha_carga','label'=>'Fecha de Carga'],
                    ['key'=>'reconocimiento_aduanero','label'=>'Reconocimiento Aduanero'],
                    ['key'=>'fecha_pago_despacho','label'=>'Fecha de Pago'],
                    ['key'=>'fecha_modulacion','label'=>'Fecha de Modulación'],
                    ['key'=>'fecha_entrega','label'=>'Fecha de Entrega'],
                ],
            ],
            'gastos' => [
                'label' => 'Gastos',
                'icon'  => '🧾',
                'fields'=> [
                    ['key'=>'gastos_generales','label'=>'Gastos Generales'],
                    ['key'=>'total_gastos','label'=>'Total de Gastos'],
                ],
            ],
        ];

        // Plantillas personalizadas vienen del controlador: $plantillas
        // Para editar: mandamos a JS (id, nombre, descripcion, campos[])
        $plantillasForJs = ($plantillas ?? collect())->map(function($p){
            return [
                'id' => $p->id,
                'nombre' => $p->nombre,
                'descripcion' => $p->descripcion,
                'campos' => $p->campos->pluck('campo')->values()->all(),
            ];
        })->values();
    @endphp

    <div class="p-6 space-y-8">

        {{-- Flash --}}
        @if(session('success'))
            <div class="p-4 rounded-2xl bg-green-50 border border-green-200 text-green-700">
                {{ session('success') }}
            </div>
        @endif

        {{-- Header --}}
        <div>
            <h1 class="text-3xl font-extrabold text-white">Gestión de Plantillas</h1>
            <p class="mt-2 text-gray-400">Administra las plantillas de reportes del sistema</p>
        </div>

        {{-- Plantillas por defecto --}}
        <div class="space-y-4">
            <div class="text-white font-extrabold text-lg">Plantillas por Defecto</div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                @foreach($defaultTemplates as $t)
                    <div class="rounded-3xl border border-slate-800 bg-slate-900/70 p-6">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-blue-600/20 border border-blue-500/20 flex items-center justify-center text-xl">
                                {{ $t['icon'] }}
                            </div>
                            <div class="flex-1">
                                <div class="text-white font-extrabold">{{ $t['title'] }}</div>
                                <div class="mt-2 text-sm text-gray-400 leading-relaxed">
                                    {{ $t['desc'] }}
                                </div>

                                <div class="mt-4 flex flex-wrap gap-2">
                                    @foreach($t['tags'] as $tag)
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-800 border border-slate-700 text-gray-200">
                                            {{ $tag }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Plantillas personalizadas --}}
        <div class="space-y-4" x-data="plantillasCrud(@js($sections), @js($plantillasForJs))">
            <div class="flex items-center justify-between">
                <div class="text-white font-extrabold text-lg">Plantillas Personalizadas</div>

                <button
                    type="button"
                    class="px-5 py-3 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-semibold inline-flex items-center gap-2"
                    @click="openCreate()"
                >
                    ＋ Agregar Plantilla
                </button>
            </div>

            <div class="rounded-3xl border border-slate-800 bg-slate-900/50 p-8">
                @if(($plantillas ?? collect())->isEmpty())
                    <div class="rounded-2xl border border-dashed border-slate-700 bg-slate-900/30 p-10 text-center text-gray-400">
                        <div class="text-5xl mb-4 opacity-40">📄</div>
                        No hay plantillas personalizadas creadas
                    </div>
                @else
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        @foreach($plantillas as $p)
                            <div class="rounded-3xl border border-slate-800 bg-slate-900/70 p-6">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex items-start gap-4">
                                        <div class="w-12 h-12 rounded-2xl bg-slate-800 border border-slate-700 flex items-center justify-center text-xl">
                                            🧩
                                        </div>
                                        <div>
                                            <div class="text-white font-extrabold">{{ $p->nombre }}</div>
                                            <div class="mt-1 text-xs text-gray-400">
                                                {{ $p->campos->count() }} campos seleccionados
                                            </div>
                                            @if($p->descripcion)
                                                <div class="mt-2 text-sm text-gray-400 leading-relaxed">
                                                    {{ $p->descripcion }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <button type="button"
                                                class="px-3 py-2 rounded-2xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold border border-slate-700"
                                                @click="openEdit({{ $p->id }})">
                                            ✎ Editar
                                        </button>

                                        <form method="POST" action="{{ route('plantillas.destroy', $p->id) }}"
                                              onsubmit="return confirm('¿Eliminar esta plantilla?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="px-3 py-2 rounded-2xl bg-red-600/20 hover:bg-red-600/30 text-red-200 text-xs font-semibold border border-red-500/30">
                                                🗑️ Eliminar
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                {{-- mini tags (primeros 4) --}}
                                <div class="mt-4 flex flex-wrap gap-2">
                                    @foreach($p->campos->take(4) as $c)
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-800 border border-slate-700 text-gray-200">
                                            {{ $c->campo }}
                                        </span>
                                    @endforeach
                                    @if($p->campos->count() > 4)
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-800 border border-slate-700 text-gray-400">
                                            +{{ $p->campos->count() - 4 }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Modal --}}
            <div>
                {{-- Overlay --}}
                <div
                    x-show="openModal"
                    x-transition.opacity
                    class="fixed inset-0 z-40 bg-black/70 backdrop-blur-sm"
                    @click="close()"
                    style="display:none;"
                ></div>

                {{-- Dialog --}}
                <div
                    x-show="openModal"
                    x-transition
                    class="fixed inset-0 z-50 flex items-center justify-center p-4"
                    style="display:none;"
                >
                    <div
                        class="w-full max-w-3xl rounded-3xl border border-slate-800 bg-slate-950 shadow-2xl overflow-hidden"
                        @click.stop
                    >
                        {{-- Header --}}
                        <div class="p-6 border-b border-slate-800 bg-slate-950">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <div class="text-white text-2xl font-extrabold"
                                         x-text="isEdit ? 'Editar Plantilla' : 'Crear Plantilla Personalizada'"></div>
                                    <div class="text-gray-400 text-sm mt-1">Selecciona los campos que deseas incluir en tu reporte</div>
                                </div>

                                <button type="button"
                                        class="w-10 h-10 rounded-2xl bg-slate-900 border border-slate-800 text-gray-300 hover:text-white hover:bg-slate-800 flex items-center justify-center"
                                        @click="close()"
                                        aria-label="Cerrar">✕</button>
                            </div>
                        </div>

                        {{-- Body --}}
                        <div class="p-6 bg-slate-950">
                            <div class="max-h-[70vh] overflow-y-auto pr-2">

                                <div class="mb-4">
                                    <label class="block text-sm text-gray-300 mb-2">
                                        Nombre de la Plantilla <span class="text-red-400">*</span>
                                    </label>
                                    <input type="text"
                                           x-model="templateName"
                                           class="w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-800 text-white placeholder-gray-500 focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                                           placeholder="Ej: Reporte Ejecutivo, Reporte de Clientes..." />
                                </div>

                                <div class="mb-6">
                                    <label class="block text-sm text-gray-300 mb-2">
                                        Descripción (opcional)
                                    </label>
                                    <input type="text"
                                           x-model="templateDesc"
                                           class="w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-800 text-white placeholder-gray-500 focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                                           placeholder="Ej: Reporte para gerencia, incluye lo esencial..." />
                                </div>

                                {{-- Acordeones --}}
                                <div class="space-y-4">
                                    <template x-for="(sec, key) in sections" :key="key">
                                        <div class="rounded-3xl border border-slate-800 bg-slate-900/60 overflow-hidden">

                                            <div class="px-5 py-4 flex items-center justify-between gap-4 hover:bg-slate-900/80 cursor-pointer select-none"
                                                 @click="toggle(key)">
                                                <div class="flex items-center gap-4">
                                                    <div class="w-11 h-11 rounded-2xl bg-slate-950 border border-slate-800 flex items-center justify-center text-xl">
                                                        <span x-text="sec.icon"></span>
                                                    </div>
                                                    <div class="text-left">
                                                        <div class="text-white font-extrabold" x-text="sec.label"></div>
                                                        <div class="text-xs text-gray-400">
                                                            <span x-text="selectedCount(key)"></span>/<span x-text="sec.fields.length"></span> seleccionados
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="flex items-center gap-3">
                                                    <button type="button"
                                                            class="px-4 py-2 rounded-2xl bg-slate-950 border border-slate-800 text-gray-200 text-xs font-semibold hover:bg-slate-800"
                                                            @click.stop="toggleAll(key)">
                                                        Seleccionar todo
                                                    </button>

                                                    <button type="button"
                                                            class="w-10 h-10 rounded-2xl bg-slate-950 border border-slate-800 flex items-center justify-center text-gray-300"
                                                            @click.stop="toggle(key)"
                                                            aria-label="Desplegar">
                                                        <span x-text="open[key] ? '▴' : '▾'"></span>
                                                    </button>
                                                </div>
                                            </div>

                                            <div x-show="open[key]"
                                                 x-transition.opacity.duration.150ms
                                                 x-transition.scale.origin.top.duration.150ms
                                                 class="px-5 pb-5"
                                                 style="display:none;">
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-2">
                                                    <template x-for="f in sec.fields" :key="f.key">
                                                        <label class="rounded-2xl border border-slate-800 bg-slate-950/60 px-4 py-3 flex items-center gap-3 cursor-pointer hover:bg-slate-900"
                                                               :class="isChecked(key, f.key) ? 'ring-2 ring-blue-600 border-blue-700' : ''">
                                                            <input type="checkbox"
                                                                   class="w-5 h-5 rounded border-slate-700 bg-slate-900 text-blue-600"
                                                                   :checked="isChecked(key, f.key)"
                                                                   @change="toggleField(key, f.key)" />
                                                            <span class="text-gray-200 font-semibold text-sm" x-text="f.label"></span>
                                                        </label>
                                                    </template>
                                                </div>
                                            </div>

                                        </div>
                                    </template>
                                </div>

                                <div class="mt-6 rounded-2xl border border-slate-800 bg-slate-900/40 px-5 py-4 text-gray-200">
                                    <span class="text-gray-300">Total de campos seleccionados:</span>
                                    <span class="font-extrabold text-white" x-text="totalSelected()"></span>
                                </div>

                            </div>
                        </div>

                        {{-- Footer --}}
                        <div class="p-6 border-t border-slate-800 bg-slate-950 flex items-center justify-end gap-3">
                            <button type="button"
                                    class="px-5 py-3 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-semibold border border-slate-800"
                                    @click="close()">Cancelar</button>

                            <button type="button"
                                    class="px-6 py-3 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold"
                                    @click="submit()">
                                Guardar Plantilla
                            </button>
                        </div>

                        {{-- Hidden forms --}}
                        <form x-ref="createForm" method="POST" action="{{ route('plantillas.store') }}" class="hidden">
                            @csrf
                            <input type="hidden" name="nombre" :value="templateName">
                            <input type="hidden" name="descripcion" :value="templateDesc">
                            <template x-for="c in flattenedCampos()" :key="c">
                                <input type="hidden" name="campos[]" :value="c">
                            </template>
                        </form>

                        <form x-ref="editForm" method="POST" :action="editActionUrl()" class="hidden">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="nombre" :value="templateName">
                            <input type="hidden" name="descripcion" :value="templateDesc">
                            <template x-for="c in flattenedCampos()" :key="c">
                                <input type="hidden" name="campos[]" :value="c">
                            </template>
                        </form>

                    </div>
                </div>
            </div>

        </div>

        <script>
            function plantillasCrud(sectionsFromPhp, plantillasFromPhp) {
                return {
                    // modal
                    openModal: false,
                    isEdit: false,
                    editId: null,

                    // data
                    templateName: '',
                    templateDesc: '',
                    sections: sectionsFromPhp,
                    plantillas: plantillasFromPhp,

                    open: {},
                    selected: {},

                    initSets() {
                        for (const key in this.sections) {
                            if (this.open[key] === undefined) this.open[key] = false;
                            if (!this.selected[key]) this.selected[key] = new Set();
                        }
                    },

                    reset() {
                        this.templateName = '';
                        this.templateDesc = '';
                        this.isEdit = false;
                        this.editId = null;

                        this.open = {};
                        this.selected = {};
                        this.initSets();
                    },

                    openCreate() {
                        this.reset();
                        this.openModal = true;
                    },

                    openEdit(id) {
                        this.reset();
                        this.isEdit = true;
                        this.editId = id;

                        const tpl = this.plantillas.find(p => p.id === id);
                        if (!tpl) return;

                        this.templateName = tpl.nombre ?? '';
                        this.templateDesc = tpl.descripcion ?? '';

                        // campos guardados vienen "seccion.campo"
                        // ejemplo: "registro.cliente"
                        for (const full of (tpl.campos ?? [])) {
                            const [sec, field] = String(full).split('.', 2);
                            if (sec && field && this.selected[sec]) this.selected[sec].add(field);
                        }

                        this.openModal = true;
                    },

                    close() {
                        this.openModal = false;
                    },

                    toggle(key) {
                        this.open[key] = !this.open[key];
                    },

                    isChecked(sectionKey, fieldKey) {
                        return this.selected[sectionKey].has(fieldKey);
                    },

                    toggleField(sectionKey, fieldKey) {
                        if (this.selected[sectionKey].has(fieldKey)) {
                            this.selected[sectionKey].delete(fieldKey);
                        } else {
                            this.selected[sectionKey].add(fieldKey);
                        }
                    },

                    selectedCount(sectionKey) {
                        return this.selected[sectionKey].size;
                    },

                    toggleAll(sectionKey) {
                        const fields = this.sections[sectionKey].fields.map(f => f.key);
                        const allSelected = this.selected[sectionKey].size === fields.length;
                        this.selected[sectionKey] = allSelected ? new Set() : new Set(fields);
                    },

                    totalSelected() {
                        let n = 0;
                        for (const k in this.selected) n += this.selected[k].size;
                        return n;
                    },

                    flattenedCampos() {
                        // Para BD: guardamos "seccion.campo"
                        const out = [];
                        for (const sec in this.selected) {
                            for (const field of this.selected[sec]) {
                                out.push(`${sec}.${field}`);
                            }
                        }
                        return out;
                    },

                    editActionUrl() {
                        // arma /plantillas/{id}
                        return `{{ url('/plantillas') }}/${this.editId}`;
                    },

                    submit() {
                        if (!this.templateName.trim()) {
                            alert('Ingresa un nombre para la plantilla.');
                            return;
                        }

                        if (this.flattenedCampos().length === 0) {
                            alert('Selecciona al menos un campo.');
                            return;
                        }

                        if (this.isEdit) {
                            this.$refs.editForm.submit();
                        } else {
                            this.$refs.createForm.submit();
                        }
                    },

                    // Alpine init hook
                    init() {
                        this.initSets();
                    }
                }
            }
        </script>

    </div>
</x-app-layout>
