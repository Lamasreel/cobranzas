<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-slate-900 leading-tight">
                {{ __('Perfil') }}
            </h2>
            <div class="text-sm text-slate-600">Configura tu cuenta.</div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="bg-white rounded-2xl ring-1 ring-slate-200/70 shadow-sm">
            <div class="p-6">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl ring-1 ring-slate-200/70 shadow-sm">
            <div class="p-6">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl ring-1 ring-slate-200/70 shadow-sm">
            <div class="p-6">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
