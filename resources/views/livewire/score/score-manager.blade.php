<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div class="mx-auto max-w-7xl space-y-5">

        {{-- Breadcrumb --}}
        <x-breadcrumb
            :items="[
                ['label' => 'Trang chủ', 'url' => route('home')],
                [
                    'label' => 'Quản lý điểm',
                    'url' => route('scores.index'),
                    'icon' => '<svg class=\'w-4 h-4\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'>
                                <path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\'
                                    d=\'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01\' />
                            </svg>',
                ],
            ]"
            separator="arrow" />

        {{-- Toast Notifications --}}
        <div role="status" aria-live="polite">
            @if (session()->has('message'))
            <x-toast-notification type="success" :duration="3500">
                {{ session('message') }}
            </x-toast-notification>
            @endif

            @if (session()->has('error'))
            <x-toast-notification type="error" :duration="4000">
                {{ session('error') }}
            </x-toast-notification>
            @endif

            @if (session()->has('warning'))
            <x-toast-notification type="warning" :duration="4000">
                {{ session('warning') }}
            </x-toast-notification>
            @endif
        </div>

        {{-- Main Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            {{-- Header --}}
            <x-page-header
                title="Quản lý điểm"
                description="Nhập và quản lý điểm học sinh theo lớp và học kỳ"
                :stat-value="$students?->count()"
                stat-label="Học sinh"
                icon-type="score">
            </x-page-header>

            {{-- Filters Bar --}}
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/70">
                <div class="flex items-center justify-between gap-4">
                    {{-- LEFT: Filters --}}
                    <div class="flex items-center gap-3 flex-wrap">
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

                    {{-- RIGHT: Actions --}}
                    <x-action-button
                        wire="createScoreType"
                        icon="plus"
                        :disabled="!$selectedLop">
                        Cấu hình điểm
                    </x-action-button>
                </div>
            </div>
        </div>

        {{-- Score Table Section --}}
        @if($selectedLop && $scoreTypes->isNotEmpty())
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-separate border-spacing-0">
                    <thead class="bg-slate-50 sticky top-0 z-10">
                        <tr>
                            <x-table-header class="sticky left-0 bg-slate-50 z-20">STT</x-table-header>
                            <x-table-header class="sticky left-12 bg-slate-50 z-20 min-w-48">Tên học sinh</x-table-header>

                            @foreach($scoreTypes as $type)
                            <x-table-header class="text-center min-w-24">
                                <div class="flex flex-col items-center gap-1">
                                    <span class="font-semibold">{{ $type->name }}</span>
                                    <span class="text-xs text-slate-500">(HS: {{ $type->coefficient }})</span>
                                </div>
                            </x-table-header>
                            @endforeach

                            <x-table-header class="text-center bg-primary-50">
                                <span class="font-bold text-primary-700">TB HK</span>
                            </x-table-header>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse($students as $index => $student)
                        <tr class="hover:bg-slate-50 transition-colors" wire:key="student-{{ $student->id }}">
                            {{-- STT --}}
                            <td class="px-4 py-3 text-sm text-slate-500 sticky left-0 bg-white">
                                {{ $index + 1 }}
                            </td>

                            {{-- Tên học sinh --}}
                            <td class="px-4 py-3 sticky left-12 bg-white">
                                <div class="font-semibold text-slate-900">
                                    {{ $student->student->full_name }}
                                </div>
                            </td>

                            {{-- Điểm các loại --}}
                            @foreach($scoreTypes as $type)
                            @php
                            $scoreValue = $this->getScoreValue($student->id, $type->id);
                            @endphp
                            <td class="px-4 py-3 text-center">
                                <button
                                    wire:click="openScoreForm({{ $student->id }}, {{ $type->id }})"
                                    class="px-3 py-1.5 rounded-lg text-sm font-semibold transition-all
                                           {{ $scoreValue !== null 
                                              ? 'bg-green-100 text-green-700 hover:bg-green-200' 
                                              : 'bg-slate-100 text-slate-400 hover:bg-slate-200' }}">
                                    {{ $scoreValue !== null ? number_format($scoreValue, 1) : '--' }}
                                </button>
                            </td>
                            @endforeach

                            {{-- TB HK --}}
                            <td class="px-4 py-3 text-center bg-primary-50">
                                <span class="font-bold text-primary-700">
                                    --
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ 3 + $scoreTypes->count() }}" class="px-6 py-12">
                                <x-empty-state
                                    icon="users"
                                    title="Chưa có học sinh"
                                    description="Lớp này chưa có học sinh nào">
                                </x-empty-state>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($students->hasPages())
            <div class="border-t border-slate-200">
                <x-pagination
                    :paginator="$students"
                    :per-page-options="[10, 15, 25, 50]" />
            </div>
            @endif
        </div>
        @else
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-12 text-center">
            <svg class="mx-auto w-16 h-16 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
            </svg>
            <p class="mt-4 text-lg text-slate-500">
                {{ $selectedLop ? 'Vui lòng cấu hình loại điểm trước' : 'Vui lòng chọn lớp để xem bảng điểm' }}
            </p>
            @if($selectedLop)
            <x-action-button wire="createScoreType" icon="plus" class="mt-4">
                Cấu hình điểm
            </x-action-button>
            @endif
        </div>
        @endif

        {{-- Modal: Nhập điểm --}}
        @if($showScoreForm)
        <div
            class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
            wire:click="closeScoreForm">
            <div
                class="bg-white rounded-2xl shadow-xl w-full max-w-md"
                wire:click.stop>

                {{-- Header --}}
                <div class="p-6 border-b border-slate-200 bg-gradient-to-br from-green-50 to-white">
                    <h2 class="text-xl font-bold text-slate-900">
                        Nhập điểm
                    </h2>
                </div>

                {{-- Body --}}
                <div class="p-6 space-y-5">
                    {{-- Điểm --}}
                    <x-form-input
                        label="Điểm số"
                        name="scoreValue"
                        type="number"
                        step="0.1"
                        min="0"
                        max="10"
                        wire:model.defer="scoreValue"
                        placeholder="0.0 - 10.0"
                        required />

                    {{-- Lần thi --}}
                    <x-form-input
                        label="Lần thi"
                        name="attempt"
                        type="number"
                        min="1"
                        wire:model.defer="attempt"
                        help-text="Nhập 1 cho lần đầu, 2 cho thi lại..." />

                    {{-- Ghi chú --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">
                            Ghi chú
                        </label>
                        <textarea
                            wire:model.defer="scoreNote"
                            rows="2"
                            class="w-full px-3 py-2 rounded-xl border border-slate-300
                                   focus:outline-none focus:ring-2 focus:ring-primary-500"
                            placeholder="Ghi chú (tùy chọn)"></textarea>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end gap-3">
                    <x-action-button wire="closeScoreForm" variant="secondary">
                        Hủy
                    </x-action-button>
                    <x-action-button wire="saveScore" icon="save" :loading="true">
                        Lưu điểm
                    </x-action-button>
                </div>
            </div>
        </div>
        @endif

        {{-- Modal: Cấu hình loại điểm --}}
        @if($showScoreTypeForm)
        <div
            class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
            wire:click="closeScoreTypeForm">
            <div
                class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-hidden flex flex-col"
                wire:click.stop>

                {{-- Header --}}
                <div class="flex-shrink-0 p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                    <h2 class="text-xl font-bold text-slate-900">
                        {{ $editingScoreTypeId ? 'Cập nhật loại điểm' : 'Thêm loại điểm mới' }}
                    </h2>
                </div>

                {{-- Body --}}
                <div class="flex-1 overflow-y-auto p-6 space-y-5">
                    {{-- Loại điểm --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">
                            Loại điểm <span class="text-red-500">*</span>
                        </label>
                        <select
                            wire:model.defer="scoreTypeType"
                            class="w-full px-3 py-2 rounded-xl border border-slate-300
                                   focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="">-- Chọn loại --</option>
                            <option value="1">Khảo kinh</option>
                            <option value="2">Điểm 15 phút</option>
                            <option value="3">Điểm 45 phút</option>
                            <option value="4">Giữa kỳ</option>
                            <option value="5">Cuối kỳ</option>
                        </select>
                        @error('scoreTypeType')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tên --}}
                    <x-form-input
                        label="Tên hiển thị"
                        name="typeName"
                        wire:model.defer="typeName"
                        placeholder="VD: Kiểm tra 15 phút lần 1"
                        required />

                    {{-- Thứ tự & Hệ số --}}
                    <div class="grid grid-cols-2 gap-4">
                        <x-form-input
                            label="Thứ tự"
                            name="typeOrder"
                            type="number"
                            min="0"
                            wire:model.defer="typeOrder" />

                        <x-form-input
                            label="Hệ số"
                            name="typeCoefficient"
                            type="number"
                            step="0.1"
                            min="0.1"
                            wire:model.defer="typeCoefficient" />
                    </div>

                    {{-- Điểm tối đa --}}
                    <x-form-input
                        label="Điểm tối đa"
                        name="typeMaxScore"
                        type="number"
                        step="0.1"
                        min="1"
                        wire:model.defer="typeMaxScore" />

                    {{-- Trạng thái --}}
                    <div class="border border-slate-200 rounded-xl p-4">
                        <label class="inline-flex items-center gap-3 cursor-pointer">
                            <input
                                type="checkbox"
                                wire:model.defer="typeIsActive"
                                class="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                            <span class="text-sm font-semibold text-slate-900">
                                Kích hoạt loại điểm này
                            </span>
                        </label>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex-shrink-0 px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end gap-3">
                    <x-action-button wire="closeScoreTypeForm" variant="secondary">
                        Hủy
                    </x-action-button>
                    <x-action-button wire="saveScoreType" icon="save" :loading="true">
                        Lưu
                    </x-action-button>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Loading Indicator --}}
<div wire:loading class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 flex items-center gap-3">
        <svg class="animate-spin h-6 w-6 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span class="text-gray-700">Đang xử lý...</span>
    </div>
</div>