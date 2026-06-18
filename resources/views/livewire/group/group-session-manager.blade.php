@section('topbar')
<x-breadcrumb :items="[
    ['label' => 'Trang chủ',    'url' => route('parish-admin.dashboard')],
    ['label' => 'Sinh hoạt',   'url' => '#'],
    ['label' => 'Quản lý nhóm','url' => route('groups.index')],
    ['label' => $group->name,  'url' => route('groups.members', $group->id)],
    ['label' => 'Buổi sinh hoạt'],
]" separator="arrow" />
@endsection

<div class="min-h-screen bg-slate-50 p-2 sm:p-4 lg:p-6"
    x-data="{ showForm: false }"
    x-init="
        document.addEventListener('livewire:load', () => {
            Livewire.on('openModal', () => { showForm = true; });
            Livewire.on('closeModal', () => { showForm = false; });
        });
    ">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="mx-auto max-w-7xl space-y-6">

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
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
            <x-page-header
                class="rounded-t-2xl"
                title="Buổi sinh hoạt — {{ $group->name }}"
                :count="$sessions->total()">
            </x-page-header>

            {{-- Actions Bar --}}
            <div class="p-4 lg:p-6 border-b border-slate-200 bg-slate-50/70 rounded-b-2xl">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <input
                            wire:model.debounce.300ms="search"
                            type="text"
                            placeholder="Tìm tiêu đề..."
                            class="w-56 px-3 py-2 rounded-xl border border-slate-300 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-primary-500" />

                        {{-- Thông tin nhóm --}}
                        <span class="text-sm text-slate-500 hidden lg:inline">
                            <span class="font-semibold text-slate-700">{{ $memberCount }}</span>
                            thành viên đang hoạt động
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <x-button as="a" href="{{ route('groups.members', $group->id) }}" variant="outline">
                            <x-icon name="users" />
                            Thành viên
                        </x-button>

                        <x-button wire:click="create">
                            <x-icon name="plus" />
                            Tạo buổi mới
                        </x-button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        @if($sessions->count() > 0)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full border-separate border-spacing-0">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <x-table-header>STT</x-table-header>
                                <x-table-header>Ngày</x-table-header>
                                <x-table-header>Ca</x-table-header>
                                <x-table-header>Loại</x-table-header>
                                <x-table-header>Tiêu đề</x-table-header>
                                <x-table-header>Thời gian</x-table-header>
                                <x-table-header class="text-center">Điểm danh</x-table-header>
                                <x-table-header class="text-center">Thao tác</x-table-header>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($sessions as $index => $session)
                            @php
                                $total      = $session->present_count + $session->excused_count
                                            + $session->absent_count + $session->late_count;
                                $hasRecords = $total > 0;
                            @endphp
                            <tr class="hover:bg-slate-50 transition-colors"
                                wire:key="session-{{ $session->id }}">

                                <td class="px-4 py-4 text-sm font-semibold text-slate-500">
                                    {{ ($sessions->firstItem() ?? 0) + $index }}
                                </td>

                                {{-- Ngày --}}
                                <td class="px-4 py-4">
                                    <div class="font-semibold text-slate-900">
                                        {{ $session->date->format('d/m/Y') }}
                                    </div>
                                    <div class="text-xs text-slate-400">
                                        {{ $session->date->isoFormat('dddd') }}
                                    </div>
                                </td>

                                {{-- Ca --}}
                                <td class="px-4 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full
                                                 text-xs font-semibold
                                        {{ $session->shift == 1 ? 'bg-amber-100 text-amber-700' :
                                          ($session->shift == 2 ? 'bg-blue-100 text-blue-700' :
                                                                  'bg-indigo-100 text-indigo-700') }}">
                                        {{ $shiftLabels[$session->shift] ?? 'Ca '.$session->shift }}
                                    </span>
                                </td>

                                {{-- Loại --}}
                                <td class="px-4 py-4">
                                    <span class="text-sm text-slate-600">
                                        {{ $typeLabels[$session->type] ?? '—' }}
                                    </span>
                                </td>

                                {{-- Tiêu đề --}}
                                <td class="px-4 py-4 text-sm text-slate-700">
                                    {{ $session->title ?: '—' }}
                                </td>

                                {{-- Thời gian --}}
                                <td class="px-4 py-4 text-sm text-slate-600 whitespace-nowrap">
                                    @if($session->start_time || $session->end_time)
                                        {{ substr($session->start_time ?? '--:--', 0, 5) }}
                                        –
                                        {{ substr($session->end_time ?? '--:--', 0, 5) }}
                                    @else
                                        <span class="text-slate-400">Chưa đặt</span>
                                    @endif
                                </td>

                                {{-- Điểm danh stats --}}
                                <td class="px-4 py-4">
                                    @if($hasRecords)
                                    <div class="flex items-center justify-center gap-3 text-xs">
                                        <span class="flex items-center gap-1 text-green-700 font-semibold">
                                            <span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span>
                                            {{ $session->present_count + $session->late_count }}
                                        </span>
                                        <span class="flex items-center gap-1 text-yellow-700 font-semibold">
                                            <span class="w-2 h-2 rounded-full bg-yellow-400 inline-block"></span>
                                            {{ $session->excused_count }}
                                        </span>
                                        <span class="flex items-center gap-1 text-red-700 font-semibold">
                                            <span class="w-2 h-2 rounded-full bg-red-500 inline-block"></span>
                                            {{ $session->absent_count }}
                                        </span>
                                    </div>
                                    <div class="text-center mt-1 text-xs text-slate-400">
                                        / {{ $memberCount }} thành viên
                                    </div>
                                    @else
                                    <div class="text-center text-xs text-slate-400">Chưa điểm danh</div>
                                    @endif
                                </td>

                                {{-- Thao tác --}}
                                <td class="px-4 py-4 overflow-visible">
                                    <div class="flex items-center justify-center gap-1">
                                        <x-tooltip content="Điểm danh">
                                            <a href="{{ route('groups.attendance', ['groupId' => $group->id, 'sessionId' => $session->id]) }}"
                                                class="p-2 hover:bg-primary-50 text-primary-600
                                                       rounded-lg transition-all inline-flex">
                                                <x-icon name="clipboard" />
                                            </a>
                                        </x-tooltip>

                                        <x-dropdown icon="more-vertical" align="right"
                                            variant="subtle" position="fixed">
                                            <x-dropdown-item
                                                as="a"
                                                :href="route('groups.attendance', ['groupId' => $group->id, 'sessionId' => $session->id])"
                                                icon="clipboard">
                                                Điểm danh
                                            </x-dropdown-item>

                                            <div class="h-px bg-slate-100 my-1"></div>

                                            <x-dropdown-item
                                                x-on:click="$dispatch('open-confirm', {
                                                    message: 'Xóa buổi {{ $session->date->format('d/m/Y') }}? Dữ liệu điểm danh sẽ bị xóa theo.',
                                                    wireMethod: 'delete({{ $session->id }})'
                                                })"
                                                icon="trash"
                                                class="text-red-600 hover:bg-red-50">
                                                Xóa buổi
                                            </x-dropdown-item>
                                        </x-dropdown>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Legend --}}
                <div class="px-6 py-3 border-t border-slate-100 bg-slate-50">
                    <div class="flex items-center gap-4 text-xs text-slate-500">
                        <span class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span> Có mặt / Trễ
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-yellow-400"></span> Vắng có phép
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span> Vắng không phép
                        </span>
                    </div>
                </div>

                @if($sessions->hasPages())
                <div class="p-6 border-t border-slate-200">
                    <x-pagination :paginator="$sessions" :per-page-options="[10, 15, 25, 50]" />
                </div>
                @endif
        </div>

        @else
        <x-stats.page-empty
            tone="primary"
            title="Chưa có buổi sinh hoạt nào"
            description="Tạo buổi đầu tiên để bắt đầu điểm danh">
            <x-slot name="icon">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </x-slot>
        </x-stats.page-empty>
        @endif

    </div>{{-- /max-w --}}

    {{-- ===================== MODAL TẠO BUỔI ===================== --}}
    <div
        x-show="showForm"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        role="dialog"
        aria-modal="true"
        @click="showForm = false; $wire.closeModal()"
        @keydown.escape.window="showForm = false; $wire.closeModal()">

        <div
            x-show="showForm"
            x-transition
            class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh]
                    overflow-hidden flex flex-col"
            @click.stop>

            {{-- Header --}}
            <div class="flex-shrink-0 p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">Tạo buổi sinh hoạt</h2>
                        <p class="text-sm text-slate-500 mt-1">{{ $group->name }}</p>
                    </div>
                    <button type="button"
                        @click="showForm = false; $wire.closeModal()"
                        class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                        <x-icon name="cancel" class="w-5 h-5" />
                    </button>
                </div>
            </div>

            {{-- Body --}}
            <div class="flex-1 overflow-y-auto p-6 space-y-5">

                @if($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 rounded-xl p-4">
                    <ul class="space-y-1 text-sm text-red-700">
                        @foreach($errors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- Chế độ tạo --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Chế độ tạo</label>
                    <div class="flex gap-1 bg-slate-100 p-1 rounded-xl">
                        <button type="button" wire:click="$set('createMode','single')"
                            class="flex-1 py-2 text-sm font-semibold rounded-lg transition-all
                                   {{ $createMode === 'single'
                                       ? 'bg-white text-primary-700 shadow-sm'
                                       : 'text-slate-500 hover:text-slate-700' }}">
                            Theo ngày
                        </button>
                        <button type="button" wire:click="$set('createMode','weekly')"
                            class="flex-1 py-2 text-sm font-semibold rounded-lg transition-all
                                   {{ $createMode === 'weekly'
                                       ? 'bg-white text-primary-700 shadow-sm'
                                       : 'text-slate-500 hover:text-slate-700' }}">
                            Theo tuần
                        </button>
                    </div>
                </div>

                {{-- Loại buổi --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Loại buổi <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach($typeLabels as $value => $label)
                        <label class="flex items-center gap-2 px-3 py-2.5 rounded-xl border cursor-pointer
                                      transition-all text-sm font-medium
                                      {{ $type == $value
                                          ? 'border-primary-500 bg-primary-50 text-primary-700'
                                          : 'border-slate-200 text-slate-700 hover:border-slate-300' }}">
                            <input type="radio" wire:model="type" value="{{ $value }}"
                                class="text-primary-600 focus:ring-primary-500">
                            {{ $label }}
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Ca --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Ca <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach($shiftLabels as $value => $label)
                        <label class="flex items-center gap-2 px-3 py-2.5 rounded-xl border cursor-pointer
                                      transition-all text-sm font-medium
                                      {{ $shift == $value
                                          ? 'border-primary-500 bg-primary-50 text-primary-700'
                                          : 'border-slate-200 text-slate-700 hover:border-slate-300' }}">
                            <input type="radio" wire:model="shift" value="{{ $value }}"
                                class="text-primary-600 focus:ring-primary-500">
                            {{ $label }}
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Single: Ngày --}}
                @if($createMode === 'single')
                <x-form-input label="Ngày sinh hoạt" name="startDate"
                    type="date" wire:model.defer="startDate" required />
                @endif

                {{-- Weekly: Từ ngày + đến ngày + ngày trong tuần --}}
                @if($createMode === 'weekly')
                <div class="grid grid-cols-2 gap-4">
                    <x-form-input label="Từ ngày" name="startDate"
                        type="date" wire:model.defer="startDate" required />
                    <x-form-input label="Đến ngày" name="endDate"
                        type="date" wire:model.defer="endDate" />
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Ngày trong tuần <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-4 gap-2">
                        @foreach([['0','CN'],['1','T2'],['2','T3'],['3','T4'],['4','T5'],['5','T6'],['6','T7']] as [$val, $lbl])
                        <label class="flex items-center gap-2 px-3 py-2 rounded-lg border cursor-pointer
                                      transition-all text-sm
                                      {{ in_array($val, $weekDays)
                                          ? 'border-primary-500 bg-primary-50 text-primary-700 font-semibold'
                                          : 'border-slate-200 text-slate-600 hover:border-slate-300' }}">
                            <input type="checkbox" wire:model="weekDays" value="{{ $val }}"
                                class="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                            {{ $lbl }}
                        </label>
                        @endforeach
                    </div>
                    @error('weekDays')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                @endif

                {{-- Thời gian --}}
                <div class="grid grid-cols-2 gap-4">
                    <x-form-input label="Giờ bắt đầu" name="startTime"
                        type="time" wire:model.defer="startTime" />
                    <x-form-input label="Giờ kết thúc" name="endTime"
                        type="time" wire:model.defer="endTime" />
                </div>

                {{-- Tiêu đề --}}
                <x-form-input label="Tiêu đề (không bắt buộc)" name="title"
                    wire:model.defer="title"
                    placeholder="VD: Tập bài thánh ca Giáng sinh..." />

                {{-- Note --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Ghi chú</label>
                    <textarea wire:model.defer="note" rows="2"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300
                               focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm"
                        placeholder="Ghi chú thêm..."></textarea>
                </div>

                {{-- Info --}}
                <div class="bg-blue-50 border-l-4 border-blue-400 rounded-xl p-4 text-sm text-blue-700">
                    <ul class="space-y-1">
                        <li>• Buổi đã tồn tại (trùng ngày + ca) sẽ bị bỏ qua</li>
                        @if($createMode === 'weekly')
                        <li>• Không đặt ngày kết thúc sẽ tạo trong 3 tháng tới</li>
                        @endif
                    </ul>
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex-shrink-0 px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end gap-3">
                <x-button variant="outline" @click="showForm = false; $wire.closeModal()">Hủy</x-button>
                <x-button
                    wire:click="save"
                    wire:loading.attr="disabled"
                    wire:target="save">
                    <svg wire:loading wire:target="save"
                        class="animate-spin w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10"
                            stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <x-icon name="save" />
                    Tạo buổi
                </x-button>
            </div>

        </div>
    </div>
    {{-- /modal --}}

</div>

@push('page-title')
<span class="text-slate-800 font-semibold text-sm">Buổi sinh hoạt · {{ $group->name }}</span>
@endpush