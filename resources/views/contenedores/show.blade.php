@php
    $mode = $mode ?? request('mode', 'view');
    $tab  = $tab  ?? request('tab', 'registro');

    $isEdit = $mode === 'edit';

    $tabs = [
        'registro'   => ['label' => 'Registro', 'icon' => '📋'],
        'liberacion' => ['label' => 'Liberación', 'icon' => '🔓'],
        'docs'       => ['label' => 'Envío de Docs', 'icon' => '✈️'],
        'cotizacion' => ['label' => 'Cotización Agencia', 'icon' => '💲'],
        'despacho'   => ['label' => 'Despacho', 'icon' => '🚚'],
        'gastos'     => ['label' => 'Gastos', 'icon' => '🧾'],
    ];

    $activeTab = array_key_exists($tab, $tabs) ? $tab : 'registro';

    $panelClass = "rounded-3xl border border-slate-800 bg-slate-900/70";
    $fieldLabel = "block text-sm text-gray-300 mb-2";
    $inputClass = "w-full px-4 py-3 rounded-2xl bg-slate-800 border border-slate-700 text-white placeholder-gray-500
                   focus:ring-2 focus:ring-blue-600 focus:border-blue-600";
    $readonlyClass = "opacity-70 cursor-not-allowed";

    $lib = $contenedor->liberacion;
    $libGastos = $contenedor->gastosLiberacion ?? collect();
@endphp

