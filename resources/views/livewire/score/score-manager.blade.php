<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="mx-auto max-w-7xl space-y-5">

        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[
            ['label' => 'Trang chủ', 'url' => route('dashboard')],
            ['label' => 'Quản lý điểm', 'url' => route('scores.index'),
             'icon' => '<svg class=\'w-4 h-4\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'>
                        <path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\'
                            d=\'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01\' /></svg>'],
        ]" separator="arrow" />

        {{-- Toast --}}
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

        {{-- Header Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <x-page-header
                title="Quản lý điểm"
                description="Nhập và quản lý điểm học sinh theo lớp và học kỳ"
                :stat-value="$students?->total()"
                stat-label="Học sinh"
                icon-type="score" />

            {{-- Filter Bar --}}
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/70">
                <div class="flex items-center justify-between gap-4 flex-wrap">
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

                    {{-- Search --}}
                    <input
                        wire:model.debounce.400ms="search"
                        placeholder="Tìm học sinh..."
                        class="w-52 px-3 py-2 rounded-xl border border-slate-300 text-sm
                               focus:outline-none focus:ring-2 focus:ring-primary-500" />
                </div>
            </div>

            {{-- Tabs + Actions --}}
            <div class="px-6 py-3 border-b border-slate-200 flex items-center justify-between gap-4">
                {{-- Tabs --}}
                <div class="flex gap-1 bg-slate-100 p-1 rounded-xl">
                    <button
                        wire:click="switchTab('scores')"
                        class="px-4 py-1.5 text-sm font-semibold rounded-lg transition-all
                               {{ $activeTab === 'scores'
                                   ? 'bg-white text-slate-900 shadow-sm'
                                   : 'text-slate-500 hover:text-slate-700' }}">
                        Bảng điểm
                    </button>
                    <button
                        wire:click="switchTab('config')"
                        class="px-4 py-1.5 text-sm font-semibold rounded-lg transition-all
                               {{ $activeTab === 'config'
                                   ? 'bg-white text-slate-900 shadow-sm'
                                   : 'text-slate-500 hover:text-slate-700' }}">
                        Cấu hình loại điểm
                        @if($scoreTypes->count() > 0)
                        <span class="ml-1 px-1.5 py-0.5 bg-primary-100 text-primary-700 text-xs rounded-full">
                            {{ $scoreTypes->count() }}
                        </span>
                        @endif
                    </button>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-2">
                    @if($activeTab === 'config')
                    <x-action-button
                        wire="createScoreType"
                        icon="plus"
                        :disabled="!$selectedLop">
                        Thêm loại điểm
                    </x-action-button>

                    @if($scoreTypes->count() > 0)
                    <x-action-button
                        wire="openApplyForm"
                        variant="secondary"
                        icon="copy">
                        Apply cho lớp khác
                    </x-action-button>
                    @endif
                    @endif
                </div>
            </div>
        </div>

        {{-- ===================== TAB: BẢNG ĐIỂM ===================== --}}
        @if($activeTab === 'scores')
        @if(!$selectedLop)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-12 text-center">
            <svg class="mx-auto w-16 h-16 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
            </svg>
            <p class="mt-4 text-lg text-slate-500">Vui lòng chọn lớp để xem bảng điểm</p>
        </div>

        @elseif($scoreTypes->isEmpty())
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-12 text-center">
            <svg class="mx-auto w-16 h-16 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="mt-4 text-lg text-slate-500">Lớp này chưa có cấu hình loại điểm</p>
            <button
                wire:click="switchTab('config')"
                class="mt-4 px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition text-sm font-semibold">
                Cấu hình ngay
            </button>
        </div>

        @else
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-separate border-spacing-0 text-sm">
                    <thead class="bg-slate-50 sticky top-0 z-10">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide
                                               sticky left-0 bg-slate-50 z-20 w-10">STT</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide
                                               sticky left-10 bg-slate-50 z-20 min-w-[180px]">Tên học sinh</th>

                            @foreach($scoreTypes as $type)
                            <th class="px-3 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wide min-w-[110px]">
                                <div class="flex flex-col items-center gap-0.5">
                                    <span class="text-slate-700 normal-case font-semibold">{{ $type->name }}</span>
                                    <span class="text-slate-400 font-normal">HS: {{ $type->coefficient }}</span>
                                </div>
                            </th>
                            @endforeach

                            <th class="px-4 py-3 text-center text-xs font-bold text-primary-700 uppercase tracking-wide
                                               bg-primary-50 min-w-[90px]">TB HK</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse($students as $index => $sc)
                        <tr class="hover:bg-slate-50/70 transition-colors" wire:key="sc-{{ $sc->pivot_id  }}">
                            {{-- STT --}}
                            <td class="px-4 py-3 text-slate-400 sticky left-0 bg-white">
                                {{ ($students->firstItem() ?? 0) + $index }}
                            </td>

                            {{-- Tên --}}
                            <td class="px-4 py-3 sticky left-10 bg-white">
                                <span class="font-semibold text-slate-900">
                                    {{ $sc->full_name_with_saint }}
                                </span>
                            </td>

                            {{-- Điểm từng loại --}}
                            @foreach($scoreTypes as $type)
                            @php $val = $this->getScoreValue($sc->pivot_id, $type->id); @endphp
                            <td class="px-3 py-3 text-center">
                                <button
                                    wire:click="openScoreForm({{ $sc->pivot_id  }}, {{ $type->id }})"
                                    title="{{ $val !== null ? 'Sửa điểm' : 'Nhập điểm' }}"
                                    class="w-16 py-1.5 rounded-lg font-semibold transition-all
                                                   {{ $val !== null
                                                       ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200'
                                                       : 'bg-slate-100 text-slate-400 hover:bg-slate-200 hover:text-slate-600' }}">
                                    {{ $val !== null ? number_format($val, 1) : '—' }}
                                </button>
                            </td>
                            @endforeach

                            {{-- TB HK --}}
                            <td class="px-4 py-3 text-center bg-primary-50">
                                @php $avg = $this->getAverage($sc->pivot_id); @endphp
                                @if($avg !== null)
                                <span class="font-bold text-lg
                                                         {{ $avg >= 8 ? 'text-emerald-600' : ($avg >= 5 ? 'text-primary-600' : 'text-red-500') }}">
                                    {{ number_format($avg, 1) }}
                                </span>
                                @else
                                <span class="text-slate-300 text-lg font-bold">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ 3 + $scoreTypes->count() }}" class="px-6 py-12 text-center text-slate-400">
                                Chưa có học sinh trong lớp này
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($students->hasPages())
            <div class="px-6 py-4 border-t border-slate-200">
                <x-pagination :paginator="$students" :per-page-options="[10, 15, 25, 50]" />
            </div>
            @endif
        </div>
        @endif
        @endif

        {{-- ===================== TAB: CẤU HÌNH ===================== --}}
        @if($activeTab === 'config')
        @if(!$selectedNamHoc)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-12 text-center">
            <p class="text-lg text-slate-500">Vui lòng chọn năm học để cấu hình loại điểm</p>
        </div>
        @else
        {{-- Nếu chưa chọn lớp: hiện thông báo nhẹ nhàng, vẫn cho thêm --}}
        @if(!$selectedLop)
        <div class="bg-amber-50 border border-amber-200 rounded-2xl px-6 py-4 flex items-center gap-3">
            <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-sm text-amber-700">
                Chưa chọn lớp cụ thể — loại điểm sẽ được tạo cho <strong>theo khối</strong> hoặc <strong>toàn xứ</strong>.
                Chọn lớp ở trên nếu muốn cấu hình riêng từng lớp.
            </p>
        </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            @if($scoreTypes->isNotEmpty())
            <table class="w-full border-separate border-spacing-0 text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
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
                <tbody class="divide-y divide-slate-100">
                    @foreach($scoreTypes as $st)
                    <tr class="hover:bg-slate-50 transition-colors" wire:key="st-{{ $st->id }}">
                        <td class="px-6 py-4 font-semibold text-slate-900">{{ $st->name }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                                 bg-indigo-100 text-indigo-700">
                                {{ $st->type_label }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center text-slate-600">{{ $st->order }}</td>
                        <td class="px-6 py-4 text-center font-semibold text-slate-700">{{ $st->coefficient }}</td>
                        <td class="px-6 py-4 text-center text-slate-600">{{ $st->max_score }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full
                                                 {{ $st->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-500' }}">
                                {{ $st->is_active ? 'Đang dùng' : 'Tắt' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="inline-flex items-center gap-3">
                                <x-table-action wire="editScoreType({{ $st->id }})" icon="edit">Sửa</x-table-action>

                                <span class="text-slate-300">|</span>

                                <x-table-action
                                    wire="delete({{ $st->id }})"
                                    icon="trash"
                                    color="danger"
                                    confirm="Bạn có chắc chắn muốn xóa loại điểm '{{ $st->name }}'?">
                                    Xóa
                                </x-table-action>

                                <a x-data
                                    @click="if(confirm({{ json_encode('Bạn có chắc chắn muốn xóa loại điểm \'' . $st->name . '\'?') }})) $wire.delete({{ $st->id }})"
                                    class="inline-flex items-center gap-1 text-sm font-medium text-red-600 hover:text-red-800 cursor-pointer transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Xóa
                                </a>

                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="p-12 text-center">
                <p class="text-slate-400">Chưa có loại điểm nào. Thêm loại điểm đầu tiên.</p>
            </div>
            @endif
        </div>
        @endif
        @endif

        {{-- ===================== MODAL: NHẬP ĐIỂM ===================== --}}
        @if($showScoreForm)
        <div
            class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
            role="dialog" aria-modal="true"
            wire:click="closeScoreForm">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm" wire:click.stop>

                <div class="p-5 border-b border-slate-200 bg-gradient-to-br from-emerald-50 to-white">
                    @php
                    $currentType = $scoreTypes->firstWhere('id', $currentScoreTypeId);
                    $hasExisting = isset($scoresMatrix[$currentStudentClassId][$currentScoreTypeId]);
                    @endphp
                    <h2 class="text-lg font-bold text-slate-900">
                        {{ $hasExisting ? 'Sửa điểm' : 'Nhập điểm' }}
                    </h2>
                    @if($currentType)
                    <p class="text-sm text-slate-500 mt-0.5">
                        {{ $currentType->name }} &middot; Tối đa: {{ $currentType->max_score }}
                    </p>
                    @endif
                </div>

                <div class="p-5 space-y-4">
                    @if($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-400 rounded-lg p-3 text-sm text-red-700">
                        @foreach($errors->all() as $err)
                        <div>{{ $err }}</div>
                        @endforeach
                    </div>
                    @endif

                    <x-form-input
                        label="Điểm số"
                        name="scoreValue"
                        type="number"
                        step="0.1"
                        min="0"
                        :max="$currentType?->max_score ?? 10"
                        wire:model.defer="scoreValue"
                        placeholder="0.0 – {{ $currentType?->max_score ?? 10 }}"
                        required />

                    <x-form-input
                        label="Lần thi"
                        name="attempt"
                        type="number"
                        min="1"
                        max="9"
                        wire:model.defer="attempt"
                        help-text="1 = lần đầu, 2 = thi lại..." />

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Ghi chú</label>
                        <textarea
                            wire:model.defer="scoreNote"
                            rows="2"
                            class="w-full px-3 py-2 rounded-xl border border-slate-300
                                   focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm"
                            placeholder="Tuỳ chọn"></textarea>
                    </div>
                </div>

                <div class="px-5 py-4 border-t border-slate-200 bg-slate-50 flex items-center justify-between gap-3">
                    {{-- Xoá điểm nếu đã có --}}
                    @if($hasExisting)
                    <button
                        wire:click="deleteScore({{ $currentStudentClassId }}, {{ $currentScoreTypeId }})"
                        wire:loading.attr="disabled"
                        class="px-3 py-2 text-red-600 hover:text-red-700 text-sm font-semibold
                               hover:bg-red-50 rounded-lg transition">
                        Xoá điểm
                    </button>
                    @else
                    <span></span>
                    @endif

                    <div class="flex gap-2">
                        <x-action-button wire="closeScoreForm" variant="secondary">Huỷ</x-action-button>
                        <x-action-button wire="saveScore" icon="save" :loading="true">Lưu</x-action-button>
                    </div>
                </div>
            </div>
        </div>
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

                    {{-- Phạm vi áp dụng — chỉ hiện khi TẠO MỚI --}}
                    @if(!$editingScoreTypeId)
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Áp dụng cho
                        </label>
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

                        {{-- Chọn khối khi scope = grade --}}
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

        {{-- ===================== MODAL: APPLY CHO LỚP KHÁC ===================== --}}
        @if($showApplyForm)
        <div
            class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
            role="dialog" aria-modal="true"
            wire:click="closeApplyForm">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md" wire:click.stop>

                <div class="p-6 border-b border-slate-200 bg-gradient-to-br from-indigo-50 to-white">
                    <h2 class="text-xl font-bold text-slate-900">Apply cấu hình điểm</h2>
                    <p class="text-sm text-slate-500 mt-1">
                        Copy {{ $scoreTypes->count() }} loại điểm từ lớp này sang lớp khác
                        trong học kỳ {{ $selectedSemester }}
                    </p>
                </div>

                <div class="p-6 space-y-5">
                    {{-- Phạm vi apply --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Áp dụng cho</label>
                        <div class="space-y-2">
                            <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition
                                          {{ $applyScope === 'grade' ? 'border-primary-400 bg-primary-50' : 'border-slate-200 hover:border-slate-300' }}">
                                <input type="radio" wire:model="applyScope" value="grade"
                                    class="text-primary-600 focus:ring-primary-500">
                                <div>
                                    <div class="text-sm font-semibold text-slate-900">Theo khối</div>
                                    <div class="text-xs text-slate-500">Tất cả lớp trong cùng khối</div>
                                </div>
                            </label>

                            <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition
                                          {{ $applyScope === 'parish' ? 'border-primary-400 bg-primary-50' : 'border-slate-200 hover:border-slate-300' }}">
                                <input type="radio" wire:model="applyScope" value="parish"
                                    class="text-primary-600 focus:ring-primary-500">
                                <div>
                                    <div class="text-sm font-semibold text-slate-900">Toàn giáo xứ</div>
                                    <div class="text-xs text-slate-500">Tất cả lớp trong năm học này</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Chọn khối nếu scope = grade --}}
                    @if($applyScope === 'grade')
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">
                            Chọn khối <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="applyScopeGradeId"
                            class="w-full px-3 py-2 rounded-xl border border-slate-300
                                   focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="">-- Chọn khối --</option>
                            @foreach($availableGrades as $grade)
                            <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    {{-- Overwrite option --}}
                    <div class="border border-amber-200 bg-amber-50 rounded-xl p-4">
                        <label class="inline-flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" wire:model="applyOverwrite"
                                class="mt-0.5 w-4 h-4 rounded border-slate-300 text-amber-500 focus:ring-amber-400">
                            <div>
                                <div class="text-sm font-semibold text-slate-900">Ghi đè nếu đã tồn tại</div>
                                <div class="text-xs text-amber-700 mt-0.5">
                                    Nếu lớp đích đã có loại điểm cùng type, sẽ bị ghi đè
                                </div>
                            </div>
                        </label>
                    </div>

                    {{-- Preview --}}
                    <div class="bg-slate-50 rounded-xl p-4">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">
                            Sẽ copy {{ $scoreTypes->count() }} loại điểm:
                        </p>
                        <div class="space-y-1">
                            @foreach($scoreTypes as $st)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-slate-700">{{ $st->name }}</span>
                                <span class="text-slate-400">HS: {{ $st->coefficient }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end gap-3">
                    <x-action-button wire="closeApplyForm" variant="secondary">Huỷ</x-action-button>
                    <x-action-button
                        wire="applyConfig"
                        icon="save"
                        :loading="true"
                        :disabled="$applyScope === 'grade' && !$applyScopeGradeId">
                        Xác nhận apply
                    </x-action-button>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>

{{-- Loading overlay --}}
<div wire:loading.delay class="fixed inset-0 bg-black/30 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-5 flex items-center gap-3 shadow-xl">
        <svg class="animate-spin h-5 w-5 text-primary-600" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
        </svg>
        <span class="text-sm font-medium text-slate-700">Đang xử lý...</span>
    </div>
</div>