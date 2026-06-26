@section('topbar')
<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => route('parishioners.dashboard')],
    ['label' => 'Hội đoàn']
]" />
@endsection

<div
    class="min-h-screen bg-slate-50 p-2 sm:p-4 lg:p-6"
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
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <x-page-header
                title="Quản lý hội đoàn"
                description="Danh sách các hội đoàn trong giáo xứ"
                :stat-value="$associations->total()"
                stat-label="Hội đoàn"
                icon-type="parish">
            </x-page-header>

            <div class="p-4 lg:p-6 border-b border-slate-200 bg-slate-50/70 rounded-b-2xl">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <x-search-input
                        wireModel="search"
                        placeholder="Tìm kiếm hội đoàn..."
                        class="max-w-md" />

                    <x-button wire:click="create" variant="primary">
                        <x-icon name="plus" />
                        Thêm hội đoàn
                    </x-button>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            @if ($associations->count() > 0)
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
                                TÊN HỘI ĐOÀN
                            </x-table-header>
                            <x-table-header align="center">THÁNH BỔN MẠNG</x-table-header>
                            <x-table-header
                                align="center"
                                :sortable="true"
                                sort-field="ngaythanhlap"
                                :current-sort="$sortField"
                                :sort-direction="$sortDirection">
                                NGÀY THÀNH LẬP
                            </x-table-header>
                            <x-table-header
                                align="center"
                                :sortable="true"
                                sort-field="parishioners_count"
                                :current-sort="$sortField"
                                :sort-direction="$sortDirection">
                                GIÁO DÂN
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
                        @foreach ($associations as $index => $association)
                        <tr class="hover:bg-slate-50 transition-colors" wire:key="association-{{ $association->id }}">
                            <td class="px-6 py-4 text-sm text-slate-500">
                                {{ ($associations->firstItem() ?? 0) + $index }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-semibold text-slate-900">{{ $association->name }}</span>
                                @if($association->note)
                                <p class="text-xs text-slate-500 mt-0.5 line-clamp-1">{{ $association->note }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center text-sm text-slate-700">
                                {{ $association->thanhbonmang ?: '—' }}
                            </td>
                            <td class="px-6 py-4 text-center text-sm text-slate-700">
                                {{ $association->ngaythanhlap?->format('d/m/Y') ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="inline-flex items-center gap-1.5 text-sm text-slate-700">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span class="font-semibold">{{ $association->parishioners_count ?? 0 }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full
                                    {{ $association->status
                                        ? 'bg-primary-100 text-primary-700'
                                        : 'bg-slate-200 text-slate-600' }}">
                                    {{ $association->status ? 'Hoạt động' : 'Lưu trữ' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-3">
                                    <x-tooltip content="Chỉnh sửa">
                                        <x-table-action wire="edit({{ $association->id }})" icon="edit" :icon-only="true" />
                                    </x-tooltip>
                                    <span class="text-slate-300">|</span>
                                    <x-tooltip :content="$association->status ? 'Lưu trữ' : 'Kích hoạt'">
                                        <x-table-action
                                            wire="toggleStatus({{ $association->id }})"
                                            :icon="$association->status ? 'archive' : 'check'"
                                            :color="$association->status ? 'warning' : 'success'"
                                            :loading="true"
                                            debounce="500" />
                                    </x-tooltip>
                                    <span class="text-slate-300">|</span>
                                    @php $canDelete = ($association->parishioners_count ?? 0) == 0; @endphp
                                    <x-tooltip :content="$canDelete ? 'Xóa hội đoàn' : 'Không thể xóa hội đoàn đang có giáo dân'">
                                        <x-table-action
                                            wire="delete({{ $association->id }})"
                                            icon="trash"
                                            color="danger"
                                            :confirm="$canDelete ? 'Xóa hội đoàn ' . $association->name . '?' : null"
                                            :loading="true"
                                            :disabled="!$canDelete" />
                                    </x-tooltip>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($associations->hasPages())
            <div class="px-6 py-4 border-t border-slate-200">
                <x-pagination :paginator="$associations" :per-page-options="[10, 15, 25, 50]" />
            </div>
            @endif
            @else
            <x-stats.page-empty
                tone="primary"
                title="Chưa có hội đoàn nào"
                description="Hãy thêm hội đoàn đầu tiên cho giáo xứ">
                <x-slot name="icon">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </x-slot>
                <x-button wire:click="create" variant="primary">
                    <x-icon name="plus" />
                    Thêm hội đoàn
                </x-button>
            </x-stats.page-empty>
            @endif
        </div>
    </div>

    <div
        x-show="showForm"
        x-transition.opacity
        class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="association-modal-title"
        @click="showForm = false; $wire.closeModal()"
        @keydown.escape.window="showForm = false; $wire.closeModal()">

        <div
            x-show="showForm"
            x-transition
            class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-hidden flex flex-col"
            @click.stop>

            <div class="flex-shrink-0 p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 id="association-modal-title" class="text-xl font-bold text-slate-900">
                            {{ $editingId ? 'Cập nhật hội đoàn' : 'Thêm hội đoàn mới' }}
                        </h2>
                        <p class="text-sm text-slate-600 mt-1">Thiết lập thông tin hội đoàn trong giáo xứ</p>
                    </div>
                    <button @click="showForm = false; $wire.closeModal()"
                        class="flex-shrink-0 p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-6 space-y-5">
                @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 rounded-xl p-4">
                    <ul class="space-y-1 text-sm text-red-700">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <x-form-input
                    label="Tên hội đoàn"
                    name="name"
                    wire:model.defer="name"
                    placeholder="VD: Hội đoàn Thánh Giuse..."
                    required />

                <x-form-input
                    label="Thánh bổn mạng"
                    name="thanhbonmang"
                    wire:model.defer="thanhbonmang"
                    placeholder="VD: Thánh Giuse..." />

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-form-input
                        label="Ngày thành lập"
                        name="ngaythanhlap"
                        type="date"
                        wire:model.defer="ngaythanhlap" />

                    <x-form-input
                        label="Ngày bổn mạng"
                        name="ngaybonmang"
                        type="date"
                        wire:model.defer="ngaybonmang" />
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Ghi chú</label>
                    <textarea
                        wire:model.defer="note"
                        rows="3"
                        class="w-full rounded-xl border-slate-300 text-sm focus:border-primary-500 focus:ring-primary-500"
                        placeholder="Ghi chú thêm (tùy chọn)"></textarea>
                    @error('note') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="border border-slate-200 rounded-xl p-4">
                    <div class="flex items-center gap-3">
                        <input id="association-status" type="checkbox" wire:model.defer="status"
                            class="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                        <label for="association-status" class="text-sm font-semibold text-slate-900 cursor-pointer">
                            Kích hoạt hội đoàn
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex-shrink-0 px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end gap-3">
                <x-action-button @click="showForm = false; $wire.closeModal()" variant="secondary">Huỷ</x-action-button>
                <x-action-button wire:click="save" icon="save" :loading="true">
                    {{ $editingId ? 'Cập nhật' : 'Thêm mới' }}
                </x-action-button>
            </div>
        </div>
    </div>
</div>
