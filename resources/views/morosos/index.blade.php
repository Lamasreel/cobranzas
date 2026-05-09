<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Importación completada',
        text: "{{ session('success') }}",
        confirmButtonColor: '#16a34a'
    });
</script>
@endif

<x-app-layout> 
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
            <div>
                <h2 class="text-2xl font-bold text-slate-800"> Morosos </h2>
                <p class="text-sm text-slate-500"> Gestión y seguimiento de clientes en mora </p>
            </div>

            <button
                type="button"
                id="btn-import-excel"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold shadow"
            >
                <i class="fa-solid fa-file-excel"></i>
                Importar Excel
            </button>
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    id="btn-wa-auto"
                    class="inline-flex items-center justify-center h-10 w-11 rounded-xl border border-emerald-200 bg-white hover:bg-emerald-50 text-emerald-700 shadow-sm"
                    title="Automatización WhatsApp"
                >
                    <i class="fa-solid fa-gear"></i>
                </button>
                <button
                    type="button"
                    id="btn-whatsapp-test"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold shadow"
                    title="Enviar WhatsApp de prueba"
                >
                <i class="fa-brands fa-whatsapp"></i>WhatsApp
                </button>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">

        <div class="bg-white rounded-xl shadow border border-slate-200">
            <div class="p-5">
                <form method="GET" action="{{ route('morosos.index') }}"
                      class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">

                    <div class="lg:col-span-4">
                        <label class="block text-sm font-semibold text-slate-700">Estado</label>
                        <input type="hidden" name="estado" id="estadoFilterInput" value="{{ (string) ($filters['estado'] ?? '') }}">
                        <div id="estado-chips" class="mt-1 flex flex-wrap gap-2">
                            <button type="button" data-estado="" class="estado-chip px-3 py-1.5 rounded-full border text-xs font-semibold transition">
                                Todos
                            </button>
                            <button type="button" data-estado="2" class="estado-chip px-3 py-1.5 rounded-full border text-xs font-semibold transition">
                                Pendiente
                            </button>
                            <button type="button" data-estado="3" class="estado-chip px-3 py-1.5 rounded-full border text-xs font-semibold transition">
                                Promesa
                            </button>
                            <button type="button" data-estado="4" class="estado-chip px-3 py-1.5 rounded-full border text-xs font-semibold transition">
                                Pagado
                            </button>
                        </div>
                    </div>

                    <div class="lg:col-span-5">
                        <label class="block text-sm font-semibold text-slate-700">Localidad</label>
                        <div class="relative mt-1">
                            <select
                                name="localidad"
                                id="localidadFilterSelect"
                                class="w-full rounded-xl border-slate-300 bg-white pr-10 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                            >
                                <option value="">Todas las localidades</option>
                            @foreach ($localidades as $loc)
                                <option value="{{ $loc->id }}" @selected((string) ($filters['localidad'] ?? '') === (string) $loc->id)>
                                    {{ $loc->nombre_corto }}
                                </option>
                            @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="lg:col-span-3 flex gap-2 lg:justify-end">
                        <x-primary-button class="justify-center min-w-[110px]">
                            Filtrar
                        </x-primary-button>

                        <a href="/morosos/pdf" target="_blank"
                           class="px-4 py-2 rounded-lg border text-sm font-semibold text-slate-700 hover:bg-slate-100">
                            Cartas
                        </a>
                    </div>

                </form>
            </div>
        </div>

            <div class="p-4 border-b bg-slate-50 flex items-center justify-between">
                <div class="text-sm text-slate-600">
                    Mostrando 
                    <span class="font-semibold text-slate-800">
                        {{ count($morosos) }}
                    </span> registros
                </div>
                <div class="flex items-center gap-3">
                    <button
                        id="btn-marcar-seleccionados"
                        type="button"
                        data-action="{{ route('morosos.pagado_masivo') }}"
                        class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-emerald-200 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 shadow-sm transition disabled:opacity-40 disabled:cursor-not-allowed"
                        disabled
                    >
                        <i class="fa-solid fa-circle-check"></i>
                        <span class="text-sm font-semibold">Marcar seleccionados</span>
                        <span id="seleccionados-count" class="inline-flex items-center justify-center min-w-6 h-6 rounded-full bg-emerald-700 text-white text-xs px-1">0</span>
                    </button>

                <button id="prev-rango"
                    class="px-3 py-2 rounded-xl border bg-white hover:bg-slate-100 shadow">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>

                <span id="rango-actual"
                    class="text-sm font-bold text-slate-800 px-3">
                    0-30 días
                </span>

                <button id="next-rango"
                    class="px-3 py-2 rounded-xl border bg-white hover:bg-slate-100 shadow">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
                <button id="toggle-resumen"
                    class="px-3 py-2 rounded-xl border bg-white hover:bg-slate-100 shadow text-sm font-semibold">
                    <i class="fa-regular fa-calendar"></i>
                    Ver resumen
                </button>

                </div>

                <button
                    id="btn-fullscreen-table"
                    class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 shadow-sm transition"
                >
                    <i class="fa-solid fa-expand text-sm"></i>
                    <span class="text-sm font-semibold">Pantalla completa</span>
                </button>
            </div>
            <div id="resumen-container" class="hidden bg-white rounded-xl shadow border border-slate-200 p-4">

            <div class="flex items-center justify-between mb-3">

                <h3 id="rango-titulo" class="text-lg font-bold text-slate-800">
                    Resumen
                </h3>

            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm border">
                    <thead class="bg-gray-300 text-black">
                        <tr>
                            <th class="border px-3 py-2">CIUDAD</th>
                            <th class="border px-3 py-2">TOTALES</th>
                            <th class="border px-3 py-2">TIT</th>
                            <th class="border px-3 py-2">GAR</th>
                            <th class="border px-3 py-2">PAGARON</th>
                            <th class="border px-3 py-2">% Pago</th>
                            <th class="border px-3 py-2">TIENEN WSP</th>
                            <th class="border px-3 py-2">NO WSP</th>
                            <th class="border px-3 py-2">NO TEL</th>
                            <th class="border px-3 py-2">CARTA</th>
                        </tr>
                    </thead>
                    <tbody id="resumen-body">
                        <div id="resumen-data" data-json='@json($resumen)'></div>
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex gap-6 text-sm">
                <div><b>Morosos:</b> <span id="total-morosos" class="font-bold text-slate-800"></span></div>
                <div><b>Titulares:</b> <span id="total-titulares"></span></div>
                <div><b>Pagaron:</b> <span id="total-pagaron" class="text-green-600 font-bold"></span></div>
                <div><b>Deben:</b> <span id="total-deben" class="text-red-600 font-bold"></span></div>
            </div>

        </div>
        
        <div id="tabla-container" class="bg-white rounded-xl shadow border border-slate-200 overflow-hidden">
            <div class="overflow-auto max-h-[500px]">

                <table class="morosos-sticky-table min-w-full w-full border-collapse text-xs">

                    <thead class="bg-slate-100 text-slate-700 text-[11px] uppercase tracking-wide sticky top-0 z-40">
                        <tr>
                            <th class="py-2 px-2 border text-center whitespace-nowrap">
                                <input id="check-all-morosos" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            </th>
                            <th class="py-2 px-2 border whitespace-nowrap">Orden</th>
                            <th class="py-2 px-2 border whitespace-nowrap">Tit. Gar.</th>
                            <th class="py-2 px-2 border whitespace-nowrap">DNI Titular</th>
                            <th class="py-2 px-2 border whitespace-nowrap">DNI</th>
                            <th class="py-2 px-2 border whitespace-nowrap">Nombre</th>
                            <th class="py-2 px-2 border">Calle</th>
                            <th class="py-2 px-2 border">Observaciones</th>
                            <th class="py-2 px-2 border whitespace-nowrap">Localidad</th>
                            <th class="py-2 px-2 border text-center whitespace-nowrap">Teléfono</th>
                            <th class="py-2 px-2 border whitespace-nowrap">Empleador</th>
                            <th class="py-2 px-2 border text-right whitespace-nowrap">Días</th>
                            <th class="py-2 px-2 border whitespace-nowrap">Fecha Ult. Pago</th>
                            <th class="py-2 px-2 border text-right whitespace-nowrap">Saldo Vencido</th>
                            <th class="py-2 px-2 border text-right whitespace-nowrap">Int. Pun.</th>
                            <th class="py-2 px-2 border text-right whitespace-nowrap">Saldo Total</th>
                            <th class="py-2 px-2 border text-center whitespace-nowrap">Estado</th>
                            <th class="py-2 px-2 border text-right whitespace-nowrap">FEB</th>
                            <th class="py-2 px-2 border text-right whitespace-nowrap">Imp.</th>
                            <th class="py-2 px-2 border text-right whitespace-nowrap">MAR</th>
                            <th class="py-2 px-2 border text-right whitespace-nowrap">Imp.</th>
                            <th class="py-2 px-2 border">Datos Adicionales</th>
                            <th class="py-2 px-2 border text-center whitespace-nowrap">Se envió WSP</th>
                            <th class="py-2 px-2 border text-center whitespace-nowrap">Tiene WSP</th>
                            <th class="py-2 px-2 border text-center whitespace-nowrap">Se envió SMS</th>
                            <th class="py-2 px-2 border text-center whitespace-nowrap">LLAMADA</th>
                            <th class="py-2 px-2 border text-center whitespace-nowrap">Tiene Tel</th>
                            <th class="py-2 px-2 border text-center whitespace-nowrap">Se envió Carta</th>
                            <th class="py-2 px-2 border whitespace-nowrap">Fecha Env. Carta</th>
                            <th class="py-2 px-2 border whitespace-nowrap">Fecha Promesa Pago</th>
                            <th class="py-2 px-2 border">Observaciones Promesa</th>
                        </tr>
                    </thead>

                    <tbody class="text-slate-700">

                        @php $c = 1; @endphp

                        @forelse ($morosos as $m)

                        @php
                            $estNorm = strtolower(trim((string) ($m->estado ?? '')));
                            $idEst = (int) ($m->estado_id_cliente ?? $m->id_estado ?? 0);
                            $esPagado = $idEst === 4
                                || ($estadoIdPagado !== null && $idEst === (int) $estadoIdPagado)
                                || str_contains($estNorm, 'pagad');
                            $esPromesa = ! $esPagado && (
                                $idEst === 3
                                || ($estadoIdPromesa !== null && $idEst === (int) $estadoIdPromesa)
                                || (str_contains($estNorm, 'promesa') && ! str_contains($estNorm, 'sin promesa'))
                            );

                            $badge = $esPagado
                                ? 'bg-emerald-100 text-emerald-800 ring-1 ring-inset ring-emerald-200/80'
                                : ($esPromesa
                                    ? 'bg-sky-100 text-sky-800 ring-1 ring-inset ring-sky-200/80'
                                    : 'bg-slate-100 text-slate-700 ring-1 ring-inset ring-slate-200/70');

                            $rowEstadoClass = $esPagado
                                ? 'moroso-row--pagado'
                                : ($esPromesa ? 'moroso-row--promesa' : 'moroso-row--default');

                            $phoneSources = [
                                (string) ($m->telefono ?? ''),
                                (string) ($m->telefono_1 ?? ''),
                                (string) ($m->telefono_2 ?? ''),
                                (string) ($m->telefono_3 ?? ''),
                            ];

                            $phones = [];
                            foreach ($phoneSources as $src) {
                                if (trim($src) === '') continue;
                                foreach (preg_split('/[,\;\|\/]+/', $src) as $p) {
                                    $p = trim($p);
                                    if ($p === '') continue;
                                    $phones[] = $p;
                                }
                            }
                            $phones = array_values(array_unique($phones));
                        @endphp

                        <tr
                            class="border-b transition cursor-pointer align-middle text-center {{ $rowEstadoClass }}"
                            data-moroso-id="{{ $m->id }}"
                            data-moroso-nombre="{{ e($m->nombre) }}"
                            data-moroso-estado="{{ e($m->estado) }}"
                            data-moroso-fecha="{{ e($m->fecha_promesa_pago) }}"
                            data-moroso-obs="{{ e($m->observaciones_promesa) }}"
                            data-dias="{{ e($m->dias) }}"
                        >

                            <td class="py-1.5 px-2 border text-center">
                                <input
                                    type="checkbox"
                                    class="moroso-select-checkbox h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                                    value="{{ $m->id }}"
                                    @disabled($idEst === 4)
                                >
                            </td>
                            <td class="py-1.5 px-2 border">{{ $c }}</td>
                            <td class="py-1.5 px-2 border">{{ $m->titular_garantia }}</td>
                            <td class="py-1.5 px-2 border">{{ $m->documento_titular }}</td>
                            <td class="py-1.5 px-2 border">{{ $m->documento }}</td>

                            <td class="py-1.5 px-2 border font-medium truncate">
                                {{ $m->nombre }}
                            </td>

                            <td class="py-1.5 px-2 border max-w-[180px] whitespace-normal break-words align-top">
                                {{ $m->calle }}
                            </td>

                            <td class="py-1.5 px-2 border max-w-[200px] whitespace-normal break-words align-top">
                                {{ $m->observaciones }}
                            </td>

                            <td class="py-1.5 px-2 border whitespace-nowrap">
                                {{ $m->localidad }}
                            </td>

                            <td class="py-1.5 px-2 border text-center">
                                @if (count($phones) <= 1)
                                    {{ $phones[0] ?? '-' }}
                                @else
                                    <button
                                        type="button"
                                        onclick="event.stopPropagation()"
                                        class="text-xs px-2 py-1 rounded bg-slate-100 hover:bg-slate-200"
                                        data-telefonos-cliente="{{ e($m->nombre) }}"
                                        data-telefonos='@json($phones)'
                                    >
                                        Ver {{ count($phones) }} teléfonos
                                    </button>
                                @endif
                            </td>

                            <td class="py-1.5 px-2 border max-w-[150px] truncate">
                                {{ $m->empleador }}
                            </td>

                            <td class="py-1.5 px-2 border text-right font-semibold">
                                {{ $m->dias }}
                            </td>

                            <td class="py-1.5 px-2 border whitespace-nowrap">
                                {{ $m->fecha_ultimo_pago }}
                            </td>

                            <td class="py-1.5 px-2 border text-right">
                                ${{ number_format($m->saldo_vencido, 0, ',', '.') }}
                            </td>

                            <td class="py-1.5 px-2 border text-right">
                                ${{ number_format($m->interes_punitorio, 0, ',', '.') }}
                            </td>

                            <td class="py-1.5 px-2 border text-right font-semibold">
                                ${{ number_format($m->saldo_total, 0, ',', '.') }}
                            </td>

                            <td class="py-1.5 px-2 border text-center">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-medium {{ $badge }}">
                                    {{ $m->estado }}
                                </span>
                            </td>

                            <td class="py-1.5 px-2 border text-right">{{ $m->feb }}</td>
                            <td class="py-1.5 px-2 border text-right">{{ $m->feb_importe }}</td>
                            <td class="py-1.5 px-2 border text-right">{{ $m->mar }}</td>
                            <td class="py-1.5 px-2 border text-right">{{ $m->mar_importe }}</td>

                            <td class="py-1.5 px-2 border max-w-[200px] whitespace-normal break-words align-top">
                                {{ $m->datos_adicionales }}
                            </td>

                            <td class="py-1.5 px-2 border text-center">{{ $m->wsp }}</td>

                            <td class="py-1.5 px-2 border text-center">
                                @if($m->tiene_wsp == 1)
                                    <i class="fa-solid fa-check text-green-500"></i>
                                @else
                                    <i class="fa-solid fa-xmark text-red-500"></i>
                                @endif
                            </td>

                            <td class="py-1.5 px-2 border text-center">{{ $m->sms }}</td>
                            <td class="py-1.5 px-2 border text-center">{{ $m->llamada }}</td>

                            <td class="py-1.5 px-2 border text-center">
                                @if($m->tiene_wsp == 1)
                                    <i class="fa-solid fa-check text-green-500"></i>
                                @else
                                    <i class="fa-solid fa-xmark text-red-500"></i>
                                @endif
                            </td>

                            <td class="py-1.5 px-2 border text-center">{{ $m->carta }}</td>

                            <td class="py-1.5 px-2 border whitespace-nowrap">
                                {{ $m->fecha_envio_carta }}
                            </td>

                            <td class="py-1.5 px-2 border whitespace-nowrap">
                                {{ $m->fecha_promesa_pago }}
                            </td>

                            <td class="py-1.5 px-2 border max-w-[220px] truncate">
                                {{ $m->observaciones_promesa }}
                            </td>

                        </tr>

                        @php $c++; @endphp

                        @empty
                        <tr>
                            <td colspan="31" class="moroso-empty-placeholder py-10 text-center text-slate-500 border">
                                No hay morosos para los filtros seleccionados.
                            </td>
                        </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>
            </div>
                    <div id="modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 sm:p-6 bg-slate-900/55 backdrop-blur-[2px]">
                        <div
                            class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden border border-slate-200/90 ring-1 ring-black/5"
                            role="dialog"
                            aria-modal="true"
                            aria-labelledby="modalCliente"
                        >
                            <div class="flex items-start justify-between gap-3 px-5 sm:px-6 pt-5 pb-4 border-b border-slate-100 bg-gradient-to-br from-slate-50 via-white to-emerald-50/30">
                                <div class="min-w-0 flex-1">
                                    <p class="text-[11px] font-bold uppercase tracking-widest text-emerald-700/90">
                                        Gestión de mora
                                    </p>
                                    <h2 id="modalCliente" class="text-lg sm:text-xl font-bold text-slate-900 tracking-tight mt-1 truncate">
                                    </h2>
                                    <p class="text-xs text-slate-500 mt-1">
                                        Registrá la fecha de compromiso y las notas del acuerdo.
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    onclick="closeModal()"
                                    class="shrink-0 inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 hover:text-slate-800 transition"
                                    aria-label="Cerrar"
                                >
                                    <i class="fa-solid fa-xmark text-sm"></i>
                                </button>
                            </div>

                            <form id="promesaForm" method="POST" action="{{ route('morosos.promesa') }}" class="px-5 sm:px-6 py-5 space-y-5">
                                @csrf

                                <input type="hidden" name="id" id="modalId">

                                <div class="space-y-2">
                                    <label for="modalFechaInput" class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700">
                                            <i class="fa-solid fa-calendar-days text-sm"></i>
                                        </span>
                                        Fecha de compromiso de pago
                                    </label>
                                    <input
                                        type="date"
                                        name="fecha_promesa_pago"
                                        id="modalFechaInput"
                                        class="w-full rounded-xl border-slate-300 shadow-sm text-slate-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 px-3 py-2.5 text-sm"
                                    >
                                </div>

                                <div class="space-y-2">
                                    <label for="modalObsInput" class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                                            <i class="fa-solid fa-note-sticky text-sm"></i>
                                        </span>
                                        Observaciones del acuerdo
                                    </label>
                                    <textarea
                                        name="observaciones_promesa"
                                        id="modalObsInput"
                                        rows="4"
                                        placeholder="Ej.: cuotas acordadas, monto parcial, forma de pago…"
                                        class="w-full rounded-xl border-slate-300 shadow-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 px-3 py-2.5 text-sm resize-y min-h-[100px]"
                                    ></textarea>
                                </div>

                                <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 pt-1 border-t border-slate-100">
                                    <button
                                        type="button"
                                        onclick="marcarPagado()"
                                        class="inline-flex items-center justify-center gap-2 rounded-xl border-2 border-emerald-200 bg-white px-4 py-2.5 text-sm font-semibold text-emerald-800 hover:bg-emerald-50 transition"
                                    >
                                        <i class="fa-solid fa-circle-check text-emerald-600"></i>
                                        Marcar como pagado
                                    </button>
                                    <button
                                        type="submit"
                                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-emerald-600/25 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition"
                                    >
                                        <i class="fa-solid fa-floppy-disk"></i>
                                        Guardar promesa
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div id="telefonos-modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
                        <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 relative">
                            <button onclick="closeTelefonosModal()" class="absolute top-2 right-2 text-slate-500 text-xl">✕</button>
                            <h2 class="text-xl font-bold mb-1">Teléfonos</h2>
                            <p class="text-sm text-slate-500 mb-4" id="telefonosCliente"></p>

                            <div id="telefonosLista" class="space-y-2"></div>

                            <div class="mt-5 flex justify-end">
                                <button
                                    type="button"
                                    onclick="closeTelefonosModal()"
                                    class="px-4 py-2 rounded-lg border text-sm font-semibold text-slate-700 hover:bg-slate-100"
                                >
                                    Cerrar
                                </button>
                            </div>
                        </div>
                    </div>
                        <div class="grid md:grid-cols-3 gap-4">
                            <div class="bg-white p-4 rounded-xl shadow">
                                <canvas id="graficoMontos"></canvas>
                            </div>

                            <div class="bg-white p-4 rounded-xl shadow">
                                <canvas id="graficoCantidad"></canvas>
                            </div>

                            <div class="bg-white p-4 rounded-xl shadow">
                                <canvas id="graficoPrediccion"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

    <div id="excel-modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 relative">
            <button onclick="closeExcelModal()" class="absolute top-2 right-2 text-slate-500 text-xl">✕</button>
            <h2 class="text-xl font-bold mb-3">
                Importar Excel
            </h2>

            <form id="form-import-excel" action="{{ route('morosos.subir-excel') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <input
                    type="file"
                    name="archivo"
                    accept=".xlsx,.xls,.csv"
                    required
                    class="w-full border rounded-lg p-2"
                >
                <button
                    type="submit"
                    class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-2 rounded-lg font-semibold"
                >
                    Subir e importar
                </button>
            </form>
        </div>
    </div>

