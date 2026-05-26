<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">


<aside class="w-72 shrink-0 bg-white border-r border-slate-200 min-h-screen flex flex-col">
    <div class="px-4 py-5 border-b border-slate-200">
        <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-xl bg-emerald-600 ring-1 ring-emerald-500 flex items-center justify-center shadow-sm">
                <i class="fa-solid fa-dollar-sign text-white text-lg"></i>
            </div>

            <div>
                <div class="text-sm font-semibold text-slate-900 leading-tight">
                    Cobranza Premier
                </div>

                <div class="text-xs text-slate-500">
                    Panel
                </div>
            </div>
        </div>
    </div>

    <nav class="p-3 space-y-1">
        <a
            href="{{ route('dashboard') }}"
            class="{{ request()->routeIs('dashboard') ? 'bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200/70' : 'text-slate-700 hover:bg-slate-50' }} flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition"
        >
        <i class="fa-solid fa-house"></i>
            <span>Menú</span>
        </a>

        <a
            href="{{ route('morosos.index') }}"
            class="{{ request()->routeIs('morosos.*') ? 'bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200/70' : 'text-slate-700 hover:bg-slate-50' }} flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition"
        >
        <i class="fa-solid fa-user"></i>
            <span>Estado Morosos</span>
        </a>

        <a
            href="{{ route('demandado.index') }}"
            class="{{ request()->routeIs('demandado.*') ? 'bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200/70' : 'text-slate-700 hover:bg-slate-50' }} flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition"
        >
        <i class="fa-solid fa-eye"></i>
            <span>Demandados</span>
        </a>

        <a
            href="{{ route('cartas.index') }}"
            class="{{ request()->routeIs('cartas.*') ? 'bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200/70' : 'text-slate-700 hover:bg-slate-50' }} flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition"
        >
        <i class="fa-solid fa-file"></i>
            <span>Cartas</span>
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

