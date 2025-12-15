<div class="grid grid-cols-1 md:grid-cols-4 gap-4">
    {{-- Năm học --}}
    @if($showNamHoc)
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-2">Năm học</label>
        <select wire:model.live="selectedNamHoc"
            class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-purple-500">
            <option value="">-- Chọn năm học --</option>
            @foreach($namHocs as $id => $name)
            <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
        </select>
    </div>
    @endif

    {{-- Khối --}}
    @if($showKhoi)
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-2">Khối</label>
        <select wire:model.live="selectedKhoi"
            class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-purple-500"
            @if(!$selectedNamHoc) disabled @endif>
            <option value="">-- Tất cả khối --</option>
            @foreach($khois as $id => $name)
            <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
        </select>
    </div>
    @endif

    {{-- Lớp --}}
    @if($showLop)
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-2">Lớp</label>
        <select wire:model.live="selectedLop"
            class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-purple-500"
            @if(!$selectedNamHoc) disabled @endif>
            <option value="">-- Chọn lớp --</option>
            @foreach($lops as $id => $name)
            <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
        </select>
    </div>
    @endif

    {{-- Kỳ --}}
    @if($showKy)
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-2">Kỳ</label>
        <select wire:model.live="selectedKy"
            class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-purple-500"
            @if(!$selectedNamHoc) disabled @endif>
            <option value="">-- Chọn kỳ --</option>
            @foreach($kys as $id => $name)
            <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
        </select>
    </div>
    @endif

    {{-- Reset Button --}}
    <div class="flex items-end">
        <button wire:click="resetFiltersHandler"
            type="button"
            class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 active:scale-[0.98] transition-all flex items-center justify-center gap-2">
            <svg class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span class="font-semibold text-slate-900">Đặt lại</span>
        </button>
    </div>
</div>