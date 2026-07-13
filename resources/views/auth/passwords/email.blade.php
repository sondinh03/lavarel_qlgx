@extends('frontend.layout.landing')

@section('content')
<div class="relative min-h-[calc(100vh-8rem)] flex items-center justify-center py-8">
    <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
        <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-[28rem] h-[28rem]
            rounded-full bg-primary-200/30 blur-3xl"></div>
        <div class="absolute bottom-0 right-0 w-72 h-72
            rounded-full bg-slate-300/25 blur-3xl"></div>
    </div>

    <div class="relative w-full max-w-md">
        <div class="bg-white/75 backdrop-blur-xl rounded-2xl border border-slate-200
            shadow-mac px-6 py-8">

            <div class="text-center mb-6">
                <div class="w-12 h-12 mx-auto rounded-2xl bg-primary-50/90 ring-1 ring-primary-100/80
                    flex items-center justify-center shadow-mac-sm">
                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                </div>
                <h1 class="mt-4 text-2xl font-semibold tracking-tight text-slate-900">
                    Quên mật khẩu
                </h1>
                <p class="mt-1 text-sm text-slate-500">
                    Nhập email hoặc SĐT để nhận liên kết đặt lại
                </p>
            </div>

            @if (session('status'))
            <div class="mb-5 rounded-xl bg-emerald-50/90 ring-1 ring-emerald-100/80
                px-4 py-3 text-sm text-emerald-800 shadow-mac-sm">
                {{ session('status') }}
            </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700 mb-1">
                        Email hoặc số điện thoại
                    </label>
                    <input
                        id="email"
                        type="text"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="admin@gmail.com hoặc 0901234567"
                        class="w-full px-4 py-3 rounded-xl
                            bg-white border border-slate-300 shadow-mac-sm
                            text-slate-900 placeholder:text-slate-300
                            focus:outline-none focus:ring-2 focus:ring-primary-500/25
                            focus:border-primary-400 transition">
                    <p class="text-xs text-slate-400 mt-1.5">
                        Tài khoản chỉ đăng nhập bằng SĐT (không có Gmail) vui lòng liên hệ hỗ trợ
                        @if(trim((string) config('settings.support_phone', '')) !== '' || trim((string) config('settings.support_email', '')) !== '')
                            — <x-support-contact variant="inline" />
                        @else
                            / quản trị xứ.
                        @endif
                    </p>
                    @error('email')
                    <p class="text-sm text-red-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    class="w-full py-3 rounded-xl bg-primary-500 text-white font-semibold
                        shadow-mac-sm hover:bg-primary-600
                        focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500/40
                        active:scale-[0.98] transition-all">
                    Gửi liên kết đặt lại mật khẩu
                </button>
            </form>

            <div class="text-center mt-6 pt-5 border-t border-black/[0.06] space-y-2">
                <a href="{{ route('login') }}"
                    class="inline-flex items-center gap-1.5 text-sm font-medium text-primary-600
                        hover:text-primary-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Quay lại đăng nhập
                </a>
                <div>
                    <a href="{{ route('landing') }}"
                        class="text-sm text-slate-500 hover:text-primary-600 transition">
                        Quay về trang tra cứu
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
