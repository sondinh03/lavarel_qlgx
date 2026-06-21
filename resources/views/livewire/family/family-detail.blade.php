@section('topbar')
<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => route('parishioners.dashboard')],
    ['label' => 'Gia đình', 'url' => route('families.index')],
    ['label' => $family['name'] ?? 'Chi tiết'],
]" />
@endsection

<div class="min-h-screen bg-slate-50 p-2 sm:p-4 lg:p-6" style="min-height: calc(100vh - 56px - var(--bottom-offset));">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="mx-auto max-w-7xl space-y-6">

        @if($isLoading)
        {{-- Loading state --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-12">
            <div class="flex justify-center">
                <div class="w-8 h-8 border-4 border-primary-200 border-t-primary-500 rounded-full animate-spin"></div>
            </div>
        </div>
        @else

        {{-- Header Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-4 lg:p-6 border-b border-slate-200">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">

                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-3 flex-wrap">
                            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 truncate">
                                {{ $family['name'] }}
                            </h1>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                         {{ $family['status_badge'] }}">
                                {{ $family['status_label'] }}
                            </span>
                        </div>

                        <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-slate-600">
                            <span class="flex items-center gap-1.5">
                                <x-icon name="users" class="w-4 h-4 text-slate-400" />
                                {{ $family['member_count'] }} thành viên
                            </span>
                            @if($family['parish_name'])
                            <span>{{ $family['parish_name'] }}</span>
                            @endif
                            @if($family['head'])
                            <span>Chủ hộ:
                                <a href="{{ $family['head']['url'] }}" class="font-semibold text-primary-600 hover:text-primary-700">
                                    {{ $family['head']['name'] }}
                                </a>
                            </span>
                            @endif
                            @if($family['parish_group_name'])
                            <span class="flex items-center gap-1.5">
                                <x-icon name="home" class="w-4 h-4 text-slate-400" />
                                {{ $family['parish_group_name'] }}
                            </span>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap flex-shrink-0">
                        @if($familyModel)
                        @can('update', $familyModel)
                        <x-button as="a" href="{{ route('families.edit', $family['id']) }}" variant="outline">
                            <x-icon name="edit" />
                            Chỉnh sửa
                        </x-button>
                        <x-button wire:click="openAddMemberModal" variant="primary">
                            <x-icon name="plus" />
                            Thêm thành viên
                        </x-button>
                        @endcan

                        @can('delete', $familyModel)
                        <x-dropdown icon="more-vertical" align="right" variant="subtle">
                            <x-dropdown-item
                                x-on:click="$dispatch('open-confirm', {
                                    message: 'Xóa gia đình?',
                                    description: 'Không thể xóa gia đình còn thành viên.',
                                    wireMethod: 'deleteFamily'
                                })"
                                icon="trash"
                                class="text-red-600 hover:bg-red-50">
                                Xóa gia đình
                            </x-dropdown-item>
                        </x-dropdown>
                        @endcan
                        @endif
                    </div>
                </div>
            </div>

            {{-- Tabs --}}
            <div class="px-4 lg:px-6 py-4 border-b border-slate-200 bg-slate-50/70">
                <div class="inline-flex w-full sm:w-auto max-w-full rounded-xl bg-slate-200 p-1 text-sm font-medium">
                    <button wire:click="switchTab('members')"
                        type="button"
                        class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg transition-all whitespace-nowrap
                               {{ $activeTab === 'members'
                                   ? 'bg-white shadow-sm text-primary-600 font-semibold'
                                   : 'text-slate-600 hover:text-primary-600 hover:bg-white/50' }}">
                        Thành viên
                        <span class="text-xs font-normal opacity-70">({{ $family['member_count'] }})</span>
                    </button>
                    <button wire:click="switchTab('info')"
                        type="button"
                        class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg transition-all whitespace-nowrap
                               {{ $activeTab === 'info'
                                   ? 'bg-white shadow-sm text-primary-600 font-semibold'
                                   : 'text-slate-600 hover:text-primary-600 hover:bg-white/50' }}">
                        Thông tin
                    </button>
                </div>
            </div>
        </div>

        {{-- Tab: Members --}}
        @if($activeTab === 'members')
        @if($family['member_count'] > 0)
        <div class="space-y-6">
            @foreach([
                'husband' => 'Chồng',
                'wife' => 'Vợ',
                'children' => 'Con cái',
                'others' => 'Khác',
            ] as $groupKey => $groupLabel)
            @php
                $groupMembers = $groupKey === 'husband' && $family['husband']
                    ? [$family['husband']]
                    : ($groupKey === 'wife' && $family['wife']
                        ? [$family['wife']]
                        : ($family[$groupKey] ?? []));
            @endphp
            @if(!empty($groupMembers))
            <div>
                <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wide mb-3">{{ $groupLabel }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($groupMembers as $member)
                    @include('livewire.family._member-card', ['member' => $member])
                    @endforeach
                </div>
            </div>
            @endif
            @endforeach
        </div>
        @else
        <x-stats.page-empty
            tone="primary"
            title="Chưa có thành viên"
            description="Gia đình này chưa có giáo dân nào">
            <x-slot name="icon">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </x-slot>
            <x-action-button wire="openAddMemberModal" icon="plus">
                Thêm thành viên
            </x-action-button>
        </x-stats.page-empty>
        @endif
        @endif

        {{-- Tab: Info --}}
        @if($activeTab === 'info')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="lg:col-span-2 space-y-5">
                {{-- Thông tin gia đình --}}
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                        <h2 class="text-base font-semibold text-slate-900">Thông tin gia đình</h2>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Tên gia đình</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">{{ $family['name'] }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Giáo họ</p>
                            <p class="mt-1 text-sm text-slate-700">{{ $family['parish_group_name'] ?: '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Trạng thái</p>
                            <div class="mt-2">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                             {{ $family['status_badge'] }}">
                                    {{ $family['status_label'] }}
                                </span>
                            </div>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Số thành viên</p>
                            <p class="mt-1 text-sm text-slate-700">{{ $family['member_count'] }} người</p>
                        </div>
                        @if($family['head'])
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Chủ hộ</p>
                            <a href="{{ $family['head']['url'] }}" class="mt-1 text-sm font-semibold text-primary-600 hover:text-primary-700">
                                {{ $family['head']['name'] }}
                            </a>
                        </div>
                        @endif
                        @if($family['address'] || $family['province'])
                        <div class="md:col-span-2">
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Địa chỉ</p>
                            <p class="mt-1 text-sm text-slate-700">{{ implode(', ', array_filter([$family['address'], $family['province']])) ?: '—' }}</p>
                        </div>
                        @endif
                        @if($family['level'])
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Diện gia đình</p>
                            <p class="mt-1 text-sm text-slate-700">{{ $family['level_label'] ?: $family['level'] }}</p>
                        </div>
                        @endif
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Chuyển xứ</p>
                            <p class="mt-1 text-sm text-slate-700">{{ $family['is_transferred'] ? 'Đã chuyển' : 'Đang sinh hoạt' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Thống kê</p>
                            <p class="mt-1 text-sm text-slate-700">{{ $family['is_included_in_stats'] ? 'Được thống kê' : 'Không thống kê' }}</p>
                        </div>
                    </div>
                </div>

                @if($family['note'])
                {{-- Ghi chú --}}
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                        <h2 class="text-base font-semibold text-slate-900">Ghi chú</h2>
                    </div>
                    <div class="p-6">
                        <p class="text-sm text-slate-700 whitespace-pre-line leading-relaxed">{{ $family['note'] }}</p>
                    </div>
                </div>
                @endif
            </div>

            {{-- Sidebar: Metadata --}}
            <div>
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                        <h2 class="text-base font-semibold text-slate-900">Hệ thống</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Ngày tạo</p>
                            <p class="mt-1 text-sm text-slate-700">{{ $family['created_at'] ?: '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Cập nhật cuối</p>
                            <p class="mt-1 text-sm text-slate-700">{{ $family['updated_at'] ?: '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @endif {{-- /isLoading --}}

        {{-- ══ MODAL: Thêm thành viên ══ --}}
        @if($showAddMemberModal)
        <div
            class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50 p-4"
            role="dialog"
            aria-modal="true"
            @click="$wire.closeAddMemberModal()"
            @keydown.escape.window="$wire.closeAddMemberModal()">

            <div
                class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col"
                @click.stop>

                {{-- Header --}}
                <div class="flex-shrink-0 p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-bold text-slate-900">Thêm thành viên</h2>
                            <p class="text-sm text-slate-500 mt-1">Chọn giáo dân chưa thuộc gia đình nào</p>
                        </div>
                        <button
                            @click="$wire.closeAddMemberModal()"
                            class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                            <x-icon name="x" class="w-5 h-5" />
                        </button>
                    </div>
                    <div class="mt-4">
                        <x-search-input
                            wireModel="memberSearch"
                            placeholder="Tìm kiếm giáo dân..."
                            debounce="500ms" />
                    </div>
                </div>

                {{-- Body --}}
                <div class="flex-1 overflow-y-auto">
                    @if($availableParishioners && $availableParishioners->count() > 0)

                    <div class="px-5 py-3 border-b border-slate-100 bg-slate-50/60 flex items-center gap-3">
                        <input type="checkbox" wire:model="selectAllParishioners"
                            class="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                        <span class="text-sm text-slate-600 font-medium">
                            Chọn tất cả ({{ $availableParishioners->total() }})
                        </span>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @foreach($availableParishioners as $person)
                        <label class="flex items-center gap-4 px-5 py-3.5 hover:bg-slate-50 transition cursor-pointer"
                            wire:key="avail-{{ $person->id }}">
                            <input type="checkbox" wire:model="selectedParishioners" value="{{ $person->id }}"
                                class="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-slate-800 truncate">
                                    {{ trim(($person->last_name ?? '') . ' ' . ($person->first_name ?? '')) }}
                                </p>
                                @if($person->birthday)
                                <p class="text-xs text-slate-400 mt-0.5">Sinh: {{ $person->birthday->format('d/m/Y') }}</p>
                                @endif
                            </div>
                        </label>
                        @endforeach
                    </div>

                    @if($availableParishioners->hasPages())
                    <div class="p-4 border-t border-slate-100">
                        {{ $availableParishioners->links() }}
                    </div>
                    @endif

                    @else
                    <div class="py-16 text-center">
                        <p class="text-sm text-slate-500 font-medium">Không tìm thấy giáo dân phù hợp</p>
                        <p class="mt-1 text-xs text-slate-400">Chỉ hiển thị giáo dân chưa thuộc gia đình nào</p>
                    </div>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="flex-shrink-0 px-6 py-4 border-t border-slate-200 bg-slate-50 flex items-center justify-between">
                    <span class="text-sm text-slate-600">
                        Đã chọn: <span class="font-semibold text-primary-600">{{ count($selectedParishioners) }}</span> người
                    </span>
                    <div class="flex gap-3">
                        <x-action-button @click="$wire.closeAddMemberModal()" variant="secondary">
                            Hủy
                        </x-action-button>
                        <x-action-button wire:click="addMembers" icon="plus" :loading="true">
                            Thêm vào gia đình
                        </x-action-button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- ══ MODAL: Đổi vai trò thành viên ══ --}}
        @if($showRoleModal)
        <div
            class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50 p-4"
            role="dialog"
            aria-modal="true"
            @keydown.escape.window="$wire.closeRoleModal()">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden"
                @click.outside="$wire.closeRoleModal()">

                <div class="p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                    <h3 class="text-base font-bold text-slate-900">Đổi vai trò</h3>
                    <p class="text-sm text-slate-500 mt-1">{{ $roleMemberName }}</p>
                </div>

                <div class="p-6 space-y-3">
                    @foreach([
                    'husband' => ['label' => 'Cha', 'color' => 'bg-blue-50 border-blue-200 text-blue-700'],
                    'wife' => ['label' => 'Mẹ', 'color' => 'bg-pink-50 border-pink-200 text-pink-700'],
                    'child' => ['label' => 'Con', 'color' => 'bg-emerald-50 border-emerald-200 text-emerald-700'],
                    'other' => ['label' => 'Khác', 'color' => 'bg-slate-50 border-slate-200 text-slate-600'],
                    ] as $value => $opt)
                    <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-all
                                  {{ $roleValue === $value ? $opt['color'] : 'border-slate-200 hover:bg-slate-50' }}">
                        <input type="radio" wire:model="roleValue" value="{{ $value }}"
                            class="text-primary-600 focus:ring-primary-500">
                        <span class="text-sm font-semibold">{{ $opt['label'] }}</span>
                    </label>
                    @endforeach
                </div>

                <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end gap-3">
                    <x-action-button wire:click="closeRoleModal" variant="secondary">Hủy</x-action-button>
                    <x-action-button wire:click="saveRole" icon="save" :loading="true">Lưu vai trò</x-action-button>
                </div>
            </div>
        </div>
        @endif

        {{-- ══ MODAL: Xác nhận xóa thành viên ══ --}}
        @if($showRemoveModal)
        <div
            class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50 p-4"
            role="dialog"
            aria-modal="true"
            @keydown.escape.window="$wire.closeRemoveModal()">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden"
                @click.outside="$wire.closeRemoveModal()">

                <div class="p-6 space-y-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                            <x-icon name="alert-triangle" class="w-6 h-6 text-red-600" />
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Xóa thành viên</h3>
                            <p class="text-sm text-slate-500 mt-0.5">
                                Xóa <strong class="text-slate-700">{{ $removingMemberName }}</strong> khỏi gia đình?
                            </p>
                        </div>
                    </div>

                    <p class="text-sm text-slate-500 bg-slate-50 rounded-xl px-4 py-3">
                        Hồ sơ giáo dân vẫn được giữ lại, chỉ xóa khỏi gia đình này.
                    </p>

                    <div class="flex justify-end gap-3 pt-2">
                        <x-action-button wire:click="closeRemoveModal" variant="secondary">Hủy</x-action-button>
                        <x-action-button wire:click="removeMember" variant="danger" :loading="true">
                            Xóa khỏi gia đình
                        </x-action-button>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>

@push('page-title')
<span class="text-slate-800 font-semibold text-sm">{{ $family['name'] ?? 'Chi tiết gia đình' }}</span>
@endpush
