<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="mx-auto max-w-7xl space-y-5">

        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[
            ['label' => 'Trang chủ', 'url' => route('dashboard')],
            ['label' => 'Quản lý lớp học', 'url' => route('classes.index')],
            ['label' => 'Phân công Giáo lý viên'],
        ]" separator="arrow" />

        {{-- Toast Notifications --}}
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

        {{-- Page Header --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <x-page-header
                title="Phân công Giáo lý viên"
                :description="'Lớp: ' . $class->name . ' — ' . ($class->schoolYear->name ?? 'N/A') . ' — Khối: ' . ($class->gradeLevel->name ?? 'N/A')"
                :stat-value="$currentTeachers->count()"
                stat-label="GLV phụ trách"
                icon-type="teacher" />
        </div>

        {{-- 2 Columns --}}
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-5">

            {{-- ═══ CỘT TRÁI: GLV hiện tại (2/5) ═══ --}}
            <div class="lg:col-span-2 space-y-4">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

                    {{-- Header --}}
                    <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/70">
                        <h3 class="text-base font-bold text-slate-900">GLV đang phụ trách</h3>
                        <p class="text-sm text-slate-500 mt-0.5">
                            {{ $currentTeachers->count() }} giáo lý viên
                        </p>
                    </div>

                    {{-- List --}}
                    <div class="p-4">
                        @if($currentTeachers->isNotEmpty())
                        <div class="space-y-3">
                            @foreach($currentTeachers as $ct)
                            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl hover:bg-slate-100 transition"
                                wire:key="ct-{{ $ct['id'] }}">

                                {{-- Avatar + Info --}}
                                <div class="flex items-center gap-3 flex-1 min-w-0">
                                    <div class="w-10 h-10 rounded-full flex-shrink-0
                                                flex items-center justify-center font-bold text-sm
                                                {{ $ct['role'] === 1 ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                                        {{ mb_strtoupper(mb_substr($ct['first_name'], 0, 1)) }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <p class="font-semibold text-slate-900 text-sm truncate">
                                                {{ $ct['teacher_name'] }}
                                            </p>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                                                         {{ $ct['role'] === 1 ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                                                {{ $ct['role_label'] }}
                                            </span>
                                        </div>
                                        @if($ct['phone'])
                                        <p class="text-xs text-slate-500 mt-0.5">📞 {{ $ct['phone'] }}</p>
                                        @endif
                                    </div>
                                </div>

                                {{-- Actions --}}
                                <div class="flex items-center gap-1 flex-shrink-0 ml-2">

                                    {{-- Đổi role --}}
                                    <div x-data="{ open: false }" class="relative">
                                        <button
                                            wire:click="changeRole({{ $ct['id'] }}, {{ $ct['role'] === 1 ? 2 : 1 }})"
                                            @mouseenter="open = true"
                                            @mouseleave="open = false"
                                            wire:loading.attr="disabled"
                                            class="p-1.5 text-blue-500 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                            </svg>
                                        </button>
                                        <div x-show="open" x-transition x-cloak
                                            class="absolute bottom-full right-0 mb-2 px-2.5 py-1.5
                                                   bg-slate-800 text-white text-xs font-medium
                                                   rounded-lg whitespace-nowrap shadow-lg z-20">
                                            {{ $ct['role'] === 1 ? 'Đổi → Phụ trách' : 'Đổi → Chủ nhiệm' }}
                                            <div class="absolute top-full right-3 border-4 border-transparent border-t-slate-800"></div>
                                        </div>
                                    </div>

                                    {{-- Xóa --}}
                                    <div x-data="{ open: false }" class="relative">
                                        <button
                                            @mouseenter="open = true"
                                            @mouseleave="open = false"
                                            @click="if(confirm('Xóa {{ $ct['teacher_name'] }} khỏi lớp?')) $wire.remove({{ $ct['id'] }})"
                                            class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                        <div x-show="open" x-transition x-cloak
                                            class="absolute bottom-full right-0 mb-2 px-2.5 py-1.5
                                                   bg-slate-800 text-white text-xs font-medium
                                                   rounded-lg whitespace-nowrap shadow-lg z-20">
                                            Xóa khỏi lớp
                                            <div class="absolute top-full right-3 border-4 border-transparent border-t-slate-800"></div>
                                        </div>
                                    </div>
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
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

                    {{-- Header --}}
                    <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/70">
                        <h3 class="text-base font-bold text-slate-900">Thêm Giáo lý viên</h3>
                        <p class="text-sm text-slate-500 mt-0.5">Tìm và phân công GLV cho lớp này</p>
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
                                               ? 'border-blue-500 bg-blue-50'
                                               : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50' }}">
                                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0
                                                {{ $selectedRole === 1 ? 'border-blue-500 bg-blue-500' : 'border-slate-300' }}">
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
                                               ? 'border-purple-500 bg-purple-50'
                                               : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50' }}">
                                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0
                                                {{ $selectedRole === 2 ? 'border-purple-500 bg-purple-500' : 'border-slate-300' }}">
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
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Tìm kiếm Giáo lý viên
                            </label>
                            <div class="relative">
                                <input
                                    type="text"
                                    wire:model.debounce.300ms="teacherSearch"
                                    placeholder="Nhập tên hoặc số điện thoại..."
                                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300
                                           focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm">
                                <svg class="absolute left-3 top-3 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
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
                                        class="w-4 h-4 text-primary-600 focus:ring-primary-500 flex-shrink-0">

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
                                            📞 {{ $teacher->phone_number }}
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