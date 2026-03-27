<div
    class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6"
    x-data="{ showModal: @entangle('showModal').defer }">

    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="mx-auto max-w-3xl space-y-5">

        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[
            [
                'label' => 'Trang chủ',
                'url' => route('dashboard'),
            ],
            [
                'label' => 'Quản lý Tên thánh',
                'url' => route('holy-names.index'),
                'icon' => '<svg class=\'w-4 h-4\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M12 8c-1.657 0-3 1.343-3 3v2h6v-2c0-1.657-1.343-3-3-3z\'/></svg>'
            ],
        ]" separator="arrow" />

        {{-- Toast Notifications --}}
        <div role="status" aria-live="polite">
            @if (session()->has('message'))
            <x-toast-notification type="success" :duration="3500">
                {{ session('message') }}
            </x-toast-notification>
            @endif

            @if (session()->has('error'))
            <x-toast-notification type="error" :duration="3500">
                {{ session('error') }}
            </x-toast-notification>
            @endif
        </div>

        {{-- Main Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            {{-- Header --}}
            <x-page-header
                title="Quản lý Tên thánh"
                description="Danh sách các Tên thánh trong hệ thống"
                :stat-value="$holies?->total()"
                stat-label="Tên thánh"
                icon-type="church">
            </x-page-header>

            {{-- Actions Bar --}}
            <div class="px-4 py-3 border-b border-slate-200 bg-slate-50/70">
                <div class="flex justify-end">
                    <x-action-button wire="create" icon="plus">
                        Thêm Tên thánh
                    </x-action-button>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-separate border-spacing-0">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <x-table-header>STT</x-table-header>
                            <x-table-header>Tên thánh</x-table-header>
                            <x-table-header class="text-center">Thao tác</x-table-header>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse ($holies as $i => $holy)
                        <tr class="hover:bg-slate-50 transition-colors" wire:key="holy-{{ $holy->id }}">
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
                                    <x-table-action
                                        wire="edit({{ $holy->id }})"
                                        icon="edit">
                                        Sửa
                                    </x-table-action>

                                    <span class="text-slate-300">|</span>

                                    <x-table-action
                                        wire="delete({{ $holy->id }})"
                                        icon="trash"
                                        color="danger"
                                        :loading="true"
                                        :confirm="'Xóa tên thánh ' . $holy->name . '?'">
                                        Xóa
                                    </x-table-action>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-12">
                                <x-empty-state
                                    icon="church"
                                    title="Chưa có tên thánh"
                                    description="Hãy tạo tên thánh đầu tiên">
                                    <x-action-button wire="create" icon="plus">
                                        Thêm tên thánh
                                    </x-action-button>
                                </x-empty-state>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($holies->hasPages())
            <div class="border-t border-slate-200">
                <x-pagination
                    :paginator="$holies"
                    :per-page-options="[10, 15, 25, 50]" />
            </div>
            @endif
        </div>

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
        @click="showModal = false; $wire.closeModal()">

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