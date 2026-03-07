<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="mx-auto max-w-7xl space-y-5">

        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[
            ['label' => 'Trang chủ', 'url' => route('dashboard')],
            ['label' => 'Giáo lý viên', 'url' => route('catechists.index')],
        ]" separator="arrow" />

        {{-- Toast --}}
        <div role="status" aria-live="polite">
            @if (session()->has('message'))
            <x-toast-notification type="success" :duration="3500">{{ session('message') }}</x-toast-notification>
            @endif
            @if (session()->has('error'))
            <x-toast-notification type="error" :duration="4000">{{ session('error') }}</x-toast-notification>
            @endif
            @if (session()->has('warning'))
            <x-toast-notification type="warning" :duration="4000">{{ session('warning') }}</x-toast-notification>
            @endif
        </div>

        {{-- Main Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <x-page-header
                title="Quản lý Giáo lý viên"
                description="Danh sách giáo lý viên trong giáo xứ"
                :stat-value="$teachers->total()"
                stat-label="Giáo lý viên"
                icon-type="teacher" />

            {{-- Actions Bar --}}
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/70">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    {{-- LEFT: Filters --}}
                    <div class="flex flex-wrap items-center gap-3">

                        {{-- Search --}}
                        <input
                            wire:model.debounce.500ms="search"
                            placeholder="Tìm tên, SĐT, email..."
                            class="w-56 px-3 py-2 rounded-xl border border-slate-300
                                   text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" />

                        {{-- Filter giáo họ --}}
                        <select wire:model="filterParishGroup"
                            class="px-3 py-2 rounded-xl border border-slate-300 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="">-- Tất cả giáo họ --</option>
                            @foreach($parishGroups as $pg)
                            <option value="{{ $pg->id }}">{{ $pg->name }}</option>
                            @endforeach
                        </select>

                        {{-- Filter giới tính --}}
                        <select wire:model="filterGender"
                            class="px-3 py-2 rounded-xl border border-slate-300 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="">-- Tất cả giới tính --</option>
                            <option value="male">Nam</option>
                            <option value="female">Nữ</option>
                        </select>

                        {{-- Filter trạng thái --}}
                        <select wire:model="filterActive"
                            class="px-3 py-2 rounded-xl border border-slate-300 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="">-- Tất cả trạng thái --</option>
                            <option value="1">Đang hoạt động</option>
                            <option value="0">Đã nghỉ</option>
                        </select>
                    </div>

                    {{-- RIGHT: Add button --}}
                    <x-action-button wire="create" icon="plus">
                        Thêm giáo lý viên
                    </x-action-button>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            @if($teachers->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full border-separate border-spacing-0">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <x-table-header>STT</x-table-header>
                            <x-table-header>Họ tên</x-table-header>
                            <x-table-header>Giới tính</x-table-header>
                            <x-table-header>Ngày sinh</x-table-header>
                            <x-table-header>Liên hệ</x-table-header>
                            <x-table-header>Giáo họ</x-table-header>
                            <x-table-header class="text-center">Trạng thái</x-table-header>
                            <x-table-header class="text-center">Thao tác</x-table-header>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($teachers as $index => $teacher)
                        <tr class="hover:bg-slate-50 transition-colors" wire:key="teacher-{{ $teacher->id }}">

                            {{-- STT --}}
                            <td class="px-6 py-4 text-sm text-slate-500">
                                {{ ($teachers->firstItem() ?? 0) + $index }}
                            </td>

                            {{-- Họ tên --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    {{-- Avatar placeholder --}}
                                    <div class="w-9 h-9 rounded-full bg-primary-100 flex items-center justify-center
                                                text-primary-700 font-bold text-sm flex-shrink-0">
                                        {{ mb_substr($teacher->first_name, 0, 1) }}
                                    </div>
                                    <div>
                                        @if($teacher->saint)
                                        <div class="text-xs text-slate-500">
                                            {{ $teacher->saint->name }}
                                        </div>
                                        @endif
                                        <div class="font-semibold text-slate-900">

                                            {{ $teacher->full_name }}
                                        </div>

                                    </div>
                                </div>
                            </td>

                            {{-- Giới tính --}}
                            <td class="px-6 py-4 text-sm text-slate-600">
                                {{ $teacher->gender_text }}
                            </td>

                            {{-- Ngày sinh --}}
                            <td class="px-6 py-4 text-sm text-slate-600">
                                {{ $teacher->birthday?->format('d/m/Y') ?? '—' }}
                            </td>

                            {{-- Liên hệ --}}
                            <td class="px-6 py-4">
                                <div class="space-y-1">
                                    @if($teacher->phone_number)
                                    <div class="text-sm text-slate-700">📞 {{ $teacher->phone_number }}</div>
                                    @endif
                                    @if($teacher->email)
                                    <div class="text-xs text-slate-500 truncate max-w-40">✉ {{ $teacher->email }}</div>
                                    @endif
                                </div>
                            </td>

                            {{-- Giáo họ --}}
                            <td class="px-6 py-4">
                                @if($teacher->parishGroup)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full
                                             text-xs font-semibold bg-indigo-100 text-indigo-700">
                                    {{ $teacher->parishGroup->name }}
                                </span>
                                @else
                                <span class="text-slate-400 text-sm">—</span>
                                @endif
                            </td>

                            {{-- Trạng thái --}}
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full
                                             {{ $teacher->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">
                                    {{ $teacher->is_active ? 'Hoạt động' : 'Đã nghỉ' }}
                                </span>
                            </td>

                            {{-- Thao tác --}}
                            <td class="px-6 py-4 text-center">
                                <div class="inline-flex items-center gap-3">
                                    <x-table-action wire="edit({{ $teacher->id }})" icon="edit">
                                        Sửa
                                    </x-table-action>

                                    <span class="text-slate-300">|</span>

                                    <x-table-action
                                        wire="delete({{ $teacher->id }})"
                                        icon="trash"
                                        color="danger"
                                        :loading="true"
                                        confirm="Xóa giáo lý viên này sẽ xóa luôn tài khoản đăng nhập. Bạn chắc chắn?">
                                        Xóa
                                    </x-table-action>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($teachers->hasPages())
            <div class="px-6 py-4 border-t border-slate-200">
                <x-pagination :paginator="$teachers" :per-page-options="[10, 15, 25, 50]" />
            </div>
            @endif

            @else
            <div class="text-center py-16">
                <div class="text-5xl mb-4">👨‍🏫</div>
                <p class="text-lg text-slate-500">Chưa có giáo lý viên nào</p>
                <button wire:click="create"
                    class="mt-4 px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition-all">
                    Thêm giáo lý viên đầu tiên
                </button>
            </div>
            @endif
        </div>

        {{-- Modal Form --}}
        @if($showForm)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
            role="dialog" aria-modal="true" wire:click="closeModal">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col"
                wire:click.stop>

                {{-- Header --}}
                <div class="flex-shrink-0 p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                    <h2 class="text-xl font-bold text-slate-900">
                        {{ $editingId ? 'Cập nhật giáo lý viên' : 'Thêm giáo lý viên mới' }}
                    </h2>
                    <p class="text-sm text-slate-500 mt-1">
                        {{ $editingId ? 'Chỉnh sửa thông tin' : 'Tạo tài khoản đăng nhập tự động' }}
                    </p>
                </div>

                {{-- Body --}}
                <div class="flex-1 overflow-y-auto p-6 space-y-5">

                    {{-- Errors --}}
                    @if($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-500 rounded-xl p-4">
                        <ul class="space-y-1 text-sm text-red-700">
                            @foreach($errors->all() as $error)
                            <li>• {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    {{-- Họ & Tên --}}
                    <div class="grid grid-cols-2 gap-4">
                        <x-form-input label="Họ (và tên đệm)" name="last_name"
                            wire:model.defer="last_name"
                            placeholder="VD: Nguyễn Văn" required />

                        <x-form-input label="Tên" name="first_name"
                            wire:model.defer="first_name"
                            placeholder="VD: An" required />
                    </div>

                    {{-- Giới tính & Ngày sinh --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Giới tính</label>
                            <select wire:model.defer="gender"
                                class="w-full px-3 py-2 rounded-xl border border-slate-300
                                       focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="">-- Chọn --</option>
                                <option value="male">Nam</option>
                                <option value="female">Nữ</option>
                            </select>
                        </div>

                        <x-form-input label="Ngày sinh" name="birthday"
                            type="date" wire:model.defer="birthday" />
                    </div>

                    {{-- SĐT & Email --}}
                    <div class="grid grid-cols-2 gap-4">
                        <x-form-input label="Số điện thoại" name="phone_number"
                            wire:model.defer="phone_number"
                            placeholder="0901234567" />

                        <x-form-input label="Email" name="email"
                            type="email" wire:model.defer="email"
                            placeholder="example@gmail.com" />
                    </div>

                    {{-- Địa chỉ --}}
                    <x-form-input label="Địa chỉ" name="address"
                        wire:model.defer="address"
                        placeholder="Địa chỉ cư trú" />

                    {{-- Giáo họ & Tên thánh --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Giáo họ</label>
                            <select wire:model.defer="parish_group_id"
                                class="w-full px-3 py-2 rounded-xl border border-slate-300
                                       focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="">-- Chọn giáo họ --</option>
                                @foreach($parishGroups as $pg)
                                <option value="{{ $pg->id }}">{{ $pg->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Tên thánh</label>
                            <select wire:model.defer="saint_id"
                                class="w-full px-3 py-2 rounded-xl border border-slate-300
                                       focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="">-- Chọn tên thánh --</option>
                                @foreach($saints as $saint)
                                <option value="{{ $saint->id }}">{{ $saint->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Ghi chú --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Ghi chú</label>
                        <textarea wire:model.defer="note" rows="2"
                            class="w-full px-3 py-2 rounded-xl border border-slate-300
                                   focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm"
                            placeholder="Ghi chú thêm..."></textarea>
                    </div>

                    {{-- Trạng thái --}}
                    <div class="border border-slate-200 rounded-xl p-4">
                        <div class="flex items-center gap-3">
                            <input id="teacher-active" type="checkbox"
                                wire:model.defer="is_active"
                                class="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                            <label for="teacher-active" class="text-sm font-semibold text-slate-900 cursor-pointer">
                                Giáo lý viên đang hoạt động (bỏ chọn nếu đã nghỉ hoặc không còn giảng dạy)
                            </label>
                        </div>
                    </div>

                    {{-- Info tạo tài khoản --}}
                    @if(!$editingId)
                    <div class="bg-blue-50 border-l-4 border-blue-400 rounded-xl p-4 text-sm text-blue-700">
                        <strong>Tài khoản tự động:</strong> Email đăng nhập sẽ dùng email thật (nếu có),
                        hoặc <code>SĐT@giaoly.local</code>. Mật khẩu mặc định là số điện thoại.
                    </div>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="flex-shrink-0 px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end gap-3">
                    <x-action-button wire="closeModal" variant="secondary">Hủy</x-action-button>
                    <x-action-button wire="save" icon="save" :loading="true">
                        {{ $editingId ? 'Cập nhật' : 'Thêm giáo lý viên' }}
                    </x-action-button>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>

{{-- Loading --}}
<div wire:loading class="fixed inset-0 bg-gray-900/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 flex items-center gap-3">
        <svg class="animate-spin h-6 w-6 text-primary-600" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
        <span class="text-gray-700">Đang xử lý...</span>
    </div>
</div>