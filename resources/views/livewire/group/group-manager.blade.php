@section('topbar')
<x-breadcrumb :items="[
            ['label' => 'Trang chủ', 'url' => route('dashboard')],
            ['label' => 'Sinh hoạt',  'url' => '#'],
            ['label' => 'Quản lý nhóm'],
        ]" />
@endsection
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="mx-auto max-w-7xl space-y-5">

        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[
            ['label' => 'Trang chủ', 'url' => route('dashboard')],
            ['label' => 'Sinh hoạt',  'url' => '#'],
            ['label' => 'Quản lý nhóm'],
        ]" separator="arrow" />

        {{-- Toast --}}
        <div role="status" aria-live="polite">
            @if(session()->has('message'))
            <x-toast-notification type="success" :duration="3500">{{ session('message') }}</x-toast-notification>
            @endif
            @if(session()->has('error'))
            <x-toast-notification type="error" :duration="4000">{{ session('error') }}</x-toast-notification>
            @endif
        </div>

        {{-- Main Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <x-page-header
                title="Quản lý nhóm sinh hoạt"
                description="Giáo lý viên, ca đoàn và các nhóm trong giáo xứ"
                :stat-value="$groups->total()"
                stat-label="Nhóm"
                icon-type="group" />

            {{-- Actions Bar --}}
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/70">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    {{-- LEFT: Filters --}}
                    <div class="flex flex-wrap items-center gap-3">
                        <input
                            wire:model.debounce.500ms="search"
                            placeholder="Tìm tên nhóm..."
                            class="w-56 px-3 py-2 rounded-xl border border-slate-300
                                   text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" />

                        <select wire:model="filterType"
                            class="px-3 py-2 rounded-xl border border-slate-300 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="">-- Tất cả loại nhóm --</option>
                            @foreach($typeLabels as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- RIGHT --}}
                    <x-action-button wire="create" icon="plus">
                        Thêm nhóm
                    </x-action-button>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            @if($groups->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full border-separate border-spacing-0">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <x-table-header>STT</x-table-header>
                            <x-table-header>Tên nhóm</x-table-header>
                            <x-table-header>Loại</x-table-header>
                            <x-table-header>Thành viên</x-table-header>
                            <x-table-header>Buổi sinh hoạt</x-table-header>
                            <x-table-header class="text-center">Trạng thái</x-table-header>
                            <x-table-header class="text-center">Thao tác</x-table-header>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($groups as $index => $group)
                        <tr class="hover:bg-slate-50 transition-colors"
                            wire:key="group-{{ $group->id }}">

                            {{-- STT --}}
                            <td class="px-6 py-4 text-sm text-slate-500">
                                {{ ($groups->firstItem() ?? 0) + $index }}
                            </td>

                            {{-- Tên nhóm --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    {{-- Icon theo type --}}
                                    <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0
                                            {{ $group->type == 1 ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                                        @if($group->type == 1)
                                        {{-- GLV icon --}}
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                        @else
                                        {{-- Ca đoàn icon --}}
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                                        </svg>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-semibold text-slate-900">{{ $group->name }}</div>
                                        @if($group->note)
                                        <div class="text-xs text-slate-400 mt-0.5">{{ $group->note }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Loại --}}
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                        {{ $group->type == 1 ? 'bg-blue-100 text-blue-700' :
                                           ($group->type == 2 ? 'bg-purple-100 text-purple-700' :
                                           ($group->type == 3 ? 'bg-pink-100 text-pink-700' : 'bg-slate-100 text-slate-700')) }}">
                                    {{ $group->type_label }}
                                </span>
                            </td>

                            {{-- Thành viên --}}
                            <td class="px-6 py-4">
                                <a href="{{ route('groups.members', $group->id) }}"
                                    class="inline-flex items-center gap-1.5 text-sm font-semibold
                                               text-primary-600 hover:text-primary-700 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    {{ $group->active_members_count }} thành viên
                                </a>
                            </td>

                            {{-- Buổi sinh hoạt --}}
                            <td class="px-6 py-4">
                                <a href="{{ route('groups.sessions', $group->id) }}"
                                    class="inline-flex items-center gap-1.5 text-sm font-semibold
                                               text-indigo-600 hover:text-indigo-700 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    {{ $group->sessions_count }} buổi
                                </a>
                            </td>

                            {{-- Trạng thái --}}
                            <td class="px-6 py-4 text-center">
                                <button wire:click="toggleActive({{ $group->id }})"
                                    class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full
                                               transition-colors cursor-pointer
                                               {{ $group->is_active
                                                   ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200'
                                                   : 'bg-slate-200 text-slate-600 hover:bg-slate-300' }}">
                                    {{ $group->is_active ? 'Hoạt động' : 'Tạm dừng' }}
                                </button>
                            </td>

                            {{-- Thao tác --}}
                            <td class="px-6 py-4 text-center">
                                <div class="inline-flex items-center gap-3">
                                    <x-table-action wire="edit({{ $group->id }})" icon="edit">
                                        Sửa
                                    </x-table-action>

                                    <span class="text-slate-300">|</span>

                                    <x-table-action
                                        wire="delete({{ $group->id }})"
                                        icon="trash"
                                        color="danger"
                                        :loading="true"
                                        confirm="Xóa nhóm này? Toàn bộ dữ liệu sinh hoạt sẽ bị xóa theo.">
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
            @if($groups->hasPages())
            <div class="px-6 py-4 border-t border-slate-200">
                <x-pagination :paginator="$groups" :per-page-options="[10, 15, 25]" />
            </div>
            @endif

            @else
            <div class="text-center py-16">
                <div class="text-5xl mb-4">👥</div>
                <p class="text-lg text-slate-500">Chưa có nhóm nào</p>
                <button wire:click="create"
                    class="mt-4 px-4 py-2 bg-primary-600 text-white rounded-xl
                               hover:bg-primary-700 transition-all">
                    Thêm nhóm đầu tiên
                </button>
            </div>
            @endif
        </div>

        {{-- Modal Form --}}
        @if($showForm)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
            role="dialog" aria-modal="true" wire:click="closeModal">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden flex flex-col"
                wire:click.stop>

                {{-- Header --}}
                <div class="flex-shrink-0 p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                    <h2 class="text-xl font-bold text-slate-900">
                        {{ $editingId ? 'Cập nhật nhóm' : 'Thêm nhóm mới' }}
                    </h2>
                    <p class="text-sm text-slate-500 mt-1">
                        {{ $editingId ? 'Chỉnh sửa thông tin nhóm' : 'Tạo nhóm sinh hoạt trong giáo xứ' }}
                    </p>
                </div>

                {{-- Body --}}
                <div class="p-6 space-y-5">

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

                    {{-- Tên nhóm --}}
                    <x-form-input
                        label="Tên nhóm"
                        name="name"
                        wire:model.defer="name"
                        placeholder="VD: Nhóm Giáo lý viên, Ca đoàn thiếu nhi..."
                        required />

                    {{-- Loại nhóm --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Loại nhóm <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($typeLabels as $value => $label)
                            <label class="flex items-center gap-3 px-4 py-3 rounded-xl border cursor-pointer
                                transition-all
                                {{ $type == $value
                                    ? 'border-primary-500 bg-primary-50 text-primary-700'
                                    : 'border-slate-200 hover:border-slate-300 text-slate-700' }}">
                                <input type="radio"
                                    wire:model="type"
                                    value="{{ $value }}"
                                    class="text-primary-600 focus:ring-primary-500">
                                <span class="text-sm font-medium">{{ $label }}</span>
                            </label>
                            @endforeach
                        </div>
                        @error('type')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Loại thành viên --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Loại thành viên <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="flex items-center gap-3 px-4 py-3 rounded-xl border cursor-pointer
                                transition-all
                                {{ $member_type == 'teacher'
                                    ? 'border-blue-500 bg-blue-50 text-blue-700'
                                    : 'border-slate-200 hover:border-slate-300 text-slate-700' }}">
                                <input type="radio"
                                    wire:model="member_type"
                                    value="teacher"
                                    class="text-blue-600 focus:ring-blue-500">
                                <div>
                                    <div class="text-sm font-medium">Giáo lý viên</div>
                                    <div class="text-xs text-slate-400">Từ danh sách GLV</div>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 px-4 py-3 rounded-xl border cursor-pointer
                                transition-all
                                {{ $member_type == 'student'
                                    ? 'border-purple-500 bg-purple-50 text-purple-700'
                                    : 'border-slate-200 hover:border-slate-300 text-slate-700' }}">
                                <input type="radio"
                                    wire:model="member_type"
                                    value="student"
                                    class="text-purple-600 focus:ring-purple-500">
                                <div>
                                    <div class="text-sm font-medium">Học sinh</div>
                                    <div class="text-xs text-slate-400">Từ danh sách học sinh</div>
                                </div>
                            </label>
                        </div>
                        @error('member_type')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Ghi chú --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Ghi chú</label>
                        <textarea wire:model.defer="note" rows="2"
                            class="w-full px-3 py-2 rounded-xl border border-slate-300
                                   focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm"
                            placeholder="Mô tả thêm về nhóm..."></textarea>
                    </div>

                    {{-- Trạng thái --}}
                    <div class="border border-slate-200 rounded-xl p-4">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox"
                                wire:model.defer="is_active"
                                class="w-4 h-4 rounded border-slate-300 text-primary-600
                                       focus:ring-primary-500">
                            <span class="text-sm font-semibold text-slate-900">
                                Nhóm đang hoạt động
                            </span>
                        </label>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex-shrink-0 px-6 py-4 border-t border-slate-200 bg-slate-50
                            flex justify-end gap-3">
                    <x-action-button wire="closeModal" variant="secondary">Hủy</x-action-button>
                    <x-action-button wire="save" icon="save" :loading="true">
                        {{ $editingId ? 'Cập nhật' : 'Thêm nhóm' }}
                    </x-action-button>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>