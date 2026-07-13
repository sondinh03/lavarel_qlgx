@section('topbar')
@php
    $user = auth()->user();
    $homeUrl = match (true) {
        $user?->canManageParishioners() => route('parishioners.dashboard'),
        $user?->usesCatechistLayout() => route('catechist.dashboard'),
        $user?->canManageCatechism() => route('parish-admin.dashboard'),
        default => route('landing'),
    };
    $crumbItems = [
        ['label' => 'Trang chủ', 'url' => $homeUrl],
    ];
    if ($user?->canManageParishioners()) {
        $crumbItems[] = ['label' => 'Quản lý giáo dân', 'url' => route('parishioners.index')];
    } elseif ($user?->canManageCatechism() || $user?->isCatechist()) {
        $crumbItems[] = ['label' => 'Học sinh', 'url' => route('students.index')];
    }
    $crumbItems[] = ['label' => $parishioner->full_name_with_saint];
@endphp
<x-breadcrumb :items="$crumbItems" />
@endsection

<div class="min-h-screen bg-slate-50 p-2 sm:p-4 lg:p-6" style="min-height: calc(100vh - 56px - var(--bottom-offset));">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="mx-auto max-w-7xl space-y-6">

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

            {{-- Profile summary --}}
            <div class="p-4 lg:p-6 border-b border-slate-200">
                <div class="flex flex-col sm:flex-row gap-4 sm:items-start justify-between">
                    <div class="flex items-start gap-4 flex-1 min-w-0">
                        @if($parishioner->avatar_path)
                        <img src="{{ $parishioner->avatar_url }}"
                            alt="{{ $parishioner->full_name }}"
                            class="w-20 h-20 rounded-2xl object-cover shadow-sm ring-4 ring-primary-50 flex-shrink-0">
                        @else
                        <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700
                            text-white flex items-center justify-center text-2xl font-bold
                            shadow-sm ring-4 ring-primary-50 flex-shrink-0">
                            {{ mb_substr($parishioner->full_name, 0, 1, 'UTF-8') }}
                        </div>
                        @endif

                        <div class="flex-1 min-w-0">
                            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 truncate">
                                {{ $parishioner->full_name_with_saint }}
                            </h1>
                            <div class="flex flex-wrap items-center gap-2 sm:gap-3 text-sm mt-1">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $parishioner->gender === 'male' ? 'bg-primary-100 text-primary-700' : 'bg-pink-100 text-pink-700' }}">
                                    {{ $parishioner->gender_name }}
                                </span>
                                @if($parishioner->age)
                                <span class="hidden sm:inline text-slate-300">|</span>
                                <span class="text-slate-600">{{ $parishioner->age }} tuổi</span>
                                @endif
                                @if($parishioner->birth_order)
                                <span class="hidden sm:inline text-slate-300">|</span>
                                <span class="text-slate-500">Con thứ {{ $parishioner->birth_order }}</span>
                                @endif
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $parishioner->status ? 'bg-green-100 text-green-700' : 'bg-slate-200 text-slate-500' }}">
                                    {{ $parishioner->status_name }}
                                </span>
                                @if(!$parishioner->is_active)
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-orange-100 text-orange-700">Không sinh hoạt</span>
                                @endif
                                @if($parishioner->is_new_convert)
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">Tân tòng</span>
                                @endif
                                @if($parishioner->is_deceased)
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-700 text-white">
                                    Đã qua đời {{ $parishioner->death_date?->format('d/m/Y') }}
                                </span>
                                @endif
                            </div>
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-slate-600 mt-2">
                                @if($parishioner->phone)
                                <span class="flex items-center gap-1.5">
                                    <x-icon name="phone" class="w-4 h-4 text-slate-400" />
                                    {{ $parishioner->phone }}
                                </span>
                                @endif
                                @if($parishioner->parishGroup?->name)
                                <span>{{ $parishioner->parishGroup->name }}</span>
                                @endif
                                @if($parishioner->association?->name)
                                <span>{{ $parishioner->association->name }}</span>
                                @endif
                                @if($parishioner->parish?->name)
                                <span>{{ $parishioner->parish->name }}</span>
                                @endif
                                <span>{{ $parishioner->married_status_name }}</span>
                            </div>
                        </div>
                    </div>

                    @can('update', $parishioner)
                    <div class="flex items-center gap-2 flex-shrink-0 flex-wrap">
                        <x-button as="a" href="{{ route('parishioners.export-lylich', $parishioner) }}" variant="outline">
                            <x-icon name="download" />
                            Lý lịch cá nhân
                        </x-button>
                        <x-button type="button" variant="outline" wire:click="openDonXinRuaToiModal">
                            <x-icon name="download" />
                            Đơn xin rửa tội
                        </x-button>
                        @can('delete', $parishioner)
                        <x-button variant="danger" wire:click="$set('showDeleteConfirm', true)">
                            <x-icon name="trash" />
                            Xóa
                        </x-button>
                        @endcan
                    </div>
                    @endcan
                </div>
            </div>

            {{-- Tabs --}}
            <div class="px-4 lg:px-6 py-4 border-b border-slate-200 bg-slate-50/70 overflow-x-auto">
                <div class="inline-flex w-max max-w-full rounded-xl bg-slate-200 p-1 text-sm font-medium">
                    @foreach([
                        'basic' => 'Cơ bản & Địa chỉ',
                        'parish' => 'Sinh hoạt xứ',
                        'sacrament' => 'Bí tích',
                        'marriage' => 'Hôn phối',
                        'family' => 'Gia đình',
                        'deceased' => 'Tử vong',
                    ] as $tab => $label)
                    <button wire:click="goToTab('{{ $tab }}')" type="button"
                        class="inline-flex items-center justify-center gap-2 px-3 sm:px-4 py-2.5 rounded-lg transition-all whitespace-nowrap
                            {{ $activeTab === $tab
                                ? 'bg-white shadow-sm text-primary-600 font-semibold'
                                : 'text-slate-600 hover:text-primary-600 hover:bg-white/50' }}">
                        {{ $label }}
                        @if($tab === 'deceased' && $parishioner->is_deceased)
                        <span class="w-2 h-2 rounded-full bg-slate-500 inline-block"></span>
                        @endif
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Tab content --}}
            <div class="p-4 lg:p-6 space-y-6">
                @if($activeTab === 'basic')
                    @include('livewire.parishioners.partials.show.tab-basic')
                @elseif($activeTab === 'parish')
                    @include('livewire.parishioners.partials.show.tab-parish')
                @elseif($activeTab === 'sacrament')
                    @include('livewire.parishioners.partials.show.tab-sacrament')
                @elseif($activeTab === 'marriage')
                    @include('livewire.parishioners.partials.show.tab-marriage')
                @elseif($activeTab === 'family')
                    @include('livewire.parishioners.partials.show.tab-family')
                @elseif($activeTab === 'deceased')
                    @include('livewire.parishioners.partials.show.tab-deceased')
                @endif
            </div>
        </div>
    </div>

    @include('livewire.parishioners.partials.modals.edit-modals')

    @if($showDeleteConfirm)
    <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 space-y-4">
            <h3 class="font-bold text-slate-900">Xóa giáo dân?</h3>
            <p class="text-sm text-slate-500">Bạn có chắc muốn xóa <strong>{{ $parishioner->full_name_with_saint }}</strong>?</p>
            <div class="flex justify-end gap-3">
                <button wire:click="$set('showDeleteConfirm', false)" class="px-4 py-2 text-sm border rounded-xl">Hủy</button>
                <button wire:click="delete" wire:loading.attr="disabled" class="px-4 py-2 text-sm text-white bg-red-600 rounded-xl">Xóa</button>
            </div>
        </div>
    </div>
    @endif

    @if($showDonXinRuaToiModal)
    <div class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        wire:click="$set('showDonXinRuaToiModal', false)">
        <div class="bg-white/90 backdrop-blur-xl rounded-2xl border border-black/[0.06] shadow-mac
            w-full max-w-lg max-h-[90vh] flex flex-col overflow-hidden"
            wire:click.stop>
            <div class="flex-shrink-0 px-6 py-5 border-b border-black/[0.06]">
                <h2 class="text-xl font-bold text-slate-900">Xuất đơn xin rửa tội</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Nhập tên người được rửa tội, con thứ, ngày sinh, nơi sinh và người đỡ đầu. Các thông tin còn lại lấy từ hồ sơ giáo dân.
                </p>
            </div>

            <div class="flex-1 overflow-y-auto p-6 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">
                        Tên thánh, họ tên người được rửa tội <span class="text-red-500 normal-case">*</span>
                    </label>
                    <input type="text"
                        wire:model.defer="baptism_candidate_name"
                        class="w-full h-11 px-4 py-2.5 bg-white/80 backdrop-blur-sm border border-black/[0.06] rounded-xl
                            text-sm text-slate-900 shadow-mac-sm
                            focus:outline-none focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40"
                        placeholder="VD: Gioan Nguyễn Văn A">
                    @error('baptism_candidate_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">
                        Con thứ <span class="text-red-500 normal-case">*</span>
                    </label>
                    <input type="number" min="1" max="99"
                        wire:model.defer="baptism_candidate_birth_order"
                        class="w-full h-11 px-4 py-2.5 bg-white/80 backdrop-blur-sm border border-black/[0.06] rounded-xl
                            text-sm text-slate-900 shadow-mac-sm
                            focus:outline-none focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40"
                        placeholder="VD: 1">
                    @error('baptism_candidate_birth_order')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">
                        Ngày sinh <span class="text-red-500 normal-case">*</span>
                    </label>
                    <input type="date"
                        wire:model.defer="baptism_candidate_birthday"
                        class="w-full h-11 px-4 py-2.5 bg-white/80 backdrop-blur-sm border border-black/[0.06] rounded-xl
                            text-sm text-slate-900 shadow-mac-sm
                            focus:outline-none focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40">
                    @error('baptism_candidate_birthday')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">
                        Nơi sinh <span class="text-red-500 normal-case">*</span>
                    </label>
                    <input type="text"
                        wire:model.defer="baptism_candidate_birth_place"
                        class="w-full h-11 px-4 py-2.5 bg-white/80 backdrop-blur-sm border border-black/[0.06] rounded-xl
                            text-sm text-slate-900 shadow-mac-sm
                            focus:outline-none focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40"
                        placeholder="VD: Giáo xứ / địa chỉ nơi sinh">
                    @error('baptism_candidate_birth_place')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">
                        Tên thánh, họ tên người đỡ đầu <span class="text-red-500 normal-case">*</span>
                    </label>
                    <input type="text"
                        wire:model.defer="godparent_name"
                        class="w-full h-11 px-4 py-2.5 bg-white/80 backdrop-blur-sm border border-black/[0.06] rounded-xl
                            text-sm text-slate-900 shadow-mac-sm
                            focus:outline-none focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40"
                        placeholder="VD: Giuse Trần Văn B">
                    @error('godparent_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex-shrink-0 px-6 py-4 border-t border-black/[0.06] bg-slate-50/70 flex justify-end gap-3">
                <x-button type="button" variant="outline" wire:click="$set('showDonXinRuaToiModal', false)">
                    Hủy
                </x-button>
                <x-button type="button" variant="primary"
                    wire:click="exportDonXinRuaToi"
                    wire:loading.attr="disabled"
                    wire:target="exportDonXinRuaToi">
                    <span wire:loading.remove wire:target="exportDonXinRuaToi">Xuất file</span>
                    <span wire:loading wire:target="exportDonXinRuaToi">Đang xuất…</span>
                </x-button>
            </div>
        </div>
    </div>
    @endif

    <div wire:loading.delay wire:target="saveBasic,saveAddress,saveFamily,saveParish,saveMarriage,saveDeceased,delete,exportDonXinRuaToi"
        class="fixed inset-0 bg-black/20 flex items-center justify-center z-[60]">
        <div class="bg-white rounded-xl px-6 py-4 flex items-center gap-3 shadow-lg">
            <svg class="animate-spin h-5 w-5 text-primary-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
            <span class="text-sm text-slate-700">Đang xử lý...</span>
        </div>
    </div>
</div>
