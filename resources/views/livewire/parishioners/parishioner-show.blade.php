<div class="min-h-screen bg-slate-50 p-4 sm:p-6">
    <div class="mx-auto max-w-5xl space-y-5">

        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[
            ['label' => 'Trang chủ', 'url' => route('dashboard')],
            ['label' => 'Giáo dân', 'url' => route('parishioners.index')],
            ['label' => $parishioner->full_name_with_saint],
        ]" />

        {{-- Toast --}}
        @if(session('message'))
        <x-toast-notification type="success">{{ session('message') }}</x-toast-notification>
        @endif
        @if(session('error'))
        <x-toast-notification type="error">{{ session('error') }}</x-toast-notification>
        @endif

        {{-- ===== HEADER CARD ===== --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="bg-gradient-to-r from-primary-600 to-primary-500 h-24"></div>

            <div class="px-6 pb-6">
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 -mt-12">

                    {{-- Avatar --}}
                    <div class="flex items-end gap-4">
                        <div class="w-24 h-24 rounded-2xl border-4 border-white shadow-md overflow-hidden bg-slate-100 flex items-center justify-center flex-shrink-0">
                            @if($parishioner->avatar_path)
                            <img src="{{ asset('storage/' . $parishioner->avatar_path) }}"
                                alt="{{ $parishioner->full_name }}"
                                class="w-full h-full object-cover" />
                            @else
                            <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            @endif
                        </div>

                        <div class="mb-1">
                            <h1 class="text-xl font-bold text-slate-900 leading-tight">
                                {{ $parishioner->full_name_with_saint }}
                            </h1>
                            <div class="flex flex-wrap items-center gap-2 mt-1">
                                {{-- Gender badge: dùng màu semantic (blue/pink), không phải primary --}}
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold
                                    {{ $parishioner->gender === 'male' ? 'bg-primary-100 text-primary-700' : 'bg-pink-100 text-pink-700' }}">
                                    {{ $parishioner->gender_name }}
                                </span>
                                @if($parishioner->age)
                                <span class="text-sm text-slate-500">{{ $parishioner->age }} tuổi</span>
                                @endif
                                {{-- Status badge: green = active, slate = inactive --}}
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold
                                    {{ $parishioner->status ? 'bg-green-100 text-green-700' : 'bg-slate-200 text-slate-500' }}">
                                    {{ $parishioner->status_name }}
                                </span>
                                @if($parishioner->is_new_convert)
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">
                                    Tân tòng
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-2 sm:mb-1">
                        <a href="{{ route('parishioners.index') }}"
                            class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition">
                            ← Quay lại
                        </a>
                        <button wire:click="$set('showDeleteConfirm', true)"
                            class="px-4 py-2 text-sm font-medium text-red-600 bg-red-50 border border-red-200 rounded-xl hover:bg-red-100 transition">
                            Xóa
                        </button>
                    </div>
                </div>

                {{-- Quick info --}}
                <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4 pt-4 border-t border-slate-100">
                    <div>
                        <p class="text-xs text-slate-400 font-medium">Điện thoại</p>
                        <p class="text-sm font-semibold text-slate-800 mt-0.5">{{ $parishioner->phone ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 font-medium">Giáo họ</p>
                        <p class="text-sm font-semibold text-slate-800 mt-0.5">{{ $parishioner->parishGroup?->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 font-medium">Giáo xứ</p>
                        <p class="text-sm font-semibold text-slate-800 mt-0.5">{{ $parishioner->parish?->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 font-medium">Hôn nhân</p>
                        <p class="text-sm font-semibold text-slate-800 mt-0.5">{{ $parishioner->married_status_name }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== TABS ===== --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

            {{-- Tab Nav --}}
            <div class="border-b border-slate-200 px-6">
                <nav class="flex gap-1 -mb-px overflow-x-auto">
                    @foreach([
                    'basic' => ['label' => 'Cơ bản & Địa chỉ', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                    'sacrament' => ['label' => 'Bí tích', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                    'marriage' => ['label' => 'Hôn phối', 'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'],
                    'family' => ['label' => 'Gia đình', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                    ] as $tab => $config)
                    <button wire:click="goToTab('{{ $tab }}')"
                        class="flex items-center gap-2 px-4 py-3.5 text-sm font-medium border-b-2 transition whitespace-nowrap
                            {{ $activeTab === $tab
                                ? 'border-primary-500 text-primary-600'
                                : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $config['icon'] }}" />
                        </svg>
                        {{ $config['label'] }}
                    </button>
                    @endforeach
                </nav>
            </div>

            {{-- Tab Content --}}
            <div class="p-6">

                {{-- ===== TAB: CƠ BẢN & ĐỊA CHỈ ===== --}}
                @if($activeTab === 'basic')
                <div class="space-y-6">

                    {{-- Thông tin cơ bản --}}
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-base font-semibold text-slate-800">Thông tin cơ bản</h3>
                        <button wire:click="openEditBasic"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-primary-600 bg-primary-50 rounded-lg hover:bg-primary-100 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Chỉnh sửa
                        </button>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-4">
                        @include('livewire.parishioners.partials.info-item', ['label' => 'Họ và tên', 'value' => $parishioner->full_name])
                        @include('livewire.parishioners.partials.info-item', ['label' => 'Tên thánh', 'value' => $parishioner->saint?->name])
                        @include('livewire.parishioners.partials.info-item', ['label' => 'Giới tính', 'value' => $parishioner->gender_name])
                        @include('livewire.parishioners.partials.info-item', ['label' => 'Ngày sinh', 'value' => $parishioner->birthday?->format('d/m/Y')])
                        @include('livewire.parishioners.partials.info-item', ['label' => 'Tuổi', 'value' => $parishioner->age ? $parishioner->age . ' tuổi' : null])
                        @include('livewire.parishioners.partials.info-item', ['label' => 'CCCD', 'value' => $parishioner->cccd])
                        @include('livewire.parishioners.partials.info-item', ['label' => 'Điện thoại', 'value' => $parishioner->phone])
                        @include('livewire.parishioners.partials.info-item', ['label' => 'Email', 'value' => $parishioner->email])
                        @include('livewire.parishioners.partials.info-item', ['label' => 'Nghề nghiệp', 'value' => config('parishioner.career.' . $parishioner->career)])
                        @include('livewire.parishioners.partials.info-item', ['label' => 'Học vấn', 'value' => config('parishioner.education_level.' . $parishioner->education_level)])
                        @include('livewire.parishioners.partials.info-item', ['label' => 'Trình độ giáo lý', 'value' => config('parishioner.catechism_level.' . $parishioner->catechism_level)])
                        @include('livewire.parishioners.partials.info-item', ['label' => 'Chức vụ', 'value' => config('parishioner.position.' . $parishioner->position)])
                    </div>

                    @if($parishioner->note)
                    <div class="mt-2 p-3 bg-amber-50 border border-amber-100 rounded-xl text-sm text-slate-700">
                        <p class="text-xs font-semibold text-amber-600 mb-1">Ghi chú</p>
                        {{ $parishioner->note }}
                    </div>
                    @endif

                    {{-- Địa chỉ --}}
                    <div class="border-t border-slate-100 pt-5">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-base font-semibold text-slate-800">Địa chỉ</h3>
                            <button wire:click="openEditAddress"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-primary-600 bg-primary-50 rounded-lg hover:bg-primary-100 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Chỉnh sửa
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-4">
                            @include('livewire.parishioners.partials.info-item', ['label' => 'Quê quán', 'value' => $parishioner->origin])
                            @include('livewire.parishioners.partials.info-item', ['label' => 'Thường trú', 'value' => implode(', ', array_filter([$parishioner->permanent_residence, $parishioner->permanent_province]))])
                            @include('livewire.parishioners.partials.info-item', ['label' => 'Tạm trú', 'value' => implode(', ', array_filter([$parishioner->temporary_residence, $parishioner->temporary_province]))])
                        </div>
                    </div>

                </div>
                @endif

                {{-- ===== TAB: BÍ TÍCH ===== --}}
                @if($activeTab === 'sacrament')
                @livewire('parishioners.sacraments-manager',
                ['parishionerId' => $parishioner->id],
                key('sacraments-' . $parishioner->id))
                @endif

                {{-- ===== TAB: HÔN PHỐI ===== --}}
                @if($activeTab === 'marriage')
                <div>
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-base font-semibold text-slate-800">Thông tin hôn phối</h3>
                        <button wire:click="openEditMarriage"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-primary-600 bg-primary-50 rounded-lg hover:bg-primary-100 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            {{ $marriage ? 'Chỉnh sửa' : 'Thêm hôn phối' }}
                        </button>
                    </div>

                    @if($marriage)
                    <div class="space-y-4">
                        {{-- Vợ/Chồng --}}
                        @php
                        $spouse = $parishioner->gender === 'male' ? $marriage->wife : $marriage->husband;
                        $spouseLabel = $parishioner->gender === 'male' ? 'Vợ' : 'Chồng';
                        @endphp

                        @if($spouse)
                        <a href="{{ route('parishioners.show', $spouse->id) }}"
                            class="flex items-center gap-3 p-4 border border-slate-200 rounded-xl hover:border-primary-300 hover:bg-primary-50/30 transition group">
                            <div class="w-10 h-10 rounded-full overflow-hidden bg-slate-100 flex-shrink-0">
                                @if($spouse->avatar_path)
                                <img src="{{ asset('storage/' . $spouse->avatar_path) }}" class="w-full h-full object-cover" />
                                @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs text-slate-400 font-medium">{{ $spouseLabel }}</p>
                                <p class="font-semibold text-slate-900 text-sm group-hover:text-primary-700">
                                    {{ $spouse->full_name_with_saint }}
                                </p>
                            </div>
                            <svg class="w-4 h-4 text-slate-400 group-hover:text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        @endif

                        <div class="grid grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-4">
                            @include('livewire.parishioners.partials.info-item', ['label' => 'Ngày kết hôn', 'value' => $marriage->married_date?->format('d/m/Y')])
                            @include('livewire.parishioners.partials.info-item', ['label' => 'Trạng thái', 'value' => $marriage->status_name])
                            @include('livewire.parishioners.partials.info-item', ['label' => 'Số chứng chỉ', 'value' => $marriage->certificate_number])
                            @include('livewire.parishioners.partials.info-item', ['label' => 'Nơi kết hôn', 'value' => $marriage->parish?->name ?? $marriage->parish_name])
                            @include('livewire.parishioners.partials.info-item', ['label' => 'Nhân chứng 1', 'value' => $marriage->witness_1])
                            @include('livewire.parishioners.partials.info-item', ['label' => 'Nhân chứng 2', 'value' => $marriage->witness_2])
                        </div>

                        @if($marriage->note)
                        <div class="p-3 bg-slate-50 border border-slate-100 rounded-xl text-sm text-slate-700">
                            <p class="text-xs font-semibold text-slate-400 mb-1">Ghi chú</p>
                            {{ $marriage->note }}
                        </div>
                        @endif
                    </div>
                    @else
                    <div class="text-center py-12">
                        <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                        <p class="text-slate-400 text-sm">Chưa có thông tin hôn phối</p>
                        <button wire:click="openEditMarriage"
                            class="mt-3 px-4 py-2 text-sm font-medium text-primary-600 bg-primary-50 rounded-xl hover:bg-primary-100 transition">
                            Thêm hôn phối
                        </button>
                    </div>
                    @endif
                </div>
                @endif

                {{-- ===== TAB: GIA ĐÌNH ===== --}}
                @if($activeTab === 'family')
                <div class="space-y-5">

                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-semibold text-slate-800">Thông tin gia đình</h3>
                        <button wire:click="openEditFamily"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-primary-600 bg-primary-50 rounded-lg hover:bg-primary-100 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Chỉnh sửa
                        </button>
                    </div>

                    {{-- Cha / Mẹ --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach(['father' => 'Cha', 'mother' => 'Mẹ'] as $rel => $label)
                        @php $person = $parishioner->$rel; @endphp
                        <div class="border border-slate-200 rounded-xl p-4">
                            <p class="text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wide">{{ $label }}</p>
                            @if($person)
                            <a href="{{ route('parishioners.show', $person->id) }}" class="flex items-center gap-3 group">
                                <div class="w-9 h-9 rounded-full overflow-hidden bg-slate-100 flex-shrink-0">
                                    @if($person->avatar_path)
                                    <img src="{{ asset('storage/' . $person->avatar_path) }}" class="w-full h-full object-cover" />
                                    @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-900 group-hover:text-primary-700">
                                        {{ $person->full_name_with_saint }}
                                    </p>
                                    @if($person->phone)
                                    <p class="text-xs text-slate-400">{{ $person->phone }}</p>
                                    @endif
                                </div>
                            </a>
                            @elseif($parishioner->{$rel . '_name'})
                            <p class="text-sm text-slate-700">{{ $parishioner->{$rel . '_name'} }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">Chưa có hồ sơ trong hệ thống</p>
                            @else
                            <p class="text-sm text-slate-400 italic">Chưa có thông tin</p>
                            @endif
                        </div>
                        @endforeach
                    </div>

                    {{-- Hộ gia đình --}}
                    @if($parishioner->family)
                    <div class="border border-slate-200 rounded-xl p-4">
                        <p class="text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wide">Hộ gia đình</p>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ $parishioner->family->name }}</p>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    {{ $parishioner->family->member_count }} thành viên
                                    @if($parishioner->family->parishGroup)
                                    · {{ $parishioner->family->parishGroup->name }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Con cái --}}
                    @if($parishioner->children && $parishioner->children->count() > 0)
                    <div>
                        <p class="text-xs font-semibold text-slate-400 mb-3 uppercase tracking-wide">
                            Con cái ({{ $parishioner->children->count() }})
                        </p>
                        <div class="space-y-2">
                            @foreach($parishioner->children as $child)
                            <a href="{{ route('parishioners.show', $child->id) }}"
                                class="flex items-center gap-3 p-3 border border-slate-100 rounded-xl hover:border-primary-200 hover:bg-primary-50/30 transition group">
                                <div class="w-8 h-8 rounded-full overflow-hidden bg-slate-100 flex-shrink-0">
                                    @if($child->avatar_path)
                                    <img src="{{ asset('storage/' . $child->avatar_path) }}" class="w-full h-full object-cover" />
                                    @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-slate-900 group-hover:text-primary-700">
                                        {{ $child->full_name_with_saint }}
                                    </p>
                                    <p class="text-xs text-slate-400">{{ $child->age ? $child->age . ' tuổi' : '' }} · {{ $child->gender_name }}</p>
                                </div>
                                <svg class="w-4 h-4 text-slate-300 group-hover:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                </div>
                @endif

            </div>
        </div>

    </div>

    {{-- ================================================================ --}}
    {{-- MODALS                                                           --}}
    {{-- ================================================================ --}}

    {{-- ===== MODAL: Thông tin cơ bản ===== --}}
    @if($showEditBasic)
    <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" wire:click="$set('showEditBasic', false)">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-3xl max-h-[90vh] flex flex-col" wire:click.stop>
            @include('livewire.parishioners.partials.modal-header', ['title' => 'Chỉnh sửa thông tin cơ bản', 'close' => 'showEditBasic'])
            <div class="flex-1 overflow-y-auto p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @include('livewire.parishioners.partials.field-text', ['wire' => 'last_name', 'label' => 'Họ', 'required' => true])
                    @include('livewire.parishioners.partials.field-text', ['wire' => 'first_name', 'label' => 'Tên', 'required' => true])
                    @include('livewire.parishioners.partials.field-select', ['wire' => 'gender', 'label' => 'Giới tính', 'options' => ['male' => 'Nam', 'female' => 'Nữ']])
                    @include('livewire.parishioners.partials.field-date', ['wire' => 'birthday', 'label' => 'Ngày sinh'])
                    @include('livewire.parishioners.partials.field-text', ['wire' => 'cccd', 'label' => 'CCCD'])
                    @include('livewire.parishioners.partials.field-text', ['wire' => 'phone', 'label' => 'Điện thoại'])
                    @include('livewire.parishioners.partials.field-text', ['wire' => 'email', 'label' => 'Email', 'type' => 'email'])
                    @include('livewire.parishioners.partials.field-select', ['wire' => 'career', 'label' => 'Nghề nghiệp', 'options' => config('parishioner.career', []), 'nullable' => true])
                    @include('livewire.parishioners.partials.field-select', ['wire' => 'education_level', 'label' => 'Học vấn', 'options' => config('parishioner.education_level', []), 'nullable' => true])
                    @include('livewire.parishioners.partials.field-select', ['wire' => 'catechism_level', 'label' => 'Trình độ giáo lý', 'options' => config('parishioner.catechism_level', []), 'nullable' => true])
                    @include('livewire.parishioners.partials.field-select', ['wire' => 'position', 'label' => 'Chức vụ', 'options' => config('parishioner.position', []), 'nullable' => true])
                </div>
                <div class="grid grid-cols-2 gap-3 pt-2">
                    @include('livewire.parishioners.partials.field-checkbox', ['wire' => 'status', 'label' => 'Kích hoạt'])
                    @include('livewire.parishioners.partials.field-checkbox', ['wire' => 'is_active', 'label' => 'Đang sinh hoạt tại xứ'])
                    @include('livewire.parishioners.partials.field-checkbox', ['wire' => 'is_new_convert', 'label' => 'Tân tòng'])
                    @include('livewire.parishioners.partials.field-checkbox', ['wire' => 'is_included_in_stats', 'label' => 'Tính vào thống kê'])
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Ghi chú</label>
                    <textarea wire:model.defer="note" rows="3"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Ảnh đại diện</label>
                    <input wire:model="avatar" type="file" accept="image/*" class="w-full text-sm text-slate-600" />
                    @if($currentAvatarPath)
                    <img src="{{ asset('storage/' . $currentAvatarPath) }}" class="w-14 h-14 rounded-full mt-2 object-cover" />
                    @endif
                    @error('avatar') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            @include('livewire.parishioners.partials.modal-footer', ['close' => 'showEditBasic', 'save' => 'saveBasic'])
        </div>
    </div>
    @endif

    {{-- ===== MODAL: Địa chỉ ===== --}}
    @if($showEditAddress)
    <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" wire:click="$set('showEditAddress', false)">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col" wire:click.stop>
            @include('livewire.parishioners.partials.modal-header', ['title' => 'Chỉnh sửa địa chỉ', 'close' => 'showEditAddress'])
            <div class="flex-1 overflow-y-auto p-6 space-y-4">
                @include('livewire.parishioners.partials.field-text', ['wire' => 'origin', 'label' => 'Quê quán'])
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide pt-1">Thường trú</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @include('livewire.parishioners.partials.field-text', ['wire' => 'permanent_province', 'label' => 'Tỉnh/TP'])
                    @include('livewire.parishioners.partials.field-text', ['wire' => 'permanent_residence', 'label' => 'Địa chỉ chi tiết'])
                </div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide pt-1">Tạm trú</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @include('livewire.parishioners.partials.field-text', ['wire' => 'temporary_province', 'label' => 'Tỉnh/TP'])
                    @include('livewire.parishioners.partials.field-text', ['wire' => 'temporary_residence', 'label' => 'Địa chỉ chi tiết'])
                </div>
            </div>
            @include('livewire.parishioners.partials.modal-footer', ['close' => 'showEditAddress', 'save' => 'saveAddress'])
        </div>
    </div>
    @endif

    {{-- ===== MODAL: Gia đình ===== --}}
    @if($showEditFamily)
    <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" wire:click="$set('showEditFamily', false)">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col" wire:click.stop>
            @include('livewire.parishioners.partials.modal-header', ['title' => 'Chỉnh sửa gia đình', 'close' => 'showEditFamily'])
            <div class="flex-1 overflow-y-auto p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @include('livewire.parishioners.partials.field-text', ['wire' => 'father_name', 'label' => 'Tên cha'])
                    @include('livewire.parishioners.partials.field-text', ['wire' => 'mother_name', 'label' => 'Tên mẹ'])
                    @include('livewire.parishioners.partials.field-select', ['wire' => 'married', 'label' => 'Tình trạng hôn nhân', 'options' => ['0' => 'Độc thân', '1' => 'Đã kết hôn', '2' => 'Góa', '3' => 'Ly hôn']])
                </div>
                <p class="text-xs text-slate-400 mt-4">
                    Để liên kết cha/mẹ theo hồ sơ trong hệ thống, vui lòng dùng chức năng tìm kiếm (đang phát triển).
                </p>
            </div>
            @include('livewire.parishioners.partials.modal-footer', ['close' => 'showEditFamily', 'save' => 'saveFamily'])
        </div>
    </div>
    @endif

    {{-- ===== MODAL: Hôn phối ===== --}}
    @if($showEditMarriage)
    <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" wire:click="$set('showEditMarriage', false)">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col" wire:click.stop>
            @include('livewire.parishioners.partials.modal-header', ['title' => 'Chỉnh sửa hôn phối', 'close' => 'showEditMarriage'])
            <div class="flex-1 overflow-y-auto p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @include('livewire.parishioners.partials.field-date', ['wire' => 'married_date', 'label' => 'Ngày kết hôn'])
                    @include('livewire.parishioners.partials.field-select', ['wire' => 'marriage_status', 'label' => 'Trạng thái', 'options' => \App\Models\Marriage::statusOptions()])
                    @include('livewire.parishioners.partials.field-text', ['wire' => 'certificate_number', 'label' => 'Số chứng chỉ'])
                    @include('livewire.parishioners.partials.field-text', ['wire' => 'marriage_parish_name','label' => 'Nơi kết hôn'])
                    @include('livewire.parishioners.partials.field-text', ['wire' => 'witness_1', 'label' => 'Nhân chứng 1'])
                    @include('livewire.parishioners.partials.field-text', ['wire' => 'witness_2', 'label' => 'Nhân chứng 2'])
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Ghi chú</label>
                    <textarea wire:model.defer="marriage_note" rows="2"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition"></textarea>
                </div>
            </div>
            {{-- Footer riêng cho modal hôn phối vì có nút Xóa bên trái --}}
            <div class="flex items-center justify-between px-6 py-4 border-t border-slate-100 bg-slate-50 flex-shrink-0">
                @if($marriage_id)
                <button wire:click="deleteMarriage" wire:confirm="Xóa hôn phối này?"
                    class="px-4 py-2 text-sm font-medium text-red-600 bg-red-50 border border-red-200 rounded-xl hover:bg-red-100 transition">
                    Xóa hôn phối
                </button>
                @else
                <div></div>
                @endif
                <div class="flex items-center gap-3">
                    <button wire:click="$set('showEditMarriage', false)"
                        class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition">
                        Hủy
                    </button>
                    <button wire:click="saveMarriage" wire:loading.attr="disabled"
                        class="px-6 py-2 text-sm font-medium text-white bg-gradient-to-r from-primary-500 to-primary-600 rounded-xl hover:bg-primary-700 active:scale-95 shadow-sm transition disabled:opacity-60">
                        <span wire:loading.remove wire:target="saveMarriage">Lưu</span>
                        <span wire:loading wire:target="saveMarriage">Đang lưu...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ===== MODAL: Xác nhận xóa giáo dân ===== --}}
    @if($showDeleteConfirm)
    <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 space-y-4">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900">Xóa giáo dân?</h3>
                    <p class="text-sm text-slate-500 mt-1">
                        Bạn có chắc muốn xóa <strong>{{ $parishioner->full_name_with_saint }}</strong>?
                        Hành động này không thể hoàn tác.
                    </p>
                </div>
            </div>
            <div class="flex justify-end gap-3">
                <button wire:click="$set('showDeleteConfirm', false)"
                    class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition">
                    Hủy
                </button>
                <button wire:click="delete" wire:loading.attr="disabled"
                    class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-xl hover:bg-red-700 transition disabled:opacity-60">
                    <span wire:loading.remove wire:target="delete">Xóa</span>
                    <span wire:loading wire:target="delete">Đang xóa...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Loading overlay toàn trang --}}
    <div wire:loading.delay wire:target="saveBasic,saveAddress,saveFamily,saveMarriage,delete"
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