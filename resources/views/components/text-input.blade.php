@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'w-full rounded-xl border-gray-200 bg-white text-gray-900 placeholder:text-gray-400 shadow-sm focus:border-emerald-500 focus:ring-emerald-500']) !!}>
