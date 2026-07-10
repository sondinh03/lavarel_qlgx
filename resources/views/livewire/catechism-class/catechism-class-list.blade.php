@section('topbar')
<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => route('parish-admin.dashboard')],
    ['label' => 'Quản lý lớp học'],
]" />
@endsection

<div class="min-h-screen bg-apple-gray p-2 sm:p-4 lg:p-6"
    style="min-height: calc(100vh - 56px - var(--bottom-offset));"
    x-data="{ showForm: false }"
    x-init="
        document.addEventListener('livewire:load', () => {
            Livewire.on('openModal', () => { showForm = true; });
            Livewire.on('closeModal', () => { showForm = false; });
        });
    ">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="mx-auto max-w-7xl">
        <x-mac-panel :overflow="true">
            <x-page-header
                title="Quản lý lớp học"
                description="Danh sách các lớp học theo năm học và khối"
                icon-type="students" />

            <div class="p-4 lg:p-6 mac-hairline-b bg-white/30">
                <div class="flex flex-col gap-4">
                    <div class="flex items-end gap-3">
                        <div class="flex-1 min-w-0">
                            <livewire:filters.filter-bar
                                :parish-id="$parishId"
                                :show-nam-hoc="true"
                                :show-khoi="true"
                                :show-lop="false"
                                :show-ky="false"
                                :selected-nam-hoc="$selectedNamHoc"
                                :selected-khoi="$selectedGradeLevel" />
                        </div>

                        <div class="flex-shrink-0 pb-0.5">
                            <x-button wire:click="resetFilters" variant="subtle">
                                <x-icon name="refresh" />
                                Đặt lại
                            </x-button>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <x-search-input
                            wire-model="search"
                            placeholder="Tìm theo tên lớp..."
                            debounce="500ms"
                            class="max-w-md" />

                        <x-button wire:click="create" variant="primary" :disabled="!$selectedNamHoc">
                            <x-icon name="plus" />
                            Thêm lớp
                        </x-button>
                    </div>
                </div>
            </div>

            @if($selectedNamHoc)
            <div class="px-4 lg:px-6 py-3 mac-hairline-b bg-slate-100/80 text-sm text-slate-700">
                @if($selectedGradeLevel)
                    Đang xem lớp học theo khối đã chọn
                    <span class="text-slate-500">— bỏ lọc khối để xem toàn năm học</span>
                @else
                    Đang xem toàn bộ lớp học trong năm học đã chọn
                @endif
            </div>

            @if($classes && $classes->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full border-separate border-spacing-0">
                    <thead class="bg-slate-50/50 mac-hairline-b">
                        <tr>
                            <x-table-header class="w-12">STT</x-table-header>
                            <x-table-header
                                :sortable="true" sort-field="name"
                                :current-sort="$sortField" :sort-direction="$sortDirection">
                                Tên lớp
                            </x-table-header>
                            <x-table-header
                                :sortable="true" sort-field="grade_level_id"
                                :current-sort="$sortField" :sort-direction="$sortDirection">
                                Khối
                            </x-table-header>
                            <x-table-header
                                class="text-center"
                                :sortable="true" sort-field="students_count"
                                :current-sort="$sortField" :sort-direction="$sortDirection">
                                Sĩ số
                            </x-table-header>
                            <x-table-header>Giáo lý viên</x-table-header>
                            <x-table-header
                                class="text-center"
                                :sortable="true" sort-field="is_active"
                                :current-sort="$sortField" :sort-direction="$sortDirection">
                                Trạng thái
                            </x-table-header>
                            <x-table-header class="text-center">Thao tác</x-table-header>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-black/[0.04]">
                        @foreach($classes as $index => $class)
                        <tr class="hover:bg-black/[0.03] transition-colors" wire:key="class-{{ $class->id }}">
                            <td class="px-4 py-3 text-sm text-slate-500">
                                {{ ($classes->firstItem() ?? 0) + $index }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm font-semibold text-slate-900">{{ $class->name }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                    {{ $class->gradeLevel->name ?? '—' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center text-sm font-semibold text-slate-900">
                                {{ $class->students_count ?? 0 }}
                            </td>
                            <td class="px-4 py-3 relative">
                                @if(($class->teachers_count ?? 0) > 0)
                                <div x-data="{ open: false }" class="inline-block">
                                    <a href="{{ route('classes.catechists', ['id' => $class->id]) }}"
                                        @mouseenter="open = true"
                                        @mouseleave="open = false"
                                        class="flex items-center gap-2 text-sm font-medium text-slate-800 hover:text-primary-600 transition-colors">
                                        <span class="truncate max-w-[140px]">{{ $class->teacher_names[0] ?? 'GLV' }}</span>
                                        @if($class->teachers_count > 1)
                                        <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-semibold text-primary-700 bg-primary-100 rounded-full">
                                            +{{ $class->teachers_count - 1 }}
                                        </span>
                                        @endif
                                    </a>
                                    <div x-show="open" x-transition x-cloak
                                        class="absolute left-0 top-full mt-2 min-w-48 p-3 bg-white rounded-xl shadow-md border border-slate-200 z-20">
                                        @foreach($class->teacher_names ?? [] as $teacherName)
                                        <div class="text-sm text-slate-700 py-0.5">{{ $teacherName }}</div>
                                        @endforeach
                                    </div>
                                </div>
                                @else
                                <a href="{{ route('classes.catechists', ['id' => $class->id]) }}"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold
                                           bg-amber-50 text-amber-700 hover:bg-amber-100 transition-colors">
                                    Chưa có GLV — Phân công
                                </a>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                    {{ $class->is_active ? 'bg-primary-100 text-primary-700' : 'bg-slate-200 text-slate-600' }}">
                                    {{ $class->is_active ? 'Hoạt động' : 'Tắt' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 overflow-visible">
                                <div class="flex items-center justify-center gap-1">
                                    <x-tooltip content="Danh sách học sinh">
                                        <a href="{{ route('students.index', ['class' => $class->id]) }}"
                                            class="p-2 hover:bg-primary-50 text-primary-600 rounded-lg transition-all">
                                            <x-icon name="users" class="w-4 h-4" />
                                        </a>
                                    </x-tooltip>
                                    <x-tooltip content="Phân công GLV">
                                        <a href="{{ route('classes.catechists', ['id' => $class->id]) }}"
                                            class="p-2 hover:bg-primary-50 text-primary-600 rounded-lg transition-all">
                                            <x-icon name="catechists" class="w-4 h-4" />
                                        </a>
                                    </x-tooltip>
                                    <x-tooltip content="Chỉnh sửa">
                                        <button
                                            wire:click="edit({{ $class->id }})"
                                            class="p-2 hover:bg-primary-50 text-primary-600 rounded-lg transition-all">
                                            <x-icon name="edit" />
                                        </button>
                                    </x-tooltip>

                                    <x-dropdown icon="more-vertical" align="right" variant="subtle" position="fixed">
                                        <x-dropdown-item
                                            x-on:click="$dispatch('open-confirm', {
                                                message: 'Xóa lớp {{ $class->name }}?',
                                                wireMethod: 'delete({{ $class->id }})'
                                            })"
                                            icon="trash"
                                            class="text-red-600 hover:bg-red-50">
                                            Xóa lớp học
                                        </x-dropdown-item>
                                    </x-dropdown>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($classes->hasPages())
            <div class="mac-hairline-t">
                <x-pagination :paginator="$classes" :per-page-options="[10, 15, 25, 50]" />
            </div>
            @endif

            @else
            <x-stats.page-empty
                :panel="false"
                tone="primary"
                title="Chưa có lớp học nào"
                description="Bắt đầu bằng cách thêm lớp học đầu tiên cho năm học đã chọn">
                <x-slot name="icon">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </x-slot>
                <x-button wire:click="create" variant="primary">
                    <x-icon name="plus" />
                    Thêm lớp học
                </x-button>
            </x-stats.page-empty>
            @endif

            @else
            <x-stats.page-empty
                :panel="false"
                title="Vui lòng chọn năm học"
                description="Chọn năm học ở bộ lọc phía trên để xem danh sách lớp">
                <x-slot name="icon">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </x-slot>
            </x-stats.page-empty>
            @endif
        </x-mac-panel>

    </div>

    {{-- Modal thêm / sửa lớp --}}
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
            class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-hidden flex flex-col"
            @click.stop>

            <div class="flex-shrink-0 p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">
                            {{ $editingId ? 'Cập nhật lớp học' : 'Thêm lớp học mới' }}
                        </h2>
                        <p class="text-sm text-slate-500 mt-1">Thông tin cơ bản về lớp học</p>
                    </div>
                    <button type="button"
                        @click="showForm = false; $wire.closeModal()"
                        class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                        <x-icon name="cancel" class="w-5 h-5" />
                    </button>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-6 space-y-5">
                @if($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 rounded-xl p-4">
                    <p class="text-sm font-semibold text-red-800 mb-2">Vui lòng kiểm tra lại thông tin</p>
                    <ul class="space-y-1 text-sm text-red-700">
                        @foreach($errors->all() as $error)
                        <li>· {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <x-form-input label="Tên lớp" name="name" wire:model.defer="name"
                    placeholder="VD: Lớp 1A, Lớp Thiếu Nhi A..." required />

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Khối học <span class="text-red-500">*</span>
                    </label>
                    <select wire:model.defer="gradeLevelId"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300
                               focus:outline-none focus:ring-2 focus:ring-primary-500
                               {{ $availableGradeLevels->isEmpty() ? 'bg-slate-100 cursor-not-allowed text-slate-400' : 'bg-white' }}">
                        <option value="">— Chọn khối —</option>
                        @forelse($availableGradeLevels as $level)
                        <option value="{{ $level->id }}">{{ $level->name }}</option>
                        @empty
                        <option value="" disabled>Chưa có khối học nào</option>
                        @endforelse
                    </select>
                    @if($availableGradeLevels->isEmpty())
                    <p class="mt-1.5 text-xs text-amber-600">Vui lòng tạo khối học trước</p>
                    @endif
                    @error('gradeLevelId')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <x-form-input label="Sức chứa (tùy chọn)" name="capacity" type="number"
                    wire:model.defer="capacity" placeholder="VD: 30"
                    help-text="Số học sinh tối đa. Để trống nếu không giới hạn." />

                <div class="border border-slate-200 rounded-xl p-4">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input id="class-is-active" type="checkbox" wire:model.defer="isActive"
                            class="mt-0.5 w-4 h-4 rounded border-slate-300 text-primary-500 focus:ring-primary-500">
                        <div>
                            <span class="text-sm font-semibold text-slate-900">Kích hoạt lớp học</span>
                            <p class="text-xs text-slate-500 mt-0.5">Lớp đang hoạt động và có thể nhận học sinh</p>
                        </div>
                    </label>
                </div>

                @if($editingId)
                <div class="bg-primary-50 border border-primary-100 rounded-xl p-4 text-sm text-primary-700">
                    Sau khi lưu, nhấn icon GLV ở cột <strong>Thao tác</strong> để phân công giáo lý viên.
                </div>
                @else
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-sm text-slate-600">
                    Sau khi tạo lớp, nhấn <strong>Chưa có GLV — Phân công</strong> hoặc icon GLV ở cột Thao tác.
                </div>
                @endif
            </div>

            <div class="flex-shrink-0 px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end gap-3">
                <x-button variant="outline" @click="showForm = false; $wire.closeModal()">Hủy</x-button>
                <x-button variant="primary" wire:click="save" :loading="true" loading-target="save"
                    :disabled="$availableGradeLevels->isEmpty()">
                    <x-icon name="save" />
                    {{ $editingId ? 'Cập nhật' : 'Tạo lớp' }}
                </x-button>
            </div>
        </div>
    </div>
</div>