@section('topbar')
<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => route('dashboard')],
    ['label' => 'Quản lý lớp học', 'url' => route('classes.index')],
    ['label' => 'Phân công Giáo lý viên'],
]" />
@endsection

<div class="min-h-screen bg-slate-50 p-6">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="mx-auto max-w-7xl space-y-5">
        {{-- Page Header --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <x-page-header
                title="Phân công Giáo lý viên"
                :description="'Lớp: ' . $class->name . ' — ' . ($class->schoolYear->name ?? 'N/A')"
                :stat-value="$currentTeachers->count()"
                stat-label="GLV phụ trách"
                icon-type="teacher" />
        </div>

        {{-- 2 Columns --}}
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

            {{-- ═══ CỘT TRÁI: GLV hiện tại (2/5) ═══ --}}
            <div class="lg:col-span-2 space-y-4">
                <div class="bg-white rounded-2xl border border-slate-200 transition overflow-hidden">

                    {{-- Header --}}
                    <div class="p-6 border-b border-slate-200">
                        <h3 class="text-base font-semibold text-slate-900">GLV đang phụ trách</h3>
                        <p class="text-sm text-slate-500 mt-1">
                            {{ $currentTeachers->count() }} giáo lý viên
                        </p>
                    </div>

                    {{-- List --}}
                    <div class="p-6 space-y-6">
                        @if($currentTeachers->isNotEmpty())
                        <div class="space-y-3">
                            @foreach($currentTeachers as $ct)
                            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl hover:bg-slate-100 transition"
                                wire:key="ct-{{ $ct['id'] }}">

                                {{-- Avatar + Info --}}
                                <div class="flex items-center gap-3 flex-1 min-w-0">
                                    <div class="w-10 h-10 rounded-full flex-shrink-0
                                                flex items-center justify-center font-bold text-sm
                                                {{ $ct['role'] === 1 ? 'bg-primary-100 text-primary-700' : 'bg-slate-100 text-primary-700' }}">
                                        {{ mb_strtoupper(mb_substr($ct['first_name'], 0, 1)) }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <p class="font-semibold text-slate-900 text-sm truncate">
                                                {{ $ct['teacher_name'] }}
                                            </p>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                                                         {{ $ct['role'] === 1 ? 'bg-primary-100 text-primary-700' : 'bg-slate-100 text-primary-700' }}">
                                                {{ $ct['role_label'] }}
                                            </span>
                                        </div>
                                        @if($ct['phone'])

                                        <p class="text-xs text-slate-500 mt-0.5">
                                            <x-icon name="phone" class="w-3 h-3 text-slate-400 inline-block mr-1" /> {{ $ct['phone'] }}
                                        </p>
                                        @endif
                                    </div>
                                </div>

                                {{-- Actions --}}
                                <div class="flex items-center gap-2 flex-shrink-0 ml-2">

                                    {{-- Đổi role --}}
                                    <x-tooltip content="Đổi vai trò của giáo lý viên">
                                        <x-table-action wire="changeRole({{ $ct['id'] }}, {{ $ct['role'] === 1 ? 2 : 1 }})"
                                            icon="arrows-right-left"
                                            color="primary"
                                            :loading="true" />
                                    </x-tooltip>

                                    {{-- Xóa --}}
                                    <x-tooltip content="Xóa giáo lý viên khỏi lớp">
                                        <x-table-action
                                            wire="remove({{ $ct['id'] }})"
                                            icon="trash"
                                            color="red"
                                            :loading="true" />
                                    </x-tooltip>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="text-center py-10">
                            <div class="text-4xl mb-3">👨‍🏫</div>
                            <p class="text-sm text-slate-500">Chưa có GLV nào được phân công</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ═══ CỘT PHẢI: Form phân công (3/5) ═══ --}}
            <div class="lg:col-span-3">
                <div class="bg-white rounded-2xl border border-slate-200 transition overflow-hidden">

                    {{-- Header --}}
                    <div class="p-6 border-b border-slate-200">
                        <h3 class="text-base font-semibold text-slate-900">Thêm Giáo lý viên</h3>
                        <p class="text-sm text-slate-500 mt-1">Tìm và phân công GLV cho lớp này</p>
                    </div>

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

                        {{-- Chọn vai trò --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Vai trò <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 gap-3">

                                {{-- Chủ nhiệm --}}
                                <div wire:click="$set('selectedRole', 1)"
                                    class="flex items-center gap-3 p-4 border-2 rounded-xl cursor-pointer transition
                                           {{ $selectedRole === 1
                                               ? 'border-primary-500 bg-primary-50'
                                               : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50' }}">
                                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0
                                                {{ $selectedRole === 1 ? 'border-primary-500 bg-primary-500' : 'border-slate-300' }}">
                                        @if($selectedRole === 1)
                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 12 12">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 3L4.5 8.5L2 6" />
                                        </svg>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-semibold text-slate-900 text-sm">Chủ nhiệm</div>
                                        <div class="text-xs text-slate-500">Phụ trách chính</div>
                                    </div>
                                </div>

                                {{-- Phụ trách --}}
                                <div wire:click="$set('selectedRole', 2)"
                                    class="flex items-center gap-3 p-4 border-2 rounded-xl cursor-pointer transition
                                           {{ $selectedRole === 2
                                               ? 'border-primary-500 bg-primary-50'
                                               : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50' }}">
                                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0
                                                {{ $selectedRole === 2 ? 'border-primary-500 bg-primary-500' : 'border-slate-300' }}">
                                        @if($selectedRole === 2)
                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 12 12">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 3L4.5 8.5L2 6" />
                                        </svg>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-semibold text-slate-900 text-sm">Phụ trách</div>
                                        <div class="text-xs text-slate-500">Hỗ trợ</div>
                                    </div>
                                </div>
                            </div>
                            @error('selectedRole')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Search --}}
                        <div>
                            <x-search-input wireModel="teacherSearch" placeholder="Nhập tên hoặc số điện thoại..." class="mt-2" />
                        </div>

                        {{-- Danh sách GLV --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Chọn Giáo lý viên <span class="text-red-500">*</span>
                            </label>

                            @if($availableTeachers->isNotEmpty())
                            <div class="border border-slate-200 rounded-xl overflow-y-auto max-h-80 divide-y divide-slate-100">
                                @foreach($availableTeachers as $teacher)
                                <label
                                    class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 cursor-pointer transition
                                           {{ $selectedTeacherId == $teacher->id ? 'bg-primary-50' : '' }}">
                                    <input
                                        type="radio"
                                        wire:model="selectedTeacherId"
                                        value="{{ $teacher->id }}"
                                        class="w-4 h-4 accent-primary-600 focus:ring-primary-600 flex-shrink-0">

                                    <div class="w-9 h-9 rounded-full bg-primary-100 text-primary-700
                                                flex items-center justify-center font-bold text-sm flex-shrink-0">
                                        {{ mb_strtoupper(mb_substr($teacher->first_name, 0, 1)) }}
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-slate-900 text-sm truncate">
                                            {{ $teacher->full_name }}
                                        </p>
                                        @if($teacher->phone_number)
                                        <p class="text-xs text-slate-500 mt-0.5">
                                            <x-icon name="phone" class="w-3 h-3 text-slate-400 inline-block mr-1" />
                                            {{ $teacher->phone_number }}
                                        </p>
                                        @endif
                                    </div>

                                    @if($selectedTeacherId == $teacher->id)
                                    <svg class="w-5 h-5 text-primary-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    @endif
                                </label>
                                @endforeach
                            </div>
                            @else
                            <div class="text-center py-10 border border-slate-200 rounded-xl">
                                <div class="text-4xl mb-3">🔍</div>
                                <p class="text-sm text-slate-500">
                                    {{ empty($teacherSearch) ? 'Tất cả GLV đã được phân công' : 'Không tìm thấy kết quả' }}
                                </p>
                            </div>
                            @endif

                            @error('selectedTeacherId')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Submit --}}
                        <div class="flex justify-end pt-2">
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

        </div>{{-- end grid --}}
    </div>
</div>