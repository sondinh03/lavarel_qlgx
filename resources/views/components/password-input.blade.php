@props([
    'error' => false,
])

@php
    $inputClass = 'w-full h-11 px-4 py-2.5 pr-11 rounded-xl border text-sm bg-white/80 backdrop-blur-sm shadow-mac-sm
        focus:outline-none focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40 transition-all '
        . ($error ? 'border-red-300 bg-red-50/80' : 'border-black/[0.06]');
@endphp

<div x-data="{ show: false }" class="relative">
    <input
        :type="show ? 'text' : 'password'"
        {{ $attributes->merge(['class' => $inputClass]) }}
    >

    <button
        type="button"
        @click="show = !show"
        class="absolute inset-y-0 right-0 flex items-center px-3
            text-slate-400 hover:text-slate-600 transition"
        tabindex="-1"
        aria-label="Hiện/ẩn mật khẩu">
        <svg x-show="!show" xmlns="http://www.w3.org/2000/svg"
            class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
        </svg>
        <svg x-cloak x-show="show" xmlns="http://www.w3.org/2000/svg"
            class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.956 9.956 0 012.223-3.592M6.223 6.223A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.956 9.956 0 01-4.293 5.293M6.223 6.223L3 3m3.223 3.223l11.554 11.554" />
        </svg>
    </button>
</div>
