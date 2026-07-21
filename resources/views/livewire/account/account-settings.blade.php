@section('title', 'Tài khoản')

<div class="relative min-h-[calc(100vh-8rem)] py-4 sm:py-6 px-3 sm:px-4 lg:px-6">
    <div class="mx-auto max-w-2xl space-y-5">
        <x-mac-panel :overflow="true">
            <x-page-header
                icon-type="default"
                title="Tài khoản"
                description="Tên đăng nhập là số điện thoại hoặc email bạn nhập khi đăng nhập.">
            </x-page-header>

            <form wire:submit.prevent="updateProfile" class="p-4 lg:p-6 space-y-5">
                <h2 class="text-xs font-semibold text-slate-500 tracking-wide uppercase px-1">
                    Thông tin tài khoản
                </h2>

                @if($login_is_phone && $login_identifier !== '')
                <div class="rounded-xl bg-primary-50/80 border border-primary-100 px-4 py-3 text-sm text-primary-800">
                    Tên đăng nhập hiện tại:
                    <code class="font-mono text-xs bg-primary-100 px-1.5 py-0.5 rounded">{{ $login_identifier }}</code>
                    (số điện thoại). Ô email bên dưới chỉ dùng khi bạn muốn đổi sang email thật.
                </div>
                @endif

                <div class="flex flex-col sm:flex-row gap-5 sm:gap-6">
                    <x-avatar-upload
                        wireModel="avatar_path"
                        :existing="$existing_avatar"
                        inputId="account_avatar_upload"
                        removeMethod="removeAvatar" />

                    <div class="flex-1 min-w-0 space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">
                                Họ và tên
                                <span class="font-normal normal-case text-slate-400">(khuyến khích)</span>
                            </label>
                            <input type="text" wire:model.defer="name" placeholder="Nguyễn Văn A"
                                class="w-full h-11 px-4 py-2.5 rounded-xl border text-sm bg-white/80 backdrop-blur-sm shadow-mac-sm
                                    focus:outline-none focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40 transition-all
                                    {{ $errors->has('name') ? 'border-red-300 bg-red-50/80' : 'border-black/[0.06]' }}" />
                            @error('name')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">
                                @if($login_is_phone)
                                    Email đăng nhập
                                    <span class="font-normal normal-case text-slate-400">— tùy chọn đổi sang email thật</span>
                                @else
                                    Email <span class="text-red-500 normal-case">*</span>
                                    <span class="font-normal normal-case text-slate-400">— tên đăng nhập</span>
                                @endif
                            </label>
                            <input type="email" wire:model.defer="email" placeholder="email@example.com"
                                class="w-full h-11 px-4 py-2.5 rounded-xl border text-sm bg-white/80 backdrop-blur-sm shadow-mac-sm
                                    focus:outline-none focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40 transition-all
                                    {{ $errors->has('email') ? 'border-red-300 bg-red-50/80' : 'border-black/[0.06]' }}" />
                            @error('email')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                            @if($login_is_phone)
                            <p class="mt-1 text-xs text-slate-400">Không cần sửa nếu vẫn muốn đăng nhập bằng số điện thoại.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="pt-2 flex justify-end">
                    <button type="submit"
                        wire:loading.attr="disabled"
                        wire:target="updateProfile,avatar_path"
                        class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl
                            bg-primary-500 text-white text-sm font-semibold shadow-mac-sm
                            hover:bg-primary-600 disabled:opacity-60
                            focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500/40
                            active:scale-[0.98] transition-all">
                        <span wire:loading.remove wire:target="updateProfile">Lưu thông tin</span>
                        <span wire:loading wire:target="updateProfile">Đang lưu...</span>
                    </button>
                </div>
            </form>
        </x-mac-panel>

        <x-mac-panel :overflow="true">
            <div class="px-6 py-5 mac-hairline-b bg-white/40 rounded-t-2xl">
                <h2 class="text-[22px] font-semibold tracking-tight text-slate-900">Đổi mật khẩu</h2>
                <p class="text-sm text-slate-500 mt-0.5">Mật khẩu mới tối thiểu 8 ký tự.</p>
            </div>

            <form wire:submit.prevent="updatePassword" class="p-4 lg:p-6 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">
                        Mật khẩu hiện tại <span class="text-red-500 normal-case">*</span>
                    </label>
                    <x-password-input
                        wire:model.defer="current_password"
                        :error="$errors->has('current_password')"
                        autocomplete="current-password" />
                    @error('current_password')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">
                            Mật khẩu mới <span class="text-red-500 normal-case">*</span>
                        </label>
                        <x-password-input
                            wire:model.defer="password"
                            :error="$errors->has('password')"
                            autocomplete="new-password" />
                        @error('password')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">
                            Xác nhận mật khẩu <span class="text-red-500 normal-case">*</span>
                        </label>
                        <x-password-input
                            wire:model.defer="password_confirmation"
                            :error="$errors->has('password_confirmation')"
                            autocomplete="new-password" />
                        @error('password_confirmation')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="pt-2 flex justify-end">
                    <button type="submit"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl
                            bg-primary-500 text-white text-sm font-semibold shadow-mac-sm
                            hover:bg-primary-600 disabled:opacity-60
                            focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500/40
                            active:scale-[0.98] transition-all">
                        <span wire:loading.remove wire:target="updatePassword">Đổi mật khẩu</span>
                        <span wire:loading wire:target="updatePassword">Đang lưu...</span>
                    </button>
                </div>
            </form>
        </x-mac-panel>
    </div>
</div>