<div id="wa-auto-modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 relative">
        <button type="button" onclick="closeWaAutoModal()" class="absolute top-2 right-2 text-slate-500 text-xl">✕</button>
        <h2 class="text-xl font-bold mb-1">
            WhatsApp automático
        </h2>
        <p class="text-sm text-slate-500 mb-4">
            Se envía en el día del mes y hora configurados (clientes en promesa con fecha de promesa = hoy).
        </p>
        <div class="space-y-4">
            <label class="flex items-center justify-between gap-3 p-3 rounded-xl border border-slate-200 bg-slate-50">
                <div>
                    <div class="text-sm font-semibold text-slate-800">Activar</div>
                    <div class="text-xs text-slate-600">Requiere `schedule:run` cada minuto en el servidor</div>
                </div>
                <input id="waAutoEnabled" type="checkbox" class="h-5 w-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
            </label>

            <div>
                <label class="block text-sm font-semibold text-slate-700">Día del mes (1-31)</label>
                <input id="waAutoDay" type="number" min="1" max="31" class="mt-1 w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700">Hora (HH:MM)</label>
                <input id="waAutoTime" type="time" class="mt-1 w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
            </div>

            <div class="flex items-center justify-between gap-2 pt-2">
                <button type="button" onclick="closeWaAutoModal()" class="px-4 py-2 rounded-lg border text-sm font-semibold text-slate-700 hover:bg-slate-100">
                    Cancelar
                </button>
                <button type="button" id="waAutoSave" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold">
                    Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<div id="whatsapp-config"
    data-url="{{ route('morosos.whatsapp_test') }}"
    data-csrf="{{ csrf_token() }}"></div>

