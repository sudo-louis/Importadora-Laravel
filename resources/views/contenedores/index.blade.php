<x-app-layout>
    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-extrabold">Contenedores</h1>

            <button @click="$dispatch('open-create')"
                    class="px-6 py-3 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-semibold">
                + Agregar contenedor
            </button>
        </div>

        {{-- Flash --}}
        @if(session('success'))
            <div class="p-4 rounded-2xl bg-green-50 border border-green-200 text-green-700">
                {{ session('success') }}
            </div>
        @endif

        {{-- Filtros (como tu diseño) --}}
        <form method="GET" class="p-6 rounded-3xl border border-slate-800 bg-slate-900/70">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm text-gray-300 mb-2">Buscar</label>
                    <input name="search" value="{{ request('search') }}"
                           class="w-full px-4 py-3 rounded-2xl bg-slate-800 border border-slate-700 text-white placeholder-gray-500
                                  focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                           placeholder="Número, cliente, naviera...">
                </div>

                <div>
                    <label class="block text-sm text-gray-300 mb-2">Estado</label>
                    <select name="estado"
                            class="w-full px-4 py-3 rounded-2xl bg-slate-800 border border-slate-700 text-white
                                   focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                        <option value="todos" {{ request('estado') === 'todos' ? 'selected' : '' }}>Todos los estados</option>
                        <option value="pendiente" {{ request('estado') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                        <option value="en_proceso" {{ request('estado') === 'en_proceso' ? 'selected' : '' }}>En proceso</option>
                        <option value="entregado" {{ request('estado') === 'entregado' ? 'selected' : '' }}>Entregado</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm text-gray-300 mb-2">Fecha Desde</label>
                    <input type="date" name="desde" value="{{ request('desde') }}"
                           class="w-full px-4 py-3 rounded-2xl bg-slate-800 border border-slate-700 text-white
                                  focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                </div>

                <div>
                    <label class="block text-sm text-gray-300 mb-2">Fecha Hasta</label>
                    <input type="date" name="hasta" value="{{ request('hasta') }}"
                           class="w-full px-4 py-3 rounded-2xl bg-slate-800 border border-slate-700 text-white
                                  focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 mt-4">
                <a href="{{ route('contenedores.index') }}"
                   class="px-4 py-2 rounded-2xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-gray-200">
                    Limpiar
                </a>
                <button class="px-6 py-2 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-semibold">
                    Filtrar
                </button>
            </div>
        </form>

        {{-- Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @forelse($contenedores as $c)
                <div class="p-6 rounded-3xl border border-slate-800 bg-slate-900/70">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-blue-600 flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4a2 2 0 0 0 1-1.73z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-xl font-extrabold text-white">{{ $c->numero_contenedor }}</div>

                                @php
                                    $badge = match($c->estado){
                                        'pendiente' => 'bg-yellow-100 text-yellow-700',
                                        'en_proceso' => 'bg-teal-100 text-teal-700',
                                        'entregado' => 'bg-green-100 text-green-700',
                                        default => 'bg-gray-200 text-gray-700'
                                    };
                                @endphp

                                <span class="inline-flex mt-2 px-3 py-1 rounded-full text-xs font-semibold {{ $badge }}">
                                    {{ $c->estado_label }}
                                </span>
                            </div>
                        </div>

                        {{-- Acciones --}}
                        <div class="flex items-center gap-3 text-gray-300">
                            {{-- VER --}}
                            <a href="{{ route('contenedores.show', ['contenedor' => $c->id, 'mode' => 'view', 'tab' => 'registro']) }}"
                               class="hover:text-white" title="Ver">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7S2 12 2 12z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </a>

                            {{-- EDITAR (abre mismo show en modo edit) --}}
                            <a href="{{ route('contenedores.show', ['contenedor' => $c->id, 'mode' => 'edit', 'tab' => 'registro']) }}"
                               class="hover:text-white" title="Editar">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M12 20h9"/>
                                    <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/>
                                </svg>
                            </a>

                            {{-- ELIMINAR --}}
                            <form method="POST" action="{{ route('contenedores.destroy', $c) }}"
                                  onsubmit="return confirm('¿Eliminar contenedor?')">
                                @csrf @method('DELETE')
                                <button class="hover:text-red-400" title="Eliminar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M3 6h18"/>
                                        <path d="M8 6V4h8v2"/>
                                        <path d="M19 6l-1 14H6L5 6"/>
                                        <path d="M10 11v6M14 11v6"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="mt-6 space-y-4 text-gray-200">
                        <div>
                            <div class="text-xs text-gray-400">Cliente</div>
                            <div class="font-semibold">{{ $c->cliente }}</div>
                        </div>

                        <div>
                            <div class="text-xs text-gray-400">Mercancía</div>
                            <div class="font-semibold">{{ $c->mercancia_recibida }}</div>
                        </div>

                        <div class="pt-2 space-y-2 text-sm text-gray-300">
                            <div class="flex items-center gap-2">
                                <span>📅</span> Llegada: {{ optional($c->fecha_llegada)->format('Y-m-d') }}
                            </div>
                            <div class="flex items-center gap-2">
                                <span>📍</span> Proveedor: {{ $c->proveedor }}
                            </div>
                            <div class="flex items-center gap-2">
                                <span>⚓</span> Naviera: {{ $c->naviera }}
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-800 text-xs text-gray-400">
                            Registrado por: {{ $c->creador?->name ?? '—' }}<br>
                            {{ $c->created_at?->format('Y-m-d H:i') }}
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full p-10 rounded-3xl border border-slate-800 bg-slate-900/70 text-center text-gray-300">
                    No hay contenedores registrados.
                </div>
            @endforelse
        </div>

        <div class="pt-2">
            {{ $contenedores->links() }}
        </div>
    </div>

    {{-- Modal Crear --}}
    <x-contenedores.modal-create />
</x-app-layout>
