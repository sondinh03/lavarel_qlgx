<div class="p-6 space-y-6">

    {{-- Thông báo --}}
    @if(session('sacrament_message'))
    <div class="px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
        {{ session('sacrament_message') }}
    </div>
    @endif
    @if(session('sacrament_error'))
    <div class="px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
        {{ session('sacrament_error') }}
    </div>
    @endif

    {{-- Danh sách bí tích theo nhóm --}}
    @foreach($groupedSacraments as $type => $group)
    <div class="border border-slate-200 rounded-xl overflow-hidden">

        {{-- Header từng loại bí tích --}}
        <div class="flex items-center justify-between px-4 py-3 bg-slate-50 border-b border-slate-200">
            <div class="flex items-center gap-2">
                <span class="text-sm font-semibold text-slate-800">{{ $group['label'] }}</span>
                @if($group['records']->count() > 0)
                <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-700">
                    Đã có
                </span>
                @else
                <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-slate-200 text-slate-500">
                    Chưa có
                </span>
                @endif
            </div>

            {{-- Nút thêm: luôn hiện với anointing, còn lại chỉ hiện khi chưa có --}}
            @if($group['multiple'] || $group['records']->count() === 0)
            <button wire:click="create('{{ $type }}')"
                class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Thêm
            </button>
            @endif
        </div>

        {{-- Records --}}
        @if($group['records']->count() > 0)
        <div class="divide-y divide-slate-100">
            @foreach($group['records'] as $sacrament)
            <div class="flex items-center justify-between px-4 py-3" wire:key="sacrament-{{ $sacrament->id }}">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-x-6 gap-y-1 flex-1">

                    @if($sacrament->received_date)
                    <div>
                        <p class="text-xs text-slate-400">Ngày lãnh nhận</p>
                        <p class="text-sm font-medium text-slate-800">{{ $sacrament->received_date->format('d/m/Y') }}</p>
                    </div>
                    @endif

                    @if($sacrament->certificate_number || $sacrament->book_number)
                    <div>
                        <p class="text-xs text-slate-400">Số chứng chỉ / sách</p>
                        <p class="text-sm font-medium text-slate-800">
                            {{ $sacrament->certificate_number }}
                            @if($sacrament->book_number) · Sách {{ $sacrament->book_number }} @endif
                        </p>
                    </div>
                    @endif

                    @if($sacrament->giver)
                    <div>
                        <p class="text-xs text-slate-400">Người ban</p>
                        <p class="text-sm font-medium text-slate-800">{{ $sacrament->giver }}</p>
                    </div>
                    @endif

                    @if($sacrament->parish_name || $sacrament->parish?->name)
                    <div>
                        <p class="text-xs text-slate-400">Nơi lãnh nhận</p>
                        <p class="text-sm font-medium text-slate-800">
                            {{ $sacrament->parish?->name ?? $sacrament->parish_name }}
                        </p>
                    </div>
                    @endif

                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-2 ml-4 flex-shrink-0">
                    <button wire:click="edit({{ $sacrament->id }})"
                        class="text-xs text-primary-600 hover:text-primary-700 font-medium">
                        Sửa
                    </button>
                    <span class="text-slate-300">|</span>
                    <button wire:click="delete({{ $sacrament->id }})"
                        wire:confirm="Xóa bí tích {{ $group['label'] }} này?"
                        class="text-xs text-red-500 hover:text-red-600 font-medium">
                        Xóa
                    </button>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="px-4 py-3 text-sm text-slate-400 italic">Chưa có thông tin</div>
        @endif

    </div>
    @endforeach

    {{-- ===== FORM thêm/sửa bí tích ===== --}}
    @if($showForm)
    <div class="border-2 border-primary-200 rounded-xl p-5 bg-primary-50/30 space-y-4">

        <h4 class="font-semibold text-slate-800 text-sm">
            {{ $editingId ? 'Cập nhật bí tích' : 'Thêm bí tích' }}
            @if($activeType)
            — {{ $typeOptions[$activeType] ?? $activeType }}
            @endif
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            {{-- Loại bí tích --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Loại bí tích <span class="text-red-500">*</span></label>
                <select wire:model="type"
                    {{ $activeType ? 'disabled' : '' }}
                    class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 @error('type') border-red-400 @enderror">
                    <option value="">-- Chọn --</option>
                    @foreach($typeOptions as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Ngày lãnh nhận --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Ngày lãnh nhận</label>
                <input wire:model.defer="received_date" type="date"
                    class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 @error('received_date') border-red-400 @enderror" />
                @error('received_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Số chứng chỉ --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Số chứng chỉ</label>
                <input wire:model.defer="certificate_number" type="text"
                    class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" />
            </div>

            {{-- Số sách --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Số sách</label>
                <input wire:model.defer="book_number" type="number" min="1"
                    class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" />
            </div>

            {{-- Người ban bí tích --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Người ban bí tích</label>
                <input wire:model.defer="giver" type="text" placeholder="Tên linh mục..."
                    class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" />
            </div>

            {{-- Người đỡ đầu --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Người đỡ đầu / chứng nhân</label>
                <input wire:model.defer="sponsor" type="text"
                    class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" />
            </div>

            {{-- Nơi lãnh nhận (text nếu ngoài hệ thống) --}}
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Nơi lãnh nhận</label>
                <input wire:model.defer="parish_name" type="text" placeholder="Tên giáo xứ nơi lãnh nhận..."
                    class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" />
                <p class="text-xs text-slate-400 mt-1">Nhập tên giáo xứ nếu không có trong hệ thống</p>
            </div>

            {{-- Ghi chú --}}
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Ghi chú</label>
                <textarea wire:model.defer="note" rows="2"
                    class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                    placeholder="Ghi chú thêm..."></textarea>
            </div>

        </div>

        {{-- Buttons --}}
        <div class="flex justify-end gap-3 pt-2">
            <button wire:click="closeForm"
                class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition">
                Hủy
            </button>
            <button wire:click="save" wire:loading.attr="disabled"
                class="px-6 py-2 text-sm font-medium text-white bg-primary-600 rounded-xl hover:bg-primary-700 transition disabled:opacity-60">
                <span wire:loading.remove wire:target="save">Lưu bí tích</span>
                <span wire:loading wire:target="save">Đang lưu...</span>
            </button>
        </div>

    </div>
    @endif

</div>