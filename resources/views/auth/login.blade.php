<x-guest-layout>
    <div class="min-h-screen grid grid-cols-1 lg:grid-cols-2">
        {{-- IZQUIERDA (imagen + branding) --}}
        <div class="relative hidden lg:block">
            <div class="absolute inset-0">
                <img src="{{ asset('images/login-bg.webp') }}" class="h-full w-full object-cover opacity-70" alt="">
                <div class="absolute inset-0 bg-blue-900/70"></div>
            </div>

            <div class="relative h-full flex flex-col justify-center px-16">
                <div class="w-28 h-28 rounded-3xl bg-white/20 backdrop-blur flex items-center justify-center mb-6">
                    {{-- Icono barco (SVG simple, puedes cambiarlo después) --}}
                    <svg class="w-14 h-14 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M3 20h18M4 18l8-3 8 3M4 18V9l8-3 8 3v9M12 6V3"/>
                        <path d="M4 14l8-3 8 3"/>
                    </svg>
                </div>

                <h1 class="text-6xl font-extrabold tracking-tight">logistic.mx</h1>
                <p class="mt-3 text-xl text-white/90">Sistema de gestión de contenedores</p>

                <ul class="mt-10 space-y-4 text-lg text-white/90">
                    <li class="flex items-center gap-3">
                        <span class="w-2 h-2 rounded-full bg-blue-300"></span>
                        Control total de importaciones
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="w-2 h-2 rounded-full bg-blue-300"></span>
                        Seguimiento en tiempo real
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="w-2 h-2 rounded-full bg-blue-300"></span>
                        Historial completo de auditoría
                    </li>
                </ul>
            </div>
        </div>

        {{-- DERECHA (formulario) --}}
        <div class="flex items-center justify-center px-6 py-10">
            <div class="w-full max-w-xl bg-slate-900/70 border border-slate-800 rounded-3xl shadow-2xl p-10">
                <h2 class="text-3xl font-bold">Iniciar Sesión</h2>
                <p class="mt-2 text-gray-400">Ingresa tus credenciales para acceder</p>

                {{-- Status --}}
                <x-auth-session-status class="mt-6" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-6"
                      x-data="{ showPass:false }">
                    @csrf

                    {{-- Usuario --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-200 mb-2">Usuario</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                </svg>
                            </span>

                            <input name="name" value="{{ old('name') }}"
                                   class="w-full pl-12 pr-4 py-4 rounded-2xl bg-slate-800 border border-slate-700 text-white placeholder-gray-500 focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                                   placeholder="Ingresa tu usuario" required autofocus>
                        </div>
                        @error('name')
                            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Contraseña --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-200 mb-2">Contraseña</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M12 17a2 2 0 0 0 2-2v-2a2 2 0 0 0-4 0v2a2 2 0 0 0 2 2z"/>
                                    <path d="M6 10V8a6 6 0 1 1 12 0v2"/>
                                    <path d="M6 10h12v10H6z"/>
                                </svg>
                            </span>

                            <input :type="showPass ? 'text':'password'" name="password"
                                   class="w-full pl-12 pr-12 py-4 rounded-2xl bg-slate-800 border border-slate-700 text-white placeholder-gray-500 focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                                   placeholder="Ingresa tu contraseña" required>

                            <button type="button" @click="showPass = !showPass"
                                    class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400 hover:text-white">
                                <svg x-show="!showPass" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7S2 12 2 12z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg x-show="showPass" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M3 3l18 18"/>
                                    <path d="M10.58 10.58A3 3 0 0 0 12 15a3 3 0 0 0 2.42-4.42"/>
                                    <path d="M9.88 5.1A10.94 10.94 0 0 1 12 5c7 0 10 7 10 7a18.7 18.7 0 0 1-3.02 4.35"/>
                                    <path d="M6.61 6.61C3.83 8.7 2 12 2 12s3 7 10 7a10.9 10.9 0 0 0 4.39-.9"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Remember --}}
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 text-sm text-gray-300">
                            <input type="checkbox" name="remember"
                                   class="rounded border-slate-700 bg-slate-800 text-blue-600 focus:ring-blue-600">
                            Recordarme
                        </label>

                        @if (Route::has('password.request'))
                            <a class="text-sm text-blue-400 hover:text-blue-300" href="{{ route('password.request') }}">
                                ¿Olvidaste tu contraseña?
                            </a>
                        @endif
                    </div>

                    {{-- Botón --}}
                    <button type="submit"
                            class="w-full py-4 rounded-2xl font-semibold bg-blue-600 hover:bg-blue-700 transition">
                        Iniciar Sesión
                    </button>

                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
