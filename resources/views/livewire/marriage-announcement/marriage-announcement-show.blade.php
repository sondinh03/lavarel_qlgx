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

$input = 'w-full h-11 px-4 py-2.5 bg-white/80 backdrop-blur-sm border border-black/[0.06] rounded-xl
    text-sm text-slate-900 shadow-mac-sm
    focus:outline-none focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40';
$label = 'block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase';
@endphp

@section('topbar')
<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => route('parishioners.dashboard')],
    ['label' => 'Rao hôn phối', 'url' => route('marriage-announcements.index')],
    ['label' => $item->name],
]" />
@endsection

<div class="min-h-screen bg-apple-gray p-2 sm:p-4 lg:p-6" style="min-height: calc(100vh - 56px - var(--bottom-offset));">
    <div class="mx-auto max-w-4xl space-y-5">

        <x-mac-panel :overflow="true">
            <div class="p-4 lg:p-6">
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
                        <x-button type="button" variant="outline" size="sm" wire:click="openGioiThieuHonPhoiModal">
                            <x-icon name="download" class="w-4 h-4" />
                            Giấy giới thiệu HP
                        </x-button>
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
        </x-mac-panel>

        <x-mac-panel :overflow="true">
            <div class="p-4 lg:p-6">
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
        </x-mac-panel>

        @foreach([
            ['participant' => $groom, 'title' => 'Bên nam'],
            ['participant' => $bride, 'title' => 'Bên nữ'],
        ] as $block)
        <x-mac-panel :overflow="true">
            <div class="px-4 py-3 mac-hairline-b bg-slate-50/80">
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
                <div class="text-xs text-slate-600 pt-2 mac-hairline-t">
                    <p class="font-semibold text-slate-500 mb-1">{{ $prefixLabel }}</p>
                    <p>{{ implode(' · ', array_filter([$labels['diocese'], $labels['deanery'], $labels['management'], $labels['parish']])) }}</p>
                </div>
                @endif
                @endforeach
                @endif
            </div>
        </x-mac-panel>
        @endforeach

    </div>

    @if($showGioiThieuHonPhoiModal)
    <div class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        wire:click="$set('showGioiThieuHonPhoiModal', false)">
        <div class="bg-white/90 backdrop-blur-xl rounded-2xl border border-black/[0.06] shadow-mac
            w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden"
            wire:click.stop>
            <div class="flex-shrink-0 px-6 py-5 mac-hairline-b">
                <h2 class="text-xl font-bold text-slate-900">Xuất giấy giới thiệu hôn phối</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Điền từ hồ sơ rao. Chọn đương sự (bên được giới thiệu), kiểm tra thông tin rồi xuất.
                </p>
            </div>

            <div class="flex-1 overflow-y-auto p-6 space-y-5">
                <div>
                    <label class="{{ $label }}">Đương sự (bên được giới thiệu)</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center gap-2 rounded-xl border border-black/[0.06] px-3 py-2.5 text-sm cursor-pointer
                            {{ $subject_side === 'groom' ? 'bg-primary-50 border-primary-200 text-primary-800' : 'bg-white' }}">
                            <input type="radio" wire:model="subject_side" value="groom" class="text-primary-600">
                            Bên nam
                        </label>
                        <label class="flex items-center gap-2 rounded-xl border border-black/[0.06] px-3 py-2.5 text-sm cursor-pointer
                            {{ $subject_side === 'bride' ? 'bg-primary-50 border-primary-200 text-primary-800' : 'bg-white' }}">
                            <input type="radio" wire:model="subject_side" value="bride" class="text-primary-600">
                            Bên nữ
                        </label>
                    </div>
                </div>

                <div>
                    <label class="{{ $label }}">Kính gửi Cha Chánh xứ</label>
                    <input type="text" wire:model.defer="greeting_parish" class="{{ $input }}"
                        placeholder="Để trống = giáo xứ hồ sơ rao">
                </div>

                <div class="rounded-xl border border-black/[0.06] p-4 space-y-3">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Đương sự</p>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="{{ $label }}">Xưng hô</label>
                            <select wire:model.defer="a_honorific" class="{{ $input }}">
                                <option value="Anh">Anh</option>
                                <option value="Chị">Chị</option>
                                <option value="Anh (Chị)">Anh (Chị)</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="{{ $label }}">Họ tên <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.defer="a_holy_name" class="{{ $input }}">
                            @error('a_holy_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="{{ $label }}">Ngày sinh <span class="text-red-500">*</span></label>
                            <input type="date" wire:model.defer="a_birthday" class="{{ $input }}">
                            @error('a_birthday') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="{{ $label }}">Nơi sinh</label>
                            <input type="text" wire:model.defer="a_birth_place" class="{{ $input }}">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="{{ $label }}">Tên bố</label>
                            <input type="text" wire:model.defer="a_father_name" class="{{ $input }}">
                        </div>
                        <div>
                            <label class="{{ $label }}">Tên mẹ</label>
                            <input type="text" wire:model.defer="a_mother_name" class="{{ $input }}">
                        </div>
                    </div>
                    <div>
                        <label class="{{ $label }}">Địa chỉ</label>
                        <input type="text" wire:model.defer="a_address" class="{{ $input }}">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="{{ $label }}">Giáo họ</label>
                            <input type="text" wire:model.defer="a_parish_group" class="{{ $input }}">
                        </div>
                        <div>
                            <label class="{{ $label }}">Giáo xứ</label>
                            <input type="text" wire:model.defer="a_parish" class="{{ $input }}">
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-black/[0.06] p-4 space-y-3">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Người kết bạn</p>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="{{ $label }}">Xưng hô</label>
                            <select wire:model.defer="b_honorific" class="{{ $input }}">
                                <option value="Anh">Anh</option>
                                <option value="Chị">Chị</option>
                                <option value="Anh (Chị)">Anh (Chị)</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="{{ $label }}">Họ tên <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.defer="b_holy_name" class="{{ $input }}">
                            @error('b_holy_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="{{ $label }}">Ngày sinh <span class="text-red-500">*</span></label>
                            <input type="date" wire:model.defer="b_birthday" class="{{ $input }}">
                            @error('b_birthday') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="{{ $label }}">Nơi sinh</label>
                            <input type="text" wire:model.defer="b_birth_place" class="{{ $input }}">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="{{ $label }}">Tên bố</label>
                            <input type="text" wire:model.defer="b_father_name" class="{{ $input }}">
                        </div>
                        <div>
                            <label class="{{ $label }}">Tên mẹ</label>
                            <input type="text" wire:model.defer="b_mother_name" class="{{ $input }}">
                        </div>
                    </div>
                    <div>
                        <label class="{{ $label }}">Địa chỉ</label>
                        <input type="text" wire:model.defer="b_address" class="{{ $input }}">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="{{ $label }}">Giáo họ</label>
                            <input type="text" wire:model.defer="b_parish_group" class="{{ $input }}">
                        </div>
                        <div>
                            <label class="{{ $label }}">Giáo xứ</label>
                            <input type="text" wire:model.defer="b_parish" class="{{ $input }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex-shrink-0 px-6 py-4 mac-hairline-t bg-slate-50/70 flex justify-end gap-3">
                <x-button type="button" variant="outline" wire:click="$set('showGioiThieuHonPhoiModal', false)">Hủy</x-button>
                <x-button type="button" variant="primary"
                    wire:click="exportGioiThieuHonPhoi"
                    wire:loading.attr="disabled"
                    wire:target="exportGioiThieuHonPhoi">
                    <span wire:loading.remove wire:target="exportGioiThieuHonPhoi">Xuất file</span>
                    <span wire:loading wire:target="exportGioiThieuHonPhoi">Đang xuất…</span>
                </x-button>
            </div>
        </div>
    </div>
    @endif
</div>
