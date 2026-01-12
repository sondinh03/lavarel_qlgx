<div class="grid grid-cols-1 md:grid-cols-4 gap-4">

    {{-- Năm học --}}
    @if($showNamHoc)
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-2">
            Năm học
        </label>

        <select
            wire:model="selectedNamHoc"
            class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl
                       text-slate-900
                       focus:outline-none focus:ring-2 focus:ring-primary-500
                       disabled:bg-slate-100 disabled:text-slate-400">
            <!-- <option value="">-- Chọn năm học --</option> -->

            @foreach($namHocs as $id => $name)
            <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
        </select>
    </div>
    @endif

    {{-- Khối --}}
    @if($showKhoi)
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-2">
            Khối
        </label>

        <select
            wire:model="selectedKhoi"
            @disabled(!$selectedNamHoc)
            class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl
                       text-slate-900
                       focus:outline-none focus:ring-2 focus:ring-primary-500
                       disabled:bg-slate-100 disabled:text-slate-400">
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
        <label class="block text-sm font-semibold text-slate-700 mb-2">
            Lớp
        </label>

        <select
            wire:model="selectedLop"
            @disabled(!$selectedNamHoc || !$selectedKhoi)
            class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl
                       text-slate-900
                       focus:outline-none focus:ring-2 focus:ring-primary-500
                       disabled:bg-slate-100 disabled:text-slate-400">
            <option value="">-- Tất cả lớp --</option>

            @foreach($lops as $id => $name)
            <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
        </select>
    </div>
    @endif

    {{-- Kỳ --}}
    @if($showKy)
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-2">
            Học kỳ
        </label>

        <select
            wire:model="selectedKy"
            @disabled(!$selectedNamHoc)
            class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl
                       text-slate-900
                       focus:outline-none focus:ring-2 focus:ring-primary-500
                       disabled:bg-slate-100 disabled:text-slate-400">
            <option value="">-- Chọn học kỳ --</option>

            @foreach($kys as $id => $name)
            <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
        </select>
    </div>
    @endif

    {{-- Loading indicator 
    <div
        wire:loading.delay
        class="md:col-span-4 text-sm text-slate-500 flex items-center gap-2">
        <svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10"
                stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8v4l3-3-3-3v4a12 12 0 00-12 12h4z" />
        </svg>
        <span>Đang tải dữ liệu...</span>
    </div> 
    --}}
</div>