<div
    id="wa-auto-bootstrap"
    data-json='@json($whatsappAuto ?? ["enabled" => false, "day_of_month" => 1, "time" => "09:00"])'
></div>

<div
    id="wa-auto-api"
    data-update-url="{{ route('morosos.whatsapp_automation.update') }}"
    data-csrf="{{ csrf_token() }}"
></div>

<div
    id="morosos-datos"
    data-totales='@json($totales)'
    data-conteo='@json($conteo)'
    data-prediccion='@json($prediccion)'>
</div>

<script>
const morososDatosEl = document.getElementById('morosos-datos');
const totales = JSON.parse(morososDatosEl.dataset.totales);
const conteo = JSON.parse(morososDatosEl.dataset.conteo);
const prediccion = JSON.parse(morososDatosEl.dataset.prediccion);
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {

    const colores = {
        pagado: '#16a34a',
        promesa: '#2563eb',
        pendiente: '#dc2626'
    };

    const animacion = {
        duration: 1500,
        easing: 'easeOutBounce'
    };

    new Chart(document.getElementById('graficoMontos'), {
        type: 'doughnut',
        data: {
            labels: ['Pagados', 'Promesa', 'Pendientes'],
            datasets: [{
                data: [
                    totales.pagados,
                    totales.promesa,
                    totales.pendiente
                ],
                backgroundColor: [
                    colores.pagado,
                    colores.promesa,
                    colores.pendiente
                ]
            }]
        },
        options: {
            animation: animacion,
            plugins: {
                title: {
                    display: true,
                    text: 'Montos Saldo Vencido + Intereses Punitorios ($)'
                }
            }
        }
    });

    new Chart(document.getElementById('graficoCantidad'), {
        type: 'bar',
        data: {
            labels: ['Pagados', 'Promesa', 'Pendientes'],
            datasets: [{
                label: 'Clientes',
                data: [
                    conteo.pagados,
                    conteo.promesa,
                    conteo.pendiente
                ]
            }]
        },
        options: {
            animation: animacion,
            plugins: {
                title: {
                    display: true,
                    text: 'Cantidad de clientes'
                }
            }
        }
    });

    new Chart(document.getElementById('graficoPrediccion'), {
        type: 'pie',
        data: {
            labels: ['Cobrado futuro', 'Pendiente'],
            datasets: [{
                data: [
                    prediccion.pagado_futuro,
                    prediccion.pendiente_futuro
                ],
                backgroundColor: [
                    '#22c55e',
                    '#ef4444'
                ]
            }]
        },
        options: {
            animation: {
                duration: 2000,
                easing: 'easeOutElastic'
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Escenario futuro (Saldo Vencido + Intereses Punitorios)'
                }
            }
        }
    });

});

