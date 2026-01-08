@php
    $current = request()->route()?->getName();
@endphp

<aside class="w-72 hidden lg:flex flex-col border-r border-gray-200 dark:border-slate-800 bg-white dark:bg-slate-900">
    {{-- Brand --}}
    <div class="h-16 flex items-center px-6 border-b border-gray-200 dark:border-slate-800">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center">
                {{-- Barco simple --}}
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M3 20h18M4 18l8-3 8 3M4 18V9l8-3 8 3v9M12 6V3"/>
                    <path d="M4 14l8-3 8 3"/>
                </svg>
            </div>
            <div>
                <div class="font-extrabold leading-4">logistic.mx</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Gestión de contenedores</div>
            </div>
        </div>
    </div>

    {{-- Nav --}}
    <nav class="flex-1 px-3 py-4 space-y-2 overflow-y-auto">

        {{-- Dashboard --}}
        <a href="{{ route('dashboard') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-2xl transition
                  {{ $current === 'dashboard' ? 'bg-blue-50 text-blue-700 dark:bg-slate-800 dark:text-white' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-slate-800' }}">
            <span class="text-blue-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M3 13h8V3H3v10zM13 21h8V11h-8v10zM13 3h8v6h-8V3zM3 17h8v4H3v-4z"/>
                </svg>
            </span>
            <span class="font-semibold">Dashboard</span>
        </a>

        {{-- Contenedores --}}
        <a href="{{ route('contenedores.index') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-2xl transition
                  {{ $current === 'contenedores.index' ? 'bg-blue-50 text-blue-700 dark:bg-slate-800 dark:text-white' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-slate-800' }}">
            <span class="text-teal-600 dark:text-teal-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M3 7h18M3 12h18M3 17h18"/>
                </svg>
            </span>
            <span class="font-semibold">Contenedores</span>
        </a>

        {{-- Reportes + Plantillas --}}
        <div x-data="{ open: {{ in_array($current, ['reportes.index','plantillas.index']) ? 'true' : 'false' }} }"
             class="space-y-2">
            <button type="button" @click="open = !open"
                    class="w-full flex items-center justify-between px-4 py-3 rounded-2xl transition
                           {{ in_array($current, ['reportes.index','plantillas.index']) ? 'bg-blue-50 text-blue-700 dark:bg-slate-800 dark:text-white' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-slate-800' }}">
                <div class="flex items-center gap-3">
                    <span class="text-purple-600 dark:text-purple-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M9 17v-2a4 4 0 0 1 4-4h2"/>
                            <path d="M9 7h6"/>
                            <path d="M9 11h6"/>
                            <path d="M7 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"/>
                        </svg>
                    </span>
                    <span class="font-semibold">Reportes</span>
                </div>

                <svg class="w-4 h-4 text-gray-400 transition" :class="{ 'rotate-180': open }"
                     fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="open" x-collapse class="pl-3 space-y-2">
                <a href="{{ route('reportes.index') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-2xl transition
                          {{ $current === 'reportes.index' ? 'bg-gray-100 dark:bg-slate-800' : 'hover:bg-gray-100 dark:hover:bg-slate-800' }}">
                    <span class="text-gray-500 dark:text-gray-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M9 17v-2a4 4 0 0 1 4-4h2"/>
                            <path d="M7 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"/>
                        </svg>
                    </span>
                    <span class="font-medium">Reportes</span>
                </a>

                <a href="{{ route('plantillas.index') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-2xl transition
                          {{ $current === 'plantillas.index' ? 'bg-gray-100 dark:bg-slate-800' : 'hover:bg-gray-100 dark:hover:bg-slate-800' }}">
                    <span class="text-gray-500 dark:text-gray-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 7h10M7 11h10M7 15h6"/>
                            <path d="M7 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"/>
                        </svg>
                    </span>
                    <span class="font-medium">Plantillas</span>
                </a>
            </div>
        </div>

        {{-- Usuarios --}}
        <a href="{{ route('usuarios.index') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-2xl transition
                  {{ $current === 'usuarios.index' ? 'bg-blue-50 text-blue-700 dark:bg-slate-800 dark:text-white' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-slate-800' }}">
            <span class="text-amber-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M20 8v6M23 11h-6"/>
                </svg>
            </span>
            <span class="font-semibold">Usuarios</span>
        </a>

        {{-- Actividad --}}
        <div x-data="{ open: {{ in_array($current, ['actividad.contenedores','actividad.usuarios']) ? 'true' : 'false' }} }"
             class="space-y-2">
            <button type="button" @click="open = !open"
                    class="w-full flex items-center justify-between px-4 py-3 rounded-2xl transition
                           {{ in_array($current, ['actividad.contenedores','actividad.usuarios']) ? 'bg-blue-50 text-blue-700 dark:bg-slate-800 dark:text-white' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-slate-800' }}">
                <div class="flex items-center gap-3">
                    <span class="text-indigo-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M12 8v4l3 3"/>
                            <circle cx="12" cy="12" r="9"/>
                        </svg>
                    </span>
                    <span class="font-semibold">Actividad</span>
                </div>

                <svg class="w-4 h-4 text-gray-400 transition" :class="{ 'rotate-180': open }"
                     fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="open" x-collapse class="pl-3 space-y-2">
                <a href="{{ route('actividad.contenedores') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-2xl transition
                          {{ $current === 'actividad.contenedores' ? 'bg-gray-100 dark:bg-slate-800' : 'hover:bg-gray-100 dark:hover:bg-slate-800' }}">
                    <span class="text-gray-500 dark:text-gray-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M3 7h18M3 12h18M3 17h18"/>
                        </svg>
                    </span>
                    <span class="font-medium">Por contenedores</span>
                </a>

                <a href="{{ route('actividad.usuarios') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-2xl transition
                          {{ $current === 'actividad.usuarios' ? 'bg-gray-100 dark:bg-slate-800' : 'hover:bg-gray-100 dark:hover:bg-slate-800' }}">
                    <span class="text-gray-500 dark:text-gray-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                        </svg>
                    </span>
                    <span class="font-medium">Por usuarios</span>
                </a>
            </div>
        </div>
    </nav>

    {{-- Footer --}}
    <div class="px-6 py-4 border-t border-gray-200 dark:border-slate-800">
        <div class="text-xs text-gray-500 dark:text-gray-400">
            © {{ date('Y') }} logistic.mx
        </div>
    </div>
</aside>