<x-app-layout>
    <div class="space-y-6">

        {{-- Header superior --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-blue-600 flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4a2 2 0 0 0 1-1.73z"/>
                    </svg>
                </div>

                <div>
                    <div class="text-2xl font-extrabold text-white">
                        {{ $contenedor->numero_contenedor }}
                    </div>
                    <div class="text-sm text-gray-400">
                        Cliente: {{ $contenedor->cliente }}
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                @if(!$isEdit)
                    <a href="{{ route('contenedores.index') }}"
                       class="px-5 py-3 rounded-2xl bg-slate-800 hover:bg-slate-700 text-white font-semibold">
                        Cerrar
                    </a>

                    <a href="{{ route('contenedores.show', ['contenedor' => $contenedor->id, 'mode' => 'edit', 'tab' => $activeTab]) }}"
                       class="px-5 py-3 rounded-2xl bg-green-600 hover:bg-green-700 text-white font-semibold inline-flex items-center gap-2">
                        ✎ Editar Contenedor
                    </a>
                @else
                    <a href="{{ route('contenedores.show', ['contenedor' => $contenedor->id, 'mode' => 'view', 'tab' => $activeTab]) }}"
                       class="px-5 py-3 rounded-2xl bg-slate-800 hover:bg-slate-700 text-white font-semibold">
                        Cancelar
                    </a>

                    {{-- Guardar por pestaña (Liberación) --}}
                    @if($activeTab === 'liberacion')
                        <button form="form-liberacion"
                                class="px-5 py-3 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-semibold inline-flex items-center gap-2">
                            💾 Guardar Cambios
                        </button>
                    @else
                        <button class="px-5 py-3 rounded-2xl bg-slate-800 text-gray-400 font-semibold cursor-not-allowed" disabled>
                            💾 Guardar Cambios
                        </button>
                    @endif
                @endif
            </div>
        </div>

        {{-- Flash --}}
        @if(session('success'))
            <div class="p-4 rounded-2xl bg-green-50 border border-green-200 text-green-700">
                {{ session('success') }}
            </div>
        @endif

        {{-- Tabs --}}
        <div class="border-b border-slate-800">
            <div class="flex flex-wrap gap-6 text-gray-300">
                @foreach($tabs as $key => $t)
                    @php
                        $active = $activeTab === $key;
                        $tabUrl = route('contenedores.show', [
                            'contenedor' => $contenedor->id,
                            'mode' => $mode,
                            'tab'  => $key
                        ]);
                    @endphp

                    <a href="{{ $tabUrl }}"
                       class="py-4 inline-flex items-center gap-2 font-semibold
                              {{ $active ? 'text-blue-400 border-b-2 border-blue-500' : 'hover:text-white' }}">
                        <span>{{ $t['icon'] }}</span>
                        <span>{{ $t['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Panel principal --}}
        <div class="{{ $panelClass }} p-6">
            <div class="flex items-center justify-between mb-6">
                <div class="text-xl font-extrabold text-white">
                    {{ $tabs[$activeTab]['label'] }}
                </div>

                @if($isEdit && $activeTab === 'registro')
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-800 border border-slate-700 text-gray-200">
                        Solo lectura
                    </span>
                @endif
            </div>

            {{-- REGISTRO (read-only siempre) --}}
            @if($activeTab === 'registro')
                @php
                    $box = "p-5 rounded-2xl border border-slate-700 bg-slate-800/40";
                    $label = "text-sm text-gray-400";
                    $val = "mt-2 text-lg font-semibold text-white";
                @endphp

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                    <div class="{{ $box }}">
                        <div class="{{ $label }}">Contenedor/Guía</div>
                        <div class="{{ $val }}">{{ $contenedor->numero_contenedor }}</div>
                    </div>
                    <div class="{{ $box }}">
                        <div class="{{ $label }}">Cliente</div>
                        <div class="{{ $val }}">{{ $contenedor->cliente }}</div>
                    </div>
                    <div class="{{ $box }}">
                        <div class="{{ $label }}">Fecha de Llegada</div>
                        <div class="{{ $val }}">{{ optional($contenedor->fecha_llegada)->format('Y-m-d') }}</div>
                    </div>
                    <div class="{{ $box }}">
                        <div class="{{ $label }}">Proveedor</div>
                        <div class="{{ $val }}">{{ $contenedor->proveedor }}</div>
                    </div>
                    <div class="{{ $box }}">
                        <div class="{{ $label }}">Naviera</div>
                        <div class="{{ $val }}">{{ $contenedor->naviera }}</div>
                    </div>
                    <div class="{{ $box }}">
                        <div class="{{ $label }}">Mercancía Recibida</div>
                        <div class="{{ $val }}">{{ $contenedor->mercancia_recibida }}</div>
                    </div>
                </div>
            @endif

            {{-- LIBERACION --}}
            @if($activeTab === 'liberacion')
                @php
                    $canEdit = $isEdit;
                    $dis = $canEdit ? '' : 'disabled';
                @endphp

                <form id="form-liberacion"
                      method="POST"
                      action="{{ route('contenedores.liberacion.update', ['contenedor' => $contenedor->id]) }}"
                      class="space-y-6"
                      x-data="liberacionForm({
                        revalidacion: {{ (int)($lib?->revalidacion ?? 0) }},
                        gastos: @js($libGastos->map(fn($g)=>['descripcion'=>$g->descripcion,'monto'=>(float)$g->monto])->values()),
                      })">
                    @csrf
                    @method('PUT')

                    {{-- Top: Naviera + Dias libres --}}
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                        <div>
                            <label class="{{ $fieldLabel }}">Naviera</label>
                            <input {{ $dis }}
                                   name="naviera"
                                   value="{{ old('naviera', $lib?->naviera) }}"
                                   class="{{ $inputClass }} {{ !$canEdit ? $readonlyClass : '' }}"
                                   placeholder="Ej: Marítima Global SA">
                        </div>

                        <div>
                            <label class="{{ $fieldLabel }}">Días Libres de Pago</label>
                            <input {{ $dis }}
                                   type="number"
                                   min="0"
                                   name="dias_libres"
                                   value="{{ old('dias_libres', $lib?->dias_libres) }}"
                                   class="{{ $inputClass }} {{ !$canEdit ? $readonlyClass : '' }}"
                                   placeholder="Ej: 7">
                        </div>
                    </div>

                    {{-- Revalidación checkbox + fecha --}}
                    <div class="flex items-center gap-3">
                        <input {{ $dis }}
                               type="checkbox"
                               name="revalidacion"
                               value="1"
                               x-model="revalidacion"
                               class="w-5 h-5 rounded border-slate-700 bg-slate-800 text-blue-600">
                        <span class="text-gray-200 font-semibold">Revalidación</span>

                        <div class="flex-1"></div>

                        <div class="w-full lg:w-1/2">
                            <label class="{{ $fieldLabel }}">Fecha de Revalidación</label>
                            <input {{ $dis }}
                                   type="date"
                                   name="fecha_revalidacion"
                                   :disabled="!revalidacion || {{ $canEdit ? 'false':'true' }}"
                                   value="{{ old('fecha_revalidacion', optional($lib?->fecha_revalidacion)->format('Y-m-d')) }}"
                                   class="{{ $inputClass }} {{ !$canEdit ? $readonlyClass : '' }}">
                        </div>
                    </div>

                    {{-- Costo liberación + fecha liberación --}}
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                        <div>
                            <label class="{{ $fieldLabel }}">Costo de Liberación</label>
                            <input {{ $dis }}
                                   type="number"
                                   step="0.01"
                                   min="0"
                                   name="costo_liberacion"
                                   value="{{ old('costo_liberacion', $lib?->costo_liberacion) }}"
                                   class="{{ $inputClass }} {{ !$canEdit ? $readonlyClass : '' }}"
                                   placeholder="$ 0.00">
                        </div>

                        <div>
                            <label class="{{ $fieldLabel }}">Fecha de Liberación</label>
                            <input {{ $dis }}
                                   type="date"
                                   name="fecha_liberacion"
                                   value="{{ old('fecha_liberacion', optional($lib?->fecha_liberacion)->format('Y-m-d')) }}"
                                   class="{{ $inputClass }} {{ !$canEdit ? $readonlyClass : '' }}">
                        </div>
                    </div>

                    {{-- Garantía + fecha garantía --}}
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                        <div>
                            <label class="{{ $fieldLabel }}">Garantía (Monto)</label>
                            <input {{ $dis }}
                                   type="number"
                                   step="0.01"
                                   min="0"
                                   name="garantia"
                                   value="{{ old('garantia', $lib?->garantia) }}"
                                   class="{{ $inputClass }} {{ !$canEdit ? $readonlyClass : '' }}"
                                   placeholder="$ 0.00">
                        </div>

                        <div>
                            <label class="{{ $fieldLabel }}">Fecha de Garantía</label>
                            <input {{ $dis }}
                                   type="date"
                                   name="fecha_garantia"
                                   value="{{ old('fecha_garantia', optional($lib?->fecha_garantia)->format('Y-m-d')) }}"
                                   class="{{ $inputClass }} {{ !$canEdit ? $readonlyClass : '' }}">
                        </div>
                    </div>

                    {{-- Devolución de garantía + Flete --}}
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                        <div>
                            <label class="{{ $fieldLabel }}">Devolución de Garantía</label>
                            <select {{ $dis }}
                                    name="devolucion_garantia"
                                    class="{{ $inputClass }} {{ !$canEdit ? $readonlyClass : '' }}">
                                <option value="">Seleccione un estado</option>
                                <option value="pendiente" @selected(old('devolucion_garantia', $lib?->devolucion_garantia) === 'pendiente')>Pendiente</option>
                                <option value="entregado" @selected(old('devolucion_garantia', $lib?->devolucion_garantia) === 'entregado')>Entregado</option>
                            </select>
                        </div>

                        <div>
                            <label class="{{ $fieldLabel }}">Flete Marítimo</label>
                            <input {{ $dis }}
                                   type="number"
                                   step="0.01"
                                   min="0"
                                   name="flete_maritimo"
                                   value="{{ old('flete_maritimo', $lib?->flete_maritimo) }}"
                                   class="{{ $inputClass }} {{ !$canEdit ? $readonlyClass : '' }}"
                                   placeholder="$ 0.00">
                        </div>
                    </div>

                    {{-- Demora parcial + fecha demora --}}
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                        <div>
                            <label class="{{ $fieldLabel }}">Costo Demora Parcial</label>
                            <input {{ $dis }}
                                   type="number"
                                   step="0.01"
                                   min="0"
                                   name="costos_demora"
                                   value="{{ old('costos_demora', $lib?->costos_demora) }}"
                                   class="{{ $inputClass }} {{ !$canEdit ? $readonlyClass : '' }}"
                                   placeholder="$ 0.00">
                        </div>

                        <div>
                            <label class="{{ $fieldLabel }}">Fecha</label>
                            <input {{ $dis }}
                                   type="date"
                                   name="fecha_demora"
                                   value="{{ old('fecha_demora', optional($lib?->fecha_demora)->format('Y-m-d')) }}"
                                   class="{{ $inputClass }} {{ !$canEdit ? $readonlyClass : '' }}">
                        </div>
                    </div>

                    {{-- Fecha flete (en tu captura sale abajo a la derecha como "Fecha") --}}
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                        <div></div>
                        <div>
                            <label class="{{ $fieldLabel }}">Fecha</label>
                            <input {{ $dis }}
                                   type="date"
                                   name="fecha_flete"
                                   value="{{ old('fecha_flete', optional($lib?->fecha_flete)->format('Y-m-d')) }}"
                                   class="{{ $inputClass }} {{ !$canEdit ? $readonlyClass : '' }}">
                        </div>
                    </div>

                    {{-- GASTOS ADICIONALES --}}
                    <div class="rounded-2xl border border-slate-700 bg-slate-800/30 p-5">
                        <div class="flex items-center justify-between">
                            <div class="text-white font-extrabold">Gastos Adicionales</div>

                            <div class="flex items-center gap-3">
                                <div class="px-4 py-2 rounded-2xl border border-slate-700 bg-slate-800 text-emerald-400 font-bold">
                                    $ Total: <span x-text="totalFormatted()"></span>
                                </div>

                                <button type="button"
                                        @click="addGasto()"
                                        class="px-4 py-2 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-semibold inline-flex items-center gap-2"
                                        {{ $canEdit ? '' : 'disabled' }}
                                        :class="{{ $canEdit ? '""' : "'opacity-50 cursor-not-allowed'" }}">
                                    + Agregar Gasto
                                </button>
                            </div>
                        </div>

                        <div class="mt-4 space-y-3">
                            {{-- Sin gastos --}}
                            <template x-if="gastos.length === 0">
                                <div class="text-center text-gray-400 py-8">
                                    No hay gastos adicionales registrados
                                </div>
                            </template>

                            {{-- Filas --}}
                            <template x-for="(g, idx) in gastos" :key="idx">
                                <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 items-center">
                                    <div class="lg:col-span-9">
                                        <input type="text"
                                               :name="`gastos[${idx}][descripcion]`"
                                               x-model="g.descripcion"
                                               class="{{ $inputClass }}"
                                               placeholder="Descripción del gasto"
                                               {{ $canEdit ? '' : 'disabled' }}
                                               :class="{{ $canEdit ? '""' : "'opacity-70 cursor-not-allowed'" }}">
                                    </div>

                                    <div class="lg:col-span-2">
                                        <input type="number"
                                               step="0.01"
                                               min="0"
                                               :name="`gastos[${idx}][monto]`"
                                               x-model="g.monto"
                                               class="{{ $inputClass }}"
                                               placeholder="$ 0.00"
                                               {{ $canEdit ? '' : 'disabled' }}
                                               :class="{{ $canEdit ? '""' : "'opacity-70 cursor-not-allowed'" }}">
                                    </div>

                                    <div class="lg:col-span-1 flex justify-end">
                                        <button type="button"
                                                @click="removeGasto(idx)"
                                                class="text-red-400 hover:text-red-300"
                                                title="Eliminar"
                                                {{ $canEdit ? '' : 'disabled' }}
                                                :class="{{ $canEdit ? '""' : "'opacity-50 cursor-not-allowed'" }}">
                                            🗑️
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </form>

                <script>
                    function liberacionForm({ revalidacion, gastos }) {
                        return {
                            revalidacion: !!revalidacion,
                            gastos: (gastos && gastos.length) ? gastos : [],
                            addGasto() {
                                this.gastos.push({ descripcion: '', monto: 0 });
                            },
                            removeGasto(i) {
                                this.gastos.splice(i, 1);
                            },
                            total() {
                                return this.gastos.reduce((sum, g) => {
                                    const n = parseFloat(g.monto ?? 0);
                                    return sum + (isNaN(n) ? 0 : n);
                                }, 0);
                            },
                            totalFormatted() {
                                return this.total().toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                            }
                        }
                    }
                </script>
            @endif

            {{-- OTRAS pestañas (placeholder) --}}
            @if(in_array($activeTab, ['docs','cotizacion','despacho','gastos']))
                <div class="p-6 rounded-2xl bg-slate-800/40 border border-slate-700 text-gray-200">
                    <div class="text-sm text-gray-400 mb-2">
                        Esta pestaña la habilitamos cuando me pases los campos.
                    </div>
                    <div class="font-semibold">
                        Modo actual: {{ $isEdit ? 'Editar' : 'Ver' }}
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
