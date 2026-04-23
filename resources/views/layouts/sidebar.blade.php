<aside class="w-72 shrink-0 bg-white border-r border-slate-200 min-h-screen flex flex-col">
    <div class="px-4 py-5 border-b border-slate-200">
        <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-xl bg-emerald-600 ring-1 ring-emerald-500 flex items-center justify-center shadow-sm">
                <x-application-logo class="h-6 w-6 fill-current text-white" />
            </div>
            <div>
                <div class="text-sm font-semibold text-slate-900 leading-tight">Cobranza</div>
                <div class="text-xs text-slate-500">Panel</div>
            </div>
        </div>
    </div>

    <nav class="p-3 space-y-1">
        <a
            href="{{ route('dashboard') }}"
            class="{{ request()->routeIs('dashboard') ? 'bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200/70' : 'text-slate-700 hover:bg-slate-50' }} flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition"
        >
            <span class="h-2.5 w-2.5 rounded-full {{ request()->routeIs('dashboard') ? 'bg-emerald-600' : 'bg-slate-300' }}"></span>
            <span>Menú</span>
        </a>

        <a
            href="{{ route('morosos.index') }}"
            class="{{ request()->routeIs('morosos.*') ? 'bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200/70' : 'text-slate-700 hover:bg-slate-50' }} flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition"
        >
            <span class="h-2.5 w-2.5 rounded-full {{ request()->routeIs('morosos.*') ? 'bg-emerald-600' : 'bg-slate-300' }}"></span>
            <span>Ver morosos</span>
        </a>
    </nav>

    <div class="mt-auto p-3 border-t border-slate-200">
        <div class="px-3 py-2">
            <div class="text-sm font-semibold text-slate-900">{{ Auth::user()->name }}</div>
            <div class="text-xs text-slate-500">{{ Auth::user()->email }}</div>
        </div>

        <div class="mt-2 space-y-1">
            <a href="{{ route('profile.edit') }}" class="block px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                Perfil
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full text-left block px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Salir
                </button>
            </form>
        </div>
    </div>
</aside>

