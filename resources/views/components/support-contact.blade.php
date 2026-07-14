@props([
    'variant' => 'panel', // panel | compact | inline
])

@php
    $phone = trim((string) config('settings.support_phone', ''));
    $email = trim((string) config('settings.support_email', ''));
    $zalo  = trim((string) config('settings.support_zalo', ''));
    $note  = trim((string) config('settings.support_note', ''));
    $hasContact = $phone !== '' || $email !== '' || $zalo !== '' || $note !== '';

    $telHref = $phone !== ''
        ? 'tel:' . preg_replace('/[^\d+]/', '', $phone)
        : '';
    $mailHref = $email !== ''
        ? 'https://mail.google.com/mail/?view=cm&fs=1&to=' . rawurlencode($email)
        : '';
@endphp

@if($hasContact)
    @if($variant === 'panel')
    <div {{ $attributes->merge(['class' => 'rounded-xl bg-slate-50/80 border border-black/[0.06] p-4 shadow-mac-sm']) }}>
        <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-xl bg-white/90 border border-black/[0.06]
                flex items-center justify-center flex-shrink-0 shadow-mac-sm">
                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <div class="min-w-0 flex-1 space-y-1.5">
                <p class="text-sm font-semibold text-slate-900">Cần hỗ trợ?</p>
                @if($note !== '')
                <p class="text-xs text-slate-500 leading-relaxed whitespace-pre-line">{{ $note }}</p>
                @endif
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 text-sm">
                    @if($phone !== '')
                    <a href="{{ $telHref }}"
                        class="inline-flex items-center gap-1.5 font-medium text-primary-700 hover:text-primary-800 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        {{ $phone }}
                    </a>
                    @endif
                    @if($email !== '')
                    <a href="{{ $mailHref }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-1.5 font-medium text-primary-700 hover:text-primary-800 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        {{ $email }}
                    </a>
                    @endif
                    @if($zalo !== '')
                    <a href="{{ $zalo }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-1.5 font-medium text-primary-700 hover:text-primary-800 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                        </svg>
                        Zalo
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @elseif($variant === 'compact')
    <div {{ $attributes->merge(['class' => 'text-center space-y-1']) }}>
        <p class="text-xs font-semibold text-slate-500 tracking-wide uppercase">Hỗ trợ</p>
        @if($note !== '')
        <p class="text-xs text-slate-500 leading-relaxed whitespace-pre-line">{{ $note }}</p>
        @endif
        <div class="flex flex-wrap items-center justify-center gap-x-3 gap-y-1 text-sm">
            @if($phone !== '')
            <a href="{{ $telHref }}"
                class="font-medium text-primary-700 hover:text-primary-800 transition">{{ $phone }}</a>
            @endif
            @if($phone !== '' && ($email !== '' || $zalo !== ''))
            <span class="text-slate-300">·</span>
            @endif
            @if($email !== '')
            <a href="{{ $mailHref }}"
                target="_blank"
                rel="noopener noreferrer"
                class="font-medium text-primary-700 hover:text-primary-800 transition">{{ $email }}</a>
            @endif
            @if($email !== '' && $zalo !== '')
            <span class="text-slate-300">·</span>
            @endif
            @if($zalo !== '')
            <a href="{{ $zalo }}"
                target="_blank"
                rel="noopener noreferrer"
                class="font-medium text-primary-700 hover:text-primary-800 transition">Zalo</a>
            @endif
        </div>
    </div>
    @else
    {{-- inline --}}
    <span {{ $attributes->merge(['class' => 'inline-flex flex-wrap items-center gap-x-2 gap-y-0.5 text-sm']) }}>
        @if($phone !== '')
        <a href="{{ $telHref }}"
            class="font-medium text-primary-700 hover:text-primary-800 transition">{{ $phone }}</a>
        @endif
        @if($phone !== '' && ($email !== '' || $zalo !== ''))
        <span class="text-slate-300">·</span>
        @endif
        @if($email !== '')
        <a href="{{ $mailHref }}"
            target="_blank"
            rel="noopener noreferrer"
            class="font-medium text-primary-700 hover:text-primary-800 transition">{{ $email }}</a>
        @endif
        @if($email !== '' && $zalo !== '')
        <span class="text-slate-300">·</span>
        @endif
        @if($zalo !== '')
        <a href="{{ $zalo }}"
            target="_blank"
            rel="noopener noreferrer"
            class="font-medium text-primary-700 hover:text-primary-800 transition">Nhóm zalo hỗ trợ</a>
        @endif
        @if($note !== '' && ($phone !== '' || $email !== '' || $zalo !== ''))
        <span class="text-slate-400 text-xs">({{ $note }})</span>
        @elseif($note !== '')
        <span class="text-slate-500 text-xs">{{ $note }}</span>
        @endif
    </span>
    @endif
@endif
