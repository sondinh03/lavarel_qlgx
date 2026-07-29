{{-- Chuyển tab, chỉ ban giáo lý thấy --}}
@if($canManageScoreConfig)
@php
    $tabs = [
        'scores'  => 'Bảng điểm',
        'config'  => 'Cấu hình loại điểm',
        'weights' => 'Cách tính điểm',
    ];
@endphp
<div class="px-4 lg:px-6 py-3 mac-hairline-b bg-white/20">
    <div class="flex gap-1 p-1 rounded-xl bg-black/[0.04] border border-black/[0.04] w-fit">
        @foreach($tabs as $tab => $label)
        <button
            wire:click="switchTab('{{ $tab }}')"
            type="button"
            class="px-4 py-1.5 text-sm font-semibold rounded-lg transition-all
                   {{ $activeTab === $tab
                       ? 'bg-white/90 text-primary-600 shadow-mac-sm'
                       : 'text-slate-600 hover:text-slate-900' }}">
            {{ $label }}
        </button>
        @endforeach
    </div>
</div>
@endif
