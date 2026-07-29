{{-- Tab bảng điểm: trạng thái rỗng, rồi thẻ (GLV) hoặc bảng (ban giáo lý) --}}
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

@elseif($activeScoreTypes->isEmpty())
<x-stats.page-empty
    :panel="false"
    tone="primary"
    title="Lớp chưa có cấu hình loại điểm"
    description="{{ $canManageScoreConfig
        ? 'Thêm loại điểm trước khi nhập điểm cho học sinh'
        : 'Liên hệ ban giáo lý để cấu hình loại điểm' }}">
    @if($canManageScoreConfig)
    <x-button wire:click="switchTab('config')" variant="primary">
        Cấu hình ngay
    </x-button>
    @endif
</x-stats.page-empty>

@else
@include('livewire.score.partials.discard-draft-dialog')

@if($isCatechist)
@include('livewire.score.partials.student-cards')
@else
@include('livewire.score.partials.score-table')
@endif
@endif