let clienteId = null;

function openModal(id, cliente, estado, fecha, obs) {
    clienteId = id;

    document.getElementById('modalId').value = id;
    document.getElementById('modalCliente').innerText = cliente;
    document.getElementById('modalFechaInput').value = fecha ?? '';
    document.getElementById('modalObsInput').value = obs ?? '';

    const modal = document.getElementById('modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeModal() {
    const modal = document.getElementById('modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function openTelefonosModal(cliente, telefonos) {
    document.getElementById('telefonosCliente').innerText = cliente ?? '';
    const cont = document.getElementById('telefonosLista');
    cont.innerHTML = '';

    (telefonos ?? []).forEach((t) => {
        const row = document.createElement('div');
        row.className = 'flex items-center justify-between gap-3 p-3 rounded-lg border border-slate-200 bg-slate-50';

        const label = document.createElement('div');
        label.className = 'font-semibold text-slate-800 break-all';
        label.innerText = t;

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-sm font-semibold text-slate-700 hover:bg-slate-100';
        btn.innerText = 'Copiar';
        btn.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(t);
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Copiado',
                    showConfirmButton: false,
                    timer: 1200
                });
            } catch (e) {
                // fallback silencioso
            }
        });

        row.appendChild(label);
        row.appendChild(btn);
        cont.appendChild(row);
    });

    const modal = document.getElementById('telefonos-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeTelefonosModal() {
    const modal = document.getElementById('telefonos-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function marcarPagado() {
    if (!clienteId) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/morosos/pagado/' + clienteId;

    const token = document.createElement('input');
    token.type = 'hidden';
    token.name = '_token';
    token.value = '{{ csrf_token() }}';

    form.appendChild(token);
    document.body.appendChild(form);

    confirmAndSubmitPagado(form, document.getElementById('modalCliente')?.innerText || 'Cliente');
}

function confirmAndSubmitPagado(form, clienteNombre) {
    Swal.fire({
        title: 'Marcar como pagado',
        text: `¿Confirmás que ${clienteNombre} ya pagó?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, marcar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#16a34a'
    }).then((r) => {
        if (!r.isConfirmed) return;

        Swal.fire({
            title: 'Marcando pago…',
            text: 'Por favor esperá',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        form.submit();
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const estadoInput = document.getElementById('estadoFilterInput');
    const estadoChips = document.querySelectorAll('.estado-chip');
    const estadoActivo = String(estadoInput?.value ?? '');
    const selectAll = document.getElementById('check-all-morosos');
    const selectables = document.querySelectorAll('.moroso-select-checkbox');
    const btnMasivo = document.getElementById('btn-marcar-seleccionados');
    const countEl = document.getElementById('seleccionados-count');

    function paintEstadoChips(value) {
        estadoChips.forEach((chip) => {
            const selected = String(chip.dataset.estado ?? '') === String(value ?? '');
            chip.classList.toggle('bg-emerald-600', selected);
            chip.classList.toggle('text-white', selected);
            chip.classList.toggle('border-emerald-600', selected);
            chip.classList.toggle('shadow', selected);
            chip.classList.toggle('bg-white', !selected);
            chip.classList.toggle('text-slate-700', !selected);
            chip.classList.toggle('border-slate-300', !selected);
            chip.classList.toggle('hover:bg-slate-100', !selected);
        });
    }

    paintEstadoChips(estadoActivo);
    estadoChips.forEach((chip) => {
        chip.addEventListener('click', () => {
            if (!estadoInput) return;
            estadoInput.value = chip.dataset.estado ?? '';
            paintEstadoChips(estadoInput.value);
        });
    });

    document.querySelectorAll('tr[data-moroso-id]').forEach((row) => {
        row.addEventListener('click', () => {
            openModal(
                row.dataset.morosoId,
                row.dataset.morosoNombre,
                row.dataset.morosoEstado,
                row.dataset.morosoFecha,
                row.dataset.morosoObs
            );
        });
    });

    function getSelectedIds() {
        return Array.from(selectables)
            .filter((el) => el.checked && !el.disabled)
            .map((el) => Number(el.value))
            .filter((v) => Number.isFinite(v) && v > 0);
    }

    function syncBulkUi() {
        const ids = getSelectedIds();
        const enabled = ids.length > 0;

        if (btnMasivo) btnMasivo.disabled = !enabled;
        if (countEl) countEl.textContent = String(ids.length);

        if (selectAll) {
            const enabledChecks = Array.from(selectables).filter((el) => !el.disabled);
            const checkedCount = enabledChecks.filter((el) => el.checked).length;
            selectAll.checked = enabledChecks.length > 0 && checkedCount === enabledChecks.length;
            selectAll.indeterminate = checkedCount > 0 && checkedCount < enabledChecks.length;
        }
    }

    if (selectAll) {
        selectAll.addEventListener('click', (e) => e.stopPropagation());
        selectAll.addEventListener('change', () => {
            const checked = !!selectAll.checked;
            selectables.forEach((el) => {
                if (el.disabled) return;
                el.checked = checked;
            });
            syncBulkUi();
        });
    }

    selectables.forEach((check) => {
        check.addEventListener('click', (e) => e.stopPropagation());
        check.addEventListener('change', (e) => {
            e.stopPropagation();
            syncBulkUi();
        });
    });

    if (btnMasivo) {
        btnMasivo.addEventListener('click', () => {
            const ids = getSelectedIds();
            if (!ids.length) return;

            Swal.fire({
                title: 'Marcar seleccionados como pagados',
                text: `Se actualizarán ${ids.length} clientes.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, marcar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#16a34a'
            }).then((r) => {
                if (!r.isConfirmed) return;

                Swal.fire({
                    title: 'Actualizando estados…',
                    text: 'Por favor esperá',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = btnMasivo.dataset.action || '/morosos/pagado-masivo';

                const token = document.createElement('input');
                token.type = 'hidden';
                token.name = '_token';
                token.value = '{{ csrf_token() }}';
                form.appendChild(token);

                ids.forEach((id) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = String(id);
                    form.appendChild(input);
                });

                document.body.appendChild(form);
                form.submit();
            });
        });
    }

    syncBulkUi();

    document.querySelectorAll('button[data-telefonos]').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation(); // 🔥 ESTO ES CLAVE

            let phones = [];
            try {
                phones = JSON.parse(btn.dataset.telefonos || '[]');
            } catch (e) {
                phones = [];
            }

            openTelefonosModal(
                btn.dataset.telefonosCliente || '',
                phones
            );
        });
    });
    const promesaForm = document.getElementById('promesaForm');
    if (!promesaForm) return;

    promesaForm.addEventListener('submit', (e) => {
        e.preventDefault();

        const fecha = document.getElementById('modalFechaInput')?.value || '';
        const obs = document.getElementById('modalObsInput')?.value || '';

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
            confirmButtonColor: '#0284c7'
        }).then((r) => {
            if (!r.isConfirmed) return;

            Swal.fire({
                title: 'Guardando promesa…',
                text: 'Por favor esperá',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            promesaForm.submit();
        });
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('btn-whatsapp-test');
    if (!btn) return;

    btn.addEventListener('click', async () => {
        const cfg = document.getElementById('whatsapp-config');
        const url = cfg?.dataset?.url || '';
        const csrf = cfg?.dataset?.csrf || '';

        if (!url || !csrf) {
            Swal.fire({
                icon: 'error',
                title: 'Config faltante',
                text: 'No se encontró url/csrf para enviar WhatsApp.'
            });
            return;
        }

        Swal.fire({
            title: 'Enviando WhatsApp…',
            text: 'Mensaje de prueba',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        try {
            const resp = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({})
            });
            const data = await resp.json().catch(() => ({}));

            if (!resp.ok || data.ok === false) {
                const graphMsg =
                    (data && data.error && (data.error.error && data.error.error.message)) ? data.error.error.message
                    : (data && data.error && data.error.message) ? data.error.message
                    : null;

                const details =
                    (data && data.error && data.error.error_data && (data.error.error_data.details || data.error.error_data.error_subcode)) ? (data.error.error_data.details || data.error.error_data.error_subcode)
                    : null;

                const extra = [graphMsg, details, data && data.exception].filter(Boolean).join('\n');
                const msg = (data && data.message) ? data.message : 'Error enviando WhatsApp';

                const err = new Error(extra ? (msg + '\n\n' + extra) : msg);
                err.debug = { status: data && data.status, error: data && data.error, exception: data && data.exception };
                throw err;
            }

            Swal.fire({
                icon: 'success',
                title: 'Enviado',
                text: data.message || 'Mensaje enviado.'
            });
        } catch (e) {
            Swal.fire({
                icon: 'error',
                title: 'No se pudo enviar',
                html: `<div class="text-left text-sm whitespace-pre-wrap">${(e && e.message) ? e.message : 'Error'}</div>`
            });
        }
    });
});

