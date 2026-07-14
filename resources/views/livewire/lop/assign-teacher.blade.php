@section('topbar')
<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => route('parish-admin.dashboard')],
    ['label' => 'Quản lý lớp học', 'url' => route('classes.index')],
    ['label' => 'Phân công Giáo lý viên'],
]" />
@endsection

@php
    $yearLabel = $class->schoolYear?->name;
    $glvCount = $currentTeachers->count();
@endphp

<div
    class="min-h-screen bg-apple-gray p-2 sm:p-4 lg:p-6"
    style="min-height: calc(100vh - 56px - var(--bottom-offset));">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="mx-auto max-w-7xl">
        <x-mac-panel :overflow="true">
            <x-page-header
                title="Phân công Giáo lý viên"
                :description="$yearLabel ? 'Năm học: ' . $yearLabel : 'Chọn lớp để phân công giáo lý viên'"
                icon-type="teacher">
                <x-slot name="actions">
                    <x-button as="a" href="{{ route('classes.index') }}" variant="subtle">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Về danh sách lớp
                    </x-button>
                </x-slot>
            </x-page-header>

            <div class="p-4 lg:p-6 mac-hairline-b bg-white/30">
                <div class="max-w-md">
                    <x-select-input
                        label="Lớp học"
                        placeholder="Chọn lớp"
                        :options="$classOptions"
                        wire:model="classId"
                        :value="$classId" />
                </div>
            </div>

            @if (session()->has('message'))
            <x-toast-notification type="success" :duration="3500">{{ session('message') }}</x-toast-notification>
            @endif
            @if (session()->has('error'))
            <x-toast-notification type="error" :duration="4500">{{ session('error') }}</x-toast-notification>
            @endif
            @if (session()->has('warning'))
            <x-toast-notification type="warning" :duration="4500">{{ session('warning') }}</x-toast-notification>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-5 lg:divide-x lg:divide-black/[0.06]">
                {{-- GLV hiện tại --}}
                <div class="lg:col-span-2 flex flex-col min-h-0">
                    <div class="px-4 lg:px-5 py-3.5 mac-hairline-b bg-white/30">
                        <h2 class="text-sm font-semibold text-slate-900">GLV đang phụ trách</h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            {{ $glvCount }} giáo lý viên trên lớp này
                        </p>
                    </div>

                    <div class="p-3 sm:p-4 space-y-2 flex-1">
                        @forelse($currentTeachers as $ct)
                        <div
                            wire:key="ct-{{ $ct['id'] }}"
                            class="flex items-center gap-3 p-3 rounded-xl
                                bg-slate-50/70 border border-black/[0.04]
                                hover:bg-white/80 hover:border-primary-200/60
                                transition">
                            <div class="w-10 h-10 rounded-xl flex-shrink-0 flex items-center justify-center
                                text-sm font-semibold shadow-mac-sm
                                {{ $ct['role'] === 1
                                    ? 'bg-primary-100 text-primary-700'
                                    : 'bg-slate-100 text-slate-600' }}">
                                {{ mb_strtoupper(mb_substr($ct['first_name'] ?: $ct['teacher_name'], 0, 1)) }}
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <p class="font-semibold text-slate-900 text-sm truncate">
                                        {{ $ct['teacher_name'] }}
                                    </p>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold
                                        {{ $ct['role'] === 1
                                            ? 'bg-primary-100 text-primary-700'
                                            : 'bg-slate-100 text-slate-600' }}">
                                        {{ $ct['role_label'] }}
                                    </span>
                                </div>
                                @if($ct['phone'])
                                <p class="text-xs text-slate-500 mt-0.5 truncate">
                                    {{ $ct['phone'] }}
                                </p>
                                @endif
                            </div>

                            <div class="flex items-center gap-1 flex-shrink-0">
                                <x-tooltip content="Đổi vai trò">
                                    <x-table-action
                                        wire="changeRole({{ $ct['id'] }}, {{ $ct['role'] === 1 ? 2 : 1 }})"
                                        icon="arrows-right-left"
                                        color="primary"
                                        :loading="true" />
                                </x-tooltip>
                                <x-tooltip content="Gỡ khỏi lớp">
                                    <x-table-action
                                        wire="remove({{ $ct['id'] }})"
                                        icon="trash"
                                        color="red"
                                        :loading="true" />
                                </x-tooltip>
                            </div>
                        </div>
                        @empty
                        <div class="rounded-xl border border-dashed border-black/[0.08] bg-slate-50/50
                            px-4 py-12 text-center">
                            <div class="mx-auto w-11 h-11 rounded-xl bg-white border border-black/[0.06]
                                flex items-center justify-center shadow-mac-sm mb-3">
                                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-slate-700">Chưa có GLV</p>
                            <p class="text-xs text-slate-500 mt-1">Chọn giáo lý viên ở cột bên phải để phân công</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                {{-- Form phân công --}}
                <div class="lg:col-span-3 flex flex-col min-h-0 border-t border-black/[0.06] lg:border-t-0">
                    <div class="px-4 lg:px-5 py-3.5 mac-hairline-b bg-white/30">
                        <h2 class="text-sm font-semibold text-slate-900">Thêm Giáo lý viên</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Chọn vai trò và tìm GLV để phân công</p>
                    </div>

                    <div class="p-4 lg:p-5 space-y-5 flex-1">
                        @if($errors->any())
                        <div class="rounded-xl bg-red-50 ring-1 ring-red-100 px-3.5 py-2.5 text-sm text-red-700 shadow-mac-sm">
                            <ul class="space-y-0.5 list-disc list-inside">
                                @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-2">
                                Vai trò <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 gap-2.5">
                                <button
                                    type="button"
                                    wire:click="$set('selectedRole', 1)"
                                    class="flex items-center gap-3 p-3.5 rounded-xl text-left transition
                                        border shadow-mac-sm
                                        {{ $selectedRole === 1
                                            ? 'border-primary-300 bg-primary-50/80 ring-1 ring-primary-500/20'
                                            : 'border-black/[0.06] bg-white/70 hover:bg-slate-50/80' }}">
                                    <span class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0
                                        {{ $selectedRole === 1 ? 'border-primary-500 bg-primary-500' : 'border-slate-300' }}">
                                        @if($selectedRole === 1)
                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 12 12">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 3L4.5 8.5L2 6" />
                                        </svg>
                                        @endif
                                    </span>
                                    <span>
                                        <span class="block text-sm font-semibold text-slate-900">Chủ nhiệm</span>
                                        <span class="block text-xs text-slate-500">Phụ trách chính</span>
                                    </span>
                                </button>

                                <button
                                    type="button"
                                    wire:click="$set('selectedRole', 2)"
                                    class="flex items-center gap-3 p-3.5 rounded-xl text-left transition
                                        border shadow-mac-sm
                                        {{ $selectedRole === 2
                                            ? 'border-primary-300 bg-primary-50/80 ring-1 ring-primary-500/20'
                                            : 'border-black/[0.06] bg-white/70 hover:bg-slate-50/80' }}">
                                    <span class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0
                                        {{ $selectedRole === 2 ? 'border-primary-500 bg-primary-500' : 'border-slate-300' }}">
                                        @if($selectedRole === 2)
                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 12 12">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 3L4.5 8.5L2 6" />
                                        </svg>
                                        @endif
                                    </span>
                                    <span>
                                        <span class="block text-sm font-semibold text-slate-900">Phụ trách</span>
                                        <span class="block text-xs text-slate-500">Hỗ trợ</span>
                                    </span>
                                </button>
                            </div>
                            @error('selectedRole')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-2">Tìm kiếm</label>
                            <x-search-input
                                wireModel="teacherSearch"
                                placeholder="Tên hoặc số điện thoại..."
                                debounce="400ms"
                                class="max-w-full" />
                        </div>

                        <div>
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <label class="block text-sm font-medium text-slate-600">
                                    Chọn Giáo lý viên <span class="text-red-500">*</span>
                                </label>
                                @if($availableTeachers->isNotEmpty())
                                <span class="text-xs text-slate-400">{{ $availableTeachers->count() }} kết quả</span>
                                @endif
                            </div>

                            @if($availableTeachers->isNotEmpty())
                            <div class="rounded-xl border border-black/[0.06] bg-white/60 overflow-hidden shadow-mac-sm
                                max-h-80 overflow-y-auto divide-y divide-black/[0.04]">
                                @foreach($availableTeachers as $teacher)
                                <label
                                    wire:key="avail-{{ $teacher->id }}"
                                    class="flex items-center gap-3 px-3.5 py-3 cursor-pointer transition
                                        {{ (int) $selectedTeacherId === (int) $teacher->id
                                            ? 'bg-primary-50/70'
                                            : 'hover:bg-slate-50/80' }}">
                                    <input
                                        type="radio"
                                        wire:model="selectedTeacherId"
                                        value="{{ $teacher->id }}"
                                        class="w-4 h-4 accent-primary-600 focus:ring-primary-500/30 flex-shrink-0">

                                    <div class="w-9 h-9 rounded-xl bg-primary-100 text-primary-700
                                        flex items-center justify-center font-semibold text-sm flex-shrink-0 shadow-mac-sm">
                                        {{ mb_strtoupper(mb_substr($teacher->first_name ?: $teacher->full_name, 0, 1)) }}
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-slate-900 text-sm truncate">
                                            {{ $teacher->full_name }}
                                        </p>
                                        @if($teacher->phone_number)
                                        <p class="text-xs text-slate-500 mt-0.5 truncate">
                                            {{ $teacher->phone_number }}
                                        </p>
                                        @endif
                                    </div>

                                    @if((int) $selectedTeacherId === (int) $teacher->id)
                                    <svg class="w-5 h-5 text-primary-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    @endif
                                </label>
                                @endforeach
                            </div>
                            @else
                            <div class="rounded-xl border border-dashed border-black/[0.08] bg-slate-50/50
                                px-4 py-10 text-center">
                                <p class="text-sm font-medium text-slate-700">
                                    {{ trim($teacherSearch) === '' ? 'Tất cả GLV đã được phân công' : 'Không tìm thấy kết quả' }}
                                </p>
                                <p class="text-xs text-slate-500 mt-1">
                                    {{ trim($teacherSearch) === '' ? 'Hoặc chưa có giáo lý viên trong giáo xứ' : 'Thử đổi từ khóa tìm kiếm' }}
                                </p>
                            </div>
                            @endif

                            @error('selectedTeacherId')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end mac-hairline-t -mx-4 lg:-mx-5 px-4 lg:px-5 pt-4">
                            <x-action-button
                                wire="assign"
                                icon="check"
                                :loading="true"
                                :disabled="!$selectedTeacherId">
                                Phân công
                            </x-action-button>
                        </div>
                    </div>
                </div>
            </div>
        </x-mac-panel>
    </div>
</div>
