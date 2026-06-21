@section('topbar')
<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => route('parishioners.dashboard')],
    ['label' => 'Giáo họ']
]" />
@endsection

<div
    class="min-h-screen bg-slate-50 p-2 sm:p-4 lg:p-6"
    style="min-height: calc(100vh - 56px - var(--bottom-offset));"
    x-data="{ showForm: false }"
    x-init="
        document.addEventListener('livewire:load', () => {
            Livewire.on('openModal', () => {
                showForm = true;
            });
            Livewire.on('closeModal', () => {
                showForm = false;
            });
        });
    ">

    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="mx-auto max-w-7xl space-y-6">
        {{-- Main Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

            {{-- Header --}}
            <x-page-header
                title="Quản lý giáo họ"
                description="Danh sách các giáo họ trong giáo xứ"
                :stat-value="$groups->count()"
                stat-label="Giáo họ"
                icon-type="parish">
            </x-page-header>

            {{-- Actions Bar --}}
            <div class="p-4 lg:p-6 border-b border-slate-200 bg-slate-50/70 rounded-b-2xl">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <x-search-input
                        wireModel="search"
                        placeholder="Tìm kiếm giáo họ..."
                        class="max-w-md" />

                    <x-button wire:click="create" variant="primary">
                        <x-icon name="plus" />
                        Thêm giáo họ
                    </x-button>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            @if ($groups->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full border-separate border-spacing-0">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <x-table-header>STT</x-table-header>
                            <x-table-header
                                align="center"
                                :sortable="true"
                                sort-field="name"
                                :current-sort="$sortField"
                                :sort-direction="$sortDirection">
                                TÊN GIÁO HỌ
                            </x-table-header>

                            <x-table-header
                                align="center"
                                :sortable="true"
                                sort-field="students_count"
                                :current-sort="$sortField"
                                :sort-direction="$sortDirection">
                                HỌC SINH
                            </x-table-header>

                            <x-table-header
                                align="center"
                                :sortable="true"
                                sort-field="status"
                                :current-sort="$sortField"
                                :sort-direction="$sortDirection">
                                TRẠNG THÁI
                            </x-table-header>
                            <x-table-header class="text-center">Thao tác</x-table-header>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @foreach ($groups as $index => $group)
                        <tr class="hover:bg-slate-50 transition-colors"
                            wire:key="group-{{ $group->id }}">

                            {{-- STT --}}
                            <td class="px-6 py-4 text-sm text-slate-500">
                                {{ ($groups->firstItem() ?? 0) + $index }}
                            </td>

                            {{-- Tên --}}
                            <td class="px-6 py-4">
                                <span class="font-semibold text-slate-900">
                                    {{ $group->name }}
                                </span>
                            </td>

                            {{-- Học sinh --}}
                            <td class="px-6 py-4 text-center">
                                <div class="inline-flex items-center gap-1.5 text-sm text-slate-700">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span class="font-semibold">
                                        {{ $group->students_count ?? $group->students()->count() }}
                                    </span>
                                </div>
                            </td>

                            {{-- Trạng thái --}}
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full
                                            {{ $group->status
                                                ? 'bg-primary-100 text-primary-700'
                                                : 'bg-slate-200 text-slate-600' }}">
                                    {{ $group->status ? 'Hoạt động' : 'Lưu trữ' }}
                                </span>
                            </td>

                            {{-- Thao tác --}}
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-3">
                                    <x-tooltip content='Chỉnh sửa'>
                                        <x-table-action
                                            wire="edit({{ $group->id }})"
                                            icon="edit"
                                            :icon-only="true">
                                        </x-table-action>
                                    </x-tooltip>

                                    <span class="text-slate-300">|</span>

                                    <x-tooltip :content="$group->status ? 'Lưu trữ' : 'Kích hoạt'">
                                        <x-table-action
                                            wire="toggleStatus({{ $group->id }})"
                                            :icon="$group->status ? 'archive' : 'check'"
                                            :color="$group->status ? 'warning' : 'success'"
                                            :loading="true"
                                            debounce="500">
                                        </x-table-action>
                                    </x-tooltip>

                                    <span class="text-slate-300">|</span>

                                    @php
                                    $canDelete = $group->students_count == 0;
                                    @endphp

                                    <x-tooltip :content="$canDelete
                                        ? 'Xóa giáo họ' 
                                        : 'Không thể xóa giáo họ đang có học sinh'">
                                        <x-table-action
                                            wire="delete({{ $group->id }})"
                                            icon="trash"
                                            color="danger"
                                            :confirm="$canDelete
                                            ? 'Xóa giáo họ ' . $group->name . '?' 
                                            : null"
                                            :loading="true"
                                            :disabled="!$canDelete">
                                        </x-table-action>
                                    </x-tooltip>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($groups->hasPages())
            <div class="px-6 py-4 border-t border-slate-200">
                <x-pagination :paginator="$groups" :per-page-options="[10, 15, 25, 50]" />
            </div>
            @endif
            @else
            <x-stats.page-empty
                tone="primary"
                title="Chưa có giáo họ nào"
                description="Hãy thêm giáo họ đầu tiên cho giáo xứ">
                <x-slot name="icon">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </x-slot>
                <x-button wire:click="create" variant="primary">
                    <x-icon name="plus" />
                    Thêm giáo họ
                </x-button>
            </x-stats.page-empty>
            @endif
        </div>
    </div>

    <div
        x-show="showForm"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="group-modal-title"
        @click="showForm = false; $wire.closeModal()"
        @keydown.escape.window="showForm = false; $wire.closeModal()"
        @keydown.enter.window="if(showForm) $wire.save()">

        {{-- Modal box --}}
        <div
            x-show="showForm"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 scale-95"
            class="bg-white rounded-2xl shadow-xl w-full max-w-md max-h-[90vh] overflow-hidden flex flex-col"
            @click.stop>

            {{-- Header --}}
            <div class="flex-shrink-0 p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 id="group-modal-title" class="text-xl font-bold text-slate-900">
                            {{ $editingId ? 'Cập nhật giáo họ' : 'Thêm giáo họ mới' }}
                        </h2>
                        <p class="text-sm text-slate-600 mt-1">
                            Thiết lập thông tin giáo họ trong giáo xứ
                        </p>
                    </div>

                    <button
                        @click="showForm = false; $wire.closeModal()"
                        class="flex-shrink-0 p-1 rounded-lg text-slate-400 hover:text-slate-600
                               hover:bg-slate-100 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Body --}}
            <div class="flex-1 overflow-y-auto p-6 space-y-5">

                {{-- Error Summary --}}
                @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="flex-1">
                            <h4 class="text-sm font-semibold text-red-800 mb-2">
                                Vui lòng kiểm tra lại thông tin
                            </h4>
                            <ul class="space-y-1 text-sm text-red-700">
                                @foreach ($errors->all() as $error)
                                <li class="flex items-start gap-2">
                                    <span class="text-red-400 font-bold">•</span>
                                    <span>{{ $error }}</span>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Tên giáo họ --}}
                <x-form-input
                    label="Tên giáo họ"
                    name="name"
                    wire:model.defer="name"
                    placeholder="VD: Giáo họ Thánh Giuse..."
                    required />

                {{-- Trạng thái --}}
                <div class="border border-slate-200 rounded-xl p-4">
                    <div class="flex items-center gap-3">
                        <input
                            id="group-status"
                            type="checkbox"
                            wire:model.defer="status"
                            class="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                        <label for="group-status" class="text-sm font-semibold text-slate-900 cursor-pointer">
                            Kích hoạt giáo họ
                        </label>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex-shrink-0 px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end gap-3">
                <x-action-button
                    @click="showForm = false; $wire.closeModal()"
                    variant="secondary">
                    Huỷ
                </x-action-button>
                <x-action-button wire:click="save" icon="save" :loading="true">
                    {{ $editingId ? 'Cập nhật' : 'Thêm mới' }}
                </x-action-button>
            </div>

        </div>
    </div>
</div>