<header class="h-16 flex items-center justify-between px-6 border-b border-gray-200 dark:border-slate-800 bg-white dark:bg-slate-900">
    <div class="flex items-center gap-3">
        <div class="text-lg font-bold">
            {{ ucfirst(str_replace('.', ' ', request()->route()?->getName() ?? '')) }}
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button type="button"
                @click="toggleTheme()"
                class="w-11 h-11 rounded-2xl border border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800 hover:bg-gray-100 dark:hover:bg-slate-800/70 transition flex items-center justify-center"
                title="Cambiar tema">
            <svg x-show="!darkMode" class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M12 3v2M12 19v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M3 12h2M19 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
                <circle cx="12" cy="12" r="4"/>
            </svg>
            <svg x-show="darkMode" class="w-5 h-5 text-gray-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
            </svg>
        </button>

        <div class="hidden sm:flex items-center gap-3 px-4 py-2 rounded-2xl bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700">
            <div class="w-9 h-9 rounded-xl bg-blue-600 flex items-center justify-center text-white font-bold">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div class="leading-4">
                <div class="text-sm font-semibold">{{ auth()->user()->name }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">{{ auth()->user()->email }}</div>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="px-4 py-2 rounded-2xl bg-gray-200 hover:bg-gray-300 dark:bg-slate-800 dark:hover:bg-slate-700 border border-gray-300 dark:border-slate-700 transition text-sm font-semibold">
                Cerrar sesión
            </button>
        </form>
    </div>
</header>
