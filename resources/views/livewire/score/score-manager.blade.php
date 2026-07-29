@section('topbar')
@php
    $isCatechist = auth()->user()?->usesCatechistLayout() ?? false;
    $homeUrl = $isCatechist
        ? route('catechist.dashboard')
        : route('parish-admin.dashboard');
@endphp
<x-breadcrumb :items="[
        ['label' => 'Trang chủ', 'url' => $homeUrl],
        ['label' => 'Kết quả học tập'],
    ]" />
@endsection

@php
    $isCatechist = auth()->user()?->usesCatechistLayout() ?? false;
@endphp

<div class="min-h-screen bg-apple-gray p-2 sm:p-4 lg:p-6" style="min-height: calc(100vh - 56px - var(--bottom-offset));">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="mx-auto max-w-7xl">
        <x-mac-panel :overflow="true">
            <x-page-header
                title="Kết quả học tập"
                :description="$isCatechist
                    ? (($canBrowseAllScoreClasses ?? false)
                        ? ($canEditScores ? 'Nhập điểm toàn giáo xứ' : 'Xem điểm toàn giáo xứ')
                        : 'Xem điểm theo lớp được phân công')
                    : ($canEditScores ? 'Nhập và quản lý điểm học sinh theo lớp và học kỳ' : 'Xem điểm học sinh theo lớp và học kỳ')"
                icon-type="score" />

            @include('livewire.score.partials.filters')

            @include('livewire.score.partials.tabs')

            @if($activeTab === 'scores')
            @include('livewire.score.partials.tab-scores')
            @endif

            @if($activeTab === 'config')
            @include('livewire.score.partials.tab-config')
            @endif

            @if($activeTab === 'weights' && $canManageScoreConfig)
            @include('livewire.score.partials.tab-weights')
            @endif

        </x-mac-panel>

        @if($isCatechist && $viewingStudent)
        @include('livewire.score.partials.student-detail-modal')
        @endif

        @if($activeTab === 'scores')
        @include('livewire.score.partials.score-input-keyboard')
        @endif

        @if($showScoreTypeForm)
        @include('livewire.score.partials.score-type-modal')
        @endif
    </div>
</div>
