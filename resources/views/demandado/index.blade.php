<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Demandados</h2>
                <p class="text-xs text-slate-500">Cartera judicial y gestión de cobranzas</p>
            </div>

            <a href="#"
               class="px-3 py-2 bg-emerald-600 text-white text-xs font-bold rounded-lg hover:bg-emerald-700">
                + Nuevo demandado
            </a>
        </div>
    </x-slot>

    <div class="py-4 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">

                <div class="px-4 py-3 border-b border-slate-200 bg-white flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Listado de demandados</h3>
                        <p class="text-xs text-slate-500">Documentos, contacto, estado legal y última gestión</p>
                    </div>

                    <span class="text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1 rounded-full">
                        {{ $demandadosCantidad }} registros
                    </span>
                </div>

                <div id="tabla-container" class="bg-white rounded-xl shadow border border-slate-200 overflow-hidden">

                    <div class="overflow-auto max-h-[700px]">

                        <table class="morosos-sticky-table min-w-full w-full border-collapse text-xs">

                            <thead class="bg-slate-100 text-slate-700 text-[11px] uppercase tracking-wide sticky top-0 z-40">
                                <tr>
                                    <th class="py-2 px-2 border whitespace-nowrap">ID</th>
                                    <th class="py-2 px-2 border whitespace-nowrap">Demandado</th>
                                    <th class="py-2 px-2 border whitespace-nowrap">Documento</th>
                                    <th class="py-2 px-2 border whitespace-nowrap">Teléfono</th>
                                    <th class="py-2 px-2 border whitespace-nowrap">Observaciones</th>
                                    <th class="py-2 px-2 border whitespace-nowrap">Estado</th>
                                    <th class="py-2 px-2 border whitespace-nowrap">Fecha Demanda</th>
                                    <th class="py-2 px-2 border whitespace-nowrap">Última Gestión</th>
                                    <th class="py-2 px-2 border whitespace-nowrap">Acciones</th>
                                </tr>
                            </thead>

                            <tbody class="text-slate-700">

                                <?php if (!empty($demandados)) { ?>

                                    <?php foreach ($demandados as $demandado) { ?>

                                        <?php

                                            $estadoClass = 'bg-slate-100 text-slate-700 ring-1 ring-inset ring-slate-200';

                                            if ($demandado->estado == 'Pendiente') {
                                                $estadoClass = 'bg-yellow-100 text-yellow-800 ring-1 ring-inset ring-yellow-200';
                                            }

                                            if ($demandado->estado == 'En proceso') {
                                                $estadoClass = 'bg-blue-100 text-blue-800 ring-1 ring-inset ring-blue-200';
                                            }

                                            if ($demandado->estado == 'Notificado') {
                                                $estadoClass = 'bg-purple-100 text-purple-800 ring-1 ring-inset ring-purple-200';
                                            }

                                            if ($demandado->estado == 'Acuerdo') {
                                                $estadoClass = 'bg-sky-100 text-sky-800 ring-1 ring-inset ring-sky-200';
                                            }

                                            if ($demandado->estado == 'Judicial') {
                                                $estadoClass = 'bg-red-100 text-red-800 ring-1 ring-inset ring-red-200';
                                            }

                                            if ($demandado->estado == 'Embargo') {
                                                $estadoClass = 'bg-orange-100 text-orange-800 ring-1 ring-inset ring-orange-200';
                                            }

                                            if ($demandado->estado == 'Finalizado') {
                                                $estadoClass = 'bg-emerald-100 text-emerald-800 ring-1 ring-inset ring-emerald-200';
                                            }

                                        ?>

                                        <tr class="border-b hover:bg-slate-50 transition">

                                            <td class="py-1.5 px-2 border text-center font-semibold whitespace-nowrap">
                                                #{{ $demandado->id }}
                                            </td>

                                            <td class="py-1.5 px-2 border whitespace-nowrap">

                                                <div class="font-semibold text-slate-800">
                                                    {{ $demandado->nombre_completo }}
                                                </div>

                                                <div class="text-[10px] text-slate-400">
                                                    Gestión judicial
                                                </div>

                                            </td>

                                            <td class="py-1.5 px-2 border whitespace-nowrap">

                                                <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 text-[11px] font-medium">
                                                    {{ $demandado->documento }}
                                                </span>

                                            </td>

                                            <td class="py-1.5 px-2 border text-center whitespace-nowrap">

                                                <?php if($demandado->telefono != null && $demandado->telefono != '') { ?>

                                                    <span class="font-medium text-slate-700">
                                                        {{ $demandado->telefono }}
                                                    </span>

                                                <?php } else { ?>

                                                    <span class="text-red-500 text-[11px]">
                                                        Sin teléfono
                                                    </span>

                                                <?php } ?>

                                            </td>

                                            <td class="py-1.5 px-2 border max-w-[220px] whitespace-normal break-words align-top">

                                                <?php if($demandado->observaciones != null && $demandado->observaciones != '') { ?>

                                                    {{ $demandado->observaciones }}

                                                <?php } else { ?>

                                                    <span class="text-slate-400 italic">
                                                        Sin observaciones
                                                    </span>

                                                <?php } ?>

                                            </td>

                                            <td class="py-1.5 px-2 border text-center whitespace-nowrap">

                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-medium {{ $estadoClass }}">
                                                    {{ $demandado->estado }}
                                                </span>

                                            </td>

                                            <td class="py-1.5 px-2 border whitespace-nowrap text-center">

                                                <?php if($demandado->fecha_demanda != null) { ?>

                                                    {{ $demandado->fecha_demanda }}

                                                <?php } else { ?>

                                                    -

                                                <?php } ?>

                                            </td>

                                            <td class="py-1.5 px-2 border whitespace-nowrap text-center">

                                                <?php if($demandado->fecha_ultima_gestion != null) { ?>

                                                    {{ $demandado->fecha_ultima_gestion }}

                                                <?php } else { ?>

                                                    -

                                                <?php } ?>

                                            </td>

                                            <td class="py-1.5 px-2 border text-center whitespace-nowrap">

                                                <div class="flex items-center justify-center gap-1">

                                                    <button
                                                        class="px-2 py-1 rounded bg-slate-800 hover:bg-slate-700 text-white text-[10px] font-semibold">
                                                        Ver
                                                    </button>

                                                    <button
                                                        class="px-2 py-1 rounded bg-red-700 hover:bg-red-600 text-white text-[10px] font-semibold">
                                                        Legal
                                                    </button>

                                                </div>

                                            </td>

                                        </tr>

                                    <?php } ?>

                                <?php } else { ?>

                                    <tr>
                                        <td colspan="9"
                                            class="py-10 text-center text-slate-500 border">
                                            No hay demandados registrados.
                                        </td>
                                    </tr>

                                <?php } ?>

                            </tbody>

                        </table>

                    </div>

                    </div>
        </div>
    </div>
</x-app-layout>

