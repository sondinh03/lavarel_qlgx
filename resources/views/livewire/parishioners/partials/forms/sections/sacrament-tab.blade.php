<div class="space-y-6">
    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
        Bí tích sẽ được lưu khi bạn bấm <strong>Lưu</strong> ở cuối form.
    </div>

    @foreach($groupedPendingSacraments as $type => $group)
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden" wire:key="pending-group-{{ $type }}">
        <div class="flex items-center justify-between px-5 py-3 bg-slate-50 border-b border-slate-200">
            <div class="flex items-center gap-2">
                <span class="text-sm font-semibold text-slate-900">{{ $group['label'] }}</span>
                @if(count($group['records']) > 0)
                <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-primary-100 text-primary-700">
                    {{ count($group['records']) }} bản ghi
                </span>
                @else
                <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-slate-200 text-slate-600">Chưa có</span>
                @endif
            </div>

            @if($group['multiple'] || count($group['records']) === 0)
            <button type="button" wire:click="openSacramentForm('{{ $type }}')"
                class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium bg-primary-500 hover:bg-primary-600 text-white rounded-xl transition-all">
                Thêm
            </button>
            @endif
        </div>

        @if(count($group['records']) > 0)
        <div class="divide-y divide-slate-100">
            @foreach($group['records'] as $sacrament)
            <div class="flex items-start justify-between px-5 py-4" wire:key="pending-sacrament-{{ $sacrament['_index'] }}">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-x-6 gap-y-2 flex-1 text-sm">
                    @if(!empty($sacrament['received_date']))
                    <div>
                        <p class="text-xs text-slate-500">Ngày lãnh nhận</p>
                        <p class="font-medium text-slate-900">{{ \Carbon\Carbon::parse($sacrament['received_date'])->format('d/m/Y') }}</p>
                    </div>
                    @endif

                    @if(!empty($sacrament['certificate_number']) || !empty($sacrament['book_number']))
                    <div>
                        <p class="text-xs text-slate-500">Số chứng chỉ / sách</p>
                        <p class="font-medium text-slate-900">
                            {{ $sacrament['certificate_number'] ?? '' }}
                            @if(!empty($sacrament['book_number'])) · Sách {{ $sacrament['book_number'] }} @endif
                        </p>
                    </div>
                    @endif

                    @if(!empty($sacrament['giver']))
                    <div>
                        <p class="text-xs text-slate-500">Người ban</p>
                        <p class="font-medium text-slate-900">{{ $sacrament['giver'] }}</p>
                    </div>
                    @endif

                    @if(!empty($sacrament['parish_name']))
                    <div>
                        <p class="text-xs text-slate-500">Nơi lãnh nhận</p>
                        <p class="font-medium text-slate-900">{{ $sacrament['parish_name'] }}</p>
                    </div>
                    @endif

                    @if(!empty($sacrament['note']))
                    <div class="sm:col-span-2 md:col-span-4">
                        <p class="text-xs text-slate-500">Ghi chú</p>
                        <p class="text-slate-700">{{ $sacrament['note'] }}</p>
                    </div>
                    @endif
                </div>

                <div class="flex items-center gap-3 ml-4 flex-shrink-0 text-sm">
                    <button type="button" wire:click="editPendingSacrament({{ $sacrament['_index'] }})"
                        class="text-primary-600 hover:text-primary-700 font-medium">Sửa</button>
                    <button type="button" wire:click="removePendingSacrament({{ $sacrament['_index'] }})"
                        class="text-xs text-red-500 hover:text-red-600 font-medium">Xóa</button>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="px-5 py-4 text-sm text-slate-500 italic">Chưa có thông tin</div>
        @endif
    </div>
    @endforeach

    @if($showSacramentForm)
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-5">
        <h4 class="text-sm font-semibold text-slate-900">
            {{ $editingSacramentIndex !== null ? 'Cập nhật bí tích trong danh sách' : 'Thêm bí tích vào danh sách' }}
        </h4>

        @include('livewire.parishioners.partials.forms.sacrament-form-fields')

        <div class="flex justify-end gap-3">
            <button type="button" wire:click="closeSacramentForm"
                class="px-4 py-2 text-sm text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition">
                Hủy
            </button>
            <button type="button" wire:click="addPendingSacrament" wire:loading.attr="disabled"
                class="px-6 py-2 text-sm text-white bg-primary-500 hover:bg-primary-600 rounded-xl transition disabled:opacity-60">
                <span wire:loading.remove wire:target="addPendingSacrament">
                    {{ $editingSacramentIndex !== null ? 'Cập nhật danh sách' : 'Thêm vào danh sách' }}
                </span>
                <span wire:loading wire:target="addPendingSacrament">Đang xử lý...</span>
            </button>
        </div>
    </div>
    @endif
</div>
