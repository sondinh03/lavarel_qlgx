@section('topbar')
<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => auth()->user()->usesCatechistLayout() ? route('catechist.dashboard') : route('parish-admin.dashboard')],
    ['label' => 'Học sinh', 'url' => route('students.index')],
    ['label' => $student['full_name_with_saint'] ?? 'Chi tiết'],
]" />
@endsection

<div class="min-h-screen bg-apple-gray p-2 sm:p-4 lg:p-6"
    style="min-height: calc(100vh - 56px - var(--bottom-offset));">
    <a href="#student-profile-main" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="student-profile-main" class="mx-auto max-w-7xl">
        <x-mac-panel :overflow="true">

            {{-- Profile summary --}}
            <div class="p-4 lg:p-6 mac-hairline-b bg-white/40">
                <div class="flex flex-col sm:flex-row gap-4 sm:items-start justify-between">

                    <div class="flex items-start gap-4 flex-1 min-w-0">
                        @if($student['avatar_path'])
                        <img src="{{ media_url($student['avatar_path']) }}"
                            alt="{{ $student['full_name'] }}"
                            class="w-20 h-20 rounded-2xl object-cover shadow-mac-sm ring-4 ring-primary-50/80 flex-shrink-0">
                        @else
                        <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700
                            text-white flex items-center justify-center text-2xl font-semibold
                            shadow-mac-sm ring-4 ring-primary-50/80 flex-shrink-0">
                            {{ mb_substr($student['full_name'], 0, 1, 'UTF-8') }}
                        </div>
                        @endif

                        <div class="flex-1 min-w-0">
                            <h1 class="text-[22px] font-semibold tracking-tight text-slate-900 truncate">
                                {{ $student['full_name_with_saint'] }}
                            </h1>
                            <div class="flex flex-wrap items-center gap-2 sm:gap-3 text-sm mt-1.5">
                                <span class="text-slate-500">
                                    Mã HS:
                                    <span class="font-mono font-semibold text-primary-600">{{ $student['student_code'] }}</span>
                                </span>

                                <span class="hidden sm:inline text-black/10">|</span>

                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-semibold {{ $student['status_badge_class'] }}">
                                    {{ $student['status_label'] }}
                                </span>

                                @if($student['current_class'])
                                <span class="hidden sm:inline text-black/10">|</span>
                                <span class="text-slate-500">
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

            {{-- Body: main info + sidebar --}}
            <div class="p-4 lg:p-6">
                <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 lg:gap-8">

                    {{-- Main column --}}
                    <div class="lg:col-span-3 space-y-6">

                        <section>
                            <h2 class="text-xs font-semibold text-slate-500 tracking-wide uppercase mb-1 px-1">
                                Thông tin cá nhân
                            </h2>
                            <div class="divide-y divide-black/[0.04] rounded-xl bg-white/40 border border-black/[0.04]">
                                <x-info-row label="Tên thánh" :value="$student['saint_name']" />
                                <x-info-row label="Họ và tên" :value="$student['full_name']" />
                                <x-info-row label="Ngày sinh" :value="$student['birthday']" />
                                <x-info-row label="Giới tính" :value="$student['gender_label']" />
                            </div>
                        </section>

                        <section>
                            <h2 class="text-xs font-semibold text-slate-500 tracking-wide uppercase mb-1 px-1">
                                Gia đình & liên hệ
                            </h2>
                            <div class="divide-y divide-black/[0.04] rounded-xl bg-white/40 border border-black/[0.04]">
                                <x-info-row label="Họ tên bố" :value="$student['father_name']" />
                                <x-info-row label="Họ tên mẹ" :value="$student['mother_name']" />
                                <x-info-row label="Số điện thoại" :value="$student['phone']" />
                                <x-info-row label="Email" :value="$student['email']" />
                            </div>
                        </section>

                        <section>
                            <h2 class="text-xs font-semibold text-slate-500 tracking-wide uppercase mb-1 px-1">
                                Giáo xứ
                            </h2>
                            <div class="divide-y divide-black/[0.04] rounded-xl bg-white/40 border border-black/[0.04]">
                                <x-info-row label="Giáo xứ" :value="$student['parish']" />
                                <x-info-row label="Giáo họ" :value="$student['parish_group']" />
                            </div>
                        </section>

                    </div>

                    {{-- Side column --}}
                    <div class="lg:col-span-2 space-y-6">

                        <section>
                            <h2 class="text-xs font-semibold text-slate-500 tracking-wide uppercase mb-2 px-1">
                                Lịch sử học tập
                                @if(count($student['class_history']) > 0)
                                <span class="font-normal normal-case tracking-normal text-slate-400">
                                    ({{ count($student['class_history']) }})
                                </span>
                                @endif
                            </h2>

                            @if(count($student['class_history']) > 0)
                            <div class="space-y-2">
                                @foreach($student['class_history'] as $i => $class)
                                <div class="flex items-start gap-3 p-3 rounded-xl bg-white/40 border border-black/[0.04]">
                                    <div class="w-7 h-7 rounded-full bg-primary-50/80 text-primary-700
                                        flex items-center justify-center text-xs font-semibold flex-shrink-0 mt-0.5">
                                        {{ $i + 1 }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="font-semibold text-sm text-slate-900 truncate">{{ $class['class_name'] }}</div>
                                            @if($i === 0)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold
                                                bg-primary-50/80 text-primary-700 flex-shrink-0">
                                                Hiện tại
                                            </span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-slate-500 mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-0.5">
                                            @if($class['school_year'])
                                            <span>{{ $class['school_year'] }}</span>
                                            @endif
                                            @if($class['joined_at'])
                                            <span>Tham gia {{ $class['joined_at'] }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <div class="rounded-xl bg-white/40 border border-black/[0.04] px-4 py-8 text-center">
                                <p class="text-sm font-medium text-slate-500">Chưa có lịch sử học tập</p>
                                <p class="text-xs text-slate-400 mt-1">Học sinh chưa được ghi danh vào lớp nào</p>
                            </div>
                            @endif
                        </section>

                        <section>
                            <h2 class="text-xs font-semibold text-slate-500 tracking-wide uppercase mb-2 px-1">
                                Ghi chú
                            </h2>
                            <div class="text-sm text-slate-700 rounded-xl bg-white/40 border border-black/[0.04] p-3 min-h-[72px] leading-relaxed">
                                {{ $student['note'] ?: 'Không có ghi chú' }}
                            </div>
                        </section>

                        <section>
                            <h2 class="text-xs font-semibold text-slate-500 tracking-wide uppercase mb-2 px-1">
                                Hồ sơ giáo dân
                            </h2>
                            <div class="rounded-xl bg-white/40 border border-black/[0.04] px-4 py-3">
                                @if($student['parishioner_url'])
                                <a href="{{ $student['parishioner_url'] }}"
                                    class="inline-flex items-center gap-1.5 text-sm font-semibold text-primary-600 hover:text-primary-700 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                    Xem hồ sơ giáo dân
                                </a>
                                @else
                                <span class="text-sm text-slate-400 italic">Chưa liên kết</span>
                                @endif
                            </div>
                        </section>

                        <div class="px-1 space-y-1 text-xs text-slate-400">
                            <p>Tạo: {{ $student['created_at'] ?: '—' }}</p>
                            <p>Cập nhật: {{ $student['updated_at'] ?: '—' }}</p>
                        </div>

                    </div>
                </div>
            </div>
        </x-mac-panel>
    </div>
</div>