function readWaAutoSettingsFromDom() {
    const el = document.getElementById('wa-auto-bootstrap');
    try {
        return JSON.parse(el?.dataset?.json || '{}');
    } catch (e) {
        return {};
    }
}

function openWaAutoModal() {
    const s = readWaAutoSettingsFromDom();
    document.getElementById('waAutoEnabled').checked = !!s.enabled;
    document.getElementById('waAutoDay').value = String(s.day_of_month ?? 1);
    document.getElementById('waAutoTime').value = String(s.time ?? '09:00');

    const modal = document.getElementById('wa-auto-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeWaAutoModal() {
    const modal = document.getElementById('wa-auto-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

document.addEventListener('DOMContentLoaded', () => {
    const openBtn = document.getElementById('btn-wa-auto');
    const saveBtn = document.getElementById('waAutoSave');
    const api = document.getElementById('wa-auto-api');

    if (openBtn) {
        openBtn.addEventListener('click', (e) => {
            e.preventDefault();
            openWaAutoModal();
        });
    }

    if (saveBtn) {
        saveBtn.addEventListener('click', async () => {
            const url = api?.dataset?.updateUrl || '';
            const csrf = api?.dataset?.csrf || '';
            if (!url || !csrf) {
                Swal.fire({ icon: 'error', title: 'Config faltante', text: 'No se encontró url/csrf.' });
                return;
            }

            const enabled = document.getElementById('waAutoEnabled').checked;
            const day = parseInt(document.getElementById('waAutoDay').value, 10);
            const time = document.getElementById('waAutoTime').value;

            if (!Number.isFinite(day) || day < 1 || day > 31) {
                Swal.fire({ icon: 'error', title: 'Día inválido', text: 'Tiene que ser entre 1 y 31.' });
                return;
            }
            if (!time) {
                Swal.fire({ icon: 'error', title: 'Hora inválida', text: 'Seleccioná una hora.' });
                return;
            }

            Swal.fire({
                title: 'Guardando…',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            try {
                const resp = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        enabled,
                        day_of_month: day,
                        time
                    })
                });

                const data = await resp.json().catch(() => ({}));
                if (!resp.ok || data.ok === false) {
                    const msg = (data && data.message) ? data.message : 'No se pudo guardar';
                    const errs = (data && data.errors) ? JSON.stringify(data.errors) : '';
                    throw new Error(errs ? (msg + '\n\n' + errs) : msg);
                }

                const boot = document.getElementById('wa-auto-bootstrap');
                if (boot && data.settings) {
                    boot.dataset.json = JSON.stringify(data.settings);
                }

                Swal.fire({ icon: 'success', title: 'Listo', text: 'Configuración guardada.' });
                closeWaAutoModal();
            } catch (e) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    html: `<div class="text-left text-sm whitespace-pre-wrap">${(e && e.message) ? e.message : 'Error'}</div>`
                });
            }
        });
    }
});

        document.addEventListener('DOMContentLoaded', () => {
            const btn = document.getElementById('btn-fullscreen-table');
            const container = document.getElementById('tabla-container');

            if (!btn || !container) return;

            btn.addEventListener('click', async () => {
                try {
                    if (!document.fullscreenElement) {
                        await container.requestFullscreen();
                    } else {
                        await document.exitFullscreen();
                    }
                } catch (e) {
                    console.error('Error fullscreen:', e);
                }
            });
        });
        document.addEventListener('fullscreenchange', () => {
            const icon = document.querySelector('#btn-fullscreen-table i');
            const text = document.querySelector('#btn-fullscreen-table span');

            if (!icon || !text) return;

            if (document.fullscreenElement) {
                icon.classList.replace('fa-expand', 'fa-compress');
                text.textContent = 'Salir';
            } else {
                icon.classList.replace('fa-compress', 'fa-expand');
                text.textContent = 'Pantalla completa';
            }
        });
        document.addEventListener('DOMContentLoaded', () => {

        const dataEl = document.getElementById('resumen-data');
        const data = JSON.parse(dataEl.dataset.json || '{}');

        const rangos = [
            { key: '0_30', label: '0-30 días' },
            { key: '30_60', label: '30-60 días' },
            { key: '60_90', label: '60-90 días' },
            { key: '90_120', label: '90-120 días' },
            { key: '120_150', label: '120-150 días' },
            { key: '150_180', label: '150-180 días' },
            { key: '180_365', label: '180-365' },
            { key: '365_plus', label: '365+ días' }
        ];

        let index = 0;

        const tbody = document.getElementById('resumen-body');
        const label = document.getElementById('rango-actual');

        function render() {

            const rango = rangos[index];
            const resumen = data[rango.key] || {};

            label.textContent = rango.label;
            filtrarTabla(rango);

            tbody.innerHTML = '';

            let totalMorosos = 0;
            let totalTitulares = 0;
            let totalPagaron = 0;

            Object.entries(resumen).forEach(([loc, r]) => {

                const porcentaje = r.total > 0
                    ? ((r.pagaron / r.total) * 100).toFixed(0)
                    : 0;

                totalMorosos += r.total;
                totalTitulares += r.tit;
                totalPagaron += r.pagaron;

                tbody.insertAdjacentHTML('beforeend', `
                    <tr>
                        <td class="border px-3 py-2">${loc}</td>
                        <td class="border px-3 py-2">${r.total}</td>
                        <td class="border px-3 py-2">${r.tit}</td>
                        <td class="border px-3 py-2">${r.gar}</td>
                        <td class="border px-3 py-2">${r.pagaron}</td>
                        <td class="border px-3 py-2">${porcentaje}%</td>
                        <td class="border px-3 py-2">${r.wsp}</td>
                        <td class="border px-3 py-2">${r.no_wsp}</td>
                        <td class="border px-3 py-2">${r.no_tel}</td>
                        <td class="border px-3 py-2">${r.carta}</td>
                    </tr>
                `);
            });

            document.getElementById('total-morosos').textContent = totalMorosos;
            document.getElementById('total-titulares').textContent = totalTitulares;
            document.getElementById('total-pagaron').textContent = totalPagaron;
            document.getElementById('total-deben').textContent = totalTitulares - totalPagaron;
        }

        document.getElementById('prev-rango').addEventListener('click', () => {
            if (index > 0) {
                index--;
                render();
            }
        });

        document.getElementById('next-rango').addEventListener('click', () => {
            if (index < rangos.length - 1) {
                index++;
                render();
            }
        });

        render();
        });
        document.addEventListener('DOMContentLoaded', () => {

        const btn = document.getElementById('toggle-resumen');
        const resumen = document.getElementById('resumen-container');
        const tabla = document.getElementById('tabla-container');

        let mostrandoResumen = false;

        btn.addEventListener('click', () => {

            mostrandoResumen = !mostrandoResumen;

            if (mostrandoResumen) {
                resumen.classList.remove('hidden');
                tabla.classList.add('hidden');
                btn.textContent = 'Ver tabla';
            } else {
                resumen.classList.add('hidden');
                tabla.classList.remove('hidden');
                btn.textContent = 'Ver resumen';
            }
        });

        });

        function filtrarTabla(rango) {

const filas = document.querySelectorAll('#tabla-container tbody tr');

filas.forEach(fila => {

    const dias = parseInt(fila.dataset.dias || '0');

    let mostrar = false;

    switch (rango.key) {

        case '0_30':
            mostrar = dias >= 0 && dias <= 30;
            break;

        case '30_60':
            mostrar = dias > 30 && dias <= 60;
            break;

        case '60_90':
            mostrar = dias > 60 && dias <= 90;
            break;

        case '90_120':
            mostrar = dias > 90 && dias <= 120;
            break;

        case '120_150':
            mostrar = dias > 120 && dias <= 150;
            break;

        case '150_180':
            mostrar = dias > 150 && dias <= 180;
            break;

        case '180_365':
            mostrar = dias > 180 && dias <= 365;
            break;

        case '365_plus':
            mostrar = dias >= 365;
            break;
    }

    fila.style.display = mostrar ? '' : 'none';
});
}



            document.addEventListener('DOMContentLoaded', () => {

            const btn = document.getElementById('btn-import-excel');
            const modal = document.getElementById('excel-modal');

            if (btn && modal) {
                btn.addEventListener('click', () => {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                });
            }

            });

            function closeExcelModal() {
            const modal = document.getElementById('excel-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            }

            
document.addEventListener('DOMContentLoaded', () => {
    const formExcel = document.getElementById('form-import-excel');

    if (!formExcel) return;

    formExcel.addEventListener('submit', function () {
        let segundos = 0;

        Swal.fire({
            title: 'Importando Excel...',
            html: `
                <div class="text-sm text-slate-600">
                    Procesando archivo. No cierres esta ventana.
                </div>
                <div class="mt-3 text-lg font-bold text-emerald-600">
                    <span id="contador-importacion">0</span> segundos
                </div>
            `,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();

                const contador = document.getElementById('contador-importacion');

                window.importExcelTimer = setInterval(() => {
                    segundos++;
                    if (contador) {
                        contador.textContent = segundos;
                    }
                }, 1000);
            }
        });
    });
});
</script>


 <style>
 .limit-2-lines {
    display: -webkit-box;
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;
    overflow: hidden;
    cursor: pointer;
}

.limit-2-lines.expanded {
    -webkit-line-clamp: unset;
    overflow: visible;
}

.table-auto-layout {
    table-layout: auto;
}
 #tabla-container:fullscreen {
    width: 100vw;
    height: 100vh;
    padding: 12px;
    background: #f8fafc;
    display: flex;
    flex-direction: column;
}

