{{-- Modal thêm/sửa loại điểm --}}
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
