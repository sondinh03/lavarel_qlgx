@section('topbar')
<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => route('parishioners.dashboard')],
    ['label' => 'Rao hôn phối', 'url' => route('marriage-announcements.index')],
    ['label' => $isEdit ? 'Chỉnh sửa' : 'Tạo mới'],
]" />
@endsection

<div class="min-h-screen bg-slate-50 p-2 sm:p-4 lg:p-6" style="min-height: calc(100vh - 56px - var(--bottom-offset));">
    <div class="mx-auto max-w-4xl space-y-6">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-4 lg:p-6 border-b border-slate-200">
                <h1 class="text-xl font-bold text-slate-900">{{ $isEdit ? 'Chỉnh sửa rao hôn phối' : 'Tạo hồ sơ rao hôn phối' }}</h1>
            </div>

            <div class="px-4 lg:px-6 py-3 border-b border-slate-100 bg-slate-50/70 overflow-x-auto">
                <div class="inline-flex rounded-xl bg-slate-200 p-1 text-sm font-medium min-w-max">
                    @foreach(['general' => 'Thông tin chung', 'schedule' => 'Lịch rao', 'groom' => 'Bên nam', 'bride' => 'Bên nữ'] as $tab => $tabLabel)
                    <button type="button" wire:click="switchFormTab('{{ $tab }}')"
                        class="px-4 py-2 rounded-lg whitespace-nowrap transition {{ $activeTab === $tab ? 'bg-white shadow-sm text-primary-600 font-semibold' : 'text-slate-600' }}">
                        {{ $tabLabel }}
                    </button>
                    @endforeach
                </div>
            </div>

            <form wire:submit.prevent="save" class="p-4 lg:p-6 space-y-6">
                @if($errors->any())
                <div class="p-4 rounded-xl bg-red-50 border border-red-100 text-sm text-red-700">
                    <ul class="list-disc pl-4 space-y-1">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @if($activeTab === 'general')
                @include('livewire.marriage-announcement.partials.form-general')
                @elseif($activeTab === 'schedule')
                @include('livewire.marriage-announcement.partials.form-schedule')
                @elseif($activeTab === 'groom')
                @include('livewire.marriage-announcement.partials.form-participant', ['role' => 'groom'])
                @elseif($activeTab === 'bride')
                @include('livewire.marriage-announcement.partials.form-participant', ['role' => 'bride'])
                @endif

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <x-button type="button" variant="outline" wire:click="cancel">Hủy</x-button>
                    <x-button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">Lưu hồ sơ</span>
                        <span wire:loading wire:target="save">Đang lưu...</span>
                    </x-button>
                </div>
            </form>
        </div>
    </div>
</div>
