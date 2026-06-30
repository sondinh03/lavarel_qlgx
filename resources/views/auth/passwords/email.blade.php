@extends('frontend.layout.landing')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-50 to-slate-100 px-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-sm border border-slate-200 p-6">

        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-slate-900">
                Quên mật khẩu
            </h1>
            <p class="text-slate-500 mt-1 text-sm">
                Nhập email hoặc SĐT để nhận liên kết đặt lại mật khẩu
            </p>
        </div>

        @if (session('status'))
        <div class="mb-4 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
            {{ session('status') }}
        </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">
                    Email hoặc số điện thoại
                </label>
                <input
                    type="text"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="VD: admin@gmail.com hoặc 0901234567"
                    class="w-full px-3 py-2 rounded-xl border border-slate-300
                           focus:ring-2 focus:ring-primary-500 focus:outline-none">
                <p class="text-xs text-slate-500 mt-1">
                    Tài khoản chỉ đăng nhập bằng SĐT (không có Gmail) vui lòng liên hệ quản trị xứ.
                </p>
                @error('email')
                <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button
                type="submit"
                class="w-full py-2.5 rounded-xl bg-primary-600 text-white font-semibold
                       hover:bg-primary-700 transition">
                Gửi liên kết đặt lại mật khẩu
            </button>
        </form>

        <div class="text-center mt-6 space-y-2">
            <a href="{{ route('login') }}"
                class="block text-sm text-primary-600 hover:underline">
                ← Quay lại đăng nhập
            </a>
            <a href="{{ route('landing') }}"
                class="block text-sm text-slate-500 hover:text-primary-600">
                Quay về trang tra cứu
            </a>
        </div>

    </div>
</div>
@endsection
