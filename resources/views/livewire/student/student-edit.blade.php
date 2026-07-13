@section('topbar')
<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => auth()->user()->usesCatechistLayout() ? route('catechist.dashboard') : route('parish-admin.dashboard')],
    ['label' => 'Học sinh', 'url' => route('students.index')],
    ['label' => $isEdit ? 'Chỉnh sửa học sinh' : 'Thêm học sinh mới'],
]" />
@endsection

<div class="min-h-screen bg-apple-gray p-2 sm:p-4 lg:p-6"
    style="min-height: calc(100vh - 56px - var(--bottom-offset));">
    <a href="#student-form-main" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="student-form-main" class="mx-auto max-w-4xl">

            <x-mac-panel :overflow="true">

                <x-page-header
                    icon-type="students"
                    :title="$isEdit ? 'Chỉnh sửa học sinh' : 'Thêm học sinh mới'"
                    :description="$isEdit ? 'Cập nhật thông tin học sinh giáo lý' : 'Điền đầy đủ thông tin để thêm học sinh mới'">
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

                        {{-- Identity: avatar + personal fields --}}
                        <section>
                            <h2 class="text-xs font-semibold text-slate-500 tracking-wide uppercase mb-3 px-1">
                                Thông tin cá nhân
                            </h2>

                            <div class="flex flex-col sm:flex-row gap-5 sm:gap-6">
                                <x-avatar-upload
                                    wireModel="avatar_path"
                                    :existing="$existing_avatar"
                                    inputId="avatar_upload"
                                    removeMethod="removeAvatar" />

                                {{-- Personal fields --}}
                                <div class="flex-1 min-w-0 grid grid-cols-1 sm:grid-cols-4 gap-4">
                                    <div class="sm:col-span-1">
                                        <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">Tên thánh</label>
                                        <x-searchable-select
                                            wireModel="saint_id"
                                            :options="$this->saints"
                                            placeholder="-- Chọn --"
                                            labelKey="name"
                                            valueKey="id"
                                            :value="$this->saint_id" />
                                        @error('saint_id')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">
                                            Họ <span class="text-red-500 normal-case">*</span>
                                        </label>
                                        <input type="text" wire:model.defer="last_name" placeholder="Nguyễn"
                                            class="w-full h-11 px-4 py-2.5 rounded-xl border text-sm bg-white/80 backdrop-blur-sm shadow-mac-sm
                                                focus:outline-none focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40 transition-all
                                                {{ $errors->has('last_name') ? 'border-red-300 bg-red-50/80' : 'border-black/[0.06]' }}" />
                                        @error('last_name')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="sm:col-span-1">
                                        <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">
                                            Tên <span class="text-red-500 normal-case">*</span>
                                        </label>
                                        <input type="text" wire:model.defer="first_name" placeholder="An"
                                            class="w-full h-11 px-4 py-2.5 rounded-xl border text-sm bg-white/80 backdrop-blur-sm shadow-mac-sm
                                                focus:outline-none focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40 transition-all
                                                {{ $errors->has('first_name') ? 'border-red-300 bg-red-50/80' : 'border-black/[0.06]' }}" />
                                        @error('first_name')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="sm:col-span-1">
                                        <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">
                                            Giới tính <span class="text-red-500 normal-case">*</span>
                                        </label>
                                        <select wire:model.defer="gender"
                                            class="w-full h-11 px-4 py-2.5 rounded-xl border border-black/[0.06] bg-white/80 backdrop-blur-sm text-sm
                                                shadow-mac-sm focus:outline-none focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40 transition-all">
                                            <option value="male">Nam</option>
                                            <option value="female">Nữ</option>
                                        </select>
                                        @error('gender')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="sm:col-span-3">
                                        <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">Ngày sinh</label>
                                        <input type="date" wire:model.defer="birthday"
                                            class="w-full h-11 px-4 py-2.5 rounded-xl border border-black/[0.06] bg-white/80 backdrop-blur-sm text-sm
                                                shadow-mac-sm focus:outline-none focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40 transition-all" />
                                        @error('birthday')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </section>

                        <div class="mac-hairline-b"></div>

                        {{-- Family & contact --}}
                        <section>
                            <h2 class="text-xs font-semibold text-slate-500 tracking-wide uppercase mb-3 px-1">
                                Gia đình & liên hệ
                            </h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">Tên cha</label>
                                    <input type="text" wire:model.defer="father_name" placeholder="Họ và tên cha"
                                        class="w-full h-11 px-4 py-2.5 rounded-xl border border-black/[0.06] bg-white/80 backdrop-blur-sm text-sm
                                            shadow-mac-sm focus:outline-none focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40 transition-all" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">Tên mẹ</label>
                                    <input type="text" wire:model.defer="mother_name" placeholder="Họ và tên mẹ"
                                        class="w-full h-11 px-4 py-2.5 rounded-xl border border-black/[0.06] bg-white/80 backdrop-blur-sm text-sm
                                            shadow-mac-sm focus:outline-none focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40 transition-all" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">Điện thoại</label>
                                    <input type="tel" wire:model.defer="phone" placeholder="0123 456 789"
                                        class="w-full h-11 px-4 py-2.5 rounded-xl border border-black/[0.06] bg-white/80 backdrop-blur-sm text-sm
                                            shadow-mac-sm focus:outline-none focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40 transition-all" />
                                    @error('phone')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">Email</label>
                                    <input type="email" wire:model.defer="email" placeholder="email@example.com"
                                        class="w-full h-11 px-4 py-2.5 rounded-xl border text-sm bg-white/80 backdrop-blur-sm shadow-mac-sm
                                            focus:outline-none focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40 transition-all
                                            {{ $errors->has('email') ? 'border-red-300 bg-red-50/80' : 'border-black/[0.06]' }}" />
                                    @error('email')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </section>

                        <div class="mac-hairline-b"></div>

                        {{-- Parish --}}
                        <section>
                            <h2 class="text-xs font-semibold text-slate-500 tracking-wide uppercase mb-3 px-1">
                                Giáo xứ
                            </h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">
                                        Giáo xứ <span class="text-red-500 normal-case">*</span>
                                    </label>
                                    <x-searchable-select
                                        wireModel="parish_id"
                                        :options="$this->parishes"
                                        placeholder="-- Chọn giáo xứ --"
                                        labelKey="name"
                                        valueKey="id"
                                        :value="$this->parish_id" />
                                    @error('parish_id')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">Giáo họ</label>
                                    <x-searchable-select
                                        wireModel="parish_group_id"
                                        :options="$this->parishGroups"
                                        placeholder="-- Chọn giáo họ --"
                                        labelKey="name"
                                        valueKey="id"
                                        :value="$this->parish_group_id" />
                                    @error('parish_group_id')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </section>

                        <div class="mac-hairline-b"></div>

                        {{-- Status + note --}}
                        <section class="space-y-4">
                            <h2 class="text-xs font-semibold text-slate-500 tracking-wide uppercase mb-1 px-1">
                                Trạng thái & ghi chú
                            </h2>

                            <label class="flex items-start gap-3 cursor-pointer select-none rounded-xl bg-white/40 border border-black/[0.04] p-4">
                                <input id="is_active" type="checkbox" wire:model.defer="is_active"
                                    class="w-4 h-4 mt-0.5 rounded border-black/20 text-primary-500 focus:ring-primary-500/25" />
                                <div>
                                    <span class="text-sm font-semibold text-slate-700">Đang học (kích hoạt)</span>
                                    <p class="text-xs text-slate-500 mt-0.5">Học sinh đang theo học tại lớp giáo lý</p>
                                </div>
                            </label>

                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">Ghi chú</label>
                                <textarea wire:model.defer="note" rows="4"
                                    placeholder="Ghi chú thêm về học sinh..."
                                    class="w-full px-4 py-2.5 rounded-xl border border-black/[0.06] bg-white/80 backdrop-blur-sm text-sm
                                        shadow-mac-sm focus:outline-none focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40 transition-all resize-none"></textarea>
                                @error('note')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </section>

                    </div>

                    <div class="px-4 lg:px-6 py-4 mac-hairline-t bg-white/30 flex items-center justify-end gap-3">
                        <x-button as="a" variant="outline" href="{{
                            $isEdit
                                ? route('students.show', $studentId)
                                : route('students.index', $classId ? ['class' => $classId] : [])
                        }}">
                            <x-icon name="cancel" />
                            Hủy
                        </x-button>

                        <x-button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="avatar_path,save">
                            <x-icon name="save" />
                            Lưu
                        </x-button>
                    </div>
                </form>
            </x-mac-panel>
    </div>
</div>
