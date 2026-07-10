@section('topbar')
<x-breadcrumb :items="[
        ['label' => 'Trang chủ', 'url' => route('parish-admin.dashboard')],
        ['label' => 'Quản lý điểm', 'url' => route('scores.index')],
        ['label' => 'Bảng điểm', 'url' => route('scores.index')],
    ]" />
@endsection

<div class="min-h-screen bg-apple-gray p-2 sm:p-4 lg:p-6" style="min-height: calc(100vh - 56px - var(--bottom-offset));">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="mx-auto max-w-7xl">
        <x-mac-panel :overflow="true">
            <x-page-header
                title="Quản lý điểm"
                description="Nhập và quản lý điểm học sinh theo lớp và học kỳ"
                icon-type="score" />

            <div class="p-4 lg:p-6 mac-hairline-b bg-white/30">
                <div class="flex flex-col gap-4">
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

                    <x-search-input
                        wire-model="search"
                        placeholder="Tìm học sinh..."
                        debounce="400ms"
                        class="max-w-md" />
                </div>
            </div>

            <div class="px-4 lg:px-6 py-3 mac-hairline-b flex items-center justify-between gap-4 flex-wrap">
                {{-- Tabs --}}
                <div class="flex gap-1 bg-slate-100 p-1 rounded-xl">
                    <button
                        wire:click="switchTab('scores')"
                        class="px-4 py-1.5 text-sm font-semibold rounded-lg transition-all
                               {{ $activeTab === 'scores'
                                   ? 'bg-white text-primary-600 shadow-sm'
                                   : 'text-slate-600 hover:text-slate-900' }}">
                        Bảng điểm
                    </button>
                    <button
                        wire:click="switchTab('config')"
                        class="px-4 py-1.5 text-sm font-semibold rounded-lg transition-all
                               {{ $activeTab === 'config'
                                   ? 'bg-white text-primary-600 shadow-sm'
                                   : 'text-slate-600 hover:text-slate-900' }}">
                        Cấu hình loại điểm
                    </button>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-2 flex-wrap">
                    @if($activeTab === 'scores')
                    {{-- Nút đến trang thống kê --}}
                    <a
                        href="{{ route('scores.statistics', ['namHoc' => $selectedNamHoc, 'khoi' => $selectedKhoi, 'lop' => $selectedLop, 'semester' => $selectedSemester]) }}"
                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-300
                               text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:border-slate-400 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Thống kê
                    </a>
                    <x-button wire:click="saveAllScores" variant="primary">
                        <x-icon name="save" />
                        Lưu tất cả
                    </x-button>
                    <x-button wire:click="exportScores" variant="outline">
                        <x-icon name="file-export" />
                        Xuất Excel
                    </x-button>
                    @endif
                    @if($activeTab === 'config')
                    <x-button wire:click="createScoreType" variant="primary">
                        <x-icon name="plus" />
                        Thêm loại điểm
                    </x-button>
                    @endif
                </div>
            </div>

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
            description="Thêm loại điểm trước khi nhập điểm cho học sinh">
            <x-button wire:click="switchTab('config')" variant="primary">
                Cấu hình ngay
            </x-button>
        </x-stats.page-empty>

        @else

        {{-- Confirm discard draft --}}
        <div
            x-data="{ show: false, action: '', value: '' }"
            x-on:confirm-discard-draft.window="show = true; action = $event.detail.action; value = $event.detail.value">
            <div x-show="show" x-cloak
                class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 space-y-4">
                    <h3 class="text-lg font-bold text-slate-900">Bạn có thay đổi chưa lưu</h3>
                    <p class="text-sm text-slate-500">
                        Nếu tiếp tục, điểm đã nhập nhưng chưa lưu sẽ bị mất.
                    </p>
                    <div class="flex justify-end gap-3">
                        <button @click="show = false"
                            class="px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                            Ở lại
                        </button>
                        <button @click="show = false; $wire.confirmDiscard(action, value)"
                            class="px-4 py-2 text-sm font-semibold text-white bg-red-500 hover:bg-red-600 rounded-xl transition">
                            Bỏ thay đổi
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-h-[70vh] overflow-y-auto">
            <div class="overflow-x-auto">
                <table class="w-full border-separate border-spacing-0 text-sm">
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
                                    class="bg-primary-50 text-primary-700 font-bold">
                                    Điểm<br>trung bình
                                </x-table-header>

                                <x-table-header align="center" class="bg-primary-50 text-primary-700 font-bold">
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
                                    'emerald' => 'bg-emerald-100 text-emerald-700',
                                    'blue'    => 'bg-blue-100 text-blue-700',
                                    'amber'   => 'bg-amber-100 text-amber-700',
                                    'yellow'  => 'bg-yellow-100 text-yellow-700',
                                    'orange'  => 'bg-orange-100 text-orange-700',
                                    'red'     => 'bg-red-100 text-red-700',
                                    default   => 'bg-slate-100 text-slate-400',
                                };
                            @endphp
                            <tr class="hover:bg-black/[0.03] transition-colors" wire:key="sc-{{ $sc->pivot_id }}">

                                <td class="px-4 py-3 text-slate-400 sticky left-0 bg-white">
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
                                               border transition-all outline-none placeholder:text-slate-300
                                               focus:ring-2 focus:ring-primary-400 focus:border-primary-400
                                               [appearance:textfield]
                                               [&::-webkit-outer-spin-button]:appearance-none
                                               [&::-webkit-inner-spin-button]:appearance-none
                                               {{ isset($scoresMatrix[$sc->pivot_id][$type->id])
                                                   ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                                   : 'border-slate-200 bg-white text-slate-500' }}" />
                                </td>
                                @endforeach

                                {{-- Điểm TB --}}
                                <td class="px-4 py-3 text-center bg-primary-50">
                                    @if($avg !== null)
                                    <span class="font-bold text-lg
                                         {{ $avg >= 8 ? 'text-emerald-600' : ($avg >= 5 ? 'text-primary-600' : 'text-red-500') }}">
                                        {{ number_format($avg, 1) }}
                                    </span>
                                    @else
                                    <span class="text-slate-300 text-lg font-bold">—</span>
                                    @endif
                                </td>

                                {{-- Xếp loại --}}
                                <td class="px-4 py-3 text-center bg-primary-50">
                                    @if($ratingLabel)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $badgeClass }}">
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

        @if($activeTab === 'config')
        @if(!$selectedNamHoc)
        <x-stats.page-empty
            :panel="false"
            tone="slate"
            title="Vui lòng chọn năm học"
            description="Chọn năm học để cấu hình loại điểm" />
        @else
        @if(!$selectedLop)
        <div class="mx-4 lg:mx-6 my-4 px-4 py-3 mac-hairline-b bg-amber-50/50 text-sm text-amber-800 rounded-lg">
            Chưa chọn lớp cụ thể — loại điểm sẽ được tạo theo <strong>khối</strong> hoặc <strong>toàn xứ</strong>.
            Chọn lớp ở trên nếu muốn cấu hình riêng từng lớp.
        </div>
        @endif

        @if($scoreTypes->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="w-full border-separate border-spacing-0 text-sm">
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
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">
                                {{ $st->type_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-slate-600">{{ $st->order }}</td>
                        <td class="px-4 py-3 text-center font-semibold text-slate-700">{{ $st->coefficient }}</td>
                        <td class="px-4 py-3 text-center text-slate-600">{{ $st->max_score }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full
                                {{ $st->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-500' }}">
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
            class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
            role="dialog" aria-modal="true"
            wire:click="closeScoreTypeForm">
            <div
                class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-hidden flex flex-col"
                wire:click.stop>

                <div class="flex-shrink-0 p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                    <h2 class="text-xl font-bold text-slate-900">
                        {{ $editingScoreTypeId ? 'Cập nhật loại điểm' : 'Thêm loại điểm' }}
                    </h2>
                    <p class="text-sm text-slate-500 mt-0.5">
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

                <div class="flex-1 overflow-y-auto p-6 space-y-5">
                    @if($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-500 rounded-xl p-4 text-sm text-red-700 space-y-1">
                        @foreach($errors->all() as $err)<div>• {{ $err }}</div>@endforeach
                    </div>
                    @endif

                    @if(!$editingScoreTypeId)
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Áp dụng cho</label>
                        <div class="grid grid-cols-3 gap-2">
                            @if($selectedLop)
                            <label class="flex flex-col items-center gap-1 p-3 rounded-xl border cursor-pointer
                                          text-center transition-all select-none
                                          {{ $createScope === 'class'
                                              ? 'border-primary-400 bg-primary-50 ring-1 ring-primary-300'
                                              : 'border-slate-200 hover:border-slate-300' }}">
                                <input type="radio" wire:model="createScope" value="class" class="sr-only">
                                <span class="text-sm font-semibold text-slate-800">Lớp này</span>
                                <span class="text-xs text-slate-400">Chỉ lớp đang chọn</span>
                            </label>
                            @endif

                            <label class="flex flex-col items-center gap-1 p-3 rounded-xl border cursor-pointer
                                          text-center transition-all select-none
                                          {{ $createScope === 'grade'
                                              ? 'border-primary-400 bg-primary-50 ring-1 ring-primary-300'
                                              : 'border-slate-200 hover:border-slate-300' }}">
                                <input type="radio" wire:model="createScope" value="grade" class="sr-only">
                                <span class="text-sm font-semibold text-slate-800">Theo khối</span>
                                <span class="text-xs text-slate-400">Tất cả lớp cùng khối</span>
                            </label>

                            <label class="flex flex-col items-center gap-1 p-3 rounded-xl border cursor-pointer
                                          text-center transition-all select-none
                                          {{ $createScope === 'parish'
                                              ? 'border-primary-400 bg-primary-50 ring-1 ring-primary-300'
                                              : 'border-slate-200 hover:border-slate-300' }}">
                                <input type="radio" wire:model="createScope" value="parish" class="sr-only">
                                <span class="text-sm font-semibold text-slate-800">Toàn xứ</span>
                                <span class="text-xs text-slate-400">Tất cả lớp năm học</span>
                            </label>
                        </div>

                        @if($createScope === 'grade')
                        <div class="mt-3">
                            <select wire:model="createScopeGradeId"
                                class="w-full px-3 py-2 rounded-xl border border-slate-300
                                       focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm">
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
                        <label class="block text-sm font-semibold text-slate-700 mb-1">
                            Loại điểm <span class="text-red-500">*</span>
                        </label>
                        <select wire:model.defer="scoreTypeType"
                            class="w-full px-3 py-2 rounded-xl border border-slate-300
                                   focus:outline-none focus:ring-2 focus:ring-primary-500">
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

                    <div class="border border-slate-200 rounded-xl p-4">
                        <label class="inline-flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" wire:model.defer="typeIsActive"
                                class="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                            <span class="text-sm font-semibold text-slate-900">Kích hoạt loại điểm này</span>
                        </label>
                    </div>
                </div>

                <div class="flex-shrink-0 px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end gap-3">
                    <x-action-button wire="closeScoreTypeForm" variant="secondary">Huỷ</x-action-button>
                    <x-action-button wire="saveScoreType" icon="save" :loading="true">Lưu</x-action-button>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>