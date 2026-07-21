@section('title', 'Đăng ký quản trị xứ')

<div class="relative min-h-[calc(100vh-8rem)] py-6 sm:py-10">
    <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
        <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-[28rem] h-[28rem]
            rounded-full bg-primary-200/30 blur-3xl"></div>
        <div class="absolute bottom-0 right-0 w-72 h-72
            rounded-full bg-slate-300/25 blur-3xl"></div>
    </div>

    <div class="relative mx-auto max-w-2xl px-3 sm:px-4">
        <div class="mb-4">
            <a href="{{ route('login') }}"
                class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-primary-600 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Về trang đăng nhập
            </a>
        </div>

        @if($submitted)
        <x-mac-panel :overflow="true">
            <div class="px-6 py-10 text-center space-y-5">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-emerald-50/90 ring-1 ring-emerald-100/80
                    flex items-center justify-center shadow-mac-sm">
                    <svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Đã gửi yêu cầu</h1>
                    <p class="mt-2 text-sm text-slate-500">Mã tham chiếu của bạn</p>
                    <p class="mt-2 text-xl font-mono font-semibold text-primary-600 tracking-wide">{{ $referenceCode }}</p>
                </div>
                <p class="text-sm text-slate-500 max-w-md mx-auto leading-relaxed">
                    Bạn đã đăng ký tài khoản với vai trò
                    <strong class="font-semibold text-slate-700">{{ $submittedRoleLabel }}</strong>.
                    Quản trị hệ thống sẽ duyệt yêu cầu. Sau khi được duyệt, đăng nhập bằng email và mật khẩu đã đăng ký.
                </p>
                <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
                    <a href="{{ route('login') }}"
                        class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl
                            bg-primary-500 text-white text-sm font-semibold shadow-mac-sm
                            hover:bg-primary-600 active:scale-[0.98] transition-all">
                        Đến trang đăng nhập
                    </a>
                    <a href="{{ route('landing') }}"
                        class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl
                            bg-white/80 border border-black/[0.08] text-slate-700 text-sm font-semibold
                            shadow-mac-sm hover:bg-slate-50 active:scale-[0.98] transition-all">
                        Về trang chủ
                    </a>
                </div>
            </div>
        </x-mac-panel>
        @else
        <x-mac-panel :overflow="true">
            <x-page-header
                icon-type="default"
                title="Đăng ký quản trị xứ"
                description="Gửi yêu cầu tài khoản. Quản trị hệ thống duyệt trước khi bạn đăng nhập được.">
                <x-slot name="actions">
                    <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-semibold
                        bg-primary-50/80 text-primary-700 shadow-mac-sm">
                        Công khai
                    </span>
                </x-slot>
            </x-page-header>

            <form wire:submit.prevent="submit">
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
                    {{-- Giáo phận / Giáo hạt / Giáo xứ --}}
                    <section>
                        <h2 class="text-xs font-semibold text-slate-500 tracking-wide uppercase mb-3 px-1">
                            Giáo phận & giáo xứ
                        </h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">
                                    Giáo phận <span class="text-red-500 normal-case">*</span>
                                </label>
                                <x-searchable-select
                                    wire:key="diocese-{{ $dioceseId ?? 'none' }}"
                                    wireModel="dioceseId"
                                    :options="$dioceseOptions"
                                    :live="true"
                                    placeholder="— Chọn giáo phận —"
                                    labelKey="name"
                                    valueKey="id"
                                    :value="$dioceseId" />
                                @error('dioceseId')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">
                                    Giáo hạt <span class="text-red-500 normal-case">*</span>
                                </label>
                                <x-searchable-select
                                    wire:key="deanery-{{ $dioceseId ?? 'none' }}-{{ $deaneryId ?? 'none' }}"
                                    wireModel="deaneryId"
                                    :options="$deaneryOptions"
                                    :live="true"
                                    placeholder="{{ $dioceseId ? '— Chọn giáo hạt —' : 'Chọn giáo phận trước' }}"
                                    labelKey="name"
                                    valueKey="id"
                                    :value="$deaneryId" />
                                @error('deaneryId')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">
                                    Giáo xứ <span class="text-red-500 normal-case">*</span>
                                </label>
                                @if($useCustomParish)
                                <input type="text"
                                    wire:model.defer="customParishName"
                                    placeholder="Ví dụ: Bùi Chu"
                                    class="w-full h-11 px-4 py-2.5 rounded-xl border text-sm bg-white/80 backdrop-blur-sm shadow-mac-sm
                                        focus:outline-none focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40 transition-all
                                        {{ $errors->has('customParishName') ? 'border-red-300 bg-red-50/80' : 'border-black/[0.06]' }}" />
                                <p class="mt-1 text-xs text-slate-400">
                                    Chỉ nhập tên riêng; hệ thống tự thêm tiền tố “Giáo xứ”.
                                </p>
                                @else
                                <x-searchable-select
                                    wire:key="parish-{{ $deaneryId ?? 'none' }}-{{ $targetParishId ?? 'none' }}"
                                    wireModel="targetParishId"
                                    :options="$parishOptions"
                                    :live="true"
                                    placeholder="{{ $deaneryId ? '— Chọn giáo xứ —' : 'Chọn giáo hạt trước' }}"
                                    labelKey="name"
                                    valueKey="id"
                                    :value="$targetParishId" />
                                @endif
                                @error('targetParishId')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                                @error('customParishName')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <label class="mt-3 inline-flex items-start gap-2.5 cursor-pointer select-none px-1
                            {{ $deaneryId ? '' : 'opacity-50 pointer-events-none' }}">
                            <input type="checkbox"
                                wire:model="useCustomParish"
                                @if(! $deaneryId) disabled @endif
                                class="mt-0.5 rounded-md border-black/15 text-primary-600
                                    focus:ring-primary-500/30 shadow-mac-sm">
                            <span>
                                <span class="block text-sm font-medium text-slate-700">Giáo xứ chưa có trong danh sách</span>
                                <span class="block text-xs text-slate-400 mt-0.5">Tick để nhập tên giáo xứ mới (sẽ gắn giáo hạt đã chọn)</span>
                            </span>
                        </label>

                        @if($useCustomParish)
                        <div class="mt-4 space-y-3 px-1">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <h3 class="text-xs font-semibold text-slate-500 tracking-wide uppercase">
                                        Giáo họ <span class="text-red-500 normal-case">*</span>
                                    </h3>
                                    <p class="text-xs text-slate-400 mt-0.5 red">Nhập đầy đủ tên các giáo họ/giáo giâu/giáo khu sẽ sử dụng trong hệ thống</p>
                                </div>
                                <button type="button"
                                    wire:click="addParishGroupRow"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold
                                        bg-primary-50/80 text-primary-700 border border-primary-200/60
                                        hover:bg-primary-100/80 transition shadow-mac-sm">
                                    + Thêm giáo họ
                                </button>
                            </div>

                            <div class="space-y-2">
                                @foreach($parishGroupNames as $index => $groupName)
                                <div wire:key="parish-group-row-{{ $index }}" class="flex items-center gap-2">
                                    <input type="text"
                                        wire:model.defer="parishGroupNames.{{ $index }}"
                                        placeholder="Tên giáo họ {{ $index + 1 }}"
                                        class="flex-1 h-11 px-4 py-2.5 rounded-xl border text-sm bg-white/80 backdrop-blur-sm shadow-mac-sm
                                            focus:outline-none focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40 transition-all
                                            {{ $errors->has('parishGroupNames') || $errors->has('parishGroupNames.'.$index)
                                                ? 'border-red-300 bg-red-50/80'
                                                : 'border-black/[0.06]' }}" />
                                    <button type="button"
                                        wire:click="removeParishGroupRow({{ $index }})"
                                        class="inline-flex items-center justify-center w-10 h-10 rounded-xl
                                            border border-black/[0.06] text-slate-400 hover:text-red-600 hover:bg-red-50/80
                                            transition shadow-mac-sm"
                                        title="Xóa dòng"
                                        @if(count($parishGroupNames) <= 1) disabled @endif>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                @endforeach
                            </div>
                            @error('parishGroupNames')
                            <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                            @error('parishGroupNames.*')
                            <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        @endif
                    </section>

                    <div class="mac-hairline-b"></div>

                    {{-- Roles --}}
                    <section>
                        <h2 class="text-xs font-semibold text-slate-500 tracking-wide uppercase mb-3 px-1">
                            Quyền cần tạo <span class="text-red-500 normal-case">*</span>
                        </h2>
                        <p class="text-xs text-slate-400 mb-3 px-1 leading-relaxed">
                            Chỉ chọn một quyền quản trị.
                        </p>
                        <div class="grid grid-cols-1 gap-2.5">
                            @foreach($this->roleCatalog as $roleKey => $roleMeta)
                            <label wire:key="role-{{ $roleKey }}"
                                wire:click.prevent="selectRole('{{ $roleKey }}')"
                                class="flex items-start gap-3 p-3.5 rounded-xl border cursor-pointer transition-all
                                {{ $selectedRole === $roleKey
                                    ? 'border-primary-300/60 bg-primary-50/80 shadow-mac-sm'
                                    : 'border-black/[0.06] bg-white/60 hover:bg-white/80' }}">
                                <x-checkbox
                                    class="mt-0.5 pointer-events-none"
                                    :checked="$selectedRole === $roleKey"
                                    tabindex="-1" />
                                <span class="min-w-0">
                                    <span class="block text-sm font-semibold text-slate-800">{{ $roleMeta['label'] }}</span>
                                    <span class="block text-xs text-slate-500 mt-0.5 leading-relaxed">{{ $roleMeta['description'] }}</span>
                                </span>
                            </label>
                            @endforeach
                        </div>
                        @error('selectedRole')
                        <p class="mt-2 text-xs text-red-500 px-1">{{ $message }}</p>
                        @enderror
                    </section>

                    <div class="mac-hairline-b"></div>

                    {{-- Account --}}
                    <section>
                        <h2 class="text-xs font-semibold text-slate-500 tracking-wide uppercase mb-3 px-1">
                            Thông tin tài khoản
                        </h2>
                        <p class="text-xs text-slate-400 mb-3 px-1 leading-relaxed">
                            Email + mật khẩu dùng để đăng nhập. Họ tên khuyến khích điền. SĐT tuỳ chọn (liên hệ khi duyệt).
                        </p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
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
                                    Email <span class="text-red-500 normal-case">*</span>
                                    <span class="font-normal normal-case text-slate-400">— đăng nhập</span>
                                </label>
                                <input type="email" wire:model.defer="email" placeholder="email@example.com"
                                    class="w-full h-11 px-4 py-2.5 rounded-xl border text-sm bg-white/80 backdrop-blur-sm shadow-mac-sm
                                        focus:outline-none focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40 transition-all
                                        {{ $errors->has('email') ? 'border-red-300 bg-red-50/80' : 'border-black/[0.06]' }}" />
                                @error('email')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">
                                    Số điện thoại
                                    <span class="font-normal normal-case text-slate-400">(tuỳ chọn)</span>
                                </label>
                                <input type="text" wire:model.defer="phone" placeholder="0901234567"
                                    class="w-full h-11 px-4 py-2.5 rounded-xl border text-sm bg-white/80 backdrop-blur-sm shadow-mac-sm
                                        focus:outline-none focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40 transition-all
                                        border-black/[0.06]" />
                                @error('phone')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">
                                    Mật khẩu <span class="text-red-500 normal-case">*</span>
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

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">
                                    Ghi chú
                                </label>
                                <textarea wire:model.defer="note" rows="3"
                                    placeholder="Chức danh, lý do đăng ký..."
                                    class="w-full px-4 py-2.5 rounded-xl border text-sm bg-white/80 backdrop-blur-sm shadow-mac-sm
                                        focus:outline-none focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40 transition-all
                                        border-black/[0.06]"></textarea>
                                @error('note')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </section>
                </div>

                <div class="px-4 lg:px-6 py-4 mac-hairline-t bg-white/40 flex flex-col-reverse sm:flex-row
                    sm:items-center sm:justify-between gap-3 rounded-b-2xl">
                    <a href="{{ route('login') }}"
                        class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-medium
                            text-slate-600 hover:text-slate-900 transition">
                        Đã có tài khoản? Đăng nhập
                    </a>
                    <button type="submit"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl
                            bg-primary-500 text-white text-sm font-semibold shadow-mac-sm
                            hover:bg-primary-600 disabled:opacity-60
                            focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500/40
                            active:scale-[0.98] transition-all">
                        <span wire:loading.remove wire:target="submit">Gửi yêu cầu đăng ký</span>
                        <span wire:loading wire:target="submit">Đang gửi...</span>
                    </button>
                </div>
            </form>
        </x-mac-panel>
        @endif
    </div>
</div>
