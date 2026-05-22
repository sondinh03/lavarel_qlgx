@section('topbar')
<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => route('dashboard')],
    ['label' => 'Gia đình', 'url' => route('families.index')],
    ['label' => $family['name'] ?? 'Chi tiết'],
]" />
@endsection

<div
    class="min-h-screen bg-slate-50 p-2 sm:p-4 lg:p-6"
    style="min-height: calc(100vh - 56px - var(--bottom-offset));"
    x-data="{ showAddModal: false }"
    x-init="
        document.addEventListener('livewire:load', () => {
            Livewire.on('openModal',  () => { showAddModal = true; });
            Livewire.on('closeModal', () => { showAddModal = false; });
        });
    ">

    <div class="max-w-5xl mx-auto space-y-6">

        {{-- LOADING --}}
        @if($isLoading)
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-12">
            <div class="flex justify-center">
                <div class="w-8 h-8 border-4 border-primary-200 border-t-primary-500 rounded-full animate-spin"></div>
            </div>
        </div>
        @else

        {{-- HERO --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="bg-gradient-to-br from-primary-50 to-white p-6">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">

                    <div class="min-w-0">
                        <div class="flex items-center gap-3 flex-wrap">
                            <h1 class="text-2xl font-bold text-slate-900 truncate">
                                {{ $family['name'] }}
                            </h1>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                         {{ $family['status_badge'] }}">
                                {{ $family['status_label'] }}
                            </span>
                        </div>

                        <div class="mt-3 flex flex-wrap items-center gap-4 text-sm text-slate-600">
                            <span class="flex items-center gap-1.5">
                                <x-icon name="users" class="w-4 h-4 text-slate-400" />
                                {{ $family['member_count'] }} thành viên
                            </span>
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
                        <x-button as="a" href="{{ route('families.edit', $family['id']) }}" variant="secondary">
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

            {{-- TABS --}}
            <div class="border-t border-slate-200 px-4">
                <div class="flex items-center gap-1 py-2">
                    <button wire:click="switchTab('members')"
                        class="px-4 py-2 rounded-xl text-sm font-semibold transition-all
                               {{ $activeTab === 'members' ? 'bg-primary-100 text-primary-700' : 'text-slate-500 hover:bg-slate-100' }}">
                        Thành viên
                        <span class="ml-1 text-xs font-normal opacity-60">({{ $family['member_count'] }})</span>
                    </button>
                    <button wire:click="switchTab('info')"
                        class="px-4 py-2 rounded-xl text-sm font-semibold transition-all
                               {{ $activeTab === 'info' ? 'bg-primary-100 text-primary-700' : 'text-slate-500 hover:bg-slate-100' }}">
                        Thông tin
                    </button>
                </div>
            </div>
        </div>

        {{-- ══ TAB: THÀNH VIÊN ══ --}}
        @if($activeTab === 'members')

        @if($family['member_count'] > 0)
        <div class="space-y-4">

            {{-- CHỒNG --}}
            @if($family['husband'])
            @include('livewire.family._member-card', [
            'member' => $family['husband'],
            'roleColor' => 'blue',
            ])
            @endif

            {{-- VỢ --}}
            @if($family['wife'])
            @include('livewire.family._member-card', [
            'member' => $family['wife'],
            'roleColor' => 'pink',
            ])
            @endif

            {{-- CON CÁI --}}
            @foreach($family['children'] as $child)
            @include('livewire.family._member-card', [
            'member' => $child,
            'roleColor' => 'green',
            ])
            @endforeach

            {{-- THÀNH VIÊN KHÁC (chưa có role) --}}
            @foreach($family['others'] as $other)
            @include('livewire.family._member-card', [
            'member' => $other,
            'roleColor' => 'gray',
            ])
            @endforeach

        </div>
        @else
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-12">
            <x-empty-state
                icon="users"
                title="Chưa có thành viên"
                description="Gia đình này chưa có giáo dân nào">
                <x-action-button wire="openAddMemberModal" icon="plus">
                    Thêm thành viên
                </x-action-button>
            </x-empty-state>
        </div>
        @endif

        @endif

        {{-- ══ TAB: THÔNG TIN ══ --}}
        @if($activeTab === 'info')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="lg:col-span-2 space-y-5">
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
                    </div>
                </div>

                @if($family['note'])
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

            <div class="space-y-5">
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

        @endif {{-- end !isLoading --}}
    </div>

    {{-- ══ MODAL: Thêm thành viên ══ --}}
    <div
        x-show="showAddModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        role="dialog" aria-modal="true"
        @click="showAddModal = false; $wire.closeAddMemberModal()"
        @keydown.escape.window="showAddModal = false; $wire.closeAddMemberModal()">

        <div
            x-show="showAddModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 scale-95"
            class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col"
            @click.stop>

            <div class="flex-shrink-0 p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">Thêm thành viên</h2>
                        <p class="text-sm text-slate-500 mt-1">Chọn giáo dân chưa thuộc gia đình nào</p>
                    </div>
                    <button
                        @click="showAddModal = false; $wire.closeAddMemberModal()"
                        class="p-2 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="mt-4">
                    <x-search-input wire-model="memberSearch" placeholder="Tìm kiếm giáo dân..." debounce="500ms" />
                </div>
            </div>

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
                <div class="p-4 border-t border-slate-100">{{ $availableParishioners->links() }}</div>
                @endif

                @else
                <div class="py-16 text-center">
                    <p class="text-sm text-slate-500 font-medium">Không tìm thấy giáo dân phù hợp</p>
                    <p class="mt-1 text-xs text-slate-400">Chỉ hiển thị giáo dân chưa thuộc gia đình nào</p>
                </div>
                @endif
            </div>

            <div class="flex-shrink-0 px-6 py-4 border-t border-slate-200 bg-slate-50 flex items-center justify-between">
                <span class="text-sm text-slate-600">
                    Đã chọn: <span class="font-semibold text-primary-600">{{ count($selectedParishioners) }}</span> người
                </span>
                <div class="flex gap-3">
                    <x-action-button @click="showAddModal = false; $wire.closeAddMemberModal()" variant="secondary">
                        Hủy
                    </x-action-button>
                    <x-action-button wire:click="addMembers" icon="plus" :loading="true">
                        Thêm vào gia đình
                    </x-action-button>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ MODAL: Đổi vai trò thành viên ══ --}}
    @if($showRoleModal)
    <div
        class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        role="dialog" aria-modal="true"
        @keydown.escape.window="$wire.closeRoleModal()">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden"
            @click.outside="$wire.closeRoleModal()">

            <div class="p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                <h3 class="text-base font-bold text-slate-900">Đổi vai trò</h3>
                <p class="text-sm text-slate-500 mt-1">
                    {{ $roleMemberName }}
                </p>
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
        role="dialog" aria-modal="true"
        @keydown.escape.window="$wire.closeRemoveModal()">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden"
            @click.outside="$wire.closeRemoveModal()">
            <div class="p-6 space-y-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
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

@push('page-title')
<span class="text-slate-800 font-semibold text-sm">{{ $family['name'] ?? 'Chi tiết gia đình' }}</span>
@endpush