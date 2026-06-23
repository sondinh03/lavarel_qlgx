@section('topbar')
<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => route('parishioners.dashboard')],
    ['label' => 'Gia đình', 'url' => route('families.index')],
    ['label' => $family['name'] ?? 'Chi tiết'],
]" />
@endsection

<div class="min-h-screen bg-slate-50 p-2 sm:p-4 lg:p-6" style="min-height: calc(100vh - 56px - var(--bottom-offset));">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="mx-auto max-w-5xl space-y-5">

        @if($isLoading)
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-12">
            <div class="flex justify-center">
                <div class="w-8 h-8 border-4 border-primary-200 border-t-primary-500 rounded-full animate-spin"></div>
            </div>
        </div>
        @else

        {{-- Header --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-4 lg:p-6">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h1 class="text-xl sm:text-2xl font-bold text-slate-900">{{ $family['name'] }}</h1>
                            @if(!empty($family['code']))
                            <span class="inline-flex px-2.5 py-0.5 rounded-lg text-xs font-mono font-semibold bg-slate-100 text-slate-700">
                                {{ $family['code'] }}
                            </span>
                            @endif
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $family['status_badge'] }}">
                                {{ $family['status_label'] }}
                            </span>
                        </div>

                        <div class="mt-3 flex flex-wrap gap-2">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 text-xs font-medium text-slate-700">
                                <x-icon name="users" class="w-3.5 h-3.5 text-slate-400" />
                                {{ $family['member_count'] }} thành viên
                            </span>
                            @if($family['parish_group_name'])
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 text-xs font-medium text-slate-700">
                                <x-icon name="home" class="w-3.5 h-3.5 text-slate-400" />
                                {{ $family['parish_group_name'] }}
                            </span>
                            @endif
                            @if($family['head'])
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-primary-50 text-xs font-medium text-primary-700">
                                Chủ hộ:
                                <a href="{{ $family['head']['url'] }}" class="font-semibold hover:underline">{{ $family['head']['name'] }}</a>
                            </span>
                            @endif
                        </div>
                    </div>

                    @if($canManageMembers)
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <x-button as="a" href="{{ route('families.edit', $family['id']) }}" variant="outline" size="sm">
                            <x-icon name="edit" />
                            Sửa hộ
                        </x-button>
                        <x-button wire:click="openAddMemberModal" variant="primary" size="sm">
                            <x-icon name="plus" />
                            Thêm thành viên
                        </x-button>
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
                    </div>
                    @endif
                </div>
            </div>

            <div class="px-4 lg:px-6 py-3 border-t border-slate-100 bg-slate-50/70">
                <div class="inline-flex w-full sm:w-auto rounded-xl bg-slate-200 p-1 text-sm font-medium">
                    <button wire:click="switchTab('members')" type="button"
                        class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg transition-all whitespace-nowrap
                            {{ $activeTab === 'members' ? 'bg-white shadow-sm text-primary-600 font-semibold' : 'text-slate-600 hover:bg-white/50' }}">
                        Thành viên
                        <span class="text-xs opacity-70">({{ $family['member_count'] }})</span>
                    </button>
                    <button wire:click="switchTab('info')" type="button"
                        class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg transition-all whitespace-nowrap
                            {{ $activeTab === 'info' ? 'bg-white shadow-sm text-primary-600 font-semibold' : 'text-slate-600 hover:bg-white/50' }}">
                        Thông tin hộ
                    </button>
                </div>
            </div>
        </div>

        @if($preselectParishioner)
        <div class="px-4 py-3 rounded-2xl bg-primary-50 border border-primary-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="text-sm text-primary-900">
                <span class="font-semibold">Thêm vào hộ:</span>
                {{ $preselectParishioner->full_name_with_saint }}
            </div>
            @if($canManageMembers)
            <x-button wire:click="openAddMemberModal" variant="primary" size="sm">
                <x-icon name="plus" />
                Chọn và thêm
            </x-button>
            @endif
        </div>
        @endif

        {{-- Tab: Members --}}
        @if($activeTab === 'members')
        @if($family['member_count'] > 0)
        <div class="space-y-4">

            {{-- Vợ chồng --}}
            @if($family['husband'] || $family['wife'])
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/80">
                    <h2 class="text-sm font-semibold text-slate-800">Vợ chồng</h2>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 divide-y sm:divide-y-0 sm:divide-x divide-slate-100">
                    @include('livewire.family._spouse-cell', [
                        'member' => $family['husband'],
                        'label' => 'Chồng',
                        'tone' => 'blue',
                        'canManage' => $canManageMembers,
                    ])
                    @include('livewire.family._spouse-cell', [
                        'member' => $family['wife'],
                        'label' => 'Vợ',
                        'tone' => 'pink',
                        'canManage' => $canManageMembers,
                    ])
                </div>
            </div>
            @endif

            @if(!empty($family['children']))
            <x-family-section-card :title="'Con cái (' . count($family['children']) . ')'">
                @foreach($family['children'] as $child)
                @include('livewire.family._member-row', ['member' => $child, 'canManage' => $canManageMembers])
                @endforeach
            </x-family-section-card>
            @endif

            @if(!empty($family['others']))
            <x-family-section-card :title="'Thành viên khác (' . count($family['others']) . ')'">
                @foreach($family['others'] as $other)
                @include('livewire.family._member-row', ['member' => $other, 'canManage' => $canManageMembers])
                @endforeach
            </x-family-section-card>
            @endif
        </div>
        @else
        <x-stats.page-empty
            tone="primary"
            title="Chưa có thành viên"
            description="Thêm giáo dân vào hộ để quản lý vai trò chồng, vợ, con...">
            <x-slot name="icon">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </x-slot>
            @if($canManageMembers)
            <x-action-button wire="openAddMemberModal" icon="plus">Thêm thành viên đầu tiên</x-action-button>
            @endif
        </x-stats.page-empty>
        @endif
        @endif

        {{-- Tab: Info --}}
        @if($activeTab === 'info')
        <div class="space-y-4 max-w-3xl">
            <x-family-section-card title="Thông tin hộ gia đình">
                <x-info-row label="Tên gia đình" :value="$family['name']" />
                <x-info-row label="Giáo họ" :value="$family['parish_group_name']" />
                <x-info-row label="Giáo xứ" :value="$family['parish_name']" />
                <x-info-row label="Trạng thái" :value="$family['status_label']" />
                <x-info-row label="Số thành viên" :value="$family['member_count'] . ' người'" />
                @if($family['head'])
                <div class="px-4 py-3 flex justify-between gap-4">
                    <span class="text-slate-600">Chủ hộ</span>
                    <a href="{{ $family['head']['url'] }}" class="font-medium text-primary-600 hover:text-primary-700 text-right">
                        {{ $family['head']['name'] }}
                    </a>
                </div>
                @endif
                @if($family['address'] || $family['province'])
                <x-info-row label="Địa chỉ" :value="implode(', ', array_filter([$family['address'], $family['province']]))" />
                @endif
                @if($family['level'])
                <x-info-row label="Diện gia đình" :value="$family['level_label'] ?: $family['level']" />
                @endif
                <x-info-row label="Chuyển xứ" :value="$family['is_transferred'] ? 'Đã chuyển' : 'Đang sinh hoạt'" />
                <x-info-row label="Thống kê" :value="$family['is_included_in_stats'] ? 'Được thống kê' : 'Không thống kê'" />
            </x-family-section-card>

            @if($family['note'])
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 lg:p-5">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-2">Ghi chú</p>
                <p class="text-sm text-slate-700 whitespace-pre-line leading-relaxed">{{ $family['note'] }}</p>
            </div>
            @endif

            <x-family-section-card title="Hệ thống">
                <x-info-row label="Ngày tạo" :value="$family['created_at']" />
                <x-info-row label="Cập nhật cuối" :value="$family['updated_at']" />
            </x-family-section-card>
        </div>
        @endif

        @endif

        {{-- Modal: Thêm thành viên --}}
        @if($showAddMemberModal)
        <div class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50 p-4"
            role="dialog" aria-modal="true"
            @click="$wire.closeAddMemberModal()"
            @keydown.escape.window="$wire.closeAddMemberModal()">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[85vh] overflow-hidden flex flex-col" @click.stop>
                <div class="flex-shrink-0 px-5 py-4 border-b border-slate-200">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Thêm thành viên</h2>
                            <p class="text-sm text-slate-500 mt-0.5">Giáo dân chưa thuộc hộ nào</p>
                        </div>
                        <button type="button" @click="$wire.closeAddMemberModal()"
                            class="p-1 rounded-lg text-slate-400 hover:bg-slate-100">
                            <x-icon name="x" class="w-5 h-5" />
                        </button>
                    </div>
                    <div class="mt-3">
                        <x-search-input wireModel="memberSearch" placeholder="Tìm họ tên..." debounce="400ms" />
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto min-h-0">
                    @if($availableParishioners && $availableParishioners->count() > 0)
                    <div class="px-4 py-2.5 border-b border-slate-100 bg-slate-50/80 flex items-center gap-2 sticky top-0">
                        <input type="checkbox" wire:model="selectAllParishioners"
                            class="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                        <span class="text-xs font-medium text-slate-600">Chọn tất cả trang này</span>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @foreach($availableParishioners as $person)
                        <label class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 cursor-pointer" wire:key="avail-{{ $person->id }}">
                            <input type="checkbox" wire:model="selectedParishioners" value="{{ $person->id }}"
                                class="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-slate-800 truncate">
                                    {{ $person->full_name_with_saint }}
                                </p>
                                @if($person->birthday)
                                <p class="text-xs text-slate-400">{{ $person->birthday->format('d/m/Y') }}</p>
                                @endif
                            </div>
                        </label>
                        @endforeach
                    </div>
                    @if($availableParishioners->hasPages())
                    <div class="p-3 border-t border-slate-100">{{ $availableParishioners->links() }}</div>
                    @endif
                    @else
                    <div class="py-14 text-center px-4">
                        <p class="text-sm text-slate-600 font-medium">Không tìm thấy giáo dân</p>
                        <p class="text-xs text-slate-400 mt-1">Chỉ hiển thị người chưa có hộ gia đình</p>
                    </div>
                    @endif
                </div>

                <div class="flex-shrink-0 px-5 py-3.5 border-t border-slate-200 bg-slate-50 flex items-center justify-between gap-3">
                    <span class="text-sm text-slate-600">
                        Đã chọn <strong class="text-primary-600">{{ count($selectedParishioners) }}</strong>
                    </span>
                    <div class="flex gap-2">
                        <x-button type="button" variant="outline" size="sm" wire:click="closeAddMemberModal">Hủy</x-button>
                        <x-button type="button" variant="primary" size="sm" wire:click="addMembers" wire:loading.attr="disabled" wire:target="addMembers">
                            <span wire:loading.remove wire:target="addMembers">Thêm vào hộ</span>
                            <span wire:loading wire:target="addMembers">Đang thêm...</span>
                        </x-button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Modal: Đổi vai trò --}}
        @if($showRoleModal)
        <div class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50 p-4"
            role="dialog" aria-modal="true" @keydown.escape.window="$wire.closeRoleModal()">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden" @click.outside="$wire.closeRoleModal()">
                <div class="px-5 py-4 border-b border-slate-200">
                    <h3 class="text-base font-bold text-slate-900">Đổi vai trò</h3>
                    <p class="text-sm text-slate-500 mt-0.5 truncate">{{ $roleMemberName }}</p>
                </div>
                <div class="p-4 space-y-2">
                    @foreach([
                        'husband' => ['label' => 'Chồng', 'active' => 'border-blue-300 bg-blue-50 text-blue-800'],
                        'wife'    => ['label' => 'Vợ', 'active' => 'border-pink-300 bg-pink-50 text-pink-800'],
                        'child'   => ['label' => 'Con', 'active' => 'border-emerald-300 bg-emerald-50 text-emerald-800'],
                        'other'   => ['label' => 'Khác', 'active' => 'border-slate-300 bg-slate-50 text-slate-700'],
                    ] as $value => $opt)
                    <label class="flex items-center gap-3 px-3 py-2.5 rounded-xl border cursor-pointer transition
                        {{ $roleValue === $value ? $opt['active'] : 'border-slate-200 hover:bg-slate-50' }}">
                        <input type="radio" wire:model="roleValue" value="{{ $value }}" class="text-primary-600 focus:ring-primary-500">
                        <span class="text-sm font-semibold">{{ $opt['label'] }}</span>
                    </label>
                    @endforeach
                </div>
                <div class="px-5 py-3.5 border-t border-slate-200 bg-slate-50 flex justify-end gap-2">
                    <x-button type="button" variant="outline" size="sm" wire:click="closeRoleModal">Hủy</x-button>
                    <x-button type="button" variant="primary" size="sm" wire:click="saveRole" wire:loading.attr="disabled" wire:target="saveRole">Lưu</x-button>
                </div>
            </div>
        </div>
        @endif

        {{-- Modal: Xóa thành viên --}}
        @if($showRemoveModal)
        <div class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50 p-4"
            role="dialog" aria-modal="true" @keydown.escape.window="$wire.closeRemoveModal()">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden" @click.outside="$wire.closeRemoveModal()">
                <div class="p-5 space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                            <x-icon name="alert-triangle" class="w-5 h-5 text-red-600" />
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Xóa khỏi hộ</h3>
                            <p class="text-sm text-slate-500 mt-0.5">
                                <strong class="text-slate-700">{{ $removingMemberName }}</strong> sẽ không còn trong hộ này.
                            </p>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 bg-slate-50 rounded-xl px-3 py-2.5">
                        Hồ sơ giáo dân vẫn được giữ; chỉ gỡ liên kết hộ gia đình.
                    </p>
                    <div class="flex justify-end gap-2 pt-1">
                        <x-button type="button" variant="outline" size="sm" wire:click="closeRemoveModal">Hủy</x-button>
                        <x-button type="button" variant="danger" size="sm" wire:click="removeMember" wire:loading.attr="disabled" wire:target="removeMember">
                            Xóa khỏi hộ
                        </x-button>
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
