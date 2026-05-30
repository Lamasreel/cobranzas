<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Cartas Moratoria</h2>
                <p class="text-xs text-slate-500">
                    Generación de cartas de moratoria Tarjeta Premier
                </p>
            </div>

            <form id="form-generar-moratoria"
                  action="{{ route('cartas.moratoria.generar_pdf') }}"
                  method="POST"
                  target="_blank">
                @csrf

                <button
                    type="submit"
                    class="px-4 py-2 bg-emerald-700 text-white text-xs font-bold rounded-lg hover:bg-emerald-800 transition">
                    Generar PDF Moratoria
                </button>
            </form>
        </div>
    </x-slot>

    <div class="py-5 bg-slate-50 min-h-screen">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200">
                    <h3 class="text-sm font-bold text-slate-800">
                        Vista previa de la carta
                    </h3>
                    <p class="text-xs text-slate-500 mt-1">
                        El PDF se genera con 2 cartas por hoja A4.
                    </p>
                </div>

                <div class="p-6">
                    <div class="max-w-3xl mx-auto bg-white border border-slate-300 rounded-xl p-6 text-sm text-slate-800 shadow-sm">
                        <p class="text-center font-bold">
                            ESTUDIO DE COBRANZAS PREJURIDICAS DR PUJOL
                        </p>

                        <p class="text-center font-bold text-xs mt-1">
                            TELEF 3865 386766
                        </p>

                        <p class="text-center font-black italic text-2xl mt-6">
                            UNICA MORATORIA TARJETA PREMIER
                        </p>

                        <hr class="my-4 border-slate-800">

                        <p class="text-right text-xs">
                            Aguilares, ........ de ........................ de 2026
                        </p>

                        <p class="mt-4">
                            <strong>Sr/a:</strong> ........................................................
                        </p>

                        <p class="mt-3">
                            <strong>Domicilio:</strong> ..................................................
                        </p>

                        <p class="mt-5 text-justify leading-relaxed">
                            Como apoderado de TARJETA PREMIER le informo que acogiéndose por
                            UNICA VEZ a esta MORATORIA hasta el 30/06/2026, Ud. podrá pagar
                            su deuda con una quita de hasta 50% de los intereses.
                        </p>

                        <p class="mt-4 text-justify leading-relaxed">
                            Pasado dicho plazo iniciaremos inmediatamente las Acciones Judiciales
                            pertinentes, lo que generará, además de gastos Jurídicos, la Afectación
                            de Firmas y embargos de sueldos y/o bienes.
                        </p>

                        <p class="mt-5 font-bold">
                            Queda usted debidamente notificado.
                        </p>

                        <p class="text-right mt-8 text-xs text-slate-500">
                            Firma del abogado
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>