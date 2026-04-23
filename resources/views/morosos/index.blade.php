<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<x-app-layout> 
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
            <div>
                <h2 class="text-2xl font-bold text-slate-800"> Morosos </h2>
                <p class="text-sm text-slate-500"> Gestión y seguimiento de clientes en mora </p>
            </div>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    id="btn-wa-auto"
                    class="inline-flex items-center justify-center h-10 w-11 rounded-xl border border-emerald-200 bg-white hover:bg-emerald-50 text-emerald-700 shadow-sm"
                    title="Automatización WhatsApp"
                >
                    <svg viewBox="0 0 24 24" class="w-6 h-6" aria-hidden="true">
                        <path fill="currentColor" d="M7.2 3.1c-.2-.6.1-1.2.7-1.4.6-.2 1.2.1 1.4.7.1.3.1.6 0 .9.6.3 1.1.7 1.5 1.2.4-.5.9-.9 1.5-1.2-.1-.3-.1-.6 0-.9.2-.6.8-.9 1.4-.7.6.2.9.8.7 1.4-.1.3-.3.5-.6.7.3.6.4 1.3.4 2 0 2.2-1.2 4.1-3 5.1v1.1c0 .6-.4 1-1 1s-1-.4-1-1v-1.1c-1.8-1-3-2.9-3-5.1 0-.7.1-1.4.4-2-.3-.2-.5-.4-.6-.7z"/>
                        <path fill="currentColor" fill-opacity=".25" d="M6 14.5c0-1.1.9-2 2-2h8c1.1 0 2 .9 2 2v1c0 3.3-2.7 6-6 6s-6-2.7-6-6v-1z"/>
                    </svg>
                </button>
                <button
                    type="button"
                    id="btn-whatsapp-test"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold shadow"
                    title="Enviar WhatsApp de prueba"
                >
                    <svg viewBox="0 0 32 32" class="w-5 h-5 fill-white" aria-hidden="true">
                        <path d="M19.11 17.44c-.28-.14-1.64-.81-1.9-.9-.25-.09-.44-.14-.63.14-.19.28-.72.9-.88 1.09-.16.19-.32.21-.6.07-.28-.14-1.18-.43-2.25-1.38-.83-.74-1.39-1.65-1.55-1.93-.16-.28-.02-.43.12-.57.13-.13.28-.32.42-.48.14-.16.19-.28.28-.46.09-.19.05-.35-.02-.49-.07-.14-.63-1.52-.86-2.08-.23-.55-.47-.48-.63-.49h-.54c-.19 0-.49.07-.74.35-.25.28-.97.95-.97 2.33 0 1.38 1 2.72 1.14 2.9.14.19 1.97 3.01 4.77 4.22.66.29 1.18.46 1.58.59.66.21 1.26.18 1.74.11.53-.08 1.64-.67 1.87-1.32.23-.65.23-1.21.16-1.32-.07-.12-.25-.19-.53-.33z"/>
                        <path d="M26.67 5.33A13.21 13.21 0 0 0 16.02 0C8.73 0 2.8 5.93 2.8 13.22c0 2.33.61 4.6 1.77 6.6L2.67 32l12.47-1.86a13.17 13.17 0 0 0 6.33 1.62h.01c7.29 0 13.22-5.93 13.22-13.22 0-3.53-1.37-6.85-3.86-9.34zM21.49 29.2h-.01a11 11 0 0 1-5.61-1.53l-.4-.24-7.4 1.1 1.1-7.2-.26-.42a10.96 10.96 0 1 1 12.58 8.29z"/>
                    </svg>
                    WhatsApp
                </button>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">

        <div class="bg-white rounded-xl shadow border border-slate-200">
            <div class="p-5">
                <form method="GET" action="{{ route('morosos.index') }}" 
                      class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">

                    <div>
                        <label class="block text-sm font-semibold text-slate-700">
                            Estado
                        </label>
                        <select name="estado"
                            class="mt-1 w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">Todos</option>
                            @foreach ($estados as $e)
                                <option value="{{ $e->id }}" @selected(($filters['estado'] ?? '') === $e)>
                                    {{ $e->estado }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700">
                            Periodo
                        </label>
                        <select name="periodo"
                            class="mt-1 w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">Todos</option>
                            @foreach ($periodos as $key => $p)
                                <option value="{{ $key }}" @selected(($filters['periodo'] ?? '') === $key)>
                                    {{ $p['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700">
                            Localidad
                        </label>
                        <select name="localidad"
                            class="mt-1 w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">Todas</option>
                            @foreach ($localidades as $loc)
                                <option value="{{ $loc->id }}" @selected(($filters['localidad'] ?? '') === $loc)>
                                    {{ $loc->nombre_corto }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <x-primary-button>
                            Filtrar
                        </x-primary-button>

                        <a href="{{ route('morosos.index') }}"
                           class="px-4 py-2 rounded-lg border text-sm font-semibold text-slate-700 hover:bg-slate-100">
                            Limpiar
                        </a>
                    </div>

                </form>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow border border-slate-200 overflow-hidden">

            <div class="p-4 border-b bg-slate-50 text-sm text-slate-600">
                Mostrando 
                <span class="font-semibold text-slate-800">
                    {{ count($morosos) }}
                </span> registros
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full w-full border-separate border-spacing-0">

                    <thead class="bg-slate-800 text-white text-sm sticky top-0 z-20">
                        <tr>
                        <th class="py-3 px-4 border border-slate-700 bg-slate-800 whitespace-nowrap">Orden</th>
                        <th class="py-3 px-4 border border-slate-700 bg-slate-800 whitespace-nowrap">Tit. Gar.</th>
                            <th class="py-3 px-4 border border-slate-700 bg-slate-800 whitespace-nowrap">
                                DNI Titular
                            </th>
                            <th class="py-3 px-4 border border-slate-700 bg-slate-800 whitespace-nowrap">
                                DNI
                            </th>

                            <th class="py-3 px-4 border border-slate-700 bg-slate-800 whitespace-nowrap">
                                Nombre
                            </th>

                            <th class="py-3 px-4 border border-slate-700 text-center bg-slate-800 whitespace-nowrap">
                                Calle
                            </th>

                            <th class="py-3 px-4 border border-slate-700 text-center bg-slate-800 whitespace-nowrap">
                                Observaciones
                            </th>
                            <th class="py-3 px-4 border border-slate-700 bg-slate-800 whitespace-nowrap">Localidad</th>
                            <th class="py-3 px-4 border border-slate-700 text-center bg-slate-800 whitespace-nowrap">Teléfono</th>
                            <th class="py-3 px-4 border border-slate-700 text-center bg-slate-800 whitespace-nowrap">Empleador</th>
                            <th class="py-3 px-4 border border-slate-700 text-right bg-slate-800 whitespace-nowrap">Días</th>
                            <th class="py-3 px-4 border border-slate-700 text-center bg-slate-800 whitespace-nowrap">Fecha Ult. Pago</th>
                            <th class="py-3 px-4 border border-slate-700 text-center bg-slate-800 whitespace-nowrap">Saldo Vencido</th>
                            <th class="py-3 px-4 border border-slate-700 bg-slate-800 whitespace-nowrap">Int. Pun.</th>
                            <th class="py-3 px-4 border border-slate-700 text-center bg-slate-800 whitespace-nowrap">Saldo Total</th>
                            <th class="py-3 px-4 border border-slate-700 text-center bg-slate-800 whitespace-nowrap">Estado</th>
                            <th class="py-3 px-4 border border-slate-700 text-right bg-slate-800 whitespace-nowrap">FEB</th>
                            <th class="py-3 px-4 border border-slate-700 text-right bg-slate-800 whitespace-nowrap">Imp.</th>
                            <th class="py-3 px-4 border border-slate-700 text-right bg-slate-800 whitespace-nowrap">MAR</th>
                            <th class="py-3 px-4 border border-slate-700 text-right bg-slate-800 whitespace-nowrap">Imp.</th>
                            <th class="py-3 px-4 border border-slate-700 text-right bg-slate-800 whitespace-nowrap">Datos Adicionales</th>
                            <th class="py-3 px-4 border border-slate-700 text-right bg-slate-800 whitespace-nowrap">WSP</th>
                            <th class="py-3 px-4 border border-slate-700 text-right bg-slate-800 whitespace-nowrap">Tiene WSP</th>
                            <th class="py-3 px-4 border border-slate-700 text-right bg-slate-800 whitespace-nowrap">SMS</th>
                            <th class="py-3 px-4 border border-slate-700 text-right bg-slate-800 whitespace-nowrap">LLAMADA</th>
                            <th class="py-3 px-4 border border-slate-700 text-right bg-slate-800 whitespace-nowrap">Tiene Tel</th>
                            <th class="py-3 px-4 border border-slate-700 text-right bg-slate-800 whitespace-nowrap">Carta</th>
                            <th class="py-3 px-4 border border-slate-700 text-right bg-slate-800 whitespace-nowrap">Fecha Env. Carta</th>
                            <th class="py-3 px-4 border border-slate-700 text-right bg-slate-800 whitespace-nowrap">Fecha Promesa Pago</th>
                            <th class="py-3 px-4 border border-slate-700 text-right bg-slate-800 whitespace-nowrap">Observaciones Promesa</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-slate-800">
                        @php $c = 1; @endphp
                        @forelse ($morosos as $m)

                        @php
                            $rowClass = match ((int)$m->id_estado) {
                                4 => 'bg-green-100', // Pagado
                                3 => 'bg-orange-100',   // Compromiso
                                default => 'bg-white',
                            };

                            $badge = match ($m->estado) {
                                'Pendiente' => 'bg-yellow-100 text-yellow-800',
                                'En gestión' => 'bg-green-100 text-green-800',
                                'Compromiso de pago' => 'bg-blue-100 text-blue-800',
                                'Incobrable' => 'bg-red-100 text-red-800',
                                default => 'bg-gray-100 text-gray-700',
                            };

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
                            class="{{ $rowClass }} hover:bg-emerald-200/70 transition cursor-pointer align-top"
                            data-moroso-id="{{ $m->id }}"
                            data-moroso-nombre="{{ e($m->nombre) }}"
                            data-moroso-estado="{{ e($m->estado) }}"
                            data-moroso-fecha="{{ e($m->fecha_promesa_pago) }}"
                            data-moroso-obs="{{ e($m->observaciones_promesa) }}"
                        >

                            <td class="py-3 px-4 border border-slate-200 font-medium text-slate-700 whitespace-nowrap">
                                {{ $c }}
                            </td>
                            <td class="py-3 px-4 border border-slate-200 font-medium whitespace-nowrap">
                                {{ $m->titular_garantia }}
                            </td>

                            <td class="py-3 px-4 border border-slate-200 text-slate-600 whitespace-nowrap">
                                {{ $m->documento_titular }}
                            </td>
                            <td class="py-3 px-4 border border-slate-200 text-slate-600 whitespace-nowrap">
                                {{ $m->documento }}
                            </td>

                            <td class="py-3 px-4 border border-slate-200 whitespace-nowrap">
                                {{ $m->nombre }}
                            </td>
                            <td class="py-3 px-4 border border-slate-200 max-w-[22rem] whitespace-normal break-words">
                                {{ $m->calle }}
                            </td>
                            <td class="py-3 px-4 border border-slate-200 max-w-[28rem] whitespace-normal break-words">
                                {{ $m->observaciones }}
                            </td>
                            <td class="py-3 px-4 border border-slate-200 whitespace-nowrap">
                                {{ $m->localidad }}
                            </td>
                            <td class="py-3 px-4 border border-slate-200">
                                @if (count($phones) <= 1)
                                    <span class="whitespace-nowrap">{{ $phones[0] ?? '' }}</span>
                                @else
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-800 font-semibold whitespace-nowrap shadow-sm"
                                        data-telefonos-cliente="{{ e($m->nombre) }}"
                                        data-telefonos='@json($phones)'
                                        onclick="event.stopPropagation();"
                                        title="Ver todos los teléfonos"
                                    >
                                        <span>{{ $phones[0] }}</span>
                                        <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-700">
                                            +{{ count($phones) - 1 }}
                                        </span>
                                    </button>
                                @endif
                            </td>
                            <td class="py-3 px-4 border border-slate-200 max-w-[18rem] whitespace-normal break-words">
                                {{ $m->empleador }}
                            </td>
                            <td class="py-3 px-4 border border-slate-200 text-right whitespace-nowrap">
                                {{ $m->dias }}
                            </td>
                            <td class="py-3 px-4 border border-slate-200 whitespace-nowrap">
                                {{ $m->fecha_ultimo_pago }}
                            </td>
                            <td class="py-3 px-4 border border-slate-200 text-right whitespace-nowrap">
                                {{ $m->saldo_vencido }}
                            </td>
                            <td class="py-3 px-4 border border-slate-200 text-right whitespace-nowrap">
                                {{ $m->interes_punitorio }}
                            </td>
                            <td class="py-3 px-4 border border-slate-200 text-right whitespace-nowrap">
                                {{ $m->saldo_total }}
                            </td>
                            <td class="py-3 px-4 border border-slate-200 text-center whitespace-nowrap">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $badge }}">
                                    {{ $m->estado }}
                                </span>
                            </td>
                            <td class="py-3 px-4 border border-slate-200 text-right whitespace-nowrap">
                                {{ $m->feb }}
                            </td>
                            <td class="py-3 px-4 border border-slate-200 text-right whitespace-nowrap">
                                {{ $m->feb_importe }}
                            </td>
                            <td class="py-3 px-4 border border-slate-200 text-right whitespace-nowrap">
                                {{ $m->mar }}
                            </td>
                            <td class="py-3 px-4 border border-slate-200 text-right whitespace-nowrap">
                                {{ $m->mar_importe }}
                            </td>
                            <td class="py-3 px-4 border border-slate-200 max-w-[26rem] whitespace-normal break-words">
                                {{ $m->datos_adicionales }}
                            </td>
                            <td class="py-3 px-4 border border-slate-200 text-center whitespace-nowrap">
                                {{ $m->wsp }}
                            </td>
                            <td class="py-3 px-4 border border-slate-200 text-center whitespace-nowrap">
                                {{ $m->tiene_wsp }}
                            </td>
                            <td class="py-3 px-4 border border-slate-200 text-center whitespace-nowrap">
                                {{ $m->sms }}
                            </td>
                            <td class="py-3 px-4 border border-slate-200 text-center whitespace-nowrap">
                                {{ $m->llamada }}
                            </td>
                            <td class="py-3 px-4 border border-slate-200 text-center whitespace-nowrap">
                                {{ $m->tiene_tel }}
                            </td>
                            <td class="py-3 px-4 border border-slate-200 text-center whitespace-nowrap">
                                {{ $m->carta }}
                            </td>
                            <td class="py-3 px-4 border border-slate-200 whitespace-nowrap">
                                {{ $m->fecha_envio_carta }}
                            </td>
                            <td class="py-3 px-4 border border-slate-200 whitespace-nowrap">
                                {{ $m->fecha_promesa_pago }}
                            </td>
                            <td class="py-3 px-4 border border-slate-200 max-w-[28rem] whitespace-normal break-words">
                                {{ $m->observaciones_promesa }}
                            </td>

                            @php $c++; @endphp

                        </tr>

                        @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-500 border">
                                No hay morosos para los filtros seleccionados.
                            </td>
                        </tr>
                        @endforelse

                        </tbody>
                </table>
            </div>
                    <div id="modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
                        <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 relative">

                            <button onclick="closeModal()" class="absolute top-2 right-2 text-slate-500 text-xl">✕</button>

                            <h2 class="text-xl font-bold mb-4" id="modalCliente"></h2>

                            <form id="promesaForm" method="POST" action="{{ route('morosos.promesa') }}">
                                @csrf

                                <input type="hidden" name="id" id="modalId">

                                <div class="mb-3">
                                    <label class="text-sm font-semibold">Fecha compromiso</label>
                                    <input type="date" name="fecha_promesa_pago" id="modalFechaInput"
                                        class="w-full border rounded-lg p-2 mt-1">
                                </div>

                                <div class="mb-3">
                                    <label class="text-sm font-semibold">Observaciones</label>
                                    <textarea name="observaciones_promesa" id="modalObsInput"
                                            class="w-full border rounded-lg p-2 mt-1"></textarea>
                                </div>

                                <div class="flex justify-between mt-4">

                                    <button type="submit"
                                            class="bg-sky-600 text-white px-4 py-2 rounded-lg">
                                        Guardar promesa
                                    </button>

                                    <button type="button"
                                            onclick="marcarPagado()"
                                            class="bg-green-600 text-white px-4 py-2 rounded-lg">
                                        Marcar pagado
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
</x-app-layout>

<div id="wa-auto-modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 relative">
        <button type="button" onclick="closeWaAutoModal()" class="absolute top-2 right-2 text-slate-500 text-xl">✕</button>

        <h2 class="text-xl font-bold mb-1">WhatsApp automático</h2>
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

<div
    id="whatsapp-config"
    data-url="{{ route('morosos.whatsapp_test') }}"
    data-csrf="{{ csrf_token() }}"
></div>

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
                    text: 'Montos ($)'
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
                    text: 'Escenario futuro'
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

    Swal.fire({
        title: 'Marcar como pagado',
        text: '¿Confirmás que este cliente ya pagó?',
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

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/morosos/pagado/' + clienteId;

        const token = document.createElement('input');
        token.type = 'hidden';
        token.name = '_token';
        token.value = '{{ csrf_token() }}';

        form.appendChild(token);
        document.body.appendChild(form);
        form.submit();
    });
}

document.addEventListener('DOMContentLoaded', () => {
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

    document.querySelectorAll('button[data-telefonos]').forEach((btn) => {
        btn.addEventListener('click', () => {
            let phones = [];
            try {
                phones = JSON.parse(btn.dataset.telefonos || '[]');
            } catch (e) {
                phones = [];
            }
            openTelefonosModal(btn.dataset.telefonosCliente || '', phones);
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
 </script>