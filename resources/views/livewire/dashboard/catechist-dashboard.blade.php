@push('page-title')
<span>Trang chủ</span>
@endpush

<div class="min-h-screen bg-apple-gray p-3 sm:p-4"
    style="min-height: calc(100vh - 56px - var(--bottom-offset));">
    <div class="mx-auto max-w-2xl space-y-4">

        {{-- Greeting --}}
        <x-mac-panel class="px-5 py-5">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Giáo lý viên</p>
            <h1 class="mt-1 text-xl font-semibold tracking-tight text-slate-900">
                Xin chào, {{ auth()->user()->name }}
            </h1>
            <p class="mt-1 text-sm text-slate-500">Chọn thao tác nhanh bên dưới</p>
        </x-mac-panel>

        {{-- Quick actions --}}
        <div class="grid grid-cols-1 gap-3">
            <a href="{{ route('attendance.qr') }}"
                class="flex items-center gap-4 px-5 py-4 rounded-2xl
                    bg-white/75 backdrop-blur-xl border border-black/[0.06] shadow-mac
                    hover:bg-white transition-all touch-feedback group">
                <div class="w-11 h-11 rounded-2xl bg-primary-50/90 ring-1 ring-primary-100/80
                    flex items-center justify-center flex-shrink-0 shadow-mac-sm">
                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="font-semibold tracking-tight text-slate-900 group-hover:text-primary-700">Quét QR</p>
                    <p class="text-sm text-slate-400">Điểm danh nhanh bằng mã QR</p>
                </div>
                <svg class="w-5 h-5 text-slate-300 flex-shrink-0 group-hover:text-primary-400"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>

            <a href="{{ route('attendance.show') }}"
                class="flex items-center gap-4 px-5 py-4 rounded-2xl
                    bg-white/75 backdrop-blur-xl border border-black/[0.06] shadow-mac
                    hover:bg-white transition-all touch-feedback group">
                <div class="w-11 h-11 rounded-2xl bg-emerald-50/90 ring-1 ring-emerald-100/80
                    flex items-center justify-center flex-shrink-0 shadow-mac-sm">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="font-semibold tracking-tight text-slate-900 group-hover:text-emerald-700">Điểm danh</p>
                    <p class="text-sm text-slate-400">Điểm danh lớp học của bạn</p>
                </div>
                <svg class="w-5 h-5 text-slate-300 flex-shrink-0 group-hover:text-emerald-400"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>

            <a href="{{ route('students.index') }}"
                class="flex items-center gap-4 px-5 py-4 rounded-2xl
                    bg-white/75 backdrop-blur-xl border border-black/[0.06] shadow-mac
                    hover:bg-white transition-all touch-feedback group">
                <div class="w-11 h-11 rounded-2xl bg-blue-50/90 ring-1 ring-blue-100/80
                    flex items-center justify-center flex-shrink-0 shadow-mac-sm">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="font-semibold tracking-tight text-slate-900 group-hover:text-blue-700">Học sinh</p>
                    <p class="text-sm text-slate-400">Danh sách học sinh lớp tôi</p>
                </div>
                <svg class="w-5 h-5 text-slate-300 flex-shrink-0 group-hover:text-blue-400"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>

            @php
                try { $sessionUrl = route('session.index'); } catch (\Exception $e) { $sessionUrl = null; }
            @endphp
            @if($sessionUrl)
            <a href="{{ $sessionUrl }}"
                class="flex items-center gap-4 px-5 py-4 rounded-2xl
                    bg-white/75 backdrop-blur-xl border border-black/[0.06] shadow-mac
                    hover:bg-white transition-all touch-feedback group">
                <div class="w-11 h-11 rounded-2xl bg-amber-50/90 ring-1 ring-amber-100/80
                    flex items-center justify-center flex-shrink-0 shadow-mac-sm">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="font-semibold tracking-tight text-slate-900 group-hover:text-amber-700">Lịch sử</p>
                    <p class="text-sm text-slate-400">Phiên điểm danh đã tạo</p>
                </div>
                <svg class="w-5 h-5 text-slate-300 flex-shrink-0 group-hover:text-amber-400"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
            @endif
        </div>
    </div>
</div>
