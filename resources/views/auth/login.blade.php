@extends('frontend.layout.landing')

@section('content')
<div class="relative min-h-[calc(100vh-8rem)] flex items-center justify-center py-6 sm:py-8">
    <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
        <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-[28rem] h-[28rem]
            rounded-full bg-primary-200/30 blur-3xl"></div>
        <div class="absolute bottom-0 right-0 w-72 h-72
            rounded-full bg-slate-300/25 blur-3xl"></div>
    </div>

    <div class="relative w-full max-w-sm">
        <div class="bg-white/75 backdrop-blur-xl rounded-2xl border border-slate-200
            shadow-mac px-5 py-6 sm:px-6">

            <div class="text-center mb-5">
                <img src="{{ url(config('settings.logo')) }}"
                    alt="{{ config('settings.web_name') }}"
                    class="h-12 w-auto mx-auto rounded-xl shadow-mac-sm">
                <h1 class="mt-3 text-xl font-semibold tracking-tight text-slate-900">
                    {{ config('settings.web_name', 'Quản Lý Giáo Xứ') }}
                </h1>
                <p class="mt-0.5 text-sm text-slate-500">Đăng nhập hệ thống</p>
            </div>

            @if (session('status'))
            <div class="mb-4 rounded-xl bg-emerald-50/90 ring-1 ring-emerald-100/80
                px-3.5 py-2.5 text-sm text-emerald-800 shadow-mac-sm">
                {{ session('status') }}
            </div>
            @endif

            @if ($errors->any())
            <div class="mb-4 rounded-xl bg-red-50 ring-1 ring-red-100
                px-3.5 py-2.5 text-sm text-red-700 shadow-mac-sm" role="alert">
                {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-600 mb-1.5">
                        Email hoặc SĐT
                    </label>
                    <input
                        id="email"
                        type="text"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="email hoặc 0901234567"
                        class="w-full h-11 px-3.5 rounded-xl text-sm
                            bg-white border shadow-mac-sm
                            text-slate-900 placeholder:text-slate-300
                            focus:outline-none focus:ring-2 focus:ring-primary-500/25
                            focus:border-primary-400 transition
                            {{ $errors->has('email') ? 'border-red-300' : 'border-slate-300' }}">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-600 mb-1.5">
                        Mật khẩu
                    </label>
                    <div x-data="{ show: false }" class="relative">
                        <input
                            id="password"
                            :type="show ? 'text' : 'password'"
                            name="password"
                            required
                            autocomplete="current-password"
                            class="w-full h-11 px-3.5 pr-10 rounded-xl text-sm
                                bg-white border border-slate-300 shadow-mac-sm
                                text-slate-900
                                focus:outline-none focus:ring-2 focus:ring-primary-500/25
                                focus:border-primary-400 transition">

                        <button
                            type="button"
                            @click="show = !show"
                            class="absolute inset-y-0 right-0 flex items-center px-3
                                text-slate-400 hover:text-slate-600 transition"
                            tabindex="-1"
                            aria-label="Hiện/ẩn mật khẩu">
                            <svg x-show="!show" xmlns="http://www.w3.org/2000/svg"
                                class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg x-cloak x-show="show" xmlns="http://www.w3.org/2000/svg"
                                class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.956 9.956 0 012.223-3.592M6.223 6.223A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.956 9.956 0 01-4.293 5.293M6.223 6.223L3 3m3.223 3.223l11.554 11.554" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="remember"
                            class="rounded-md border-black/15 text-primary-600
                                focus:ring-primary-500/30 shadow-mac-sm">
                        <span class="text-slate-600">Ghi nhớ</span>
                    </label>

                    @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}"
                        class="font-medium text-primary-600 hover:text-primary-700 transition">
                        Quên mật khẩu?
                    </a>
                    @endif
                </div>

                <button
                    type="submit"
                    class="w-full h-11 rounded-xl bg-primary-500 text-white text-sm font-semibold
                        shadow-mac-sm hover:bg-primary-600
                        focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500/40
                        active:scale-[0.98] transition-all">
                    Đăng nhập
                </button>
            </form>

            <div class="mt-4 pt-4 border-t border-black/[0.06] space-y-2 text-center text-sm">
                <p class="text-slate-500">
                    Chưa có tài khoản?
                    <a href="{{ route('parish-admin.register.public') }}"
                        class="font-semibold text-primary-600 hover:text-primary-700 transition">
                        Đăng ký quản trị xứ
                    </a>
                </p>
                <a href="{{ route('landing') }}"
                    class="inline-flex items-center gap-1 text-slate-500 hover:text-primary-600 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Về trang tra cứu
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
