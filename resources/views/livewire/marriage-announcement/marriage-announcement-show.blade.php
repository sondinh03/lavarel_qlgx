@php
$groom = $item->groomParticipant();
$bride = $item->brideParticipant();

$formatStepDate = function (?string $value): ?string {
    if (!$value) return null;
    try {
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value)) return $value;
        return \Carbon\Carbon::parse($value)->format('d/m/Y');
    } catch (\Throwable) {
        return $value;
    }
};

$dates = [
    ['label' => 'Lần rao 1', 'value' => $item->announcements_one, 'done' => (bool) $item->announcements_one_done],
    ['label' => 'Lần rao 2', 'value' => $item->announcements_two, 'done' => (bool) $item->announcements_two_done],
    ['label' => 'Lần rao 3', 'value' => $item->announcements_three, 'done' => (bool) $item->announcements_three_done],
];
@endphp

@section('topbar')
<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => route('parishioners.dashboard')],
    ['label' => 'Rao hôn phối', 'url' => route('marriage-announcements.index')],
    ['label' => $item->name],
]" />
@endsection

<div class="min-h-screen bg-slate-50 p-2 sm:p-4 lg:p-6" style="min-height: calc(100vh - 56px - var(--bottom-offset));">
    <div class="mx-auto max-w-4xl space-y-5">

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 lg:p-6">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="text-xl font-bold text-slate-900">{{ $item->name }}</h1>
                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $item->status_badge }}">{{ $item->status_label }}</span>
                    </div>
                    <p class="text-sm text-slate-500 mt-2">
                        Linh mục: <strong class="text-slate-700">{{ $item->assignedPriest?->name ?? '—' }}</strong>
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    @if($canManage)
                    <x-button as="a" href="{{ route('marriage-announcements.edit', $item->id) }}" variant="outline" size="sm">Sửa</x-button>
                    @endif
                    @if($canCreateMarriage)
                    <x-button as="a" href="{{ route('marriage-announcements.create-marriage', $item->id) }}" variant="primary" size="sm">
                        Tạo hôn phối chính thức
                    </x-button>
                    @endif
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 lg:p-6">
            <h2 class="text-sm font-semibold text-slate-800 mb-4">Lịch rao (3 lần)</h2>
            <div class="space-y-4">
                @foreach($dates as $i => $step)
                <div class="flex items-center gap-4">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0
                        {{ $step['done'] ? 'bg-emerald-100 text-emerald-700' : ($step['value'] ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-400') }}">
                        {{ $i + 1 }}
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-800">{{ $step['label'] }}</p>
                        <p class="text-sm text-slate-500">
                            @if($formatted = $formatStepDate($step['value']))
                            {{ $formatted }}
                            @if($step['done'])
                            <span class="text-emerald-600 text-xs ml-1 font-semibold">· Đã rao</span>
                            @else
                            <span class="text-amber-600 text-xs ml-1">· Chờ rao</span>
                            @endif
                            @else
                            Chưa ghi nhận ngày
                            @endif
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        @foreach([
            ['participant' => $groom, 'title' => 'Bên nam'],
            ['participant' => $bride, 'title' => 'Bên nữ'],
        ] as $block)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/80">
                <h2 class="text-sm font-semibold text-slate-800">{{ $block['title'] }}</h2>
            </div>
            <div class="p-4 space-y-3">
                @if($block['participant']?->displayName())
                @if($block['participant']->parishioner)
                <a href="{{ route('parishioners.show', $block['participant']->idgiaodan) }}" class="text-sm font-semibold text-primary-600 hover:text-primary-700">
                    {{ $block['participant']->displayName() }}
                </a>
                @else
                <p class="text-sm font-semibold text-slate-800">
                    {{ $block['participant']->displayName() }}
                    <span class="ml-1 text-xs font-normal text-slate-400">(chưa có trong hệ thống)</span>
                </p>
                @endif
                @else
                <p class="text-sm text-slate-400 italic">Chưa có thông tin</p>
                @endif
                @if($block['participant']?->hasImpediment())
                <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold bg-red-100 text-red-700">Có ngăn trở</span>
                @endif
                @if($block['participant'])
                @foreach(['current' => 'Hiện tại', 'old' => 'Gốc', 'before' => 'Trước đó'] as $prefix => $prefixLabel)
                @php $labels = $block['participant']->parishGroupLabels($prefix); @endphp
                @if(array_filter($labels))
                <div class="text-xs text-slate-600 pt-2 border-t border-slate-100">
                    <p class="font-semibold text-slate-500 mb-1">{{ $prefixLabel }}</p>
                    <p>{{ implode(' · ', array_filter([$labels['diocese'], $labels['deanery'], $labels['management'], $labels['parish']])) }}</p>
                </div>
                @endif
                @endforeach
                @endif
            </div>
        </div>
        @endforeach

    </div>
</div>
