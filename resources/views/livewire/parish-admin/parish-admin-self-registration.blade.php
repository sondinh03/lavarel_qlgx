@section('title', 'Đăng ký quản trị xứ')

<div class="min-h-screen py-4 sm:py-8">
    <div class="mx-auto max-w-lg space-y-5">

        <nav class="flex flex-wrap items-center gap-x-4 gap-y-1 px-1">
            <a href="{{ route('landing') }}"
                class="text-sm text-slate-500 hover:text-primary-600 transition">
                ← Về trang chủ
            </a>
        </nav>

        <div class="text-center px-2">
            <img src="{{ url(config('settings.logo')) }}" class="h-16 w-auto mx-auto mb-3" alt="">
            <h1 class="text-2xl font-bold text-slate-900">Đăng ký quản trị xứ</h1>
            <p class="mt-2 text-sm text-slate-600">
                Gửi yêu cầu tài khoản quản trị giáo xứ. Super admin sẽ duyệt trước khi bạn có thể đăng nhập.
            </p>
        </div>

        @if($submitted)
        <div class="bg-white rounded-2xl shadow-sm border border-emerald-200 p-6 text-center space-y-4">
            <div class="w-14 h-14 mx-auto rounded-full bg-emerald-100 flex items-center justify-center">
                <svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-bold text-slate-900">Đã gửi yêu cầu thành công</h2>
                <p class="text-sm text-slate-600 mt-2">Mã tham chiếu:</p>
                <p class="text-xl font-mono font-bold text-primary-600 mt-1">{{ $referenceCode }}</p>
            </div>
            <p class="text-sm text-slate-500">
                Yêu cầu của bạn đang chờ super admin duyệt. Sau khi được duyệt, hãy đăng nhập bằng email và mật khẩu đã đăng ký.
            </p>
            <div class="flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('login') }}"
                    class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-sm font-semibold hover:bg-slate-50">
                    Đến trang đăng nhập
                </a>
                <a href="{{ route('landing') }}"
                    class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-primary-600 text-white text-sm font-semibold hover:bg-primary-700">
                    Về trang chủ
                </a>
            </div>
        </div>
        @else
        <form wire:submit.prevent="submit" class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-4 sm:p-5 space-y-4">
                @error('submit') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Giáo xứ <span class="text-red-500">*</span></label>
                    <select wire:model="targetParishId"
                        class="w-full rounded-xl border-slate-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="">— Chọn giáo xứ —</option>
                        @foreach($parishOptions as $option)
                        <option value="{{ $option['id'] }}">{{ $option['name'] }}</option>
                        @endforeach
                    </select>
                    @error('targetParishId') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Họ và tên <span class="text-red-500">*</span></label>
                    <input type="text" wire:model.defer="name"
                        class="w-full rounded-xl border-slate-300 text-sm focus:border-primary-500 focus:ring-primary-500"
                        placeholder="Nguyễn Văn A">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" wire:model.defer="email"
                        class="w-full rounded-xl border-slate-300 text-sm focus:border-primary-500 focus:ring-primary-500"
                        placeholder="email@example.com">
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Số điện thoại</label>
                    <input type="text" wire:model.defer="phone"
                        class="w-full rounded-xl border-slate-300 text-sm focus:border-primary-500 focus:ring-primary-500"
                        placeholder="0901234567">
                    @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Mật khẩu <span class="text-red-500">*</span></label>
                        <input type="password" wire:model.defer="password"
                            class="w-full rounded-xl border-slate-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Xác nhận mật khẩu <span class="text-red-500">*</span></label>
                        <input type="password" wire:model.defer="password_confirmation"
                            class="w-full rounded-xl border-slate-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        @error('password_confirmation') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Ghi chú (tuỳ chọn)</label>
                    <textarea wire:model.defer="note" rows="3"
                        class="w-full rounded-xl border-slate-300 text-sm focus:border-primary-500 focus:ring-primary-500"
                        placeholder="Chức danh, thông tin bổ sung..."></textarea>
                    @error('note') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="px-4 sm:px-5 py-4 border-t border-slate-200 bg-slate-50 flex justify-end">
                <button type="submit"
                    class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-primary-600 text-white text-sm font-semibold hover:bg-primary-700 disabled:opacity-60"
                    wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="submit">Gửi yêu cầu đăng ký</span>
                    <span wire:loading wire:target="submit">Đang gửi...</span>
                </button>
            </div>
        </form>
        @endif
    </div>
</div>
