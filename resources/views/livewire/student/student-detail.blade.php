@section('topbar')
<x-breadcrumb :items="[
        ['label' => 'Trang chủ', 'url' => route('dashboard')],
        ['label' => 'Học sinh', 'url' => route('students.index')],
        ['label' => 'Chi tiết', 'url' => null],
    ]" />
@endsection

<div class="min-h-screen bg-slate-50">
    <a href="#student-profile-main" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="student-profile-main" class="mx-auto max-w-7xl p-4 sm:p-6 space-y-6">
        {{-- CARD CONTAINER --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

            {{-- HEADER --}}
            <div class="p-6 border-b border-slate-200 bg-white">
                <div class="flex flex-col sm:flex-row gap-4 sm:items-start justify-between">

                    {{-- Avatar + Info --}}
                    <div class="flex items-start gap-4 flex-1">

                        {{-- Avatar: ảnh thật hoặc chữ cái đầu --}}
                        @if($student['avatar_path'])
                        <img src="{{ asset($student['avatar_path']) }}"
                            alt="{{ $student['full_name'] }}"
                            class="w-20 h-20 rounded-2xl object-cover shadow-lg ring-4 ring-primary-50 flex-shrink-0">
                        @else
                        <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600
                            text-white flex items-center justify-center text-2xl font-bold
                            shadow-lg ring-4 ring-primary-50 flex-shrink-0">
                            {{ mb_substr($student['full_name'], 0, 1, 'UTF-8') }}
                        </div>
                        @endif

                        <div class="flex-1 min-w-0">
                            <h1 class="text-2xl font-bold text-slate-900 mb-1">
                                {{ $student['full_name'] }}
                            </h1>
                            <div class="flex flex-wrap items-center gap-3 text-sm">
                                <span class="text-slate-600">
                                    Mã HS:
                                    <span class="font-mono font-semibold text-slate-900">
                                        {{ $student['student_code'] }}
                                    </span>
                                </span>

                                <span class="text-slate-300">|</span>

                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                             {{ $student['status_badge_class'] }}">
                                    {{ $student['status_label'] }}
                                </span>

                                @if($student['current_class'])
                                <span class="text-slate-300">|</span>
                                <span class="text-slate-600">
                                    Lớp hiện tại:
                                    <span class="font-semibold text-slate-900">{{ $student['current_class'] }}</span>
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <button wire:click="edit"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl
                                bg-primary-500 text-white font-semibold
                                hover:bg-primary-600 active:scale-95 transition-all shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            <span class="hidden sm:inline">Chỉnh sửa</span>
                        </button>
                        <button x-on:click="if (confirm('Bạn có chắc muốn xóa học sinh này không?')) $wire.delete()"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl
                                bg-red-50 text-red-600 border border-red-200 font-semibold
                                hover:bg-red-100 active:scale-95 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            <span class="hidden sm:inline">Xóa</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- TABS --}}
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                <div class="inline-flex rounded-xl bg-slate-200 p-1 text-sm font-medium">
                    <button wire:click="switchTab('basic')"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg transition-all
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
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg transition-all
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

            {{-- CONTENT --}}
            <div class="p-6 space-y-6">

                {{-- ====== TAB: THÔNG TIN CƠ BẢN ====== --}}
                @if($activeTab === 'basic')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- Thông tin cá nhân --}}
                    <div class="bg-slate-50 rounded-xl p-5 border border-slate-200 hover:shadow-md transition-all">
                        <h3 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Thông tin cá nhân
                        </h3>
                        <div class="space-y-3">
                            <x-info-row label="Tên thánh" :value="$student['saint_name']" />
                            <x-info-row label="Họ và tên" :value="$student['full_name']" />
                            <x-info-row label="Ngày sinh" :value="$student['birthday']" />
                            <x-info-row label="Giới tính" :value="$student['gender_label']" />
                        </div>
                    </div>

                    {{-- Gia đình --}}
                    <div class="bg-slate-50 rounded-xl p-5 border border-slate-200 hover:shadow-md transition-all">
                        <h3 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            Gia đình
                        </h3>
                        <div class="space-y-3">
                            <x-info-row label="Họ tên bố" :value="$student['father_name']" />
                            <x-info-row label="Họ tên mẹ" :value="$student['mother_name']" />
                            <x-info-row label="Số điện thoại" :value="$student['phone']" />
                            <x-info-row label="Email" :value="$student['email']" />
                        </div>
                    </div>

                    {{-- Giáo xứ --}}
                    <div class="bg-slate-50 rounded-xl p-5 border border-slate-200 hover:shadow-md transition-all">
                        <h3 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            Giáo xứ
                        </h3>
                        <div class="space-y-3">
                            <x-info-row label="Giáo xứ" :value="$student['parish']" />
                            <x-info-row label="Giáo họ" :value="$student['parish_group']" />
                        </div>
                    </div>

                    {{-- Hồ sơ giáo dân + Ghi chú --}}
                    <div class="bg-slate-50 rounded-xl p-5 border border-slate-200 hover:shadow-md transition-all">
                        <h3 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Thông tin bổ sung
                        </h3>
                        <div class="space-y-3">

                            {{-- Link hồ sơ giáo dân --}}
                            <div class="flex items-center justify-between py-2 border-b border-slate-200 last:border-0">
                                <span class="text-sm font-medium text-slate-600">Hồ sơ giáo dân</span>
                                @if($student['parishioner_url'])
                                <a href="{{ $student['parishioner_url'] }}"
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg
                               text-sm font-semibold text-primary-600 bg-primary-50
                               hover:bg-primary-100 transition-colors">
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

                            {{-- Ghi chú --}}
                            <div>
                                <label class="block text-sm font-medium text-slate-600 mb-1">Ghi chú</label>
                                <div class="text-sm text-slate-700 bg-white rounded-lg p-3 border border-slate-200 min-h-[60px]">
                                    {{ $student['note'] ?: 'Không có ghi chú' }}
                                </div>
                            </div>

                            <x-info-row label="Ngày tạo" :value="$student['created_at']" />
                            <x-info-row label="Cập nhật lần cuối" :value="$student['updated_at']" />
                        </div>
                    </div>

                </div>
                @endif

                {{-- ====== TAB: LỊCH SỬ HỌC TẬP ====== --}}
                @if($activeTab === 'history')
                <div class="max-w-3xl mx-auto">
                    <div class="bg-slate-50 rounded-xl p-5 border border-slate-200 hover:shadow-md transition-all">
                        <h3 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            Quá trình học tập ({{ count($student['class_history']) }} lớp)
                        </h3>

                        @if(count($student['class_history']) > 0)
                        <div class="space-y-3">
                            @foreach($student['class_history'] as $i => $class)
                            <div class="flex items-center gap-4 p-4 bg-white rounded-xl border border-slate-200 hover:border-primary-200 transition-colors">

                                {{-- Index --}}
                                <div class="w-8 h-8 rounded-full bg-primary-100 text-primary-700 
                                            flex items-center justify-center text-sm font-bold flex-shrink-0">
                                    {{ $i + 1 }}
                                </div>

                                {{-- Info --}}
                                <div class="flex-1 min-w-0">
                                    <div class="font-semibold text-slate-900">
                                        {{ $class['class_name'] }}
                                        {{-- @if($class['class_symbol'])
                                        <span class="ml-1 font-mono text-xs text-slate-500">({{ $class['class_symbol'] }})</span>
                                        @endif --}}
                                    </div>
                                    <div class="text-xs text-slate-500 mt-0.5 flex items-center gap-2">
                                        @if($class['school_year'])
                                        <span>{{ $class['school_year'] }}</span>
                                        @endif
                                        @if($class['joined_at'])
                                        <span class="text-slate-300">•</span>
                                        <span>Tham gia: {{ $class['joined_at'] }}</span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Badge lớp đầu tiên (hiện tại) --}}
                                @if($i === 0)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                             bg-primary-100 text-primary-700 flex-shrink-0">
                                    Hiện tại
                                </span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="text-center py-8">
                            <svg class="mx-auto w-12 h-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <p class="mt-3 text-sm text-slate-500 italic">Chưa có lịch sử học tập</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    @media print {
        .no-print {
            display: none !important;
        }

        body {
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }
    }
</style>
@endpush

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('print-profile', () => {
            window.print();
        });
    });
</script>
@endpush