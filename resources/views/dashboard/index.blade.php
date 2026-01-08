<x-app-layout>
    <div class="space-y-6">
        <h1 class="text-2xl font-extrabold">Dashboard</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="p-5 rounded-3xl border border-gray-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                <div class="text-gray-500 dark:text-gray-400 text-sm">Contenedores</div>
                <div class="text-3xl font-extrabold mt-2">0</div>
            </div>
            <div class="p-5 rounded-3xl border border-gray-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                <div class="text-gray-500 dark:text-gray-400 text-sm">Garantías pendientes</div>
                <div class="text-3xl font-extrabold mt-2">0</div>
            </div>
            <div class="p-5 rounded-3xl border border-gray-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                <div class="text-gray-500 dark:text-gray-400 text-sm">Revalidaciones</div>
                <div class="text-3xl font-extrabold mt-2">0</div>
            </div>
            <div class="p-5 rounded-3xl border border-gray-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                <div class="text-gray-500 dark:text-gray-400 text-sm">Sin envío de documentos</div>
                <div class="text-3xl font-extrabold mt-2">0</div>
            </div>
        </div>

        <div class="p-6 rounded-3xl border border-gray-200 dark:border-slate-800 bg-white dark:bg-slate-900">
            <div class="text-gray-500 dark:text-gray-400">Aquí vamos a poner los listados colapsables (garantías, revalidaciones, docs).</div>
        </div>
    </div>
</x-app-layout>
