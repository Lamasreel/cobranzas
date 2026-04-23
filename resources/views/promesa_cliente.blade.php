<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ 'Cobranza - Promesa de pago' }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <body class="font-sans antialiased bg-slate-50 text-slate-900">
        <div class="min-h-screen flex items-center justify-center p-6 sm:p-10">
            <div class="w-full max-w-2xl">
                <div class="mb-6">
                    <div class="flex items-center gap-3">
                        <div class="h-11 w-11 rounded-xl bg-emerald-600 ring-1 ring-emerald-500 flex items-center justify-center shadow-sm">
                            <svg viewBox="0 0 32 32" class="w-6 h-6 fill-white" aria-hidden="true">
                                <path d="M19.11 17.44c-.28-.14-1.64-.81-1.9-.9-.25-.09-.44-.14-.63.14-.19.28-.72.9-.88 1.09-.16.19-.32.21-.6.07-.28-.14-1.18-.43-2.25-1.38-.83-.74-1.39-1.65-1.55-1.93-.16-.28-.02-.43.12-.57.13-.13.28-.32.42-.48.14-.16.19-.28.28-.46.09-.19.05-.35-.02-.49-.07-.14-.63-1.52-.86-2.08-.23-.55-.47-.48-.63-.49h-.54c-.19 0-.49.07-.74.35-.25.28-.97.95-.97 2.33 0 1.38 1 2.72 1.14 2.9.14.19 1.97 3.01 4.77 4.22.66.29 1.18.46 1.58.59.66.21 1.26.18 1.74.11.53-.08 1.64-.67 1.87-1.32.23-.65.23-1.21.16-1.32-.07-.12-.25-.19-.53-.33z"/>
                                <path d="M26.67 5.33A13.21 13.21 0 0 0 16.02 0C8.73 0 2.8 5.93 2.8 13.22c0 2.33.61 4.6 1.77 6.6L2.67 32l12.47-1.86a13.17 13.17 0 0 0 6.33 1.62h.01c7.29 0 13.22-5.93 13.22-13.22 0-3.53-1.37-6.85-3.86-9.34zM21.49 29.2h-.01a11 11 0 0 1-5.61-1.53l-.4-.24-7.4 1.1 1.1-7.2-.26-.42a10.96 10.96 0 1 1 12.58 8.29z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-xl font-bold leading-tight text-slate-900">Promesa de pago</div>
                            <div class="text-sm text-slate-600">Validá documento y asigná fecha + observaciones</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-xl shadow-slate-900/5 ring-1 ring-slate-200/70 rounded-2xl p-6 sm:p-8">
                    <form method="GET" action="{{ route('promesa_cliente') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700">Documento</label>
                            <input
                                type="text"
                                name="documento"
                                value="{{ old('documento', $documento) }}"
                                class="mt-1 w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500"
                                placeholder="Ingresá DNI / documento"
                                inputmode="numeric"
                            >
                        </div>
                        <button type="submit" class="h-10 rounded-lg bg-slate-900 text-white font-semibold hover:bg-slate-800">
                            Buscar
                        </button>
                    </form>

                    @if (session('success'))
                        <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-900">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-900">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-900">
                            <div class="font-semibold">Revisá los datos</div>
                            <ul class="mt-1 list-disc pl-5 text-sm">
                                @foreach ($errors->all() as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mt-6">
                        @if ($documento !== '' && !$cliente)
                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-4 text-slate-700">
                                No encontramos un cliente con el documento <span class="font-bold">{{ $documento }}</span>.
                            </div>
                        @endif

                        @if ($cliente)
                            <div class="rounded-2xl border border-slate-200 overflow-hidden">
                                <div class="px-4 py-3 bg-slate-50 border-b border-slate-200">
                                    <div class="text-sm text-slate-500">Cliente</div>
                                    <div class="text-lg font-bold text-slate-900">{{ $cliente->nombre ?? '—' }}</div>
                                    <div class="text-sm text-slate-600">Documento: <span class="font-semibold">{{ $cliente->documento }}</span></div>
                                </div>

                                <div class="p-4">
                                    <form id="promesaClienteForm" method="POST" action="{{ route('promesa_cliente.store') }}" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        @csrf
                                        <input type="hidden" name="documento" value="{{ $cliente->documento }}">

                                        <div>
                                            <label class="block text-sm font-semibold text-slate-700">Fecha promesa de pago</label>
                                            <input
                                                type="date"
                                                name="fecha_promesa_pago"
                                                value="{{ old('fecha_promesa_pago', $cliente->fecha_promesa_pago ?? '') }}"
                                                class="mt-1 w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500"
                                                required
                                            >
                                        </div>

                                        <div class="sm:col-span-2">
                                            <label class="block text-sm font-semibold text-slate-700">Observaciones</label>
                                            <textarea
                                                name="observaciones_promesa"
                                                class="mt-1 w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500"
                                                rows="4"
                                                placeholder="Ej: se compromete a abonar el día ..."
                                            >{{ old('observaciones_promesa', $cliente->observaciones_promesa ?? '') }}</textarea>
                                        </div>

                                        <div class="sm:col-span-2 flex items-center justify-between gap-3">
                                            <a
                                                class="text-sm font-semibold text-slate-600 hover:text-slate-900"
                                                href="{{ route('promesa_cliente', ['documento' => $cliente->documento]) }}"
                                            >
                                                Recargar
                                            </a>
                                            <button
                                                type="submit"
                                                class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold shadow"
                                            >
                                                Guardar promesa
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('promesaClienteForm');
            if (!form) return;

            form.addEventListener('submit', (e) => {
                e.preventDefault();
                const fecha = form.querySelector('input[name="fecha_promesa_pago"]')?.value || '';
                const obs = form.querySelector('textarea[name="observaciones_promesa"]')?.value || '';

                Swal.fire({
                    title: 'Guardar promesa',
                    html: `
                        <div class="text-left">
                            <div><b>Fecha:</b> ${fecha ? fecha : '—'}</div>
                            <div class="mt-1"><b>Observaciones:</b> ${obs ? obs.replaceAll('<', '&lt;').replaceAll('>', '&gt;') : '—'}</div>
                        </div>
                    `,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: 'Guardar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#16a34a'
                }).then((r) => {
                    if (!r.isConfirmed) return;
                    Swal.fire({
                        title: 'Guardando…',
                        text: 'Por favor esperá',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                    form.submit();
                });
            });
        });
        </script>
    </body>
</html>

