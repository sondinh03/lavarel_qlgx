@extends('frontend.layout.landing')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-50 to-slate-100 px-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-sm border border-slate-200 p-6">

        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-slate-900">
                Đặt lại mật khẩu
            </h1>
            <p class="text-slate-500 mt-1 text-sm">
                Nhập mật khẩu mới cho tài khoản của bạn
            </p>
        </div>

        <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">
                    Email
                </label>
                <input
                    type="email"
                    name="email"
                    value="{{ $email ?? old('email') }}"
                    required
                    autofocus
                    autocomplete="username"
                    class="w-full px-3 py-2 rounded-xl border border-slate-300
                           focus:ring-2 focus:ring-primary-500 focus:outline-none bg-slate-50">
                @error('email')
                <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">
                    Mật khẩu mới
                </label>
                <div x-data="{ show: false }" class="relative">
                    <input
                        :type="show ? 'text' : 'password'"
                        name="password"
                        required
                        autocomplete="new-password"
                        class="w-full px-3 py-2 pr-10 rounded-xl border border-slate-300
                               focus:ring-2 focus:ring-primary-500 focus:outline-none">
                    <button
                        type="button"
                        @click="show = !show"
                        class="absolute right-3 top-1/2 -translate-y-1/2
                               text-slate-400 hover:text-slate-600 transition"
                        tabindex="-1">
                        <svg x-show="!show" xmlns="http://www.w3.org/2000/svg"
                            class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-show="show" xmlns="http://www.w3.org/2000/svg"
                            class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.956 9.956 0 012.223-3.592M6.223 6.223A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.956 9.956 0 01-4.293 5.293M6.223 6.223L3 3m3.223 3.223l11.554 11.554" />
                        </svg>
                    </button>
                </div>
                @error('password')
                <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">
                    Xác nhận mật khẩu
                </label>
                <div x-data="{ show: false }" class="relative">
                    <input
                        :type="show ? 'text' : 'password'"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        class="w-full px-3 py-2 pr-10 rounded-xl border border-slate-300
                               focus:ring-2 focus:ring-primary-500 focus:outline-none">
                    <button
                        type="button"
                        @click="show = !show"
                        class="absolute right-3 top-1/2 -translate-y-1/2
                               text-slate-400 hover:text-slate-600 transition"
                        tabindex="-1">
                        <svg x-show="!show" xmlns="http://www.w3.org/2000/svg"
                            class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-show="show" xmlns="http://www.w3.org/2000/svg"
                            class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.956 9.956 0 012.223-3.592M6.223 6.223A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.956 9.956 0 01-4.293 5.293M6.223 6.223L3 3m3.223 3.223l11.554 11.554" />
                        </svg>
                    </button>
                </div>
            </div>

            <button
                type="submit"
                class="w-full py-2.5 rounded-xl bg-primary-600 text-white font-semibold
                       hover:bg-primary-700 transition">
                Đặt lại mật khẩu
            </button>
        </form>

        <div class="text-center mt-6">
            <a href="{{ route('login') }}"
                class="text-sm text-slate-500 hover:text-primary-600">
                ← Quay lại đăng nhập
            </a>
        </div>

    </div>
</div>
@endsection
