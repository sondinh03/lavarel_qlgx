@section('topbar')
<x-breadcrumb :items="[
        ['label' => 'Trang chủ', 'url' => route('dashboard')],
        ['label' => 'Quản lý điểm', 'url' => route('scores.index')],
        ['label' => 'Bảng điểm', 'url' => route('scores.index')],
    ]" />
@endsection

<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="mx-auto max-w-7xl space-y-5">
        {{-- Header Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <x-page-header
                title="Quản lý điểm"
                description="Nhập và quản lý điểm học sinh theo lớp và học kỳ"
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
                <div class="flex items-center gap-2">
                    @if($activeTab === 'scores')
                    <x-button
                        wire:click="saveAllScores"
                        variant="primary">
                        <x-icon name="save" />
                        Lưu tất cả
                    </x-button>
                    <x-button
                        wire:click="exportScores"
                        variant="outline">
                        <x-icon name="file-export" />
                        Xuất Excel
                    </x-button>
                    @endif
                    @if($activeTab === 'config')
                    <x-button
                        wire:click="createScoreType" variant="primary">
                        <x-icon name="plus" />
                        Thêm loại điểm
                    </x-button>
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
        <div
            x-data="{ show: false, action: '', value: '' }"
            x-on:confirm-discard-draft.window="show = true; action = $event.detail.action; value = $event.detail.value">

            {{-- Overlay confirm --}}
            <div x-show="show" x-cloak
                class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 space-y-4">
                    <h3 class="text-lg font-bold text-slate-900">Bạn có thay đổi chưa lưu</h3>
                    <p class="text-sm text-slate-500">
                        Nếu tiếp tục, điểm đã nhập nhưng chưa lưu sẽ bị mất.
                    </p>
                    <div class="flex justify-end gap-3">
                        <button
                            @click="show = false"
                            class="px-4 py-2 text-sm font-semibold text-slate-600
                           hover:bg-slate-100 rounded-xl transition">
                            Ở lại
                        </button>
                        <button
                            @click="show = false; $wire.confirmDiscard(action, value)"
                            class="px-4 py-2 text-sm font-semibold text-white
                           bg-red-500 hover:bg-red-600 rounded-xl transition">
                            Bỏ thay đổi
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="max-h-[70vh] overflow-y-auto">
                <div class="overflow-x-auto">
                    <table class="w-full border-separate border-spacing-0 text-sm">
                        <thead class="bg-slate-50 sticky top-0 z-10">
                            <tr>
                                <x-table-header>STT</x-table-header>
                                <x-table-header :sortable="true" sort-field="first_name"
                                    :current-sort="$sortField" :sort-direction="$sortDirection">
                                    Học sinh
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
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            @forelse($students as $index => $sc)
                            <tr class="hover:bg-slate-50/70 transition-colors"
                                wire:key="sc-{{ $sc->pivot_id }}">

                                <td class="px-4 py-3 text-slate-400 sticky left-0 bg-white">
                                    {{ ($students->firstItem() ?? 0) + $index }}
                                </td>

                                <td class="px-4 py-3 sticky left-10 bg-white">
                                    <div class="flex flex-col">
                                        @if($sc->saint)
                                        <span class="text-xs text-slate-400 mt-0.5">
                                            {{ $sc->saint->name }}
                                        </span>
                                        @endif
                                        <span class="font-semibold text-slate-900 leading-tight">
                                            {{ $sc->last_name }} {{ $sc->first_name }}
                                        </span>
                                    </div>
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
                                        class="score-input w-16 py-1.5 px-2 text-center rounded-lg text-sm font-semibold
                                        border transition-all outline-none
                                        placeholder:text-slate-300
                                        focus:ring-2 focus:ring-primary-400 focus:border-primary-400
                                        [appearance:textfield]
                                        [&::-webkit-outer-spin-button]:appearance-none
                                        [&::-webkit-inner-spin-button]:appearance-none
                                        {{ isset($scoresMatrix[$sc->pivot_id][$type->id])
                                            ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                            : 'border-slate-200 bg-white text-slate-500' }}" />
                                </td>
                                @endforeach

                                <td class="px-4 py-3 text-center bg-primary-50">
                                    @php $avg = $this->getAverage($sc->pivot_id); @endphp
                                    @if($avg !== null)
                                    <span class="font-bold text-lg
                                     {{ $avg >= 8 ? 'text-emerald-600'
                                         : ($avg >= 5 ? 'text-primary-600' : 'text-red-500') }}">
                                        {{ number_format($avg, 1) }}
                                    </span>
                                    @else
                                    <span class="text-slate-300 text-lg font-bold">—</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ 3 + $scoreTypes->count() }}"
                                    class="px-6 py-12 text-center text-slate-400">
                                    Chưa có học sinh trong lớp này
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Footer: pagination + nút lưu --}}
            <div class="px-6 py-4 border-t border-slate-200">
                <div>
                    @if($students->hasPages())
                    <x-pagination :paginator="$students" :per-page-options="[10, 15, 25, 50, 100]" />
                    @endif
                </div>
            </div>
        </div>


        <script>
            document.addEventListener('keydown', function(e) {
                if (!e.target.classList.contains('score-input')) return;

                const key = e.key;
                if (!['Enter', 'ArrowDown', 'ArrowUp', 'ArrowLeft', 'ArrowRight'].includes(key)) return;

                e.preventDefault();

                const row = parseInt(e.target.dataset.row);
                const col = parseInt(e.target.dataset.col);

                let nextRow = row;
                let nextCol = col;

                switch (key) {
                    case 'Enter':
                    case 'ArrowDown':
                        nextRow = row + 1;
                        break;
                    case 'ArrowUp':
                        nextRow = row - 1;
                        break;
                    case 'Tab':
                        e.shiftKey ? nextCol = col - 1 : nextCol = col + 1;
                        break;
                }

                const next = document.querySelector(
                    `.score-input[data-row="${nextRow}"][data-col="${nextCol}"]`
                );

                if (next) {
                    next.focus();
                    next.select(); // bôi đen giá trị sẵn để gõ đè luôn
                }
            });
        </script>

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
            @else
            <div class="p-12 text-center">
                <p class="text-slate-400">Chưa có loại điểm nào. Thêm loại điểm đầu tiên.</p>
            </div>
            @endif
        </div>
        @endif
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