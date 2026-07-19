@section('topbar')
<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => route('parishioners.dashboard')],
    ['label' => 'Quản lý giáo dân', 'url' => route('parishioners.index')],
    ['label' => 'Thêm giáo dân mới'],
]" />
@endsection

<div class="min-h-screen bg-apple-gray p-2 sm:p-4 lg:p-6"
    style="min-height: calc(100vh - 56px - var(--bottom-offset));">
    <a href="#parishioner-form-main" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="parishioner-form-main" class="mx-auto max-w-4xl space-y-6">

        <x-mac-panel :overflow="true">

            <x-page-header
                icon-type="parishioners"
                title="Thêm giáo dân mới"
                description="Điền đầy đủ thông tin giáo dân">
                <x-slot name="actions">
                    <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-semibold bg-primary-100 text-primary-700">
                        Tạo mới
                    </span>
                </x-slot>
            </x-page-header>

            {{-- Tabs --}}
            <div class="px-4 lg:px-6 py-4 mac-hairline-b bg-slate-50/70 overflow-x-auto">
                <div class="inline-flex w-max max-w-full rounded-xl bg-slate-200 p-1 text-sm font-medium">
                    @foreach([
                        'basic'   => ['label' => 'Cơ bản & Phân loại', 'icon' => 'user'],
                        'address' => ['label' => 'Địa chỉ', 'icon' => 'map'],
                        'parish'  => ['label' => 'Sinh hoạt xứ', 'icon' => 'church'],
                        'family'  => ['label' => 'Gia đình (cá nhân)', 'icon' => 'family'],
                        'sacrament'=> ['label' => 'Bí tích', 'icon' => 'church'],
                        'deceased'=> ['label' => 'Tử vong', 'icon' => 'document'],
                    ] as $tab => $meta)
                    <button type="button" wire:click="switchTab('{{ $tab }}')"
                        class="flex-shrink-0 inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-lg transition-all
                            {{ $activeTab === $tab
                                ? 'bg-white shadow-mac-sm text-primary-600 font-semibold'
                                : 'text-slate-600 hover:text-primary-600 hover:bg-white/50' }}">
                        {{ $meta['label'] }}
                    </button>
                    @endforeach
                </div>
            </div>

            <form wire:submit.prevent="save">
                @if($errors->any())
                <div class="mx-4 lg:mx-6 mt-5">
                    @include('livewire.parishioners.partials.forms.form-errors')
                </div>
                @endif

                <div class="p-4 lg:p-6 space-y-6">
                    @if($activeTab === 'basic')
                        @include('livewire.parishioners.partials.forms.sections.basic-group')
                    @elseif($activeTab === 'address')
                        <x-form-section-card title="Quê quán & Địa chỉ" icon="map">
                            @include('livewire.parishioners.partials.forms.address-fields')
                        </x-form-section-card>
                    @elseif($activeTab === 'parish')
                        <x-form-section-card title="Sinh hoạt giáo xứ" icon="church">
                            @include('livewire.parishioners.partials.forms.parish-fields')
                        </x-form-section-card>
                    @elseif($activeTab === 'family')
                        <x-form-section-card title="Thông tin cá nhân (gia đình)" icon="family">
                            @include('livewire.parishioners.partials.forms.family-fields')
                        </x-form-section-card>
                    @elseif($activeTab === 'sacrament')
                        <x-form-section-card title="Bí tích" icon="church">
                            @include('livewire.parishioners.partials.forms.sections.sacrament-tab')
                        </x-form-section-card>
                    @elseif($activeTab === 'deceased')
                        <x-form-section-card title="Thông tin tử vong" icon="document">
                            @include('livewire.parishioners.partials.forms.deceased-fields')
                        </x-form-section-card>
                    @endif
                </div>

                <div class="px-4 lg:px-6 py-4 mac-hairline-t bg-slate-50/70 flex items-center justify-end gap-3">
                    <x-button type="button" variant="outline" wire:click="cancel">
                        <x-icon name="cancel" />
                        Hủy
                    </x-button>
                    <x-button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="avatar,save">
                        <x-icon name="save" />
                        <span wire:loading.remove wire:target="save">Lưu</span>
                        <span wire:loading wire:target="save">Đang lưu...</span>
                    </x-button>
                </div>
            </form>
        </x-mac-panel>
    </div>
</div>
