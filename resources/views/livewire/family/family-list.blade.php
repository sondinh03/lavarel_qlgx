@section('topbar')
<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => route('parishioners.dashboard')],
    ['label' => 'Gia đình']
]" />
@endsection

<div class="min-h-screen bg-slate-50 p-2 sm:p-4 lg:p-6"
    style="min-height: calc(100vh - 56px - var(--bottom-offset));"
    x-data="{ showForm: false }"
    x-init="
        document.addEventListener('livewire:load', () => {
            Livewire.on('openModal', () => { showForm = true; });
            Livewire.on('closeModal', () => { showForm = false; });
        });
    ">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="mx-auto max-w-7xl space-y-6">

        {{-- Main Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <x-page-header
                class="rounded-t-2xl"
                title="Danh sách gia đình"
                description="Quản lý hồ sơ gia đình trong giáo xứ"
                :stat-value="$families->total()"
                stat-label="Gia đình"
                icon-type="default" />

            <div class="p-4 lg:p-6 border-b border-slate-200 bg-slate-50/70">
                <div class="flex flex-col gap-4">

                    {{-- Filters row --}}
                    <div class="flex items-end gap-3 flex-wrap">
                        <div class="flex-1 min-w-[160px]">
                            <label class="block text-xs font-semibold text-slate-500 mb-1 uppercase tracking-wide">Giáo họ</label>
                            <select wire:model="parishGroupFilter"
                                class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm bg-white
                                       focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="">-- Tất cả giáo họ --</option>
                                @foreach($parishGroups as $group)
                                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex-1 min-w-[140px]">
                            <label class="block text-xs font-semibold text-slate-500 mb-1 uppercase tracking-wide">Trạng thái</label>
                            <select wire:model="statusFilter"
                                class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm bg-white
                                       focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="">-- Tất cả --</option>
                                <option value="1">Hoạt động</option>
                                <option value="0">Không hoạt động</option>
                            </select>
                        </div>

                        <div class="flex-shrink-0 pb-0.5">
                            <x-button wire:click="resetFilters" variant="subtle">
                                <x-icon name="refresh" />
                                Đặt lại
                            </x-button>
                        </div>
                    </div>

                    {{-- Search + actions --}}
                    <div class="flex items-center justify-between gap-3 flex-wrap">
                        <x-search-input
                            wireModel="search"
                            placeholder="Tìm theo tên gia đình, chủ hộ..."
                            class="max-w-md" />

                        <x-button as="a" href="{{ route('families.create') }}" variant="primary">
                            <x-icon name="plus" />
                            Thêm gia đình
                        </x-button>
                    </div>

                </div>
            </div>

            {{-- Stats bar --}}
            <div class="px-4 lg:px-6 py-3 border-b border-slate-100 bg-slate-50/60 flex items-center gap-6 flex-wrap text-sm">
                <span class="text-slate-500">
                    Tổng: <strong class="text-slate-800">{{ $stats['total'] }}</strong>
                </span>
                <span class="text-emerald-600">
                    Hoạt động: <strong>{{ $stats['active'] }}</strong>
                </span>
                <span class="text-slate-400">
                    Không hoạt động: <strong>{{ $stats['inactive'] }}</strong>
                </span>
            </div>
        </div>

        {{-- Table Card --}}
        @if($families && $families->count() > 0)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-separate border-spacing-0">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <x-table-header class="w-10">
                                <input type="checkbox" wire:model="selectAll"
                                    class="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                            </x-table-header>
                            <x-table-header class="w-12">STT</x-table-header>
                            <x-table-header wire:click="sortBy('name')" class="cursor-pointer select-none">
                                <div class="flex items-center gap-1">
                                    Tên gia đình
                                    @if($sortField === 'name')
                                        <x-icon name="{{ $sortDirection === 'asc' ? 'sort-asc' : 'sort-desc' }}" class="w-3.5 h-3.5 text-primary-500" />
                                    @else
                                        <x-icon name="sort" class="w-3.5 h-3.5 text-slate-300" />
                                    @endif
                                </div>
                            </x-table-header>
                            <x-table-header>Chủ hộ</x-table-header>
                            <x-table-header>Giáo họ</x-table-header>
                            <x-table-header class="text-center">Thành viên</x-table-header>
                            <x-table-header wire:click="sortBy('status')" class="cursor-pointer select-none">
                                <div class="flex items-center gap-1">
                                    Trạng thái
                                    @if($sortField === 'status')
                                        <x-icon name="{{ $sortDirection === 'asc' ? 'sort-asc' : 'sort-desc' }}" class="w-3.5 h-3.5 text-primary-500" />
                                    @else
                                        <x-icon name="sort" class="w-3.5 h-3.5 text-slate-300" />
                                    @endif
                                </div>
                            </x-table-header>
                            <x-table-header class="text-center">Thao tác</x-table-header>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @foreach($families as $index => $family)
                        <tr class="hover:bg-slate-50 transition-colors" wire:key="family-{{ $family->id }}">

                            <td class="px-4 py-3">
                                <input type="checkbox" wire:model="selectedFamilies"
                                    value="{{ $family->id }}"
                                    class="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                            </td>

                            <td class="px-4 py-3 text-sm font-semibold text-slate-400">
                                {{ ($families->firstItem() ?? 0) + $index }}
                            </td>

                            <td class="px-4 py-3">
                                <a href="{{ route('families.show', $family->id) }}"
                                    class="text-sm font-semibold text-slate-900 hover:text-primary-600 transition-colors">
                                    {{ $family->name }}
                                </a>
                                @if($family->note)
                                    <p class="text-xs text-slate-400 mt-0.5 truncate max-w-[200px]">{{ $family->note }}</p>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-sm text-slate-700 whitespace-nowrap">
                                @if($family->head)
                                    <a href="{{ route('parishioners.show', $family->head_id) }}"
                                        class="hover:text-primary-600 transition-colors">
                                        {{ trim($family->head->last_name . ' ' . $family->head->first_name) }}
                                    </a>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-sm text-slate-600">
                                {{ $family->parishGroup->name ?? '—' }}
                            </td>

                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full
                                             bg-slate-100 text-slate-700 font-semibold text-xs">
                                    {{ $family->members_count }}
                                </span>
                            </td>

                            <td class="px-4 py-3">
                                @if($family->status)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                                 bg-emerald-100 text-emerald-700">
                                        Hoạt động
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                                 bg-slate-100 text-slate-500">
                                        Không hoạt động
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    <x-tooltip content="Xem chi tiết">
                                        <a href="{{ route('families.show', $family->id) }}"
                                            class="p-2 hover:bg-slate-100 text-slate-600 rounded-lg transition-all">
                                            <x-icon name="eye" />
                                        </a>
                                    </x-tooltip>

                                    <x-tooltip content="Chỉnh sửa nhanh">
                                        <button wire:click="edit({{ $family->id }})"
                                            class="p-2 hover:bg-primary-50 text-primary-600 rounded-lg transition-all">
                                            <x-icon name="edit" />
                                        </button>
                                    </x-tooltip>

                                    <x-dropdown icon="more-vertical" align="right" variant="subtle" position="fixed">
                                        <x-dropdown-item
                                            x-on:click="$dispatch('open-confirm', {
                                                message: 'Xóa gia đình {{ $family->name }}?',
                                                description: 'Chỉ có thể xóa gia đình không có thành viên.',
                                                wireMethod: 'delete({{ $family->id }})'
                                            })"
                                            icon="trash"
                                            :disabled="$family->members_count > 0"
                                            class="{{ $family->members_count > 0 ? 'opacity-50 cursor-not-allowed' : 'text-red-600 hover:bg-red-50' }}">
                                            Xóa gia đình
                                        </x-dropdown-item>
                                    </x-dropdown>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Bulk action bar --}}
            @if(count($selectedFamilies) > 0)
            <div class="px-6 py-3 bg-primary-50 border-t border-primary-200 flex items-center justify-between">
                <span class="text-sm font-semibold text-primary-700">
                    Đã chọn {{ count($selectedFamilies) }} gia đình
                </span>
                <div class="flex items-center gap-2">
                    <x-button
                        x-on:click="$dispatch('open-confirm', {
                            message: 'Xóa {{ count($selectedFamilies) }} gia đình đã chọn?',
                            description: 'Chỉ xóa gia đình không có thành viên.',
                            wireMethod: 'bulkDelete'
                        })"
                        variant="danger-subtle"
                        size="sm">
                        Xóa đã chọn
                    </x-button>
                    <button type="button" wire:click="$set('selectedFamilies', [])"
                        class="px-3 py-1.5 text-sm font-semibold text-primary-600
                               hover:bg-primary-100 rounded-lg transition">
                        Bỏ chọn
                    </button>
                </div>
            </div>
            @endif

            {{-- Pagination --}}
            @if($families->hasPages())
            <div class="p-6 border-t border-slate-200">
                <x-pagination :paginator="$families" :per-page-options="[10, 15, 25, 50, 100]" />
            </div>
            @endif
        </div>

        @else
        <x-stats.page-empty
            tone="primary"
            title="Chưa có gia đình nào"
            description="Hãy thêm gia đình đầu tiên cho giáo xứ">
            <x-slot name="icon">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </x-slot>
            <x-button as="a" href="{{ route('families.create') }}" variant="primary">
                <x-icon name="plus" />
                Thêm gia đình
            </x-button>
        </x-stats.page-empty>
        @endif

    </div>

    {{-- Modal: Chỉnh sửa nhanh --}}
    <div
        x-show="showForm"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        role="dialog"
        aria-modal="true"
        @click="showForm = false; $wire.closeModal()"
        @keydown.escape.window="showForm = false; $wire.closeModal()">

        <div
            x-show="showForm"
            x-transition
            class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden flex flex-col"
            @click.stop>

            <div class="flex-shrink-0 p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">Cập nhật gia đình</h2>
                        <p class="text-sm text-slate-500 mt-1">Chỉnh sửa tên gia đình</p>
                    </div>
                    <button type="button"
                        @click="showForm = false; $wire.closeModal()"
                        class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                        <x-icon name="cancel" class="w-5 h-5" />
                    </button>
                </div>
            </div>

            <div class="p-6 space-y-4">
                @if($errors->has('modalName'))
                <div class="bg-red-50 border-l-4 border-red-500 rounded-xl p-4">
                    <p class="text-sm text-red-700">{{ $errors->first('modalName') }}</p>
                </div>
                @endif

                <x-form-input
                    label="Tên gia đình"
                    name="modalName"
                    wire:model.defer="modalName"
                    placeholder="VD: Gia đình ông Nguyễn Văn A..."
                    required />
            </div>

            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end gap-3">
                <x-button variant="outline" @click="showForm = false; $wire.closeModal()">Hủy</x-button>
                <x-button variant="primary" wire:click="save" :loading="true" loading-target="save">
                    <x-icon name="save" />
                    Cập nhật
                </x-button>
            </div>
        </div>
    </div>
</div>

@push('page-title')
<span class="text-slate-800 font-semibold text-sm">Danh sách gia đình</span>
@endpush
