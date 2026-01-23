@extends('frontend.layout.landing')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-50 to-slate-100 px-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-sm border border-slate-200 p-6">

        {{-- Header --}}
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-slate-900">
                Đăng nhập hệ thống
            </h1>
            <p class="text-slate-500 mt-1 text-sm">
                Quản lý giáo lý giáo xứ
            </p>
        </div>

        {{-- Form --}}
        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            {{-- Email --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">
                    Email
                </label>
                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    class="w-full px-3 py-2 rounded-xl border border-slate-300
                           focus:ring-2 focus:ring-primary-500 focus:outline-none">
                @error('email')
                <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">
                    Mật khẩu
                </label>
                <input
                    type="password"
                    name="password"
                    required
                    class="w-full px-3 py-2 rounded-xl border border-slate-300
                           focus:ring-2 focus:ring-primary-500 focus:outline-none">
                @error('password')
                <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Remember --}}
            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="remember"
                        class="rounded border-slate-300 text-primary-600">
                    <span class="text-slate-600">Ghi nhớ đăng nhập</span>
                </label>

                @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}"
                    class="text-primary-600 hover:underline">
                    Quên mật khẩu?
                </a>
                @endif
            </div>

            {{-- Submit --}}
            <button
                type="submit"
                class="w-full py-2.5 rounded-xl bg-primary-600 text-white font-semibold
                       hover:bg-primary-700 transition">
                Đăng nhập
            </button>
        </form>

        {{-- Back to landing --}}
        <div class="text-center mt-6">
            <a href="{{ route('landing') }}"
                class="text-sm text-slate-500 hover:text-primary-600">
                ← Quay về trang tra cứu
            </a>
        </div>

    </div>
</div>
@endsection