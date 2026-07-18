@php
    $inputClass = 'w-full h-11 px-4 py-2.5 rounded-xl border text-sm bg-white/80 backdrop-blur-sm shadow-mac-sm
        focus:outline-none focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40 transition-all border-black/[0.06]';
    $defaultPassword = config('qlgx.catechist_default_password', '12345678');
@endphp

@section('topbar')
<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => route('parish-admin.dashboard')],
    ['label' => 'Giáo lý viên', 'url' => route('catechists.index')],
    ['label' => $isEdit ? 'Chỉnh sửa giáo lý viên' : 'Thêm giáo lý viên mới'],
]" />
@endsection

<div class="min-h-screen bg-apple-gray p-2 sm:p-4 lg:p-6"
    style="min-height: calc(100vh - 56px - var(--bottom-offset));">
    <a href="#teacher-form-main" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="teacher-form-main" class="mx-auto max-w-4xl">
        <x-mac-panel :overflow="true">
            <x-page-header
                icon-type="students"
                :title="$isEdit ? 'Chỉnh sửa giáo lý viên' : 'Thêm giáo lý viên mới'"
                :description="$isEdit ? 'Cập nhật hồ sơ và tài khoản đăng nhập' : 'Điền thông tin để thêm giáo lý viên'">
                <x-slot name="actions">
                    <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-semibold bg-primary-50/80 text-primary-700 shadow-mac-sm">
                        {{ $isEdit ? 'Chế độ sửa' : 'Tạo mới' }}
                    </span>
                </x-slot>
            </x-page-header>

            <form wire:submit.prevent="save">
                @if($errors->any())
                <div class="mx-4 lg:mx-6 mt-5 p-4 bg-red-50/90 border border-red-200/80 rounded-xl shadow-mac-sm">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-red-800 mb-1">Vui lòng kiểm tra lại</p>
                            <ul class="text-sm text-red-700 space-y-0.5">
                                @foreach($errors->all() as $error)
                                <li>· {{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endif

                <div class="p-4 lg:p-6 space-y-6">

                    <section>
                        <h2 class="text-xs font-semibold text-slate-500 tracking-wide uppercase mb-3 px-1">
                            Thông tin cá nhân
                        </h2>
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                            <div class="sm:col-span-1">
                                <label class="block text-slate-600 mb-1.5">Tên thánh</label>
                                <x-searchable-select
                                    wireModel="saint_id"
                                    :options="$this->saints"
                                    placeholder="-- Chọn --"
                                    labelKey="name"
                                    valueKey="id"
                                    :value="$this->saint_id" />
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-slate-600 mb-1.5">
                                    Họ <span class="text-red-500">*</span>
                                </label>
                                <input type="text" wire:model.defer="last_name" placeholder="Nguyễn Văn"
                                    class="{{ $inputClass }} {{ $errors->has('last_name') ? 'border-red-300 bg-red-50/80' : '' }}" />
                                @error('last_name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div class="sm:col-span-1">
                                <label class="block text-slate-600 mb-1.5">
                                    Tên <span class="text-red-500">*</span>
                                </label>
                                <input type="text" wire:model.defer="first_name" placeholder="An"
                                    class="{{ $inputClass }} {{ $errors->has('first_name') ? 'border-red-300 bg-red-50/80' : '' }}" />
                                @error('first_name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div class="sm:col-span-1">
                                <label class="block text-slate-600 mb-1.5">Giới tính</label>
                                <select wire:model.defer="gender" class="{{ $inputClass }}">
                                    <option value="">-- Chọn --</option>
                                    <option value="male">Nam</option>
                                    <option value="female">Nữ</option>
                                </select>
                            </div>
                            <div class="sm:col-span-3">
                                <label class="block text-slate-600 mb-1.5">Ngày sinh</label>
                                <input type="date" wire:model.defer="birthday" class="{{ $inputClass }}" />
                            </div>
                        </div>
                    </section>

                    <div class="mac-hairline-b"></div>

                    <section>
                        <h2 class="text-xs font-semibold text-slate-500 tracking-wide uppercase mb-3 px-1">
                            Liên hệ & giáo họ
                        </h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-slate-600 mb-1.5">Điện thoại</label>
                                <input type="tel" wire:model.defer="phone_number" placeholder="0901234567" class="{{ $inputClass }}" />
                                @error('phone_number') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-slate-600 mb-1.5">Email</label>
                                <input type="email" wire:model.defer="email" placeholder="email@example.com"
                                    class="{{ $inputClass }} {{ $errors->has('email') ? 'border-red-300 bg-red-50/80' : '' }}" />
                                @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-slate-600 mb-1.5">Địa chỉ</label>
                                <input type="text" wire:model.defer="address" placeholder="Địa chỉ cư trú" class="{{ $inputClass }}" />
                            </div>
                            <div>
                                <label class="block text-slate-600 mb-1.5">Giáo họ</label>
                                <x-searchable-select
                                    wireModel="parish_group_id"
                                    :options="$this->parishGroups"
                                    placeholder="-- Chọn giáo họ --"
                                    labelKey="name"
                                    valueKey="id"
                                    :value="$this->parish_group_id" />
                            </div>
                        </div>
                    </section>

                    <div class="mac-hairline-b"></div>

                    <section class="space-y-4">
                        <h2 class="text-xs font-semibold text-slate-500 tracking-wide uppercase mb-1 px-1">
                            Trạng thái & ghi chú
                        </h2>

                        <label class="flex items-start gap-3 cursor-pointer select-none rounded-xl bg-white/40 border border-black/[0.04] p-4">
                            <input id="teacher-active" type="checkbox" wire:model.defer="is_active"
                                class="w-4 h-4 mt-0.5 rounded border-black/20 text-primary-500 focus:ring-primary-500/25" />
                            <div>
                                <span class="text-sm font-semibold text-slate-700">Đang hoạt động</span>
                                <p class="text-xs text-slate-500 mt-0.5">Bỏ chọn nếu đã nghỉ hoặc không còn giảng dạy</p>
                            </div>
                        </label>

                        <div>
                            <label class="block text-slate-600 mb-1.5">Ghi chú</label>
                            <textarea wire:model.defer="note" rows="3" placeholder="Ghi chú thêm..."
                                class="w-full px-4 py-2.5 rounded-xl border border-black/[0.06] bg-white/80 text-sm
                                    shadow-mac-sm focus:outline-none focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40 resize-none"></textarea>
                        </div>
                    </section>

                    <div class="mac-hairline-b"></div>

                    <section class="space-y-3">
                        <h2 class="text-xs font-semibold text-slate-500 tracking-wide uppercase mb-1 px-1">
                            Tài khoản đăng nhập
                        </h2>

                        @if(!$isEdit)
                        <label class="flex items-start gap-3 cursor-pointer select-none rounded-xl bg-white/40 border border-black/[0.04] p-4">
                            <input type="checkbox" wire:model.defer="create_account"
                                class="w-4 h-4 mt-0.5 rounded border-black/20 text-primary-500 focus:ring-primary-500/25" />
                            <div>
                                <span class="text-sm font-semibold text-slate-700">Tạo tài khoản đăng nhập tự động</span>
                                <p class="text-xs text-slate-500 mt-0.5">Cần có SĐT hoặc email</p>
                            </div>
                        </label>

                        @if($create_account)
                        <div class="rounded-xl bg-primary-50/80 border border-primary-100 px-4 py-3 text-xs text-primary-700 space-y-1">
                            <p>Email đăng nhập: email thật (nếu có) hoặc <code class="font-mono bg-primary-100 px-1 rounded">SĐT@giaoly.local</code></p>
                            <p>Mật khẩu mặc định: <code class="font-mono bg-primary-100 px-1 rounded">{{ $defaultPassword }}</code></p>
                        </div>
                        @endif
                        @else
                            @if($has_account)
                            <div class="rounded-xl bg-white/40 border border-black/[0.04] p-4 space-y-3">
                                <p class="text-sm text-slate-700">Đã có tài khoản đăng nhập.</p>
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" wire:model.defer="reset_password"
                                        class="w-4 h-4 rounded border-black/20 text-amber-500 focus:ring-amber-400" />
                                    <span class="text-sm text-slate-700">Reset mật khẩu về {{ $defaultPassword }}</span>
                                </label>
                            </div>
                            @else
                            <div class="rounded-xl bg-white/40 border border-black/[0.04] p-4 space-y-3">
                                <p class="text-sm text-amber-700">Chưa có tài khoản đăng nhập.</p>
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" wire:model.defer="create_account"
                                        class="w-4 h-4 rounded border-black/20 text-primary-500 focus:ring-primary-500/25" />
                                    <span class="text-sm text-slate-700">Tạo tài khoản ngay</span>
                                </label>
                            </div>
                            @endif
                        @endif
                    </section>
                </div>

                <div class="px-4 lg:px-6 py-4 mac-hairline-t bg-white/30 flex items-center justify-end gap-3">
                    <x-button as="a" variant="outline" href="{{
                        $isEdit ? route('catechists.show', $teacherId) : route('catechists.index')
                    }}">
                        <x-icon name="cancel" />
                        Hủy
                    </x-button>
                    <x-button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="save">
                        <x-icon name="save" />
                        Lưu
                    </x-button>
                </div>
            </form>
        </x-mac-panel>
    </div>
</div>
