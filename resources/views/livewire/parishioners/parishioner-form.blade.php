@section('topbar')
<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => route('parishioners.dashboard')],
    ['label' => 'Quản lý giáo dân', 'url' => route('parishioners.index')],
    ['label' => $isEdit ? 'Chỉnh sửa giáo dân' : 'Thêm giáo dân mới'],
]" />
@endsection

<div class="min-h-screen bg-slate-50 p-2 sm:p-4 lg:p-6"
    style="min-height: calc(100vh - 56px - var(--bottom-offset));">
    <div class="mx-auto max-w-4xl space-y-6">

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

            <x-page-header
                class="rounded-t-2xl"
                icon-type="parishioners"
                :title="$isEdit ? 'Chỉnh sửa giáo dân' : 'Thêm giáo dân mới'"
                :description="$isEdit ? ($parishioner->full_name_with_saint ?? '') : 'Điền đầy đủ thông tin giáo dân'">
                <x-slot name="actions">
                    <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-semibold bg-primary-100 text-primary-700">
                        {{ $isEdit ? 'Chế độ sửa' : 'Tạo mới' }}
                    </span>
                </x-slot>
            </x-page-header>

            {{-- Tabs --}}
            <div class="px-4 lg:px-6 py-4 border-b border-slate-200 bg-slate-50/70 overflow-x-auto">
                <div class="inline-flex w-max max-w-full rounded-xl bg-slate-200 p-1 text-sm font-medium">
                    @foreach([
                        'basic'    => 'Cơ bản',
                        'address'  => 'Địa chỉ',
                        'classify' => 'Phân loại',
                        'parish'   => 'Sinh hoạt xứ',
                        'family'   => 'Gia đình',
                        'deceased' => 'Tử vong',
                    ] as $tab => $label)
                    <button type="button" wire:click="switchTab('{{ $tab }}')"
                        class="flex-shrink-0 inline-flex items-center justify-center px-4 py-2.5 rounded-lg transition-all
                            {{ $activeTab === $tab
                                ? 'bg-white shadow-sm text-primary-600 font-semibold'
                                : 'text-slate-600 hover:text-primary-600 hover:bg-white/50' }}">
                        {{ $label }}
                    </button>
                    @endforeach
                </div>
            </div>

            <form wire:submit.prevent="save">
                @if($errors->any())
                <div class="mx-4 lg:mx-6 mt-5 p-4 bg-red-50 border border-red-200 rounded-xl">
                    <p class="text-sm font-semibold text-red-800 mb-1">Vui lòng kiểm tra lại</p>
                    <ul class="text-sm text-red-700 space-y-0.5">
                        @foreach($errors->all() as $error)
                        <li>· {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <div class="p-4 lg:p-6 space-y-6">
                    @if($activeTab === 'basic')
                    @include('livewire.parishioners.partials.forms.basic-fields')
                    @elseif($activeTab === 'address')
                    @include('livewire.parishioners.partials.forms.address-fields')
                    @elseif($activeTab === 'classify')
                    <div class="space-y-6">
                        @include('livewire.parishioners.partials.forms.classify-fields')
                        <div>
                            <h3 class="text-sm font-semibold text-slate-800 mb-3">Trạng thái</h3>
                            @include('livewire.parishioners.partials.forms.status-fields')
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Ghi chú</label>
                            <textarea wire:model.defer="note" rows="3"
                                class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"></textarea>
                        </div>
                    </div>
                    @elseif($activeTab === 'parish')
                    @include('livewire.parishioners.partials.forms.parish-fields')
                    @elseif($activeTab === 'family')
                    @include('livewire.parishioners.partials.forms.family-fields')
                    @elseif($activeTab === 'deceased')
                    @include('livewire.parishioners.partials.forms.deceased-fields')
                    @endif
                </div>

                {{-- Sticky footer --}}
                <div class="sticky bottom-0 flex items-center justify-between gap-3 px-4 lg:px-6 py-4 border-t border-slate-200 bg-white/95 backdrop-blur">
                    <button type="button" wire:click="cancel"
                        class="px-4 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition">
                        Hủy
                    </button>
                    <button type="submit" wire:loading.attr="disabled"
                        class="px-6 py-2.5 text-sm font-medium text-white bg-primary-500 rounded-xl hover:bg-primary-600 transition disabled:opacity-60">
                        <span wire:loading.remove wire:target="save">Lưu</span>
                        <span wire:loading wire:target="save">Đang lưu...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
