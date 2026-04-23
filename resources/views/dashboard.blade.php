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
                        <div class="text-sm font-semibold text-slate-900">Ver morosos</div>
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

        <a href="{{ route('profile.edit') }}" class="group bg-white rounded-2xl ring-1 ring-slate-200/70 shadow-sm hover:shadow-md transition overflow-hidden">
            <div class="p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-sm font-semibold text-slate-900">Perfil</div>
                        <div class="mt-1 text-sm text-slate-600">
                            Datos personales y contraseña.
                        </div>
                    </div>
                    <div class="h-11 w-11 rounded-2xl bg-slate-50 ring-1 ring-slate-200/70 flex items-center justify-center">
                        <div class="h-2.5 w-2.5 rounded-full bg-slate-500"></div>
                    </div>
                </div>
            </div>
            <div class="px-6 py-3 bg-white border-t border-slate-100 text-sm font-semibold text-slate-700 group-hover:text-slate-900">
                Abrir →
            </div>
        </a>
    </div>
</x-app-layout>
