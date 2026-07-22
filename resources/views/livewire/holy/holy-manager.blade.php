@section('topbar')
@php
    $isCatechismAdmin = auth()->user()?->canManageCatechism();
@endphp
<x-breadcrumb :items="[
    ['label' => $isCatechismAdmin ? 'Giáo lý' : 'Trang chủ', 'url' => $isCatechismAdmin ? route('parish-admin.dashboard') : route('parishioners.dashboard')],
    ['label' => 'Tên thánh']
]" />
@endsection

<div
    class="min-h-screen bg-apple-gray p-2 sm:p-4 lg:p-6"
    style="min-height: calc(100vh - 56px - var(--bottom-offset));"
    x-data="{ showModal: false }"
    x-init="
        document.addEventListener('livewire:load', () => {
            Livewire.on('openModal', () => {
                showModal = true;
            });
            Livewire.on('closeModal', () => {
                showModal = false;
            });
        });
    ">

    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="mx-auto max-w-7xl">
        <x-mac-panel :overflow="true">
            <x-page-header
                title="Quản lý Tên thánh"
                description="Danh sách các Tên thánh trong hệ thống"
                icon-type="church" />

            <div class="p-4 lg:p-6 mac-hairline-b bg-white/30">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <x-search-input
                        wire-model="search"
                        placeholder="Tìm kiếm tên thánh..."
                        debounce="500ms"
                        class="max-w-md" />

                    <x-button wire:click="create" variant="primary">
                        <x-icon name="plus"/>
                        Thêm Tên thánh
                    </x-button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-separate border-spacing-0">
                    <thead class="bg-slate-50/50 mac-hairline-b">
                        <tr>
                            <x-table-header>STT</x-table-header>
                            <x-table-header>Tên thánh</x-table-header>
                            <x-table-header class="text-center">Thao tác</x-table-header>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-black/[0.04]">
                        @forelse ($holies as $i => $holy)
                        <tr class="hover:bg-black/[0.03] transition-colors" wire:key="holy-{{ $holy->id }}">
                            <td class="px-4 py-3 text-sm text-slate-500">
                                {{ $holies->firstItem() + $i }}
                            </td>

                            <td class="px-4 py-3">
                                <div class="font-semibold text-slate-900">
                                    {{ $holy->name }}
                                </div>
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-3">
                                    <x-tooltip content="Sửa tên thánh">
                                        <x-table-action
                                            wire="edit({{ $holy->id }})"
                                            icon="edit"
                                            :icon-only="true">
                                        </x-table-action>
                                    </x-tooltip>

                                    <span class="text-slate-300">|</span>

                                    @php
                                    $canDelete = $holy->students_count == 0;
                                    @endphp

                                    <x-tooltip :content="$canDelete 
                                        ? 'Xóa tên thánh' 
                                        : 'Không thể xóa tên thánh đang được sử dụng'">

                                        <x-table-action
                                            wire="delete({{ $holy->id }})"
                                            icon="trash"
                                            color="danger"
                                            :confirm="$canDelete 
                                                ? 'Xóa tên thánh ' . $holy->name . '?' 
                                                : null"
                                            :loading="true"
                                            :disabled="!$canDelete">
                                        </x-table-action>

                                    </x-tooltip>


                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="p-0 border-none">
                                <x-stats.page-empty
                                    :panel="false"
                                    tone="primary"
                                    title="Chưa có tên thánh"
                                    description="Hãy tạo tên thánh đầu tiên">
                                    <x-slot name="icon">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </x-slot>
                                    <x-action-button wire="create" icon="plus">
                                        Thêm tên thánh
                                    </x-action-button>
                                </x-stats.page-empty>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($holies->hasPages())
            <div class="mac-hairline-t">
                <x-pagination
                    :paginator="$holies"
                    :per-page-options="[10, 15, 25, 50]" />
            </div>
            @endif
        </x-mac-panel>

    </div>

    {{-- Modal Form — ngoài space-y-5 để tránh margin --}}
    <div
        x-show="showModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="holy-modal-title"
        @click="showModal = false; $wire.closeModal()"
        @keydown.escape.window="showModal = false; $wire.closeModal()"
        @keydown.enter.window="if(showModal) $wire.save()">

        {{-- Modal box --}}
        <div
            x-show="showModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 scale-95"
            class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-hidden flex flex-col"
            @click.stop>

            {{-- Header --}}
            <div class="flex-shrink-0 p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 id="holy-modal-title" class="text-xl font-bold text-slate-900">
                            {{ $holyId ? 'Cập nhật Tên thánh' : 'Thêm Tên thánh mới' }}
                        </h2>
                        <p class="text-sm text-slate-600 mt-1">
                            Nhập thông tin Tên thánh
                        </p>
                    </div>

                    <button
                        @click="showModal = false; $wire.closeModal()"
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
            <div class="flex-1 overflow-y-auto p-6 space-y-4">

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

                <x-form-input
                    label="Tên thánh"
                    name="name"
                    wire:model.defer="name"
                    placeholder="Nhập tên thánh, VD: Maria, Giuse..."
                    required />
            </div>

            {{-- Footer --}}
            <div class="flex-shrink-0 px-4 py-3 border-t border-slate-200 bg-slate-50 flex justify-end gap-3">
                <x-action-button
                    @click="showModal = false; $wire.closeModal()"
                    variant="secondary">
                    Hủy
                </x-action-button>
                <x-action-button wire:click="save" icon="save" :loading="true">
                    {{ $holyId ? 'Cập nhật' : 'Thêm mới' }}
                </x-action-button>
            </div>

        </div>
    </div>

</div>