<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ 'Cobranza' }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-slate-50 text-slate-900">
        <div class="min-h-screen flex items-center justify-center p-6 sm:p-10">
            <div class="w-full max-w-md">
                <div class="flex items-center justify-center mb-6">
                    <a href="/" class="inline-flex items-center gap-3">
                        <div class="h-11 w-11 rounded-xl bg-emerald-600 ring-1 ring-emerald-500 flex items-center justify-center shadow-sm">
                            <x-application-logo class="h-7 w-7 fill-current text-white" />
                        </div>
                        <div>
                            <div class="text-lg font-semibold leading-tight text-slate-900">Cobranza</div>
                            <div class="text-sm text-slate-600">Acceso</div>
                        </div>
                    </a>
                </div>

                <div class="bg-white shadow-xl shadow-slate-900/5 ring-1 ring-slate-200/70 rounded-2xl px-6 py-6 sm:px-8">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
