@section('topbar')
<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => route('dashboard')],
    ['label' => 'Học sinh', 'url' => route('students.index')],
    ['label' => $student['full_name_with_saint'] ?? 'Chi tiết'],
]" />
@endsection

<div class="min-h-screen bg-slate-50 p-2 sm:p-4 lg:p-6"
    style="min-height: calc(100vh - 56px - var(--bottom-offset));">
    <a href="#student-profile-main" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="student-profile-main" class="mx-auto max-w-7xl space-y-6">

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

            {{-- Profile summary --}}
            <div class="p-4 lg:p-6 border-b border-slate-200">
                <div class="flex flex-col sm:flex-row gap-4 sm:items-start justify-between">

                    <div class="flex items-start gap-4 flex-1 min-w-0">
                        @if($student['avatar_path'])
                        <img src="{{ asset($student['avatar_path']) }}"
                            alt="{{ $student['full_name'] }}"
                            class="w-20 h-20 rounded-2xl object-cover shadow-sm ring-4 ring-primary-50 flex-shrink-0">
                        @else
                        <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700
                            text-white flex items-center justify-center text-2xl font-bold
                            shadow-sm ring-4 ring-primary-50 flex-shrink-0">
                            {{ mb_substr($student['full_name'], 0, 1, 'UTF-8') }}
                        </div>
                        @endif

                        <div class="flex-1 min-w-0">
                            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 truncate">
                                {{ $student['full_name_with_saint'] }}
                            </h1>
                            <div class="flex flex-wrap items-center gap-2 sm:gap-3 text-sm mt-1">
                                <span class="text-slate-600">
                                    Mã HS:
                                    <span class="font-mono font-semibold text-slate-900">{{ $student['student_code'] }}</span>
                                </span>

                                <span class="hidden sm:inline text-slate-300">|</span>

                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $student['status_badge_class'] }}">
                                    {{ $student['status_label'] }}
                                </span>

                                @if($student['current_class'])
                                <span class="hidden sm:inline text-slate-300">|</span>
                                <span class="text-slate-600">
                                    Lớp:
                                    <span class="font-semibold text-slate-900">{{ $student['current_class'] }}</span>
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 flex-shrink-0">
                        <x-button wire:click="edit" variant="primary">
                            <x-icon name="edit" />
                            Chỉnh sửa
                        </x-button>
                        <x-button variant="danger" confirm="Bạn có chắc muốn xóa học sinh này không?" wire="deleteStudent">
                            <x-icon name="trash" />
                            Xóa
                        </x-button>
                    </div>
                </div>
            </div>

            {{-- Tabs --}}
            <div class="px-4 lg:px-6 py-4 border-b border-slate-200 bg-slate-50/70">
                <div class="inline-flex w-full sm:w-auto max-w-full rounded-xl bg-slate-200 p-1 text-sm font-medium">
                    <button wire:click="switchTab('basic')"
                        type="button"
                        class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg transition-all whitespace-nowrap
                            {{ $activeTab === 'basic'
                                ? 'bg-white shadow-sm text-primary-600 font-semibold'
                                : 'text-slate-600 hover:text-primary-600 hover:bg-white/50' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Thông tin cơ bản
                    </button>

                    <button wire:click="switchTab('history')"
                        type="button"
                        class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg transition-all whitespace-nowrap
                            {{ $activeTab === 'history'
                                ? 'bg-white shadow-sm text-primary-600 font-semibold'
                                : 'text-slate-600 hover:text-primary-600 hover:bg-white/50' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        Lịch sử học tập
                    </button>
                </div>
            </div>

            {{-- Content --}}
            <div class="p-4 lg:p-6 space-y-6">

                @if($activeTab === 'basic')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 lg:gap-6">

                    <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200">
                        <h3 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-primary-100">
                                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </span>
                            Thông tin cá nhân
                        </h3>
                        <div class="space-y-3">
                            <x-info-row label="Tên thánh" :value="$student['saint_name']" />
                            <x-info-row label="Họ và tên" :value="$student['full_name']" />
                            <x-info-row label="Ngày sinh" :value="$student['birthday']" />
                            <x-info-row label="Giới tính" :value="$student['gender_label']" />
                        </div>
                    </div>

                    <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200">
                        <h3 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-primary-100">
                                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                            </span>
                            Gia đình
                        </h3>
                        <div class="space-y-3">
                            <x-info-row label="Họ tên bố" :value="$student['father_name']" />
                            <x-info-row label="Họ tên mẹ" :value="$student['mother_name']" />
                            <x-info-row label="Số điện thoại" :value="$student['phone']" />
                            <x-info-row label="Email" :value="$student['email']" />
                        </div>
                    </div>

                    <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200">
                        <h3 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-primary-100">
                                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </span>
                            Giáo xứ
                        </h3>
                        <div class="space-y-3">
                            <x-info-row label="Giáo xứ" :value="$student['parish']" />
                            <x-info-row label="Giáo họ" :value="$student['parish_group']" />
                        </div>
                    </div>

                    <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200">
                        <h3 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-primary-100">
                                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </span>
                            Thông tin bổ sung
                        </h3>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between py-2 border-b border-slate-200 last:border-0">
                                <span class="text-sm font-medium text-slate-600">Hồ sơ giáo dân</span>
                                @if($student['parishioner_url'])
                                <a href="{{ $student['parishioner_url'] }}"
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-sm font-semibold
                                        text-primary-700 bg-primary-50 hover:bg-primary-100 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                    Xem hồ sơ
                                </a>
                                @else
                                <span class="text-sm text-slate-400 italic">Chưa liên kết</span>
                                @endif
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-600 mb-1">Ghi chú</label>
                                <div class="text-sm text-slate-700 bg-white rounded-xl p-3 border border-slate-200 min-h-[60px]">
                                    {{ $student['note'] ?: 'Không có ghi chú' }}
                                </div>
                            </div>

                            <x-info-row label="Ngày tạo" :value="$student['created_at']" />
                            <x-info-row label="Cập nhật lần cuối" :value="$student['updated_at']" />
                        </div>
                    </div>

                </div>
                @endif

                @if($activeTab === 'history')
                <div class="max-w-3xl mx-auto w-full">
                    @if(count($student['class_history']) > 0)
                    <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200">
                        <h3 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-primary-100">
                                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </span>
                            Quá trình học tập ({{ count($student['class_history']) }} lớp)
                        </h3>

                        <div class="space-y-3">
                            @foreach($student['class_history'] as $i => $class)
                            <div class="flex items-center gap-4 p-4 bg-white rounded-xl border border-slate-200 hover:border-primary-200 transition-colors">
                                <div class="w-8 h-8 rounded-full bg-primary-100 text-primary-700
                                    flex items-center justify-center text-sm font-bold flex-shrink-0">
                                    {{ $i + 1 }}
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="font-semibold text-slate-900">{{ $class['class_name'] }}</div>
                                    <div class="text-xs text-slate-500 mt-0.5 flex flex-wrap items-center gap-2">
                                        @if($class['school_year'])
                                        <span>{{ $class['school_year'] }}</span>
                                        @endif
                                        @if($class['joined_at'])
                                        <span class="text-slate-300">•</span>
                                        <span>Tham gia: {{ $class['joined_at'] }}</span>
                                        @endif
                                    </div>
                                </div>

                                @if($i === 0)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                    bg-primary-100 text-primary-700 flex-shrink-0">
                                    Hiện tại
                                </span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @else
                    <x-stats.page-empty
                        title="Chưa có lịch sử học tập"
                        description="Học sinh chưa được ghi danh vào lớp nào"
                        tone="primary">
                        <x-slot name="icon">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </x-slot>
                    </x-stats.page-empty>
                    @endif
                </div>
                @endif

            </div>
        </div>
    </div>
</div>
