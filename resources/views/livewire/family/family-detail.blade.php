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

    <div class="max-w-7xl mx-auto space-y-6">

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

                    {{-- Title + meta --}}
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

                        <div class="mt-4 flex flex-wrap items-center gap-5 text-sm">
                            <div class="flex items-center gap-2 text-slate-600">
                                <x-icon name="users" class="w-4 h-4" />
                                <span>{{ $family['member_count'] }} thành viên</span>
                            </div>
                            @if($family['parish_group_name'])
                            <div class="flex items-center gap-2 text-slate-600">
                                <x-icon name="home" class="w-4 h-4" />
                                <span>{{ $family['parish_group_name'] }}</span>
                            </div>
                            @endif
                            @if($family['head_name'])
                            <div class="flex items-center gap-2 text-slate-600">
                                <x-icon name="user" class="w-4 h-4" />
                                <span>Chủ hộ: {{ $family['head_name'] }}</span>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-2 flex-wrap flex-shrink-0">
                        @if($familyModel)
                        @can('update', $familyModel)
                        <x-button
                            as="a"
                            href="{{ route('families.edit', $family['id']) }}"
                            variant="secondary">
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
                                    description: 'Không thể xóa gia đình còn thành viên. Hành động này không thể hoàn tác.',
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
                <div class="flex items-center gap-2 overflow-x-auto py-3">
                    <button
                        wire:click="switchTab('info')"
                        class="px-4 py-2 rounded-xl text-sm font-semibold transition-all
                               {{ $activeTab === 'info'
                                   ? 'bg-primary-100 text-primary-700'
                                   : 'text-slate-500 hover:bg-slate-100' }}">
                        Thông tin
                    </button>
                    <button
                        wire:click="switchTab('members')"
                        class="px-4 py-2 rounded-xl text-sm font-semibold transition-all
                               {{ $activeTab === 'members'
                                   ? 'bg-primary-100 text-primary-700'
                                   : 'text-slate-500 hover:bg-slate-100' }}">
                        Thành viên
                        <span class="ml-1.5 text-xs font-normal opacity-70">({{ $family['member_count'] }})</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- ══ TAB: THÔNG TIN ══ --}}
        @if($activeTab === 'info')
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

            {{-- LEFT --}}
            <div class="xl:col-span-2 space-y-6">

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                        <h2 class="text-base font-semibold text-slate-900">Thông tin gia đình</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <p class="text-xs uppercase tracking-wide text-slate-400 font-semibold">Tên gia đình</p>
                                <p class="mt-1 text-sm font-semibold text-slate-900">{{ $family['name'] }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wide text-slate-400 font-semibold">Giáo họ</p>
                                <p class="mt-1 text-sm text-slate-700">{{ $family['parish_group_name'] ?: '—' }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wide text-slate-400 font-semibold">Trạng thái</p>
                                <div class="mt-2">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                                 {{ $family['status_badge'] }}">
                                        {{ $family['status_label'] }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wide text-slate-400 font-semibold">Tổng thành viên</p>
                                <p class="mt-1 text-sm text-slate-700">{{ $family['member_count'] }} người</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                        <h2 class="text-base font-semibold text-slate-900">Ghi chú</h2>
                    </div>
                    <div class="p-6">
                        @if($family['note'])
                        <p class="text-sm text-slate-700 whitespace-pre-line leading-relaxed">{{ $family['note'] }}</p>
                        @else
                        <p class="text-sm text-slate-400 italic">Chưa có ghi chú.</p>
                        @endif
                    </div>
                </div>

            </div>

            {{-- RIGHT --}}
            <div class="space-y-6">

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                        <h2 class="text-base font-semibold text-slate-900">Chủ hộ</h2>
                    </div>
                    <div class="p-6">
                        @if($family['head_name'])
                        <a href="{{ $family['head_url'] }}"
                            class="flex items-center gap-4 p-4 rounded-2xl border border-slate-200
                                   hover:bg-slate-50 hover:border-primary-200 transition-all">
                            <div class="w-14 h-14 rounded-full bg-primary-100 text-primary-700
                                        flex items-center justify-center font-bold text-lg flex-shrink-0">
                                {{ mb_substr($family['head_name'], 0, 1) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-slate-900 truncate">{{ $family['head_name'] }}</p>
                                <p class="text-xs text-slate-500 mt-1">Nhấn để xem hồ sơ giáo dân</p>
                            </div>
                        </a>
                        @else
                        <div class="text-center py-8">
                            <p class="text-sm text-slate-400">Chưa chọn chủ hộ</p>
                            <a href="{{ route('families.edit', $family['id']) }}"
                                class="mt-2 inline-block text-xs text-primary-600 hover:underline">
                                Chỉnh sửa để thêm chủ hộ
                            </a>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                        <h2 class="text-base font-semibold text-slate-900">Hệ thống</h2>
                    </div>
                    <div class="p-6 space-y-5">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-slate-400 font-semibold">Ngày tạo</p>
                            <p class="mt-1 text-sm text-slate-700">{{ $family['created_at'] ?: '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-slate-400 font-semibold">Cập nhật cuối</p>
                            <p class="mt-1 text-sm text-slate-700">{{ $family['updated_at'] ?: '—' }}</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        @endif

        {{-- ══ TAB: THÀNH VIÊN ══ --}}
        @if($activeTab === 'members')
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Danh sách thành viên</h2>
                    <p class="text-sm text-slate-500 mt-1">{{ $family['member_count'] }} thành viên trong gia đình</p>
                </div>
            </div>

            @if(count($family['members']) > 0)
            <div class="divide-y divide-slate-100">
                @foreach($family['members'] as $member)
                <div class="p-5 hover:bg-slate-50 transition-colors" wire:key="member-{{ $member['id'] }}">
                    <div class="flex items-start justify-between gap-4">

                        <div class="flex items-start gap-4 min-w-0">
                            <div class="w-12 h-12 rounded-full flex-shrink-0 flex items-center justify-center
                                        font-bold text-sm
                                        {{ $member['is_head'] ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ $member['initials'] }}
                            </div>

                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <a href="{{ $member['url'] }}"
                                        class="text-sm font-semibold text-slate-900 hover:text-primary-600 transition-colors truncate">
                                        {{ $member['name'] }}
                                    </a>
                                    @if($member['is_head'])
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px]
                                                 font-semibold bg-amber-100 text-amber-700">
                                        Chủ hộ
                                    </span>
                                    @endif
                                </div>

                                <div class="mt-1.5 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500">
                                    @if($member['saint_name'])
                                    <span>Thánh: {{ $member['saint_name'] }}</span>
                                    @endif
                                    @if($member['birthday'])
                                    <span>Sinh: {{ $member['birthday'] }}</span>
                                    @endif
                                    <span>{{ $member['gender'] }}</span>
                                    @if($member['phone'])
                                    <span>{{ $member['phone'] }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-1 flex-shrink-0">
                            @if(!$member['is_head'])
                            <x-tooltip content="Đặt làm chủ hộ">
                                <button
                                    wire:click="setAsHead({{ $member['id'] }})"
                                    class="p-2 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition-all">
                                    <x-icon name="star" />
                                </button>
                            </x-tooltip>
                            @endif

                            <x-tooltip content="Xóa khỏi gia đình">
                                <button
                                    wire:click="confirmRemoveMember({{ $member['id'] }}, '{{ addslashes($member['name']) }}')"
                                    class="p-2 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-all">
                                    <x-icon name="trash" />
                                </button>
                            </x-tooltip>
                        </div>

                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="p-12">
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
        </div>
        @endif

        @endif {{-- end !isLoading --}}

    </div>

    {{-- ══ MODAL: Thêm thành viên (theo pattern parish-group) ══ --}}
    <div
        x-show="showAddModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="add-member-title"
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
            class="bg-white rounded-2xl shadow-xl w-full max-w-3xl max-h-[90vh] overflow-hidden flex flex-col"
            @click.stop>

            {{-- Header --}}
            <div class="flex-shrink-0 p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 id="add-member-title" class="text-xl font-bold text-slate-900">Thêm thành viên</h2>
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
                    <x-search-input wireModel="memberSearch" placeholder="Tìm kiếm giáo dân..." />
                </div>
            </div>

            {{-- Body --}}
            <div class="flex-1 overflow-y-auto">
                @if($availableParishioners && $availableParishioners->count() > 0)

                <div class="px-5 py-3 border-b border-slate-100 bg-slate-50/60 flex items-center gap-3">
                    <input
                        type="checkbox"
                        wire:model="selectAllParishioners"
                        class="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                    <span class="text-sm text-slate-600 font-medium">
                        Chọn tất cả ({{ $availableParishioners->total() }})
                    </span>
                </div>

                <div class="divide-y divide-slate-100">
                    @foreach($availableParishioners as $person)
                    <label
                        class="flex items-center gap-4 px-5 py-4 hover:bg-slate-50 transition cursor-pointer"
                        wire:key="avail-{{ $person->id }}">
                        <input
                            type="checkbox"
                            wire:model="selectedParishioners"
                            value="{{ $person->id }}"
                            class="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-slate-800 truncate">
                                {{ trim(($person->last_name ?? '') . ' ' . ($person->first_name ?? '')) }}
                            </p>
                            @if($person->birthday)
                            <p class="text-xs text-slate-400 mt-0.5">
                                Sinh: {{ $person->birthday->format('d/m/Y') }}
                            </p>
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
                    <svg class="mx-auto w-12 h-12 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <p class="mt-3 text-sm text-slate-500 font-medium">Không tìm thấy giáo dân phù hợp</p>
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
                    <x-action-button
                        @click="showAddModal = false; $wire.closeAddMemberModal()"
                        variant="secondary">
                        Hủy
                    </x-action-button>
                    <x-action-button wire:click="addMembers" icon="plus" :loading="true">
                        Thêm vào gia đình
                    </x-action-button>
                </div>
            </div>

        </div>
    </div>

    {{-- ══ MODAL: Xác nhận xóa thành viên ══ --}}
    @if($showRemoveModal)
    <div
        class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        role="dialog"
        aria-modal="true"
        @keydown.escape.window="$wire.closeRemoveModal()">

        <div
            class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden"
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
                    Hồ sơ giáo dân vẫn được giữ lại trong hệ thống, chỉ xóa khỏi gia đình này.
                </p>

                <div class="flex justify-end gap-3 pt-2">
                    <x-action-button wire:click="closeRemoveModal" variant="secondary">
                        Hủy
                    </x-action-button>
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
<span class="text-slate-800 font-semibold text-sm">
    {{ $family['name'] ?? 'Chi tiết gia đình' }}
</span>
@endpush