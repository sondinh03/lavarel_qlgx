@section('topbar')
<x-breadcrumb :items="[
    [ 'label' => 'Trang chủ', 'url' => route('parish-admin.dashboard')],
    ['label' => 'Năm học']
]" />
@endsection

<div
    class="min-h-screen bg-apple-gray p-2 sm:p-4 lg:p-6"
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

    <div id="main-content" class="mx-auto max-w-7xl">
        <x-mac-panel :overflow="true">
            <x-page-header
                title="Quản lý năm học"
                description="Danh sách các năm học của giáo xứ"
                icon-type="schoolYear" />

            <div class="p-4 lg:p-6 mac-hairline-b bg-white/30">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <x-search-input
                        wire-model="search"
                        placeholder="Tìm kiếm năm học..."
                        debounce="500ms"
                        class="max-w-md" />

                    <x-button wire:click="create" variant="primary">
                        <x-icon name="plus" />
                        Thêm năm học
                    </x-button>
                </div>
            </div>

            <div class="overflow-x-auto">
            <table class="w-full table-fixed border-separate border-spacing-0">
                <thead class="bg-slate-50/50 mac-hairline-b">
                    <tr>
                        <x-table-header class="w-12">STT</x-table-header>
                        <x-table-header class="w-24 text-center" :sortable="true" sort-field="name"
                            :current-sort="$sortField" :sort-direction="$sortDirection">
                            Tên năm học
                        </x-table-header>
                        <x-table-header class="w-28 text-center">Học kỳ I</x-table-header>
                        <x-table-header class="w-28 text-center">Học kỳ II</x-table-header>
                        <x-table-header class="w-24 text-center">Học kỳ hiện tại</x-table-header>
                        <x-table-header class="w-24 text-center" :sortable="true" sort-field="status"
                            :current-sort="$sortField" :sort-direction="$sortDirection">
                            Trạng thái
                        </x-table-header>
                        <x-table-header class="w-28 text-center">Thao tác</x-table-header>
                    </tr>
                </thead>

                <tbody class="divide-y divide-black/[0.04]">
                    @forelse ($namHocs as $i => $nh)
                    <tr class="hover:bg-black/[0.03] transition-colors">
                        <td class="px-4 py-3 text-sm text-slate-500">{{ $i + 1 }}</td>
                        <td class="px-4 py-3">
                            <div class="font-semibold text-slate-900">{{ $nh->name }}</div>
                        </td>
                        <td class="px-4 py-3 text-center text-sm text-slate-600">
                            @if($nh->start_date_one && $nh->end_date_one)
                            <div class="inline-flex items-center gap-1">
                                <span>{{ $nh->start_date_one->format('d/m/Y') }}</span>
                                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                <span>{{ $nh->end_date_one->format('d/m/Y') }}</span>
                            </div>
                            @else
                            <span class="text-slate-400">Chưa thiết lập</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center text-sm text-slate-600">
                            @if($nh->start_date_two && $nh->end_date_two)
                            <div class="inline-flex items-center gap-1">
                                <span>{{ $nh->start_date_two->format('d/m/Y') }}</span>
                                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                <span>{{ $nh->end_date_two->format('d/m/Y') }}</span>
                            </div>
                            @else
                            <span class="text-slate-400">Chưa thiết lập</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if ($nh->current_semester)
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-primary-100 text-primary-700">
                                HK {{ $nh->current_semester }}
                            </span>
                            @else
                            <span class="text-slate-400 text-sm">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full {{ $nh->status_class }}">
                                {{ $nh->status_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3 overflow-visible">
                            <div class="flex items-center justify-center gap-1">
                                <x-tooltip content="Chỉnh sửa">
                                    <button
                                        wire:click="edit({{ $nh->id }})"
                                        class="p-2 hover:bg-primary-50 text-primary-600 rounded-lg transition-all">
                                        <x-icon name="edit" />
                                    </button>
                                </x-tooltip>

                                <x-dropdown icon="more-vertical" align="right" variant="subtle" position="fixed">
                                    <x-dropdown-item
                                        wire:click.debounce.500ms="toggleStatus({{ $nh->id }})"
                                        :icon="$nh->status ? 'archive' : 'check'">
                                        {{ $nh->status ? 'Lưu trữ năm học' : 'Kích hoạt năm học' }}
                                    </x-dropdown-item>

                                    <x-dropdown-item
                                        as="a"
                                        :href="route('school-years.copy', ['target' => $nh->id])"
                                        icon="copy">
                                        Sao chép năm học
                                    </x-dropdown-item>

                                    <div class="h-px bg-slate-100 my-1"></div>

                                    <x-dropdown-item
                                        x-on:click="$dispatch('open-confirm', {
                                            message: 'Xóa năm học {{ $nh->name }}?',
                                            wireMethod: 'delete({{ $nh->id }})'
                                        })"
                                        icon="trash"
                                        class="text-red-600 hover:bg-red-50">
                                        Xóa năm học
                                    </x-dropdown-item>
                                </x-dropdown>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-0 border-none">
                            <x-stats.page-empty
                                :panel="false"
                                tone="primary"
                                title="Chưa có năm học"
                                description="Hãy tạo năm học đầu tiên cho giáo xứ">
                                <x-slot name="icon">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </x-slot>
                                <x-button wire:click="create" variant="primary">
                                    <x-icon name="plus" />
                                    Thêm năm học
                                </x-button>
                            </x-stats.page-empty>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </x-mac-panel>

    </div>

    {{-- Modal --}}
    <div
        x-show="showForm"
        x-transition.opacity
        class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        role="dialog"
        aria-modal="true"
        @click="showForm = false; $wire.closeModal()"
        @keydown.escape.window="showForm = false; $wire.closeModal()"
        @keydown.enter.window="console.log('[Enter] showForm=', showForm, 'target=', $event.target.tagName); if(showForm) $wire.save()">

        <div
            x-show="showForm"
            x-transition
            class="bg-white rounded-2xl shadow-xl w-full max-w-xl max-h-[90vh] overflow-hidden flex flex-col"
            @click.stop>

            {{-- Header --}}
            <div class="flex-shrink-0 p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">
                            {{ $editingId ? 'Cập nhật năm học' : 'Thêm năm học mới' }}
                        </h2>
                        <p class="text-sm text-slate-600 mt-1">
                            Thiết lập thông tin năm học và thời gian các học kỳ
                        </p>
                    </div>
                    <button
                        @click="showForm = false; $wire.closeModal()"
                        class="flex-shrink-0 p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Body --}}
            <div class="flex-1 overflow-y-auto p-6 space-y-5">
                @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="flex-1">
                            <h4 class="text-sm font-semibold text-red-800 mb-2">Vui lòng kiểm tra lại thông tin</h4>
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

                <x-form-input label="Tên năm học" name="name" wire:model="name"
                    placeholder="Ví dụ: 2025 – 2026" required />

                <div class="border border-slate-200 rounded-xl p-4 space-y-3">
                    <h3 class="text-sm font-bold text-slate-900">Học kỳ I</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-form-input label="Bắt đầu" name="start_date_one" type="date" wire:model="start_date_one" />
                        <x-form-input label="Kết thúc" name="end_date_one" type="date" wire:model="end_date_one" />
                    </div>
                </div>

                <div class="border border-slate-200 rounded-xl p-4 space-y-3">
                    <h3 class="text-sm font-bold text-slate-900">Học kỳ II</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-form-input label="Bắt đầu" name="start_date_two" type="date" wire:model="start_date_two" />
                        <x-form-input label="Kết thúc" name="end_date_two" type="date" wire:model="end_date_two" />
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex-shrink-0 px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end gap-3">
                <x-button variant="outline" @click="showForm = false; $wire.closeModal()">
                    Hủy
                </x-button>
                <x-button variant="primary" wire:click="save" :loading="true" loading-target="save">
                    <x-icon name="save" />
                    Lưu
                </x-button>
            </div>
        </div>
    </div>
</div>