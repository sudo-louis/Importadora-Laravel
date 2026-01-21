<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full"
      x-data="themeController()"
      x-init="initTheme()"
      :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'logistic.mx') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full bg-white text-gray-900 dark:bg-slate-950 dark:text-white">
<div class="min-h-screen flex">

    <x-app.sidebar />

    <div class="flex-1 flex flex-col min-w-0">

        <x-app.topbar />

        <main class="flex-1 p-6 bg-gray-50 dark:bg-slate-950">
            {{ $slot }}
        </main>
    </div>
</div>

<script>
    function themeController() {
        return {
            darkMode: false,
            initTheme() {
                const saved = localStorage.getItem('theme');
                if (saved === 'dark') this.darkMode = true;
                if (saved === 'light') this.darkMode = false;

                if (saved === null) {
                    this.darkMode = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                }
                this.applyTheme();
            },
            toggleTheme() {
                this.darkMode = !this.darkMode;
                localStorage.setItem('theme', this.darkMode ? 'dark' : 'light');
                this.applyTheme();
            },
            applyTheme() {
                document.documentElement.classList.toggle('dark', this.darkMode);
            }
        }
    }
</script>
</body>
</html>
