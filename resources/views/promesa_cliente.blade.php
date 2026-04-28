<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

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
                        <i class="fa-brands fa-whatsapp" style="color: white; font-size: 1.5rem;"></i>
                        </div>
                        <div>
                            <div class="text-xl font-bold leading-tight text-slate-900">Promesa de pago</div>
                            <div class="text-sm text-slate-600">Validá documento y asigná fecha + observaciones</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-xl shadow-slate-900/5 ring-1 ring-slate-200/70 rounded-2xl p-6 sm:p-8">
                @php
                    $tienePromesa = $cliente && $cliente->fecha_promesa_pago;
                @endphp
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
                    @if ($cliente && $cliente->fecha_promesa_pago)
                        <script>
                        document.addEventListener('DOMContentLoaded', () => {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Promesa ya registrada',
                                html: `
                                    <div class="text-left">
                                        <div><b>Fecha:</b> {{ $cliente->fecha_promesa_pago }}</div>
                                        <div class="mt-1"><b>Observaciones:</b> {{ $cliente->observaciones_promesa ?? '—' }}</div>
                                    </div>
                                `,
                                confirmButtonColor: '#f59e0b'
                            });
                        });
                        </script>
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
                                                min="{{ date('Y-m-d') }}"
                                                {{ $tienePromesa ? 'disabled' : '' }}
                                                class="mt-1 w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500"
                                                required
                                            >
                                        </div>

                                        <div class="sm:col-span-2">
                                            <label class="block text-sm font-semibold text-slate-700">Observaciones</label>
                                            <textarea
                                                name="observaciones_promesa"
                                                {{ $tienePromesa ? 'disabled' : '' }}
                                                class="mt-1 w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500"
                                                rows="4"
                                            >
                                            {{ old('observaciones_promesa', $cliente->observaciones_promesa ?? '') }}
                                            </textarea>
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
                                                {{ $tienePromesa ? 'disabled' : '' }}
                                                class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold shadow disabled:bg-slate-400"
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

