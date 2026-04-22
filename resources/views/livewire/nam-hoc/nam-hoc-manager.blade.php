@section('topbar')
<x-breadcrumb :items="[
    [ 'label' => 'Trang chủ', 'url' => route('dashboard')],
    ['label' => 'năm học']
]" />
@endsection

<div
    class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6"
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

    <div id="main-content" class="mx-auto max-w-7xl space-y-5">

        {{-- Main Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <x-page-header
                title="Quản lý năm học"
                description="Danh sách các năm học của giáo xứ"
                :stat-value="$namHocs?->count()"
                stat-label="Năm học"
                icon-type="schoolYear">
            </x-page-header>

            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/70">
                <div class="flex items-center justify-between gap-4">
                    <div class="relative w-56">
                        <input
                            wire:model.debounce.500ms="search"
                            placeholder="Tìm kiếm năm học..."
                            class="w-full px-3 py-2 pr-8 rounded-xl border border-slate-300
                                   text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" />
                        @if ($search)
                        <button wire:click="$set('search', '')"
                            class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                        @endif
                    </div>
                    <x-button wire:click="create" variant="primary">
                        <x-icon name="plus" />
                        Thêm năm học
                    </x-button>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
            <table class="w-full table-fixed">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <x-table-header class="w-12">STT</x-table-header>
                        <x-table-header class="w-24 text-center" :sortable="true" sort-field="name"
                            :current-sort="$sortField" :sort-direction="$sortDirection">
                            Tên năm học
                        </x-table-header>
                        <x-table-header class="w-28 text-center">Học kỳ I</x-table-header>
                        <x-table-header class="w-28 text-center">Học kỳ II</x-table-header>
                        <x-table-header class="w-24 text-center">HK hiện tại</x-table-header>
                        <x-table-header class="w-24 text-center" :sortable="true" sort-field="status"
                            :current-sort="$sortField" :sort-direction="$sortDirection">
                            Trạng thái
                        </x-table-header>
                        <x-table-header class="w-28 text-center">Thao tác</x-table-header>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse ($namHocs as $i => $nh)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 text-sm text-slate-500">{{ $i + 1 }}</td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-slate-900">{{ $nh->name }}</div>
                        </td>
                        <td class="px-6 py-4 text-center text-sm text-slate-600">
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
                        <td class="px-6 py-4 text-center text-sm text-slate-600">
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
                        <td class="px-6 py-4 text-center">
                            @if ($nh->current_semester)
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700">
                                HK {{ $nh->current_semester }}
                            </span>
                            @else
                            <span class="text-slate-400 text-sm">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full {{ $nh->status_class }}">
                                {{ $nh->status_label }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-3">
                                <x-tooltip content="Chỉnh sửa">
                                    <x-table-action wire="edit({{ $nh->id }})" icon="edit" :icon-only="true">
                                    </x-table-action>
                                </x-tooltip>

                                <span class="text-slate-300">|</span>

                                <x-tooltip :content="$nh->status ? 'Lưu trữ năm học' : 'Kích hoạt năm học'">
                                    <x-table-action
                                        wire="toggleStatus({{ $nh->id }})"
                                        :icon="$nh->status ? 'archive' : 'check'"
                                        :color="$nh->status ? 'warning' : 'success'"
                                        :loading="true"
                                        debounce="500">
                                    </x-table-action>
                                </x-tooltip>

                                <span class="text-slate-300">|</span>

                                <x-tooltip content="Sao chép năm học">
                                    <a href="{{ route('school-years.copy', ['target' => $nh->id]) }}"
                                        class="inline-flex items-center gap-1 text-primary-600 hover:text-primary-800 text-sm font-medium transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                    </a>
                                </x-tooltip>

                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12">
                            <x-empty-state
                                icon="calendar"
                                title="Chưa có năm học"
                                description="Hãy tạo năm học đầu tiên cho giáo xứ">
                                <x-button wire:click="create" variant="primary">
                                    <x-icon name="plus" />
                                    Thêm năm học
                                </x-button>
                            </x-empty-state>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    {{-- Modal --}}
    <div
        x-show="showForm"
        x-transition.opacity
        class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        role="dialog"
        aria-modal="true"
        @click="showForm = false; $wire.closeModal()">

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
                        @click="showForm = false; $wire('closeModal')"
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
                <x-button variant="outline" @click="showForm = false; $wire('closeModal')">
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