@php $input = "w-full px-3 py-2 rounded-xl border border-slate-300 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"; @endphp

@section('topbar')
<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => route('parishioners.dashboard')],
    ['label' => 'Rao hôn phối', 'url' => route('marriage-announcements.index')],
    ['label' => $announcement->name, 'url' => route('marriage-announcements.show', $announcement->id)],
    ['label' => 'Tạo hôn phối'],
]" />
@endsection

<div class="min-h-screen bg-apple-gray p-2 sm:p-4 lg:p-6" style="min-height: calc(100vh - 56px - var(--bottom-offset));">
    <div class="mx-auto max-w-3xl space-y-6">
        <x-mac-panel :overflow="true">
            <div class="p-4 lg:p-6 mac-hairline-b">
                <h1 class="text-xl font-bold text-slate-900">Tạo hôn phối chính thức</h1>
                <p class="text-sm text-slate-500 mt-1">Từ hồ sơ rao: {{ $announcement->name }}</p>
            </div>

            <form wire:submit.prevent="save" class="p-4 lg:p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Ngày hôn phối</label>
                        <input wire:model.defer="married_date" type="date" class="{{ $input }}" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Số chứng chỉ</label>
                        <input wire:model.defer="certificate_number" type="text" class="{{ $input }}" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Linh mục chứng hôn</label>
                        <input wire:model.defer="priest_witness" type="text" class="{{ $input }}" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Trạng thái hôn phối (giáo hội)</label>
                        <select wire:model.defer="marriage_status" class="{{ $input }}">
                            <option value="valid">Hợp lệ</option>
                            <option value="invalid">Không hợp lệ</option>
                            <option value="widowed">Góa</option>
                            <option value="divorced">Ly hôn</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nơi hôn phối (tên xứ)</label>
                        <input wire:model.defer="marriage_parish_name" type="text" class="{{ $input }}" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Tỉnh/TP</label>
                        <input wire:model.defer="place_province" type="text" class="{{ $input }}" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nhân chứng 1</label>
                        <input wire:model.defer="witness_1" type="text" class="{{ $input }}" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nhân chứng 2</label>
                        <input wire:model.defer="witness_2" type="text" class="{{ $input }}" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Ghi chú</label>
                        <textarea wire:model.defer="note" rows="3" class="{{ $input }}"></textarea>
                    </div>
                </div>

                @if($marriage_status === 'valid')
                <p class="text-xs text-slate-500 bg-emerald-50 border border-emerald-100 rounded-xl px-3 py-2">
                    Khi lưu hôn phối hợp lệ, hệ thống sẽ tự động tạo gia đình mới với bên nam làm chủ hộ.
                </p>
                @endif

                <div class="flex justify-end gap-3 pt-4 mac-hairline-t">
                    <x-button as="a" href="{{ route('marriage-announcements.show', $announcement->id) }}" variant="outline">Hủy</x-button>
                    <x-button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">Lưu hôn phối</span>
                        <span wire:loading wire:target="save">Đang lưu...</span>
                    </x-button>
                </div>
            </form>
        </x-mac-panel>
    </div>

    @if($showSuccessModal)
    <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" wire:click="closeSuccessModal">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 space-y-4" wire:click.stop>
            <div class="text-center">
                <div class="w-14 h-14 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h2 class="text-lg font-bold text-slate-900">Hôn phối đã được ghi nhận</h2>
                <p class="text-sm text-slate-500 mt-1">
                    @if($createdFamilyId)
                    Gia đình mới đã được tạo tự động. Bên nam và bên nữ đã được gán vào gia đình.
                    @else
                    Hồ sơ hôn phối đã lưu thành công.
                    @endif
                </p>
                @if(count($processWarnings))
                <div class="mt-4 text-left space-y-2">
                    @foreach($processWarnings as $warning)
                    <p class="text-sm text-amber-700 bg-amber-50 border border-amber-100 rounded-lg px-3 py-2">{{ $warning }}</p>
                    @endforeach
                </div>
                @endif
            </div>
            <div class="flex flex-col gap-2">
                @if($createdFamilyId)
                <x-button as="a" href="{{ route('families.show', $createdFamilyId) }}" variant="primary" class="w-full justify-center">
                    Xem gia đình mới
                </x-button>
                @endif
                @if($groomId)
                <x-button as="a" href="{{ route('parishioners.show', $groomId) }}?tab=marriage" variant="outline" class="w-full justify-center">
                    Xem hồ sơ bên nam
                </x-button>
                @endif
                <x-button type="button" wire:click="closeSuccessModal" variant="outline" class="w-full justify-center">
                    Đóng
                </x-button>
            </div>
        </div>
    </div>
    @endif
</div>
