@section('topbar')
@php
    $isCatechist = auth()->user()?->usesCatechistLayout() ?? false;
    $homeUrl = $isCatechist
        ? route('catechist.dashboard')
        : route('parish-admin.dashboard');
@endphp
<x-breadcrumb :items="[
        ['label' => 'Trang chủ', 'url' => $homeUrl],
        ['label' => 'Kết quả học tập'],
    ]" />
@endsection

@php $isCatechist = auth()->user()?->usesCatechistLayout() ?? false; @endphp

<div class="min-h-screen bg-apple-gray p-2 sm:p-4 lg:p-6" style="min-height: calc(100vh - 56px - var(--bottom-offset));">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="mx-auto max-w-7xl">
        <x-mac-panel :overflow="true">
            <x-page-header
                title="Kết quả học tập"
                :description="$isCatechist
                    ? (($canBrowseAllScoreClasses ?? false)
                        ? ($canEditScores ? 'Nhập điểm toàn giáo xứ khi cửa sổ đang mở' : 'Xem điểm toàn giáo xứ')
                        : 'Xem điểm theo lớp được phân công')
                    : ($canEditScores ? 'Nhập và quản lý điểm học sinh theo lớp và học kỳ' : 'Xem điểm học sinh theo lớp và học kỳ')"
                icon-type="score" />

            @if($canManageScoreConfig || ! $canEditScores)
            <div class="mx-4 lg:mx-6 mt-4 px-4 py-3 rounded-xl border shadow-mac-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3
                {{ $scoresEntryOpen
                    ? 'border-emerald-200/80 bg-emerald-50/80 text-emerald-900'
                    : 'border-amber-200/80 bg-amber-50/80 text-amber-900' }}">
                <div class="min-w-0">
                    <p class="text-sm font-semibold">
                        {{ $scoresEntryOpen ? 'Đang mở nhập/sửa điểm' : 'Đang khóa nhập/sửa điểm' }}
                    </p>
                    <p class="text-xs mt-0.5 opacity-80">
                        @if($canManageScoreConfig)
                            Giáo lý viên {{ $scoresEntryOpen ? 'có thể' : 'không thể' }} sửa điểm khi cửa sổ này
                            {{ $scoresEntryOpen ? 'đang mở' : 'đang khóa' }}.
                            Ban quản trị luôn được sửa.
                            <a href="{{ route('help.scores') }}" class="font-semibold underline hover:opacity-100 ml-1">Hướng dẫn nhập điểm →</a>
                        @else
                            Ban giáo lý chưa mở cửa sổ nhập điểm. Bạn chỉ có thể xem.
                        @endif
                    </p>
                </div>
                @if($canManageScoreConfig)
                <div class="flex items-center gap-2 flex-shrink-0">
                    <x-button as="a" href="{{ route('scores.edit-logs') }}" variant="outline" size="sm">
                        Nhật ký sửa
                    </x-button>
                    <x-button wire:click="toggleScoresEntryOpen" variant="{{ $scoresEntryOpen ? 'outline' : 'primary' }}" size="sm">
                        {{ $scoresEntryOpen ? 'Khóa nhập điểm' : 'Mở nhập điểm' }}
                    </x-button>
                </div>
                @endif
            </div>
            @endif

            <div class="p-4 lg:p-6 mac-hairline-b bg-white/30">
                @if($isCatechist && !($canBrowseAllScoreClasses ?? false))
                <div class="flex flex-col gap-4">
                    <livewire:filters.filter-bar
                        :parish-id="$parishId"
                        :show-nam-hoc="false"
                        :show-khoi="false"
                        :show-lop="true"
                        :show-ky="true"
                        :selected-nam-hoc="$selectedNamHoc"
                        :selected-khoi="$selectedKhoi"
                        :selected-lop="$selectedLop"
                        :selected-ky="$selectedSemester"
                        :allowed-class-ids="$scoreFilterAllowedClassIds ?? []" />

                    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                        <x-search-input
                            wire-model="search"
                            placeholder="Tìm học sinh..."
                            debounce="400ms"
                            class="max-w-md flex-1" />
                    </div>
                </div>
                @else
                <div class="flex flex-col gap-4">
                    <div class="flex-1 min-w-0">
                        <livewire:filters.filter-bar
                            :parish-id="$parishId"
                            :show-nam-hoc="true"
                            :show-khoi="true"
                            :show-lop="true"
                            :show-ky="true"
                            :selected-nam-hoc="$selectedNamHoc"
                            :selected-khoi="$selectedKhoi"
                            :selected-lop="$selectedLop"
                            :selected-ky="$selectedSemester" />
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <x-search-input
                            wire-model="search"
                            placeholder="Tìm học sinh..."
                            debounce="400ms"
                            class="max-w-md" />

                        <div class="flex items-center gap-2 flex-wrap justify-end">
                            @if($activeTab === 'scores')
                            @if($canManageScoreConfig)
                            <x-button
                                as="a"
                                href="{{ route('scores.statistics', ['namHoc' => $selectedNamHoc, 'khoi' => $selectedKhoi, 'lop' => $selectedLop, 'semester' => $selectedSemester]) }}"
                                variant="outline">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                Thống kê
                            </x-button>
                            @endif
                            @if($canEditScores)
                            <x-button wire:click="saveAllScores" variant="primary">
                                <x-icon name="save" />
                                Lưu
                            </x-button>
                            @endif
                            @if($canManageScoreConfig)
                            <x-button wire:click="exportScores" variant="outline">
                                <x-icon name="file-export" />
                                Xuất Excel
                            </x-button>
                            @endif
                            @endif
                            @if($activeTab === 'config' && $canManageScoreConfig)
                            <x-button wire:click="createScoreType" variant="primary">
                                <x-icon name="plus" />
                                Thêm loại điểm
                            </x-button>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            </div>

            @if($canManageScoreConfig)
            <div class="px-4 lg:px-6 py-3 mac-hairline-b bg-white/20">
                <div class="flex gap-1 p-1 rounded-xl bg-black/[0.04] border border-black/[0.04] w-fit">
                    <button
                        wire:click="switchTab('scores')"
                        type="button"
                        class="px-4 py-1.5 text-sm font-semibold rounded-lg transition-all
                               {{ $activeTab === 'scores'
                                   ? 'bg-white/90 text-primary-600 shadow-mac-sm'
                                   : 'text-slate-600 hover:text-slate-900' }}">
                        Bảng điểm
                    </button>
                    <button
                        wire:click="switchTab('config')"
                        type="button"
                        class="px-4 py-1.5 text-sm font-semibold rounded-lg transition-all
                               {{ $activeTab === 'config'
                                   ? 'bg-white/90 text-primary-600 shadow-mac-sm'
                                   : 'text-slate-600 hover:text-slate-900' }}">
                        Cấu hình loại điểm
                    </button>
                </div>
            </div>
            @endif

        @if($activeTab === 'scores')
        @if(!$selectedLop)
        <x-stats.page-empty
            :panel="false"
            tone="primary"
            title="Vui lòng chọn lớp"
            description="Chọn lớp ở bộ lọc phía trên để xem bảng điểm">
            <x-slot name="icon">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
            </x-slot>
        </x-stats.page-empty>

        @elseif($scoreTypes->isEmpty())
        <x-stats.page-empty
            :panel="false"
            tone="primary"
            title="Lớp chưa có cấu hình loại điểm"
            description="{{ $canManageScoreConfig
                ? 'Thêm loại điểm trước khi nhập điểm cho học sinh'
                : 'Liên hệ ban giáo lý để cấu hình loại điểm' }}">
            @if($canManageScoreConfig)
            <x-button wire:click="switchTab('config')" variant="primary">
                Cấu hình ngay
            </x-button>
            @endif
        </x-stats.page-empty>

        @else

        {{-- Confirm discard draft --}}
        <div
            x-data="{ show: false, action: '', value: '' }"
            x-on:confirm-discard-draft.window="show = true; action = $event.detail.action; value = $event.detail.value">
            <div x-show="show" x-cloak
                class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-[60] p-4"
                role="dialog" aria-modal="true"
                @click.self="show = false"
                @keydown.escape.window="show = false">
                <div class="bg-white/90 backdrop-blur-xl rounded-2xl border border-black/[0.06] shadow-mac
                    w-full max-w-sm p-6 space-y-4" @click.stop>
                    <h3 class="text-base font-semibold tracking-tight text-slate-900">Bạn có thay đổi chưa lưu</h3>
                    <p class="text-sm text-slate-500">
                        Nếu tiếp tục, điểm đã nhập nhưng chưa lưu sẽ bị mất.
                    </p>
                    <div class="flex gap-3 pt-1">
                        <x-button type="button" variant="outline" class="flex-1" @click="show = false">
                            Ở lại
                        </x-button>
                        <x-button type="button" variant="danger" class="flex-1"
                            @click="show = false; $wire.confirmDiscard(action, value)">
                            Bỏ thay đổi
                        </x-button>
                    </div>
                </div>
            </div>
        </div>

        @if($isCatechist)
        {{-- ══ CATECHIST: minimal card list ══ --}}
        <div class="p-4 space-y-3" wire:key="score-cards-{{ $selectedLop }}-{{ $selectedSemester }}">
            @forelse($students as $index => $sc)
            @php
                $avg = $this->getAverage($sc->pivot_id);
                $ratingLabel = null;
                $ratingColor = null;
                if ($avg !== null) {
                    if ($avg >= 9.5)      { $ratingLabel = 'Xuất sắc';   $ratingColor = 'emerald'; }
                    elseif ($avg >= 8.0)  { $ratingLabel = 'Giỏi';       $ratingColor = 'blue'; }
                    elseif ($avg >= 6.5)  { $ratingLabel = 'Khá';        $ratingColor = 'amber'; }
                    elseif ($avg >= 5.0)  { $ratingLabel = 'Trung bình'; $ratingColor = 'yellow'; }
                    elseif ($avg >= 3.5)  { $ratingLabel = 'Yếu';        $ratingColor = 'orange'; }
                    else                  { $ratingLabel = 'Kém';        $ratingColor = 'red'; }
                }
                $badgeClass = match($ratingColor) {
                    'emerald' => 'bg-emerald-50/80 text-emerald-700',
                    'blue'    => 'bg-blue-50/80 text-blue-700',
                    'amber'   => 'bg-amber-50/80 text-amber-700',
                    'yellow'  => 'bg-yellow-50/80 text-yellow-700',
                    'orange'  => 'bg-orange-50/80 text-orange-700',
                    'red'     => 'bg-red-50/80 text-red-700',
                    default   => 'bg-slate-50/80 text-slate-400',
                };
                $initials = strtoupper(
                    mb_substr($sc->last_name ?? '', 0, 1) . mb_substr($sc->first_name ?? '', 0, 1)
                );
            @endphp
            <button type="button"
                wire:key="score-card-{{ $sc->pivot_id }}"
                wire:click="openStudentScoreDetail({{ $sc->pivot_id }})"
                class="w-full text-left bg-white/70 rounded-xl border border-black/[0.06] p-4
                      flex items-center gap-3 hover:border-primary-300/50
                      hover:bg-black/[0.02] transition-all active:scale-[0.99]">

                <div class="w-11 h-11 rounded-full flex-shrink-0 flex items-center justify-center
                    text-sm font-semibold bg-primary-50/80 text-primary-800">
                    @if(!empty($sc->avatar_path))
                    <img src="{{ $sc->avatar_url }}" class="w-full h-full rounded-full object-cover" alt="" />
                    @else
                    {{ $initials }}
                    @endif
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-0.5">
                        <span class="text-sm font-medium text-slate-900 truncate">
                            {{ $sc->saint->name ?? '' }} {{ $sc->last_name }} {{ $sc->first_name }}
                        </span>
                    </div>
                    @if($ratingLabel)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $badgeClass }}">
                        {{ $ratingLabel }}
                    </span>
                    @else
                    <span class="text-xs text-slate-400">Chưa có điểm tổng kết</span>
                    @endif
                </div>

                <div class="flex-shrink-0 flex items-center gap-2">
                    @if($avg !== null)
                    <span class="text-base font-bold tabular-nums
                        {{ $avg >= 8 ? 'text-emerald-600' : ($avg >= 5 ? 'text-primary-600' : 'text-red-500') }}">
                        {{ number_format($avg, 1) }}
                    </span>
                    @else
                    <span class="text-xs font-semibold text-slate-400 tabular-nums">TB —</span>
                    @endif
                    <span class="text-[11px] font-semibold text-primary-600 bg-primary-50/90
                        ring-1 ring-primary-100/70 rounded-lg px-2 py-1">
                        Chi tiết
                    </span>
                </div>
            </button>
            @empty
            <x-stats.page-empty
                :panel="false"
                title="Chưa có học sinh trong lớp này"
                description="Chọn lớp khác hoặc liên hệ ban giáo lý">
                <x-slot name="icon">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </x-slot>
            </x-stats.page-empty>
            @endforelse

            @if($students->hasPages())
            <div class="pt-2">
                <x-pagination :paginator="$students" :per-page-options="[10, 15, 25, 50]" />
            </div>
            @endif
        </div>

        @else
        {{-- ══ ADMIN: Table ══ --}}
        <div class="max-h-[70vh] overflow-y-auto">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50/50 sticky top-0 z-10 mac-hairline-b">
                            <tr>
                                <x-table-header>STT</x-table-header>
                                <x-table-header>Tên thánh</x-table-header>
                                <x-table-header>Họ & tên đệm</x-table-header>
                                <x-table-header
                                    :sortable="true" sort-field="first_name"
                                    :current-sort="$sortField" :sort-direction="$sortDirection">
                                    Tên
                                </x-table-header>

                                @foreach($scoreTypes as $type)
                                <x-table-header align="center" class="min-w-[90px]">
                                    <x-tooltip content="Hệ số: {{ $type->coefficient }}">
                                        <span>{{ $type->name }}</span>
                                    </x-tooltip>
                                </x-table-header>
                                @endforeach

                                <x-table-header
                                    :sortable="true" sort-field="avg"
                                    :current-sort="$sortField" :sort-direction="$sortDirection"
                                    align="center"
                                    class="bg-primary-50/80 text-primary-700 font-bold">
                                    Điểm<br>trung bình
                                </x-table-header>

                                <x-table-header align="center" class="bg-primary-50/80 text-primary-700 font-bold">
                                    Xếp loại
                                </x-table-header>
                            </tr>
                        </thead>

                    <tbody class="divide-y divide-black/[0.04]">
                        @forelse($students as $index => $sc)
                            @php
                                $avg    = $this->getAverage($sc->pivot_id);
                                $rating = null;
                                $ratingLabel = null;
                                $ratingColor = null;
                                if ($avg !== null) {
                                    if ($avg >= 9.5)      { $rating = 'XUAT_SAC';   $ratingLabel = 'Xuất sắc';   $ratingColor = 'emerald'; }
                                    elseif ($avg >= 8.0)  { $rating = 'GIOI';       $ratingLabel = 'Giỏi';       $ratingColor = 'blue'; }
                                    elseif ($avg >= 6.5)  { $rating = 'KHA';        $ratingLabel = 'Khá';        $ratingColor = 'amber'; }
                                    elseif ($avg >= 5.0)  { $rating = 'TRUNG_BINH'; $ratingLabel = 'Trung bình'; $ratingColor = 'yellow'; }
                                    elseif ($avg >= 3.5)  { $rating = 'YEU';        $ratingLabel = 'Yếu';        $ratingColor = 'orange'; }
                                    else                  { $rating = 'KEM';        $ratingLabel = 'Kém';        $ratingColor = 'red'; }
                                }
                                $badgeClass = match($ratingColor) {
                                    'emerald' => 'bg-emerald-50/80 text-emerald-700',
                                    'blue'    => 'bg-blue-50/80 text-blue-700',
                                    'amber'   => 'bg-amber-50/80 text-amber-700',
                                    'yellow'  => 'bg-yellow-50/80 text-yellow-700',
                                    'orange'  => 'bg-orange-50/80 text-orange-700',
                                    'red'     => 'bg-red-50/80 text-red-700',
                                    default   => 'bg-slate-50/80 text-slate-400',
                                };
                            @endphp
                            <tr class="hover:bg-black/[0.03] transition-colors" wire:key="sc-{{ $sc->pivot_id }}">

                                <td class="px-4 py-3 text-slate-400 sticky left-0 bg-white/95 backdrop-blur-sm">
                                    {{ ($students->firstItem() ?? 0) + $index }}
                                </td>

                                <td class="px-4 py-3 text-sm text-slate-900">
                                    {{ $sc->saint->name ?? '—' }}
                                </td>

                                <td class="px-4 py-3 text-sm font-semibold text-slate-900 whitespace-nowrap">
                                    {{ $sc->last_name }}
                                </td>

                                <td class="px-4 py-3 text-sm font-semibold text-slate-900">
                                    {{ $sc->first_name }}
                                </td>

                                @foreach($scoreTypes as $colIndex => $type)
                                <td class="px-3 py-2 text-center">
                                    @if($canEditScores)
                                    <input
                                        type="text"
                                        inputmode="decimal"
                                        step="0.5"
                                        min="0"
                                        max="{{ $type->max_score }}"
                                        wire:model.defer="draftScores.{{ $sc->pivot_id }}.{{ $type->id }}"
                                        data-row="{{ $index }}"
                                        data-col="{{ $colIndex }}"
                                        class="score-input w-14 py-1.5 px-2 text-center rounded-lg text-sm font-semibold
                                               border shadow-mac-sm transition-all outline-none placeholder:text-slate-300
                                               focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40
                                               [appearance:textfield]
                                               [&::-webkit-outer-spin-button]:appearance-none
                                               [&::-webkit-inner-spin-button]:appearance-none
                                               {{ isset($scoresMatrix[$sc->pivot_id][$type->id])
                                                   ? 'border-emerald-200/80 bg-emerald-50/80 text-emerald-700'
                                                   : 'border-black/[0.06] bg-white/80 text-slate-600' }}" />
                                    @else
                                    @php
                                        $cell = $scoresMatrix[$sc->pivot_id][$type->id]['value'] ?? null;
                                    @endphp
                                    <span @class([
                                        'inline-flex min-w-[2.5rem] justify-center px-2 py-1 rounded-lg text-sm font-semibold',
                                        'bg-emerald-50/80 text-emerald-700' => $cell !== null,
                                        'text-slate-300' => $cell === null,
                                    ])>
                                        {{ $cell !== null ? number_format((float) $cell, 1) : '—' }}
                                    </span>
                                    @endif
                                </td>
                                @endforeach

                                {{-- Điểm TB --}}
                                <td class="px-4 py-3 text-center bg-primary-50/50">
                                    @if($avg !== null)
                                    <span class="font-bold text-lg tracking-tight
                                         {{ $avg >= 8 ? 'text-emerald-600' : ($avg >= 5 ? 'text-primary-600' : 'text-red-500') }}">
                                        {{ number_format($avg, 1) }}
                                    </span>
                                    @else
                                    <span class="text-slate-300 text-lg font-bold">—</span>
                                    @endif
                                </td>

                                {{-- Xếp loại --}}
                                <td class="px-4 py-3 text-center bg-primary-50/50">
                                    @if($ratingLabel)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-semibold {{ $badgeClass }}">
                                        {{ $ratingLabel }}
                                    </span>
                                    @else
                                    <span class="text-slate-300 text-xs">—</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ 5 + $scoreTypes->count() }}"
                                    class="px-6 py-12 text-center text-slate-400">
                                    Chưa có học sinh trong lớp này
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($students->hasPages())
            <div class="mac-hairline-t">
                <x-pagination :paginator="$students" :per-page-options="[10, 15, 25, 50, 100]" />
            </div>
            @endif
        @endif

        @endif
        @endif

        @if($activeTab === 'config')
        @if(!$selectedNamHoc)
        <x-stats.page-empty
            :panel="false"
            tone="slate"
            title="Vui lòng chọn năm học"
            description="Chọn năm học để cấu hình loại điểm" />
        @else
        @if(!$selectedLop)
        <div class="mx-4 lg:mx-6 my-4 px-4 py-3 rounded-xl border border-amber-200/80 bg-amber-50/80 text-sm text-amber-800 shadow-mac-sm">
            Chưa chọn lớp cụ thể — loại điểm sẽ được tạo theo <strong>khối</strong> hoặc <strong>toàn xứ</strong>.
            Chọn lớp ở trên nếu muốn cấu hình riêng từng lớp.
        </div>
        @endif

        @if($scoreTypes->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50/50 mac-hairline-b">
                    <tr>
                        <x-table-header>Tên loại điểm</x-table-header>
                        <x-table-header>Loại</x-table-header>
                        <x-table-header class="text-center">Thứ tự</x-table-header>
                        <x-table-header class="text-center">Hệ số</x-table-header>
                        <x-table-header class="text-center">Điểm tối đa</x-table-header>
                        <x-table-header class="text-center">Trạng thái</x-table-header>
                        <x-table-header class="text-center">Thao tác</x-table-header>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04]">
                    @foreach($scoreTypes as $st)
                    <tr class="hover:bg-black/[0.03] transition-colors" wire:key="st-{{ $st->id }}">
                        <td class="px-4 py-3 font-semibold text-slate-900">{{ $st->name }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-semibold bg-indigo-50/80 text-indigo-700">
                                {{ $st->type_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-slate-600">{{ $st->order }}</td>
                        <td class="px-4 py-3 text-center font-semibold text-slate-700">{{ $st->coefficient }}</td>
                        <td class="px-4 py-3 text-center text-slate-600">{{ $st->max_score }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold rounded-lg
                                {{ $st->is_active ? 'bg-emerald-50/80 text-emerald-700' : 'bg-slate-100/80 text-slate-500' }}">
                                {{ $st->is_active ? 'Đang dùng' : 'Tắt' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="inline-flex items-center gap-3">
                                <x-tooltip content="Chỉnh sửa">
                                    <x-table-action wire="editScoreType({{ $st->id }})" icon="edit" />
                                </x-tooltip>
                                <span class="text-slate-300">|</span>
                                <x-tooltip content="Xóa">
                                    <x-table-action
                                        wire="delete({{ $st->id }})"
                                        icon="trash"
                                        color="danger"
                                        :loading="true"
                                        confirm="Bạn có chắc chắn muốn xóa loại điểm '{{ $st->name }}'?" />
                                </x-tooltip>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <x-stats.page-empty
            :panel="false"
            tone="primary"
            title="Chưa có loại điểm"
            description="Thêm loại điểm đầu tiên cho phạm vi đã chọn">
            <x-button wire:click="createScoreType" variant="primary">
                <x-icon name="plus" />
                Thêm loại điểm
            </x-button>
        </x-stats.page-empty>
        @endif
        @endif
        @endif

        </x-mac-panel>

        @if($isCatechist && $viewingStudent)
        @php
            $sc = $viewingStudent;
            $avg = $this->getAverage($sc->pivot_id);
            $ratingLabel = null;
            $ratingColor = null;
            if ($avg !== null) {
                if ($avg >= 9.5)      { $ratingLabel = 'Xuất sắc';   $ratingColor = 'emerald'; }
                elseif ($avg >= 8.0)  { $ratingLabel = 'Giỏi';       $ratingColor = 'blue'; }
                elseif ($avg >= 6.5)  { $ratingLabel = 'Khá';        $ratingColor = 'amber'; }
                elseif ($avg >= 5.0)  { $ratingLabel = 'Trung bình'; $ratingColor = 'yellow'; }
                elseif ($avg >= 3.5)  { $ratingLabel = 'Yếu';        $ratingColor = 'orange'; }
                else                  { $ratingLabel = 'Kém';        $ratingColor = 'red'; }
            }
            $badgeClass = match($ratingColor) {
                'emerald' => 'bg-emerald-50/80 text-emerald-700',
                'blue'    => 'bg-blue-50/80 text-blue-700',
                'amber'   => 'bg-amber-50/80 text-amber-700',
                'yellow'  => 'bg-yellow-50/80 text-yellow-700',
                'orange'  => 'bg-orange-50/80 text-orange-700',
                'red'     => 'bg-red-50/80 text-red-700',
                default   => 'bg-slate-50/80 text-slate-400',
            };
            $initials = strtoupper(
                mb_substr($sc->last_name ?? '', 0, 1) . mb_substr($sc->first_name ?? '', 0, 1)
            );
        @endphp
        <div class="fixed inset-0 z-[200] flex items-center justify-center p-4"
            role="dialog" aria-modal="true"
            wire:key="score-detail-{{ $sc->pivot_id }}">
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" wire:click="closeStudentScoreDetail"></div>
            <div class="relative w-full max-w-md max-h-[min(85vh,calc(100vh-2rem-var(--bottom-offset,0px)))] overflow-y-auto
                bg-white rounded-2xl shadow-mac border border-black/[0.06] p-5 space-y-4">
                <div class="flex items-start gap-3">
                    <div class="w-12 h-12 rounded-full flex-shrink-0 flex items-center justify-center
                        text-sm font-semibold bg-primary-50/80 text-primary-800">
                        @if(!empty($sc->avatar_path))
                        <img src="{{ $sc->avatar_url }}" class="w-full h-full rounded-full object-cover" alt="" />
                        @else
                        {{ $initials }}
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-base font-semibold text-slate-900 truncate">
                            {{ $sc->saint->name ?? '' }} {{ $sc->last_name }} {{ $sc->first_name }}
                        </p>
                        <div class="mt-1 flex items-center gap-2 flex-wrap">
                            @if($avg !== null)
                            <span class="text-xl font-bold tabular-nums
                                {{ $avg >= 8 ? 'text-emerald-600' : ($avg >= 5 ? 'text-primary-600' : 'text-red-500') }}">
                                {{ number_format($avg, 1) }}
                            </span>
                            @if($ratingLabel)
                            <span class="inline-flex px-2 py-0.5 rounded-lg text-xs font-semibold {{ $badgeClass }}">
                                {{ $ratingLabel }}
                            </span>
                            @endif
                            @else
                            <span class="text-sm font-semibold text-slate-400">TB —</span>
                            <span class="text-xs text-slate-400">Chưa có điểm tổng kết</span>
                            @endif
                        </div>
                        @if($avg === null)
                        <p class="mt-1.5 text-[11px] text-slate-400 leading-snug">
                            Điểm trung bình chỉ tính khi đã có đủ điểm giữa kỳ và cuối kỳ (nếu lớp có cấu hình các loại này).
                        </p>
                        @endif
                    </div>
                    <button type="button" wire:click="closeStudentScoreDetail"
                        class="p-2 -mr-1 rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-2.5 pt-1 border-t border-black/[0.06]">
                    @foreach($scoreTypes as $colIndex => $type)
                    <div class="flex items-center justify-between gap-3 py-1">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-700 truncate">{{ $type->name }}</p>
                            <p class="text-[11px] text-slate-400">Hệ số {{ $type->coefficient }}</p>
                        </div>
                        @if($canEditScores)
                        <input
                            type="text"
                            inputmode="decimal"
                            step="0.5"
                            min="0"
                            max="{{ $type->max_score }}"
                            wire:model.defer="draftScores.{{ $sc->pivot_id }}.{{ $type->id }}"
                            class="score-input w-16 py-2 px-2 text-center rounded-lg text-sm font-semibold
                                   border shadow-mac-sm outline-none
                                   focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40
                                   {{ isset($scoresMatrix[$sc->pivot_id][$type->id])
                                       ? 'border-emerald-200/80 bg-emerald-50/80 text-emerald-700'
                                       : 'border-black/[0.06] bg-white text-slate-600' }}" />
                        @else
                        @php
                            $cell = $scoresMatrix[$sc->pivot_id][$type->id]['value'] ?? null;
                        @endphp
                        <span @class([
                            'inline-flex min-w-[2.75rem] justify-center px-2.5 py-1.5 rounded-lg text-sm font-semibold',
                            'bg-emerald-50/80 text-emerald-700' => $cell !== null,
                            'bg-slate-50 text-slate-300' => $cell === null,
                        ])>
                            {{ $cell !== null ? number_format((float) $cell, 1) : '—' }}
                        </span>
                        @endif
                    </div>
                    @endforeach
                </div>

                <div class="flex gap-2 pt-1">
                    <x-button type="button" variant="outline" class="flex-1" wire:click="closeStudentScoreDetail">
                        Đóng
                    </x-button>
                    @if($canEditScores)
                    <x-button type="button" variant="primary" class="flex-1" wire:click="saveAllScores">
                        <x-icon name="save" />
                        Lưu điểm
                    </x-button>
                    @endif
                </div>
            </div>
        </div>
        @endif

        @if($activeTab === 'scores')
        <script>
            document.addEventListener('keydown', function(e) {
                if (!e.target.classList.contains('score-input')) return;

                const key = e.key;
                if (!['Enter', 'ArrowDown', 'ArrowUp', 'ArrowLeft', 'ArrowRight'].includes(key)) return;

                e.preventDefault();

                const row = parseInt(e.target.dataset.row);
                const col = parseInt(e.target.dataset.col);
                let nextRow = row, nextCol = col;

                switch (key) {
                    case 'Enter':
                    case 'ArrowDown':  nextRow = row + 1; break;
                    case 'ArrowUp':    nextRow = row - 1; break;
                    case 'Tab': e.shiftKey ? nextCol = col - 1 : nextCol = col + 1; break;
                }

                const next = document.querySelector(
                    `.score-input[data-row="${nextRow}"][data-col="${nextCol}"]`
                );
                if (next) { next.focus(); next.select(); }
            });
        </script>
        @endif


        {{-- ===================== MODAL: CẤU HÌNH LOẠI ĐIỂM ===================== --}}
        @if($showScoreTypeForm)
        <div
            class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50 p-4"
            role="dialog" aria-modal="true"
            wire:click="closeScoreTypeForm"
            @keydown.escape.window="$wire.closeScoreTypeForm()">
            <div
                class="bg-white/90 backdrop-blur-xl rounded-2xl border border-black/[0.06] shadow-mac
                    w-full max-w-lg max-h-[90vh] overflow-hidden flex flex-col"
                wire:click.stop>

                <div class="flex-shrink-0 px-6 py-5 border-b border-black/[0.06]">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <h2 class="text-xl font-semibold tracking-tight text-slate-900">
                                {{ $editingScoreTypeId ? 'Cập nhật loại điểm' : 'Thêm loại điểm' }}
                            </h2>
                            <p class="mt-1 text-sm text-slate-500">
                                Học kỳ {{ $selectedSemester }}
                                @if($editingScoreTypeId)
                                    &middot; {{ $availableLops->firstWhere('id', $selectedLop)?->name ?? '' }}
                                @else
                                    &middot;
                                    @if($createScope === 'class' && $selectedLop)
                                        Lớp: {{ $availableLops->firstWhere('id', $selectedLop)?->name ?? '' }}
                                    @elseif($createScope === 'grade')
                                        Khối: {{ $availableGrades->firstWhere('id', $createScopeGradeId)?->name ?? '(chưa chọn)' }}
                                    @else
                                        Toàn giáo xứ
                                    @endif
                                @endif
                            </p>
                        </div>
                        <button wire:click="closeScoreTypeForm" type="button"
                            class="flex-shrink-0 p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-black/[0.04] transition-colors"
                            aria-label="Đóng">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto p-6 space-y-5">
                    @if($errors->any())
                    <div class="bg-red-50/90 border border-red-200/80 rounded-xl p-4 shadow-mac-sm text-sm text-red-700 space-y-1">
                        @foreach($errors->all() as $err)<div>• {{ $err }}</div>@endforeach
                    </div>
                    @endif

                    @if(!$editingScoreTypeId)
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-2 tracking-wide uppercase">Áp dụng cho</label>
                        <div class="grid grid-cols-3 gap-2">
                            @if($selectedLop)
                            <label class="flex flex-col items-center gap-1 p-3 rounded-xl border cursor-pointer
                                          text-center transition-all select-none shadow-mac-sm
                                          {{ $createScope === 'class'
                                              ? 'border-primary-300/60 bg-primary-50/80'
                                              : 'border-black/[0.06] bg-white/80 hover:bg-white' }}">
                                <input type="radio" wire:model="createScope" value="class" class="sr-only">
                                <span class="text-sm font-semibold text-slate-800">Lớp này</span>
                                <span class="text-xs text-slate-400">Chỉ lớp đang chọn</span>
                            </label>
                            @endif

                            <label class="flex flex-col items-center gap-1 p-3 rounded-xl border cursor-pointer
                                          text-center transition-all select-none shadow-mac-sm
                                          {{ $createScope === 'grade'
                                              ? 'border-primary-300/60 bg-primary-50/80'
                                              : 'border-black/[0.06] bg-white/80 hover:bg-white' }}">
                                <input type="radio" wire:model="createScope" value="grade" class="sr-only">
                                <span class="text-sm font-semibold text-slate-800">Theo khối</span>
                                <span class="text-xs text-slate-400">Tất cả lớp cùng khối</span>
                            </label>

                            <label class="flex flex-col items-center gap-1 p-3 rounded-xl border cursor-pointer
                                          text-center transition-all select-none shadow-mac-sm
                                          {{ $createScope === 'parish'
                                              ? 'border-primary-300/60 bg-primary-50/80'
                                              : 'border-black/[0.06] bg-white/80 hover:bg-white' }}">
                                <input type="radio" wire:model="createScope" value="parish" class="sr-only">
                                <span class="text-sm font-semibold text-slate-800">Toàn xứ</span>
                                <span class="text-xs text-slate-400">Tất cả lớp năm học</span>
                            </label>
                        </div>

                        @if($createScope === 'grade')
                        <div class="mt-3">
                            <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">Chọn khối</label>
                            <select wire:model="createScopeGradeId"
                                class="w-full h-11 px-4 py-2.5 rounded-xl border border-black/[0.06] bg-white/80 backdrop-blur-sm
                                       text-sm text-slate-900 shadow-mac-sm
                                       focus:outline-none focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40">
                                <option value="">-- Chọn khối --</option>
                                @foreach($availableGrades as $grade)
                                <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                    </div>
                    @endif

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">
                            Loại điểm <span class="text-red-500 normal-case">*</span>
                        </label>
                        <select wire:model.defer="scoreTypeType"
                            class="w-full h-11 px-4 py-2.5 rounded-xl border border-black/[0.06] bg-white/80 backdrop-blur-sm
                                   text-sm text-slate-900 shadow-mac-sm
                                   focus:outline-none focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40">
                            <option value="">-- Chọn loại --</option>
                            <option value="1">Khảo kinh</option>
                            <option value="2">Điểm 15 phút</option>
                            <option value="3">Điểm 45 phút</option>
                            <option value="4">Giữa kỳ</option>
                            <option value="5">Cuối kỳ</option>
                        </select>
                        @error('scoreTypeType')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <x-form-input
                        label="Tên hiển thị"
                        name="typeName"
                        wire:model.defer="typeName"
                        placeholder="VD: KT 15 phút lần 1"
                        required />

                    <div class="grid grid-cols-2 gap-4">
                        <x-form-input
                            label="Thứ tự"
                            name="typeOrder"
                            type="number" min="0" max="99"
                            wire:model.defer="typeOrder" />
                        <x-form-input
                            label="Hệ số"
                            name="typeCoefficient"
                            type="number" step="0.1" min="0.1" max="10"
                            wire:model.defer="typeCoefficient" />
                    </div>

                    <x-form-input
                        label="Điểm tối đa"
                        name="typeMaxScore"
                        type="number" step="0.1" min="1" max="100"
                        wire:model.defer="typeMaxScore" />

                    <div class="rounded-xl border border-black/[0.06] bg-white/50 p-4 shadow-mac-sm">
                        <label class="inline-flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" wire:model.defer="typeIsActive"
                                class="w-4 h-4 rounded border-black/[0.15] text-primary-600 focus:ring-primary-500/25">
                            <span class="text-sm font-semibold text-slate-900">Kích hoạt loại điểm này</span>
                        </label>
                    </div>
                </div>

                <div class="flex-shrink-0 px-6 py-4 border-t border-black/[0.06] bg-white/40 flex justify-end gap-3">
                    <x-button type="button" variant="outline" wire:click="closeScoreTypeForm">Huỷ</x-button>
                    <x-button type="button" variant="primary" wire:click="saveScoreType" wire:loading.attr="disabled">
                        <x-icon name="save" />
                        Lưu
                    </x-button>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>