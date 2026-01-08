<div x-data="{ open:false }"
     @open-create.window="open = true"
     x-show="open"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
     x-transition>

    <div @click.outside="open = false"
         class="w-full max-w-xl bg-white dark:bg-slate-900 rounded-3xl p-6 border border-gray-200 dark:border-slate-800">

        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-extrabold">Nuevo contenedor</h2>
            <button type="button" @click="open=false" class="text-gray-500 hover:text-gray-800 dark:hover:text-white">
                ✕
            </button>
        </div>

        <form method="POST" action="{{ route('contenedores.store') }}" class="space-y-4">
            @csrf

            @php
                $inputClass = "w-full px-4 py-3 rounded-2xl bg-gray-50 text-gray-900 placeholder-gray-400 border border-gray-300
                              focus:ring-2 focus:ring-blue-600 focus:border-blue-600
                              dark:bg-slate-800 dark:text-white dark:placeholder-gray-500 dark:border-slate-700";
            @endphp

            <div>
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-200">Contenedor / Guía</label>
                <input name="numero_contenedor" required class="{{ $inputClass }}" placeholder="Ej. CONT-2024-001">
                @error('numero_contenedor') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-200">Cliente que manda</label>
                <input name="cliente" required class="{{ $inputClass }}" placeholder="Ej. Tech Solutions SA">
                @error('cliente') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-200">Fecha de llegada</label>
                <input type="date" name="fecha_llegada" required class="{{ $inputClass }}">
                @error('fecha_llegada') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-200">Proveedor</label>
                <input name="proveedor" required class="{{ $inputClass }}" placeholder="Ej. Proveedor A">
                @error('proveedor') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-200">Naviera</label>
                <input name="naviera" required class="{{ $inputClass }}" placeholder="Ej. Marítima SA">
                @error('naviera') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-200">Mercancía recibida</label>
                <input name="mercancia_recibida" required class="{{ $inputClass }}" placeholder="Ej. Textiles y telas">
                @error('mercancia_recibida') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" @click="open=false"
                        class="px-4 py-2 rounded-2xl bg-gray-200 hover:bg-gray-300 dark:bg-slate-800 dark:hover:bg-slate-700">
                    Cancelar
                </button>
                <button class="px-6 py-2 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-semibold">
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>
