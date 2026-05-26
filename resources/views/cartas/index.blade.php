<x-app-layout>
    <x-slot name="header">
        <form id="form-generar-cartas" action="{{ route('cartas.generar_pdf') }}" method="POST" target="_blank">
            @csrf
        </form>

        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Cartas Documentadas</h2>
                <p class="text-xs text-slate-500">Importación y selección de morosos para emitir cartas</p>
            </div>

            <form action="{{ route('cartas.limpiar') }}" method="POST"
                onsubmit="return confirm('¿Seguro que querés eliminar todos los registros importados?')">
                @csrf
                @method('DELETE')

                <button
                    type="submit"
                    class="px-4 py-2 bg-red-100 text-red-700 border border-red-200 text-xs font-bold rounded-lg hover:bg-red-200 transition">
                    Limpiar tabla
                </button>
            </form>

            <button
                id="btn-generar-cartas"
                type="submit"
                form="form-generar-cartas"
                disabled
                class="px-4 py-2 bg-slate-300 text-white text-xs font-bold rounded-lg cursor-not-allowed transition">
                Generar seleccionadas
            </button>
        </div>
    </x-slot>

    <div class="py-5 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg text-sm font-semibold">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm font-semibold">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">

                <div class="px-4 py-4 border-b border-slate-200 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Importar archivo</h3>
                        <p class="text-xs text-slate-500">
                            Excel con documento titular, documento, nombre, calle, observaciones y localidad.
                        </p>
                    </div>

                    <form
                        id="form-importar-excel"
                        action="{{ route('cartas.importar_excel') }}"
                        method="POST"
                        enctype="multipart/form-data"
                        class="flex flex-col sm:flex-row gap-2 sm:items-center">
                        @csrf

                        <label class="cursor-pointer">
                            <input
                                id="archivo_excel"
                                type="file"
                                name="archivo_excel"
                                accept=".xlsx,.xls,.csv"
                                required
                                class="hidden">

                            <span
                                id="nombre-archivo"
                                class="inline-flex items-center justify-center min-w-[220px] px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-500 text-xs font-semibold hover:bg-slate-50 transition">
                                Seleccionar Excel
                            </span>
                        </label>

                        <button
                            id="btn-importar"
                            type="submit"
                            class="px-4 py-2 bg-slate-800 text-white text-xs font-bold rounded-lg hover:bg-slate-900 transition">
                            Importar
                        </button>
                    </form>
                </div>

                <div id="import-feedback" class="hidden px-4 py-3 bg-blue-50 border-b border-blue-100">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-5 h-5 rounded-full border-2 border-blue-200 border-t-blue-700 animate-spin"></div>
                            <p class="text-xs text-blue-800 font-semibold">
                                Importando archivo... <span id="import-segundos">0</span>s
                            </p>
                        </div>

                        <div class="w-32 h-1.5 bg-blue-100 rounded-full overflow-hidden">
                            <div id="barra-importacion" class="h-full bg-blue-700 rounded-full transition-all duration-300" style="width: 15%"></div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">

                <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Morosos cargados</h3>
                        <p class="text-xs text-slate-500">
                            {{ count($morosos ?? []) }} registros disponibles
                        </p>
                    </div>

                    <span id="contador-seleccionados"
                          class="text-xs font-bold text-slate-700 bg-slate-100 border border-slate-200 px-3 py-1 rounded-full">
                        0 seleccionados
                    </span>
                </div>

                <div class="overflow-auto max-h-[700px]">

                    <table class="min-w-full w-full border-collapse text-xs">

                        <thead class="bg-slate-100 text-slate-700 text-[11px] uppercase tracking-wide sticky top-0 z-40">
                            <tr>
                                <th class="py-2 px-2 border text-center whitespace-nowrap w-10">
                                    <input type="checkbox" id="check-todos-cartas">
                                </th>
                                <th class="py-2 px-2 border whitespace-nowrap text-left">Documento</th>
                                <th class="py-2 px-2 border whitespace-nowrap text-left">Nombre</th>
                                <th class="py-2 px-2 border whitespace-nowrap text-left">Calle</th>
                                <th class="py-2 px-2 border whitespace-nowrap text-left">Observaciones Calle</th>
                                <th class="py-2 px-2 border whitespace-nowrap text-left">Localidad</th>
                            </tr>
                        </thead>

                        <tbody class="text-slate-700">

                            @forelse($morosos ?? [] as $moroso)
                                <tr class="border-b hover:bg-slate-50 transition">

                                    <td class="py-2 px-2 border text-center whitespace-nowrap">
                                        <input
                                            type="checkbox"
                                            form="form-generar-cartas"
                                            name="cartas_seleccionadas[]"
                                            value="{{ $moroso->id ?? $moroso['id'] ?? '' }}"
                                            class="check-carta">
                                    </td>

                                    <td class="py-2 px-2 border whitespace-nowrap font-bold text-slate-700">
                                        {{ $moroso->documento ?? $moroso['documento'] ?? '-' }}
                                    </td>

                                    <td class="py-2 px-2 border whitespace-nowrap font-semibold text-slate-800">
                                        {{ $moroso->nombre ?? $moroso['nombre'] ?? '-' }}
                                    </td>

                                    <td class="py-2 px-2 border max-w-[280px] whitespace-normal break-words">
                                        {{ $moroso->calle ?? $moroso['calle'] ?? '-' }}
                                    </td>

                                    <td class="py-2 px-2 border max-w-[360px] whitespace-normal break-words text-slate-600">
                                        {{ $moroso->observaciones ?? $moroso['observaciones'] ?? '-' }}
                                    </td>

                                    <td class="py-2 px-2 border whitespace-nowrap font-semibold">
                                        {{ $moroso->localidad ?? $moroso['localidad'] ?? '-' }}
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 px-4 text-center text-slate-500">
                                        Todavía no hay morosos cargados. Importá un Excel para comenzar.
                                    </td>
                                </tr>
                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const checkTodos = document.getElementById('check-todos-cartas');
            const checks = document.querySelectorAll('.check-carta');
            const contador = document.getElementById('contador-seleccionados');
            const btnGenerar = document.getElementById('btn-generar-cartas');

            const formImportar = document.getElementById('form-importar-excel');
            const inputArchivo = document.getElementById('archivo_excel');
            const nombreArchivo = document.getElementById('nombre-archivo');
            const btnImportar = document.getElementById('btn-importar');
            const feedback = document.getElementById('import-feedback');
            const segundosTexto = document.getElementById('import-segundos');
            const barraImportacion = document.getElementById('barra-importacion');

            let segundos = 0;

            function actualizarSeleccionados() {
                const seleccionados = document.querySelectorAll('.check-carta:checked').length;

                contador.textContent = seleccionados + ' seleccionados';

                if (seleccionados > 0) {
                    btnGenerar.disabled = false;
                    btnGenerar.classList.remove('bg-slate-300', 'cursor-not-allowed');
                    btnGenerar.classList.add('bg-red-700', 'hover:bg-red-800', 'cursor-pointer');
                } else {
                    btnGenerar.disabled = true;
                    btnGenerar.classList.remove('bg-red-700', 'hover:bg-red-800', 'cursor-pointer');
                    btnGenerar.classList.add('bg-slate-300', 'cursor-not-allowed');
                }
            }

            function iniciarImportacion() {
                segundos = 0;
                feedback.classList.remove('hidden');

                btnImportar.disabled = true;
                btnImportar.textContent = 'Importando...';
                btnImportar.classList.add('opacity-70', 'cursor-not-allowed');

                setInterval(() => {
                    segundos++;
                    segundosTexto.textContent = segundos;

                    const progreso = Math.min(90, 15 + segundos * 6);
                    barraImportacion.style.width = progreso + '%';
                }, 1000);
            }

            if (inputArchivo) {
                inputArchivo.addEventListener('change', function () {
                    if (inputArchivo.files.length > 0) {
                        nombreArchivo.textContent = inputArchivo.files[0].name;
                        nombreArchivo.classList.add('text-slate-800', 'font-bold');
                    }
                });
            }

            if (formImportar) {
                formImportar.addEventListener('submit', function (event) {
                    if (!inputArchivo.files.length) {
                        event.preventDefault();
                        alert('Seleccioná un archivo Excel antes de importar.');
                        return;
                    }

                    iniciarImportacion();
                });
            }

            if (checkTodos) {
                checkTodos.addEventListener('change', function () {
                    checks.forEach(check => check.checked = checkTodos.checked);
                    actualizarSeleccionados();
                });
            }

            checks.forEach(check => {
                check.addEventListener('change', actualizarSeleccionados);
            });

            actualizarSeleccionados();
        });
    </script>
</x-app-layout>