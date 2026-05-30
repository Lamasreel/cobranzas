<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-slate-900 leading-tight">
                {{ __('Menú') }}
            </h2>
            <div class="text-sm text-slate-600">Accesos rápidos a tus módulos.</div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        <a href="{{ route('morosos.index') }}" class="group bg-white rounded-2xl ring-1 ring-slate-200/70 shadow-sm hover:shadow-md transition overflow-hidden">
            <div class="p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-sm font-semibold text-slate-900">Morosos</div>
                        <div class="mt-1 text-sm text-slate-600">
                            Filtra por estado y periodos de mora.
                        </div>
                    </div>
                    <div class="h-11 w-11 rounded-2xl bg-emerald-50 ring-1 ring-emerald-200/70 flex items-center justify-center">
                        <div class="h-2.5 w-2.5 rounded-full bg-emerald-600"></div>
                    </div>
                </div>
            </div>
            <div class="px-6 py-3 bg-white border-t border-slate-100 text-sm font-semibold text-emerald-700 group-hover:text-emerald-800">
                Abrir →
            </div>
        </a>

        <a href="{{ route('demandado.index') }}" class="group bg-white rounded-2xl ring-1 ring-slate-200/70 shadow-sm hover:shadow-md transition overflow-hidden">
            <div class="p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-sm font-semibold text-slate-900">Demandados</div>
                        <div class="mt-1 text-sm text-slate-600">
                            Seguimiento demandados.
                        </div>
                    </div>
                    <div class="h-11 w-11 rounded-2xl bg-slate-50 ring-1 ring-slate-200/70 flex items-center justify-center">
                        <div class="h-2.5 w-2.5 rounded-full bg-slate-500"></div>
                    </div>
                </div>
            </div>
            <div class="px-6 py-3 bg-white border-t border-slate-100 text-sm font-semibold text-slate-700 group-hover:text-slate-900">
                Ver demandados →
            </div>
        </a>

        <a href="{{ route('cartas.index') }}" class="group bg-white rounded-2xl ring-1 ring-slate-200/70 shadow-sm hover:shadow-md transition overflow-hidden">
            <div class="p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-sm font-semibold text-slate-900">Cartas Documentadas</div>
                        <div class="mt-1 text-sm text-slate-600">
                            Generación de cartas por importación de excel
                        </div>
                    </div>
                    <div class="h-11 w-11 rounded-2xl bg-slate-50 ring-1 ring-slate-200/70 flex items-center justify-center">
                        <div class="h-2.5 w-2.5 rounded-full bg-slate-500"></div>
                    </div>
                </div>
            </div>
            <div class="px-6 py-3 bg-white border-t border-slate-100 text-sm font-semibold text-slate-700 group-hover:text-slate-900">
                Generar Cartas →
            </div>
        </a>

        <a href="{{ route('cartas.moratoria') }}" class="group bg-white rounded-2xl ring-1 ring-slate-200/70 shadow-sm hover:shadow-md transition overflow-hidden">
            <div class="p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-sm font-semibold text-slate-900">Cartas Moratoria</div>
                        <div class="mt-1 text-sm text-slate-600">
                            Generación de cartas en moratoria.
                        </div>
                    </div>
                    <div class="h-11 w-11 rounded-2xl bg-slate-50 ring-1 ring-slate-200/70 flex items-center justify-center">
                        <div class="h-2.5 w-2.5 rounded-full bg-slate-500"></div>
                    </div>
                </div>
            </div>
            <div class="px-6 py-3 bg-white border-t border-slate-100 text-sm font-semibold text-slate-700 group-hover:text-slate-900">
                Generar Cartas →
            </div>
        </a>
    </div>
</x-app-layout>
