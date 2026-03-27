<div class="p-6 space-y-6">

    {{-- Toast --}}
    @if(session('sacrament_message'))
    <div class="px-4 py-3 bg-primary-50 border border-primary-200 text-primary-700 rounded-xl text-sm">
        {{ session('sacrament_message') }}
    </div>
    @endif

    @if(session('sacrament_error'))
    <div class="px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
        {{ session('sacrament_error') }}
    </div>
    @endif

    {{-- Groups --}}
    @foreach($groupedSacraments as $type => $group)
    <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-all border border-slate-200 overflow-hidden">

        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-3 bg-slate-50 border-b border-slate-200">

            <div class="flex items-center gap-2">
                <span class="text-sm font-semibold text-slate-900">
                    {{ $group['label'] }}
                </span>

                @if($group['records']->count() > 0)
                <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-primary-100 text-primary-700">
                    Đã có
                </span>
                @else
                <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-slate-200 text-slate-600">
                    Chưa có
                </span>
                @endif
            </div>

            @if($group['multiple'] || $group['records']->count() === 0)
            <button wire:click="create('{{ $type }}')"
                class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium bg-primary-500 hover:bg-primary-600 text-white rounded-xl transition-all">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Thêm
            </button>
            @endif

        </div>

        {{-- Records --}}
        @if($group['records']->count() > 0)

        <div class="divide-y divide-slate-100">

            @foreach($group['records'] as $sacrament)
            <div class="flex items-center justify-between px-5 py-4" wire:key="sacrament-{{ $sacrament->id }}">

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-x-6 gap-y-2 flex-1">

                    @if($sacrament->received_date)
                    <div>
                        <p class="text-xs text-slate-500">Ngày lãnh nhận</p>
                        <p class="text-sm font-medium text-slate-900">
                            {{ $sacrament->received_date->format('d/m/Y') }}
                        </p>
                    </div>
                    @endif

                    @if($sacrament->certificate_number || $sacrament->book_number)
                    <div>
                        <p class="text-xs text-slate-500">Số chứng chỉ / sách</p>
                        <p class="text-sm font-medium text-slate-900">
                            {{ $sacrament->certificate_number }}
                            @if($sacrament->book_number)
                            · Sách {{ $sacrament->book_number }}
                            @endif
                        </p>
                    </div>
                    @endif

                    @if($sacrament->giver)
                    <div>
                        <p class="text-xs text-slate-500">Người ban</p>
                        <p class="text-sm font-medium text-slate-900">
                            {{ $sacrament->giver }}
                        </p>
                    </div>
                    @endif

                    @if($sacrament->parish_name || $sacrament->parish?->name)
                    <div>
                        <p class="text-xs text-slate-500">Nơi lãnh nhận</p>
                        <p class="text-sm font-medium text-slate-900">
                            {{ $sacrament->parish?->name ?? $sacrament->parish_name }}
                        </p>
                    </div>
                    @endif

                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-3 ml-4 flex-shrink-0 text-sm">
                    <button wire:click="edit({{ $sacrament->id }})"
                        class="text-primary-600 hover:text-primary-700 font-medium">
                        Sửa
                    </button>

                    <button wire:click="confirmDelete({{ $sacrament->id }})"
                        class="text-xs text-red-500 hover:text-red-600 font-medium">
                        Xóa
                    </button>
                </div>

            </div>
            @endforeach

        </div>

        @else
        <div class="px-5 py-4 text-sm text-slate-500 italic">
            Chưa có thông tin
        </div>
        @endif

    </div>
    @endforeach


    {{-- FORM --}}
    @if($showForm)
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-5">

        <h4 class="text-sm font-semibold text-slate-900">
            {{ $editingId ? 'Cập nhật bí tích' : 'Thêm bí tích' }}
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            @php
            $input = "w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500";
            @endphp

            <div>
                <label class="text-sm font-medium text-slate-700 mb-1 block">Loại bí tích</label>
                <select wire:model="type" class="{{ $input }}">
                    <option value="">-- Chọn --</option>
                    @foreach($typeOptions as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-sm font-medium text-slate-700 mb-1 block">Ngày lãnh nhận</label>
                <input wire:model.defer="received_date" type="date" class="{{ $input }}">
            </div>

            <div>
                <label class="text-sm font-medium text-slate-700 mb-1 block">Số chứng chỉ</label>
                <input wire:model.defer="certificate_number" class="{{ $input }}">
            </div>

            <div>
                <label class="text-sm font-medium text-slate-700 mb-1 block">Số sách</label>
                <input wire:model.defer="book_number" type="number" class="{{ $input }}">
            </div>

            <div>
                <label class="text-sm font-medium text-slate-700 mb-1 block">Người ban</label>
                <input wire:model.defer="giver" class="{{ $input }}">
            </div>

            <div>
                <label class="text-sm font-medium text-slate-700 mb-1 block">Người đỡ đầu</label>
                <input wire:model.defer="sponsor" class="{{ $input }}">
            </div>

            <div class="md:col-span-2">
                <label class="text-sm font-medium text-slate-700 mb-1 block">Nơi lãnh nhận</label>
                <input wire:model.defer="parish_name" class="{{ $input }}">
            </div>

            <div class="md:col-span-2">
                <label class="text-sm font-medium text-slate-700 mb-1 block">Ghi chú</label>
                <textarea wire:model.defer="note" rows="2" class="{{ $input }}"></textarea>
            </div>

        </div>

        <div class="flex justify-end gap-3">
            <button wire:click="closeForm"
                class="px-4 py-2 text-sm text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-all">
                Hủy
            </button>

            <button wire:click="save"
                class="px-6 py-2 text-sm text-white bg-primary-500 hover:bg-primary-600 rounded-xl transition-all">
                Lưu
            </button>
        </div>

    </div>
    @endif

    @if($showDeleteConfirm)
    <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
        wire:click="$set('showDeleteConfirm', false)">

        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md"
            wire:click.stop>

            <div class="px-6 py-4 border-b border-slate-200">
                <h3 class="text-base font-semibold text-slate-900">
                    Xác nhận xóa
                </h3>
            </div>

            <div class="px-6 py-4">
                <p class="text-sm text-slate-600">
                    Bạn có chắc muốn xóa bí tích này không?
                </p>
                <p class="text-xs text-slate-400 mt-2">
                    Hành động này không thể hoàn tác.
                </p>
            </div>

            <div class="px-6 py-4 border-t border-slate-200 flex justify-end gap-3">

                <button wire:click="$set('showDeleteConfirm', false)"
                    class="px-4 py-2 text-sm text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50">
                    Hủy
                </button>

                <button wire:click="delete"
                    class="px-4 py-2 text-sm text-white bg-red-500 hover:bg-red-600 rounded-xl">
                    Xóa
                </button>

            </div>

        </div>
    </div>
    @endif

</div>