<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="mx-auto max-w-7xl space-y-5">

        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[
            [
                'label' => 'Trang chủ',
                'url' => route('home'),
            ],
            [
                'label' => 'Quản lý Tên thánh',
                'url' => route('holies'),
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
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/70">
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
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 text-sm text-slate-500">
                                {{ $holies->firstItem() + $i }}
                            </td>

                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-900">
                                    {{ $holy->name }}
                                </div>
                            </td>

                            <td class="px-6 py-4">
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
                                        onclick="confirm('Xóa Tên thánh này?') || event.stopImmediatePropagation()">
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
                                    title="Chưa có Holy"
                                    description="Hãy tạo Holy đầu tiên">
                                    <x-action-button wire="create" icon="plus">
                                        Thêm Holy
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

        {{-- Modal Form --}}
        @if ($showModal)
        <div
            class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
            role="dialog"
            aria-modal="true"
            wire:click="closeModal">

            <div
                class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden flex flex-col"
                wire:click.stop>

                {{-- Header --}}
                <div class="flex-shrink-0 p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                    <h2 class="text-xl font-bold text-slate-900">
                        {{ $holyId ? 'Cập nhật Tên thánh' : 'Thêm Tên thánh mới' }}
                    </h2>
                    <p class="text-sm text-slate-600 mt-1">
                        Nhập thông tin Tên thánh
                    </p>
                </div>

                {{-- Body --}}
                <div class="flex-1 p-6 space-y-4">
                    <x-form-input
                        label="Tên Tên thánh"
                        name="name"
                        wire:model.defer="name"
                        placeholder="Nhập tên Tên thánh"
                        required />
                </div>

                {{-- Footer --}}
                <div class="flex-shrink-0 px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end gap-3">
                    <x-action-button wire="closeModal" variant="secondary">
                        Hủy
                    </x-action-button>
                    <x-action-button wire="save" icon="save" :loading="true">
                        Lưu
                    </x-action-button>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>