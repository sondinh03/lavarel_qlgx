<div class="min-h-screen bg-slate-50 p-4 sm:p-6">
    <div class="mx-auto max-w-5xl space-y-5">

        <x-breadcrumb :items="[
            ['label' => 'Trang chủ', 'url' => route('dashboard')],
            ['label' => 'Giáo dân', 'url' => route('parishioners.index')],
            ['label' => $parishioner->full_name_with_saint],
        ]" />

        @if(session('message'))
        <x-toast-notification type="success">{{ session('message') }}</x-toast-notification>
        @endif
        @if(session('error'))
        <x-toast-notification type="error">{{ session('error') }}</x-toast-notification>
        @endif

        {{-- ===== HEADER ===== --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="bg-gradient-to-r from-primary-600 to-primary-500 h-24"></div>

            <div class="px-6 pb-6">
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 -mt-12">
                    <div class="flex items-end gap-4">
                        <div class="w-24 h-24 rounded-2xl border-4 border-white shadow-md overflow-hidden bg-slate-100 flex items-center justify-center flex-shrink-0">
                            @if($parishioner->avatar_path)
                            <img src="{{ asset('storage/' . $parishioner->avatar_path) }}" alt="{{ $parishioner->full_name }}" class="w-full h-full object-cover" />
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
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold
                                    {{ $parishioner->gender === 'male' ? 'bg-primary-100 text-primary-700' : 'bg-pink-100 text-pink-700' }}">
                                    {{ $parishioner->gender_name }}
                                </span>
                                @if($parishioner->age)
                                <span class="text-sm text-slate-500">{{ $parishioner->age }} tuổi</span>
                                @endif
                                @if($parishioner->birth_order)
                                <span class="text-sm text-slate-400">Con thứ {{ $parishioner->birth_order }}</span>
                                @endif
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold
                                    {{ $parishioner->status ? 'bg-green-100 text-green-700' : 'bg-slate-200 text-slate-500' }}">
                                    {{ $parishioner->status_name }}
                                </span>
                                @if($parishioner->is_new_convert)
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">
                                    Tân tòng
                                </span>
                                @endif
                                @if($parishioner->is_deceased)
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-700 text-white">
                                    Đã qua đời {{ $parishioner->death_date?->format('d/m/Y') }}
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
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
            <div class="border-b border-slate-200 px-6">
                <nav class="flex gap-1 -mb-px overflow-x-auto">
                    @foreach([
                        'basic'     => ['label' => 'Cơ bản & Địa chỉ',  'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                        'sacrament' => ['label' => 'Bí tích',            'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                        'marriage'  => ['label' => 'Hôn phối',           'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'],
                        'family'    => ['label' => 'Gia đình',           'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                        'deceased'  => ['label' => 'Tử vong',            'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                    ] as $tab => $config)
                    <button wire:click="goToTab('{{ $tab }}')"
                        class="flex items-center gap-2 px-4 py-3.5 text-sm font-medium border-b-2 transition whitespace-nowrap
                            {{ $activeTab === $tab
                                ? 'border-primary-500 text-primary-600'
                                : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}
                            {{ $tab === 'deceased' && $parishioner->is_deceased ? 'text-slate-700' : '' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $config['icon'] }}" />
                        </svg>
                        {{ $config['label'] }}
                        @if($tab === 'deceased' && $parishioner->is_deceased)
                        <span class="w-2 h-2 rounded-full bg-slate-500 inline-block"></span>
                        @endif
                    </button>
                    @endforeach
                </nav>
            </div>

            <div class="p-6">

                {{-- ===== TAB: CƠ BẢN ===== --}}
                @if($activeTab === 'basic')
                <div class="space-y-6">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-base font-semibold text-slate-800">Thông tin cơ bản</h3>
                        <button wire:click="openEditBasic" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-primary-600 bg-primary-50 rounded-lg hover:bg-primary-100 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Chỉnh sửa
                        </button>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-4">
                        @include('livewire.parishioners.partials.info-item', ['label' => 'Họ và tên',          'value' => $parishioner->full_name])
                        @include('livewire.parishioners.partials.info-item', ['label' => 'Tên thánh',          'value' => $parishioner->saint?->name])
                        @include('livewire.parishioners.partials.info-item', ['label' => 'Giới tính',          'value' => $parishioner->gender_name])
                        @include('livewire.parishioners.partials.info-item', ['label' => 'Ngày sinh',          'value' => $parishioner->birthday?->format('d/m/Y')])
                        @include('livewire.parishioners.partials.info-item', ['label' => 'Con thứ',            'value' => $parishioner->birth_order ? 'Con thứ ' . $parishioner->birth_order : null])
                        @include('livewire.parishioners.partials.info-item', ['label' => 'Tuổi',               'value' => $parishioner->age ? $parishioner->age . ' tuổi' : null])
                        @include('livewire.parishioners.partials.info-item', ['label' => 'CCCD',               'value' => $parishioner->cccd])
                        @include('livewire.parishioners.partials.info-item', ['label' => 'Điện thoại',         'value' => $parishioner->phone])
                        @include('livewire.parishioners.partials.info-item', ['label' => 'Email',              'value' => $parishioner->email])
                        @include('livewire.parishioners.partials.info-item', ['label' => 'Nghề nghiệp',        'value' => config('parishioner.career.' . $parishioner->career)])
                        @include('livewire.parishioners.partials.info-item', ['label' => 'Học vấn',            'value' => config('parishioner.education_level.' . $parishioner->education_level)])
                        @include('livewire.parishioners.partials.info-item', ['label' => 'Trình độ chuyên môn','value' => config('parishioner.specialist_level.' . $parishioner->specialist_level)])
                        @include('livewire.parishioners.partials.info-item', ['label' => 'Trình độ giáo lý',   'value' => config('parishioner.catechism_level.' . $parishioner->catechism_level)])
                        @include('livewire.parishioners.partials.info-item', ['label' => 'Chuyên ngành giáo lý','value' => $parishioner->catechism_major])
                        @include('livewire.parishioners.partials.info-item', ['label' => 'Chức vụ',            'value' => config('parishioner.position.' . $parishioner->position)])
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
                            <button wire:click="openEditAddress" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-primary-600 bg-primary-50 rounded-lg hover:bg-primary-100 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Chỉnh sửa
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-4">
                            @include('livewire.parishioners.partials.info-item', ['label' => 'Quê quán',  'value' => $parishioner->origin])
                            @include('livewire.parishioners.partials.info-item', ['label' => 'Thường trú','value' => implode(', ', array_filter([$parishioner->permanent_residence, $parishioner->permanent_province]))])
                            @include('livewire.parishioners.partials.info-item', ['label' => 'Tạm trú',  'value' => implode(', ', array_filter([$parishioner->temporary_residence, $parishioner->temporary_province]))])
                        </div>
                    </div>
                </div>
                @endif

                {{-- ===== TAB: BÍ TÍCH ===== --}}
                @if($activeTab === 'sacrament')
                @livewire('parishioners.sacraments-manager', ['parishionerId' => $parishioner->id], key('sacraments-' . $parishioner->id))
                @endif

                {{-- ===== TAB: HÔN PHỐI ===== --}}
                @if($activeTab === 'marriage')
                <div>
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-base font-semibold text-slate-800">Thông tin hôn phối</h3>
                        <button wire:click="openEditMarriage" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-primary-600 bg-primary-50 rounded-lg hover:bg-primary-100 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            {{ $marriage ? 'Chỉnh sửa' : 'Thêm hôn phối' }}
                        </button>
                    </div>

                    @if($marriage)
                    <div class="space-y-4">
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
                            @include('livewire.parishioners.partials.info-item', ['label' => 'Ngày kết hôn',    'value' => $marriage->married_date?->format('d/m/Y')])
                            @include('livewire.parishioners.partials.info-item', ['label' => 'Trạng thái',      'value' => $marriage->status_name])
                            @include('livewire.parishioners.partials.info-item', ['label' => 'Số chứng chỉ',   'value' => $marriage->certificate_number])
                            @include('livewire.parishioners.partials.info-item', ['label' => 'Nơi kết hôn',    'value' => $marriage->parish?->name ?? $marriage->parish_name])
                            @include('livewire.parishioners.partials.info-item', ['label' => 'Tỉnh/TP',        'value' => $marriage->place_province])
                            @include('livewire.parishioners.partials.info-item', ['label' => 'Linh mục chứng', 'value' => $marriage->priest_witness])
                            @include('livewire.parishioners.partials.info-item', ['label' => 'Nhân chứng 1',   'value' => $marriage->witness_1])
                            @include('livewire.parishioners.partials.info-item', ['label' => 'Nhân chứng 2',   'value' => $marriage->witness_2])
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
                        <button wire:click="openEditMarriage" class="mt-3 px-4 py-2 text-sm font-medium text-primary-600 bg-primary-50 rounded-xl hover:bg-primary-100 transition">
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
                        <button wire:click="openEditFamily" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-primary-600 bg-primary-50 rounded-lg hover:bg-primary-100 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Chỉnh sửa
                        </button>
                    </div>
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
                                    <p class="text-sm font-semibold text-slate-900 group-hover:text-primary-700">{{ $person->full_name_with_saint }}</p>
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

                    @if($parishioner->family)
                    <div class="border border-slate-200 rounded-xl p-4">
                        <p class="text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wide">Hộ gia đình</p>
                        <p class="text-sm font-semibold text-slate-900">{{ $parishioner->family->name }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">
                            {{ $parishioner->family->member_count }} thành viên
                            @if($parishioner->family->parishGroup)
                            · {{ $parishioner->family->parishGroup->name }}
                            @endif
                        </p>
                    </div>
                    @endif

                    @php $children = \App\Models\Parishioner::where('father_id', $parishioner->id)->orWhere('mother_id', $parishioner->id)->get(); @endphp
                    @if($children->count() > 0)
                    <div>
                        <p class="text-xs font-semibold text-slate-400 mb-3 uppercase tracking-wide">Con cái ({{ $children->count() }})</p>
                        <div class="space-y-2">
                            @foreach($children as $child)
                            <a href="{{ route('parishioners.show', $child->id) }}"
                                class="flex items-center gap-3 p-3 border border-slate-100 rounded-xl hover:border-primary-200 hover:bg-primary-50/30 transition group">
                                <div class="w-8 h-8 rounded-full overflow-hidden bg-slate-100 flex-shrink-0 flex items-center justify-center">
                                    @if($child->avatar_path)
                                    <img src="{{ asset('storage/' . $child->avatar_path) }}" class="w-full h-full object-cover" />
                                    @else
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-slate-900 group-hover:text-primary-700">{{ $child->full_name_with_saint }}</p>
                                    <p class="text-xs text-slate-400">
                                        {{ $child->birth_order ? 'Con thứ ' . $child->birth_order . ' · ' : '' }}{{ $child->age ? $child->age . ' tuổi · ' : '' }}{{ $child->gender_name }}
                                    </p>
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

                {{-- ===== TAB: TỬ VONG ===== --}}
                @if($activeTab === 'deceased')
                <div>
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-base font-semibold text-slate-800">Thông tin tử vong</h3>
                        <button wire:click="openEditDeceased" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-primary-600 bg-primary-50 rounded-lg hover:bg-primary-100 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            {{ $parishioner->is_deceased ? 'Chỉnh sửa' : 'Ghi nhận tử vong' }}
                        </button>
                    </div>

                    @if($parishioner->is_deceased)
                    <div class="p-5 bg-slate-50 border border-slate-200 rounded-xl space-y-4">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-slate-500 inline-block"></span>
                            <span class="text-sm font-semibold text-slate-700">Đã qua đời</span>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-4">
                            @include('livewire.parishioners.partials.info-item', ['label' => 'Ngày mất',    'value' => $parishioner->death_date?->format('d/m/Y')])
                            @include('livewire.parishioners.partials.info-item', ['label' => 'Số sổ mất',  'value' => $parishioner->death_book_number])
                            @include('livewire.parishioners.partials.info-item', ['label' => 'Nơi qua đời','value' => $parishioner->death_place])
                            @include('livewire.parishioners.partials.info-item', ['label' => 'Nơi an táng','value' => $parishioner->burial_place])
                        </div>
                    </div>
                    @else
                    <div class="text-center py-12">
                        <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <p class="text-slate-400 text-sm">Chưa ghi nhận thông tin tử vong</p>
                        <button wire:click="openEditDeceased" class="mt-3 px-4 py-2 text-sm font-medium text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition">
                            Ghi nhận tử vong
                        </button>
                    </div>
                    @endif
                </div>
                @endif

            </div>
        </div>

    </div>

    {{-- ===== MODAL: Thông tin cơ bản ===== --}}
    @if($showEditBasic)
    <div x-data x-show="$wire.showEditBasic"
        class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        @click="$wire.set('showEditBasic', false)" @keydown.escape.window="$wire.set('showEditBasic', false)">
        <div x-show="$wire.showEditBasic" class="bg-white rounded-2xl shadow-xl w-full max-w-3xl max-h-[90vh] flex flex-col overflow-hidden" @click.stop>

            <div class="p-6 border-b bg-gradient-to-br from-primary-50 to-white flex justify-between items-start">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">Chỉnh sửa thông tin cơ bản</h2>
                    <p class="text-sm text-slate-600 mt-1">Cập nhật thông tin cá nhân giáo dân</p>
                </div>
                <button @click="$wire.set('showEditBasic', false)" class="p-1 rounded-lg hover:bg-slate-100 text-slate-400">✕</button>
            </div>

            <div class="flex-1 overflow-y-auto p-6 space-y-5">
                @php $input = "w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"; @endphp

                <div class="border rounded-xl p-4 space-y-4">
                    <h3 class="text-sm font-bold text-slate-900">Thông tin cá nhân</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @include('livewire.parishioners.partials.field-text', ['wire' => 'last_name',   'label' => 'Họ'])
                        @include('livewire.parishioners.partials.field-text', ['wire' => 'first_name',  'label' => 'Tên'])
                        @include('livewire.parishioners.partials.field-select', ['wire' => 'gender', 'label' => 'Giới tính', 'options' => ['male' => 'Nam', 'female' => 'Nữ']])
                        @include('livewire.parishioners.partials.field-date', ['wire' => 'birthday', 'label' => 'Ngày sinh'])
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Con thứ</label>
                            <input wire:model.defer="birth_order" type="number" min="1" class="{{ $input }}" placeholder="1, 2, 3..." />
                        </div>
                    </div>
                </div>

                <div class="border rounded-xl p-4 space-y-4">
                    <h3 class="text-sm font-bold text-slate-900">Liên hệ</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @include('livewire.parishioners.partials.field-text', ['wire' => 'phone', 'label' => 'Điện thoại'])
                        @include('livewire.parishioners.partials.field-text', ['wire' => 'email', 'label' => 'Email'])
                        @include('livewire.parishioners.partials.field-text', ['wire' => 'cccd',  'label' => 'CCCD'])
                    </div>
                </div>

                <div class="border rounded-xl p-4 space-y-4">
                    <h3 class="text-sm font-bold text-slate-900">Phân loại</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Trình độ chuyên môn</label>
                            <select wire:model.defer="specialist_level" class="{{ $input }}">
                                <option value="">-- Chọn --</option>
                                @foreach(config('parishioner.specialist_level', []) as $k => $v)
                                <option value="{{ $k }}">{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Chuyên ngành giáo lý</label>
                            <input wire:model.defer="catechism_major" type="text" class="{{ $input }}" placeholder="Vd: Giáo lý hôn nhân..." />
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t bg-slate-50 flex justify-end gap-3">
                <button @click="$wire.set('showEditBasic', false)" class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition">Hủy</button>
                <button wire:click="saveBasic" class="px-5 py-2 text-sm font-medium text-white bg-primary-600 rounded-xl hover:bg-primary-700 transition">Lưu</button>
            </div>
        </div>
    </div>
    @endif

    {{-- ===== MODAL: Địa chỉ ===== --}}
    @if($showEditAddress)
    <div x-data x-show="$wire.showEditAddress"
        class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center p-4"
        @click="$wire.set('showEditAddress', false)">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl flex flex-col" @click.stop>
            <div class="p-6 border-b bg-gradient-to-br from-primary-50 flex justify-between items-center">
                <h2 class="text-xl font-bold">Địa chỉ</h2>
                <button @click="$wire.set('showEditAddress', false)" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>
            <div class="p-6 space-y-4">
                @include('livewire.parishioners.partials.field-text', ['wire' => 'origin',              'label' => 'Quê quán'])
                @include('livewire.parishioners.partials.field-text', ['wire' => 'permanent_residence', 'label' => 'Thường trú'])
                @include('livewire.parishioners.partials.field-text', ['wire' => 'permanent_province',  'label' => 'Tỉnh/TP thường trú'])
                @include('livewire.parishioners.partials.field-text', ['wire' => 'temporary_residence', 'label' => 'Tạm trú'])
                @include('livewire.parishioners.partials.field-text', ['wire' => 'temporary_province',  'label' => 'Tỉnh/TP tạm trú'])
            </div>
            <div class="p-4 border-t bg-slate-50 flex justify-end gap-2">
                <button @click="$wire.set('showEditAddress', false)" class="px-4 py-2 text-sm text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition">Hủy</button>
                <button wire:click="saveAddress" class="px-4 py-2 text-sm text-white bg-primary-600 rounded-xl hover:bg-primary-700 transition">Lưu</button>
            </div>
        </div>
    </div>
    @endif

    {{-- ===== MODAL: Gia đình ===== --}}
    @if($showEditFamily)
    <div x-data x-show="$wire.showEditFamily"
        class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        @click="$wire.set('showEditFamily', false)" @keydown.escape.window="$wire.set('showEditFamily', false)">
        <div x-show="$wire.showEditFamily" class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden" @click.stop>
            <div class="flex-shrink-0 p-6 border-b bg-gradient-to-br from-primary-50 to-white flex justify-between items-start">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">Chỉnh sửa gia đình</h2>
                    <p class="text-sm text-slate-600 mt-1">Cập nhật thông tin cha mẹ và tình trạng hôn nhân</p>
                </div>
                <button @click="$wire.set('showEditFamily', false)" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">✕</button>
            </div>
            <div class="flex-1 overflow-y-auto p-6 space-y-5">
                <div class="border border-slate-200 rounded-xl p-4 space-y-4">
                    <h3 class="text-sm font-bold text-slate-900">Cha / Mẹ</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @include('livewire.parishioners.partials.field-text', ['wire' => 'father_name', 'label' => 'Tên cha'])
                        @include('livewire.parishioners.partials.field-text', ['wire' => 'mother_name', 'label' => 'Tên mẹ'])
                    </div>
                </div>
                <div class="border border-slate-200 rounded-xl p-4 space-y-4">
                    <h3 class="text-sm font-bold text-slate-900">Hôn nhân</h3>
                    @include('livewire.parishioners.partials.field-select', ['wire' => 'married', 'label' => 'Tình trạng hôn nhân', 'options' => ['0' => 'Độc thân', '1' => 'Đã kết hôn', '2' => 'Góa', '3' => 'Ly hôn']])
                </div>
            </div>
            <div class="flex-shrink-0 px-6 py-4 border-t bg-slate-50 flex justify-end gap-3">
                <button @click="$wire.set('showEditFamily', false)" class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition">Hủy</button>
                <button wire:click="saveFamily" wire:loading.attr="disabled" class="px-5 py-2 text-sm font-medium text-white bg-primary-600 rounded-xl hover:bg-primary-700 transition disabled:opacity-60">
                    <span wire:loading.remove wire:target="saveFamily">Lưu</span>
                    <span wire:loading wire:target="saveFamily">Đang lưu...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ===== MODAL: Hôn phối ===== --}}
    @if($showEditMarriage)
    <div x-data x-show="$wire.showEditMarriage"
        class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        @click="$wire.set('showEditMarriage', false)" @keydown.escape.window="$wire.set('showEditMarriage', false)">
        <div x-show="$wire.showEditMarriage" class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden" @click.stop>

            <div class="flex-shrink-0 p-6 border-b bg-gradient-to-br from-primary-50 to-white flex justify-between items-start">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">{{ $marriage_id ? 'Chỉnh sửa hôn phối' : 'Thêm hôn phối' }}</h2>
                    <p class="text-sm text-slate-600 mt-1">Cập nhật thông tin bí tích hôn phối</p>
                </div>
                <button @click="$wire.set('showEditMarriage', false)" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">✕</button>
            </div>

            <div class="flex-1 overflow-y-auto p-6 space-y-5">
                @php $input = "w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"; @endphp

                <div class="border border-slate-200 rounded-xl p-4 space-y-4">
                    <h3 class="text-sm font-bold text-slate-900">Thông tin chính</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @include('livewire.parishioners.partials.field-date',   ['wire' => 'married_date',        'label' => 'Ngày kết hôn'])
                        @include('livewire.parishioners.partials.field-select', ['wire' => 'marriage_status',     'label' => 'Trạng thái', 'options' => \App\Models\Marriage::statusOptions()])
                        @include('livewire.parishioners.partials.field-text',   ['wire' => 'certificate_number',  'label' => 'Số chứng chỉ'])
                        @include('livewire.parishioners.partials.field-text',   ['wire' => 'marriage_parish_name','label' => 'Nơi kết hôn'])
                        @include('livewire.parishioners.partials.field-text',   ['wire' => 'place_province',      'label' => 'Tỉnh/TP nơi kết hôn'])
                    </div>
                </div>

                <div class="border border-slate-200 rounded-xl p-4 space-y-4">
                    <h3 class="text-sm font-bold text-slate-900">Người chứng</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @include('livewire.parishioners.partials.field-text', ['wire' => 'priest_witness', 'label' => 'Linh mục chứng hôn'])
                        @include('livewire.parishioners.partials.field-text', ['wire' => 'witness_1',      'label' => 'Nhân chứng 1'])
                        @include('livewire.parishioners.partials.field-text', ['wire' => 'witness_2',      'label' => 'Nhân chứng 2'])
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700">Ghi chú</label>
                    <textarea wire:model.defer="marriage_note" rows="2" class="{{ $input }}"></textarea>
                </div>
            </div>

            <div class="flex-shrink-0 px-6 py-4 border-t bg-slate-50 flex items-center justify-between">
                @if($marriage_id)
                <button wire:click="deleteMarriage" wire:confirm="Xóa hôn phối này?" class="px-4 py-2 text-sm font-medium text-red-600 bg-red-50 border border-red-200 rounded-xl hover:bg-red-100 transition">
                    Xóa hôn phối
                </button>
                @else
                <div></div>
                @endif
                <div class="flex items-center gap-3">
                    <button @click="$wire.set('showEditMarriage', false)" class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition">Hủy</button>
                    <button wire:click="saveMarriage" wire:loading.attr="disabled" class="px-5 py-2 text-sm font-medium text-white bg-primary-600 rounded-xl hover:bg-primary-700 transition disabled:opacity-60">
                        <span wire:loading.remove wire:target="saveMarriage">Lưu</span>
                        <span wire:loading wire:target="saveMarriage">Đang lưu...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ===== MODAL: Tử vong ===== --}}
    @if($showEditDeceased)
    <div x-data x-show="$wire.showEditDeceased"
        class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        @click="$wire.set('showEditDeceased', false)" @keydown.escape.window="$wire.set('showEditDeceased', false)">
        <div x-show="$wire.showEditDeceased" class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] flex flex-col overflow-hidden" @click.stop>

            <div class="flex-shrink-0 p-6 border-b bg-gradient-to-br from-slate-50 to-white flex justify-between items-start">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">Thông tin tử vong</h2>
                    <p class="text-sm text-slate-600 mt-1">Ghi nhận hoặc cập nhật thông tin tử vong</p>
                </div>
                <button @click="$wire.set('showEditDeceased', false)" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">✕</button>
            </div>

            <div class="flex-1 overflow-y-auto p-6 space-y-4">
                @php $input = "w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"; @endphp

                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input wire:model="is_deceased" type="checkbox" class="w-4 h-4 rounded text-red-500" />
                    <span class="text-sm font-medium text-slate-700">Đánh dấu đã qua đời</span>
                </label>

                @if($is_deceased)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-slate-50 border border-slate-200 rounded-xl">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Ngày mất <span class="text-red-500">*</span></label>
                        <input wire:model.defer="death_date" type="date" class="{{ $input }} @error('death_date') border-red-400 @enderror" />
                        @error('death_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Số sổ mất</label>
                        <input wire:model.defer="death_book_number" type="text" class="{{ $input }}" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nơi qua đời</label>
                        <input wire:model.defer="death_place" type="text" class="{{ $input }}" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nơi an táng</label>
                        <input wire:model.defer="burial_place" type="text" class="{{ $input }}" />
                    </div>
                </div>
                @endif
            </div>

            <div class="flex-shrink-0 px-6 py-4 border-t bg-slate-50 flex justify-end gap-3">
                <button @click="$wire.set('showEditDeceased', false)" class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition">Hủy</button>
                <button wire:click="saveDeceased" wire:loading.attr="disabled" class="px-5 py-2 text-sm font-medium text-white bg-primary-600 rounded-xl hover:bg-primary-700 transition disabled:opacity-60">
                    <span wire:loading.remove wire:target="saveDeceased">Lưu</span>
                    <span wire:loading wire:target="saveDeceased">Đang lưu...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ===== MODAL: Xác nhận xóa ===== --}}
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
                        Bạn có chắc muốn xóa <strong>{{ $parishioner->full_name_with_saint }}</strong>? Hành động này không thể hoàn tác.
                    </p>
                </div>
            </div>
            <div class="flex justify-end gap-3">
                <button wire:click="$set('showDeleteConfirm', false)" class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition">Hủy</button>
                <button wire:click="delete" wire:loading.attr="disabled" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-xl hover:bg-red-700 transition disabled:opacity-60">
                    <span wire:loading.remove wire:target="delete">Xóa</span>
                    <span wire:loading wire:target="delete">Đang xóa...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Loading overlay --}}
    <div wire:loading.delay wire:target="saveBasic,saveAddress,saveFamily,saveMarriage,saveDeceased,delete"
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