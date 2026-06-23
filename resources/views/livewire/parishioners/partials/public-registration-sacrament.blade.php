<div class="space-y-4">
    <p class="text-xs text-slate-500 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3">
        Bí tích là tùy chọn. Bạn có thể bỏ qua bước này nếu chưa có đủ thông tin.
    </p>

    @if(!empty($groupedPendingSacraments))
    <div class="space-y-2">
        @foreach($groupedPendingSacraments as $type => $group)
            @if(!empty($group['records']))
            <div class="rounded-xl border border-slate-200 p-3">
                <p class="text-sm font-semibold text-slate-800">{{ $group['label'] ?? ($typeOptions[$type] ?? $type) }}</p>
                @foreach($group['records'] as $item)
                <p class="text-xs text-slate-600 mt-1">
                    @if(!empty($item['received_date'])) Ngày: {{ $item['received_date'] }} @endif
                    @if(!empty($item['parish_name'])) — {{ $item['parish_name'] }} @endif
                </p>
                @endforeach
            </div>
            @endif
        @endforeach
    </div>
    @endif

    @if($showSacramentForm)
    <div class="rounded-xl border border-primary-200 bg-primary-50/50 p-4 space-y-3">
        @include('livewire.parishioners.partials.forms.sacrament-form-fields')
        <div class="flex gap-2">
            <button type="button" wire:click="addPendingSacrament"
                class="px-4 py-2 rounded-xl bg-primary-600 text-white text-sm font-semibold">
                {{ $editingSacramentIndex !== null ? 'Cập nhật' : 'Thêm' }}
            </button>
            <button type="button" wire:click="closeSacramentForm"
                class="px-4 py-2 rounded-xl border border-slate-300 text-sm">
                Hủy
            </button>
        </div>
    </div>
    @else
    <button type="button" wire:click="openSacramentForm"
        class="w-full py-2.5 rounded-xl border border-dashed border-primary-300 text-primary-700 text-sm font-semibold hover:bg-primary-50">
        + Thêm bí tích
    </button>
    @endif
</div>
