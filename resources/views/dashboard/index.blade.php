<x-app-layout>
    @php
        $panelClass = "rounded-3xl border border-slate-800 bg-slate-900/70";
        $softPanel  = "rounded-3xl border border-slate-800 bg-slate-900/50";
        $rowClass   = "rounded-2xl border border-slate-800 bg-slate-800/30";
        $chipBase   = "px-3 py-1 rounded-full text-xs font-bold border";
        $btnIcon    = "w-10 h-10 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white inline-flex items-center justify-center";
    @endphp

    <div class="space-y-6">

        {{-- KPIs --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
            {{-- Contenedores Activos --}}
            <div class="{{ $panelClass }} p-6">
                <div class="flex items-start justify-between">
                    <div class="w-12 h-12 rounded-2xl bg-slate-800 flex items-center justify-center text-white">
                        📦
                    </div>
                    <div class="text-green-400 text-sm font-bold">↑ 12%</div>
                </div>
                <div class="mt-4 text-3xl font-extrabold text-white">{{ $contenedoresActivos }}</div>
                <div class="text-sm text-gray-300 mt-1">Contenedores Activos</div>
                <div class="text-xs text-gray-500 mt-2">Pendiente + En proceso</div>
            </div>

            {{-- Garantías Pendientes --}}
            <div class="{{ $panelClass }} p-6">
                <div class="flex items-start justify-between">
                    <div class="w-12 h-12 rounded-2xl bg-slate-800 flex items-center justify-center text-white">
                        🧾
                    </div>
                    <div class="text-red-400 text-sm font-bold">↑ 5%</div>
                </div>
                <div class="mt-4 text-3xl font-extrabold text-white">{{ $garantiasPendientes }}</div>
                <div class="text-sm text-gray-300 mt-1">Garantías Pendientes</div>
                <div class="text-xs text-gray-500 mt-2">Devolución: pendiente</div>
            </div>

            {{-- Revalidaciones --}}
            <div class="{{ $panelClass }} p-6">
                <div class="flex items-start justify-between">
                    <div class="w-12 h-12 rounded-2xl bg-slate-800 flex items-center justify-center text-white">
                        📈
                    </div>
                    <div class="text-yellow-400 text-sm font-bold">↓ 3%</div>
                </div>
                <div class="mt-4 text-3xl font-extrabold text-white">{{ $revalidacionesPendientes }}</div>
                <div class="text-sm text-gray-300 mt-1">Revalidaciones</div>
                <div class="text-xs text-gray-500 mt-2">Requieren atención</div>
            </div>

            {{-- Sin Envío de Documentos --}}
            <div class="{{ $panelClass }} p-6">
                <div class="flex items-start justify-between">
                    <div class="w-12 h-12 rounded-2xl bg-slate-800 flex items-center justify-center text-white">
                        📄
                    </div>
                    <div class="text-red-400 text-sm font-bold">↑ 8%</div>
                </div>
                <div class="mt-4 text-3xl font-extrabold text-white">{{ $sinEnvioDocs }}</div>
                <div class="text-sm text-gray-300 mt-1">Sin Envío de Documentos</div>
                <div class="text-xs text-gray-500 mt-2">Documentación pendiente</div>
            </div>
        </div>

        {{-- Contenedores recientes --}}
        <div class="{{ $softPanel }} p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="text-white font-extrabold text-lg">Contenedores Recientes</div>
                <a href="{{ route('contenedores.index') }}" class="text-blue-400 hover:text-blue-300 text-sm font-bold">
                    Ver todos →
                </a>
            </div>

            <div class="space-y-3">
                @forelse($recientes as $c)
                    @php
                        $estado = $c->estado;
                        $chip = match($estado) {
                            'pendiente' => "bg-blue-50/10 border-blue-200/30 text-blue-300",
                            'en_proceso' => "bg-yellow-50/10 border-yellow-200/30 text-yellow-300",
                            'entregado' => "bg-green-50/10 border-green-200/30 text-green-300",
                            default => "bg-slate-50/10 border-slate-200/20 text-slate-300",
                        };
                    @endphp

                    <div class="{{ $rowClass }} p-5 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-blue-600/20 border border-blue-500/30 flex items-center justify-center text-white">
                                🧊
                            </div>

                            <div>
                                <div class="text-white font-extrabold">{{ $c->numero_contenedor }}</div>
                                <div class="text-sm text-gray-400">{{ $c->cliente }}</div>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <span class="{{ $chipBase }} {{ $chip }}">
                                {{ $c->estado_label }}
                            </span>
                            <div class="text-xs text-gray-500">
                                {{ optional($c->fecha_llegada)->format('Y-m-d') ?? '—' }}
                            </div>

                            <a class="{{ $btnIcon }}"
                               href="{{ route('contenedores.show', ['contenedor' => $c->id, 'mode' => 'view', 'tab' => 'registro']) }}"
                               title="Ver">
                                👁️
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-gray-400 py-10">
                        No hay contenedores registrados todavía.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Acordeones --}}
        <div class="space-y-4" x-data="{ open: null }">

            {{-- Garantías pendientes --}}
            <div class="{{ $softPanel }} overflow-hidden">
                <button type="button"
                        class="w-full p-6 flex items-center justify-between"
                        @click="open = (open === 'garantias' ? null : 'garantias')">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-yellow-600/20 border border-yellow-500/30 flex items-center justify-center text-yellow-300">
                            🟡
                        </div>
                        <div class="text-white font-extrabold">
                            Contenedores con Garantías Pendientes ({{ $listaGarantias->count() }})
                        </div>
                    </div>
                    <div class="text-gray-300 text-xl" x-text="open === 'garantias' ? '˄' : '˅'"></div>
                </button>

                <div x-show="open === 'garantias'" x-transition class="px-6 pb-6">
                    <div class="space-y-3">
                        @forelse($listaGarantias as $c)
                            <div class="{{ $rowClass }} p-5 flex items-center justify-between">
                                <div>
                                    <div class="text-white font-extrabold">{{ $c->numero_contenedor }}</div>
                                    <div class="text-sm text-gray-400">{{ $c->cliente }}</div>
                                    <div class="text-xs text-yellow-300 mt-1">
                                        Vence: {{ optional($c->liberacion?->fecha_garantia)->format('Y-m-d') ?? '—' }}
                                    </div>
                                </div>
                                <a class="{{ $btnIcon }}"
                                   href="{{ route('contenedores.show', ['contenedor' => $c->id, 'mode' => 'view', 'tab' => 'liberacion']) }}"
                                   title="Ver">
                                    👁️
                                </a>
                            </div>
                        @empty
                            <div class="text-center text-gray-400 py-8">No hay registros.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Revalidaciones --}}
            <div class="{{ $softPanel }} overflow-hidden">
                <button type="button"
                        class="w-full p-6 flex items-center justify-between"
                        @click="open = (open === 'revalidaciones' ? null : 'revalidaciones')">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-purple-600/20 border border-purple-500/30 flex items-center justify-center text-purple-300">
                            🟣
                        </div>
                        <div class="text-white font-extrabold">
                            Contenedores que Requieren Revalidación ({{ $listaRevalidaciones->count() }})
                        </div>
                    </div>
                    <div class="text-gray-300 text-xl" x-text="open === 'revalidaciones' ? '˄' : '˅'"></div>
                </button>

                <div x-show="open === 'revalidaciones'" x-transition class="px-6 pb-6">
                    <div class="space-y-3">
                        @forelse($listaRevalidaciones as $c)
                            <div class="{{ $rowClass }} p-5 flex items-center justify-between">
                                <div>
                                    <div class="text-white font-extrabold">{{ $c->numero_contenedor }}</div>
                                    <div class="text-sm text-gray-400">{{ $c->cliente }}</div>
                                    <div class="text-xs text-purple-300 mt-1">Revalidación pendiente</div>
                                </div>
                                <a class="{{ $btnIcon }}"
                                   href="{{ route('contenedores.show', ['contenedor' => $c->id, 'mode' => 'view', 'tab' => 'liberacion']) }}"
                                   title="Ver">
                                    👁️
                                </a>
                            </div>
                        @empty
                            <div class="text-center text-gray-400 py-8">No hay registros.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Sin envío de documentos --}}
            <div class="{{ $softPanel }} overflow-hidden">
                <button type="button"
                        class="w-full p-6 flex items-center justify-between"
                        @click="open = (open === 'docs' ? null : 'docs')">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-red-600/20 border border-red-500/30 flex items-center justify-center text-red-300">
                            🔴
                        </div>
                        <div class="text-white font-extrabold">
                            Contenedores Sin Envío de Documentos ({{ $listaSinDocs->count() }})
                        </div>
                    </div>
                    <div class="text-gray-300 text-xl" x-text="open === 'docs' ? '˄' : '˅'"></div>
                </button>

                <div x-show="open === 'docs'" x-transition class="px-6 pb-6">
                    <div class="space-y-3">
                        @forelse($listaSinDocs as $c)
                            <div class="{{ $rowClass }} p-5 flex items-center justify-between">
                                <div>
                                    <div class="text-white font-extrabold">{{ $c->numero_contenedor }}</div>
                                    <div class="text-sm text-gray-400">{{ $c->cliente }}</div>
                                    <div class="text-xs text-red-300 mt-1">Documentación faltante</div>
                                </div>
                                <a class="{{ $btnIcon }}"
                                   href="{{ route('contenedores.show', ['contenedor' => $c->id, 'mode' => 'view', 'tab' => 'docs']) }}"
                                   title="Ver">
                                    👁️
                                </a>
                            </div>
                        @empty
                            <div class="text-center text-gray-400 py-8">No hay registros.</div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>

    </div>
</x-app-layout>
