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
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <h1 class="mt-4 text-2xl font-semibold tracking-tight text-slate-900">
                    Đặt lại mật khẩu
                </h1>
                <p class="mt-1 text-sm text-slate-500">
                    Nhập mật khẩu mới cho tài khoản của bạn
                </p>
            </div>

            <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700 mb-1">
                        Email
                    </label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ $email ?? old('email') }}"
                        required
                        autofocus
                        autocomplete="username"
                        class="w-full px-4 py-3 rounded-xl
                            bg-white border border-slate-300 shadow-mac-sm
                            text-slate-700
                            focus:outline-none focus:ring-2 focus:ring-primary-500/25
                            focus:border-primary-400 transition">
                    @error('email')
                    <p class="text-sm text-red-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700 mb-1">
                        Mật khẩu mới
                    </label>
                    <div x-data="{ show: false }" class="relative">
                        <input
                            id="password"
                            :type="show ? 'text' : 'password'"
                            name="password"
                            required
                            autocomplete="new-password"
                            class="w-full px-4 py-3 pr-10 rounded-xl
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
                    @error('password')
                    <p class="text-sm text-red-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 mb-1">
                        Xác nhận mật khẩu
                    </label>
                    <div x-data="{ show: false }" class="relative">
                        <input
                            id="password_confirmation"
                            :type="show ? 'text' : 'password'"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                            class="w-full px-4 py-3 pr-10 rounded-xl
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
                </div>

                <button
                    type="submit"
                    class="w-full mt-1 py-3 rounded-xl bg-primary-500 text-white font-semibold
                        shadow-mac-sm hover:bg-primary-600
                        focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500/40
                        active:scale-[0.98] transition-all">
                    Đặt lại mật khẩu
                </button>
            </form>

            <div class="text-center mt-6 pt-5 border-t border-black/[0.06]">
                <a href="{{ route('login') }}"
                    class="inline-flex items-center gap-1.5 text-sm text-slate-500
                        hover:text-primary-600 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Quay lại đăng nhập
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