#tabla-container:fullscreen .border-b {
    flex-shrink: 0;
}

#tabla-container:fullscreen .overflow-x-auto {
    flex: 1;
    max-height: 100% !important;
    overflow: auto !important;
}

#tabla-container:fullscreen table {
    font-size: 14px;
}

#tabla-container:fullscreen thead {
    position: sticky;
    top: 0;
    z-index: 30;
}

#tabla-container .morosos-sticky-table {
--m-cw1: 2.75rem;
--m-cw2: 3.5rem;
--m-cw3: 7.25rem;
--m-cw4: 7.25rem;
--m-cw5: 12rem;
--m-l1: 0;
--m-l2: var(--m-cw1);
--m-l3: calc(var(--m-cw1) + var(--m-cw2));
--m-l4: calc(var(--m-cw1) + var(--m-cw2) + var(--m-cw3));
--m-l5: calc(var(--m-cw1) + var(--m-cw2) + var(--m-cw3) + var(--m-cw4));
}
#tabla-container .morosos-sticky-table thead th:nth-child(1),
#tabla-container .morosos-sticky-table tbody td:nth-child(1) {
position: sticky;
left: var(--m-l1);
min-width: var(--m-cw1);
max-width: var(--m-cw1);
width: var(--m-cw1);
box-sizing: border-box;
}
#tabla-container .morosos-sticky-table thead th:nth-child(2),
#tabla-container .morosos-sticky-table tbody td:nth-child(2) {
position: sticky;
left: var(--m-l2);
min-width: var(--m-cw2);
max-width: var(--m-cw2);
width: var(--m-cw2);
box-sizing: border-box;
}
#tabla-container .morosos-sticky-table thead th:nth-child(3),
#tabla-container .morosos-sticky-table tbody td:nth-child(3) {
position: sticky;
left: var(--m-l3);
min-width: var(--m-cw3);
max-width: var(--m-cw3);
width: var(--m-cw3);
box-sizing: border-box;
}
#tabla-container .morosos-sticky-table thead th:nth-child(4),
#tabla-container .morosos-sticky-table tbody td:nth-child(4) {
position: sticky;
left: var(--m-l4);
min-width: var(--m-cw4);
max-width: var(--m-cw4);
width: var(--m-cw4);
box-sizing: border-box;
}
#tabla-container .morosos-sticky-table thead th:nth-child(5),
#tabla-container .morosos-sticky-table tbody td:nth-child(5) {
position: sticky;
left: var(--m-l5);
min-width: var(--m-cw5);
max-width: var(--m-cw5);
width: var(--m-cw5);
box-sizing: border-box;
box-shadow: 4px 0 10px -4px rgba(15, 23, 42, 0.18);
}
#tabla-container .morosos-sticky-table thead th:nth-child(1) { z-index: 45; }
#tabla-container .morosos-sticky-table thead th:nth-child(2) { z-index: 44; }
#tabla-container .morosos-sticky-table thead th:nth-child(3) { z-index: 43; }
#tabla-container .morosos-sticky-table thead th:nth-child(4) { z-index: 42; }
#tabla-container .morosos-sticky-table thead th:nth-child(5) { z-index: 41; }
#tabla-container .morosos-sticky-table tbody td:nth-child(1) { z-index: 15; }
#tabla-container .morosos-sticky-table tbody td:nth-child(2) { z-index: 14; }
#tabla-container .morosos-sticky-table tbody td:nth-child(3) { z-index: 13; }
#tabla-container .morosos-sticky-table tbody td:nth-child(4) { z-index: 12; }
#tabla-container .morosos-sticky-table tbody td:nth-child(5) { z-index: 11; }
#tabla-container .morosos-sticky-table thead th:nth-child(-n+5) {
top: 0;
background-color: rgb(241 245 249);
}
#tabla-container .morosos-sticky-table tbody td:nth-child(-n+5) {
background-color: #fff;
}
#tabla-container .morosos-sticky-table tbody tr:hover td:nth-child(-n+5) {
background-color: rgb(248 250 252);
}
#tabla-container .morosos-sticky-table tbody tr.moroso-row--pagado td {
background-color:rgb(93, 243, 108);
}
#tabla-container .morosos-sticky-table tbody tr.moroso-row--pagado:hover td {
background-color: rgb(93, 243, 108);
}
#tabla-container .morosos-sticky-table tbody tr.moroso-row--promesa td {
background-color:rgb(98, 223, 237); 
}
#tabla-container .morosos-sticky-table tbody tr.moroso-row--promesa:hover td {
background-color: rgb(98, 223, 237);
}
#tabla-container .morosos-sticky-table tbody tr.moroso-row--default td {
background-color: #fff;
}
#tabla-container .morosos-sticky-table tbody tr.moroso-row--default:hover td {
background-color: rgb(248 250 252);
}
#tabla-container .morosos-sticky-table tbody td.moroso-empty-placeholder {
position: static;
left: auto;
min-width: unset;
max-width: unset;
width: auto;
z-index: auto;
box-shadow: none;
}


</style>