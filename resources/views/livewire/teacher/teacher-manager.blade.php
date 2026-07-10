@section('topbar')
<x-breadcrumb :items="[
        ['label' => 'Trang chủ', 'url' => route('parish-admin.dashboard')],
        ['label' => 'Giáo lý viên']
    ]" />
@endsection

<div class="min-h-screen bg-apple-gray p-2 sm:p-4 lg:p-6"
    style="min-height: calc(100vh - 56px - var(--bottom-offset));">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="mx-auto max-w-7xl">
        <x-mac-panel :overflow="true">
            <x-page-header
                title="Danh sách giáo lý viên"
                description="Quản lý hồ sơ và tài khoản giáo lý viên"
                icon-type="students" />

            <div class="p-4 lg:p-6 mac-hairline-b bg-white/30">
                <div class="flex flex-col gap-4">
                    <div class="flex items-end gap-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 flex-1 min-w-0">
                            <x-select-input
                                label="Giáo họ"
                                wire:model="filterParishGroup"
                                :value="$filterParishGroup"
                                :options="collect($parishGroups)->pluck('name', 'id')"
                                placeholder="-- Tất cả giáo họ --" />

                            <x-select-input
                                label="Giới tính"
                                wire:model="filterGender"
                                :value="$filterGender"
                                :options="['male' => 'Nam', 'female' => 'Nữ']"
                                placeholder="-- Tất cả giới tính --" />

                            <x-select-input
                                label="Trạng thái"
                                wire:model="filterActive"
                                :value="$filterActive"
                                :options="['1' => 'Đang hoạt động', '0' => 'Đã nghỉ']"
                                placeholder="-- Tất cả trạng thái --" />
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
                            placeholder="Tìm tên, SĐT, email..."
                            debounce="500ms"
                            class="max-w-md" />

                        <div class="flex items-center gap-2 flex-wrap justify-end">
                            <x-button as="a" href="{{ route('catechists.import') }}" variant="outline">
                                <x-icon name="upload" />
                                Import Excel
                            </x-button>
                            <x-button as="a" href="{{ route('catechists.create') }}" variant="primary">
                                <x-icon name="plus" />
                                Thêm giáo lý viên
                            </x-button>
                        </div>
                    </div>
                </div>
            </div>

            @if($teachers->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50/50 mac-hairline-b">
                        <tr>
                            <x-table-header class="w-12">STT</x-table-header>
                            <x-table-header class="w-14">Ảnh</x-table-header>
                            <x-table-header>Tên thánh</x-table-header>
                            <x-table-header class="w-[160px]">Họ & Tên đệm</x-table-header>
                            <x-table-header>Tên</x-table-header>
                            <x-table-header>Giới tính</x-table-header>
                            <x-table-header>Ngày sinh</x-table-header>
                            <x-table-header>Điện thoại</x-table-header>
                            <x-table-header>Email</x-table-header>
                            <x-table-header>Giáo họ</x-table-header>
                            <x-table-header class="text-center">Trạng thái</x-table-header>
                            <x-table-header class="text-center w-28">Thao tác</x-table-header>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04]">
                        @foreach($teachers as $index => $teacher)
                        <tr class="hover:bg-black/[0.03] transition-colors" wire:key="teacher-{{ $teacher->id }}">

                            <td class="px-4 py-3 text-sm text-slate-500">
                                {{ ($teachers->firstItem() ?? 0) + $index }}
                            </td>

                            <td class="px-4 py-3">
                                <div class="w-9 h-9 rounded-full overflow-hidden bg-slate-100 flex items-center justify-center flex-shrink-0 text-xs font-semibold text-slate-500">
                                    {{ strtoupper(mb_substr($teacher->last_name ?? '', 0, 1) . mb_substr($teacher->first_name ?? '', 0, 1)) }}
                                </div>
                            </td>

                            <td class="px-4 py-3 text-sm text-slate-900 whitespace-nowrap">
                                {{ $teacher->saint->name ?? '—' }}
                            </td>

                            <td class="px-4 py-3 text-sm font-semibold text-slate-900 whitespace-nowrap">
                                {{ $teacher->last_name ?: '—' }}
                            </td>

                            <td class="px-4 py-3 text-sm font-semibold text-slate-900 whitespace-nowrap">
                                {{ $teacher->first_name ?: '—' }}
                            </td>

                            <td class="px-4 py-3">
                                @if($teacher->gender)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-semibold
                                    {{ $teacher->gender === 'male'
                                        ? 'bg-primary-100 text-primary-700'
                                        : 'bg-pink-100 text-pink-700' }}">
                                    {{ $teacher->gender_text }}
                                </span>
                                @else
                                <span class="text-sm text-slate-400">—</span>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-sm text-slate-600 whitespace-nowrap">
                                {{ $teacher->birthday?->format('d/m/Y') ?? '—' }}
                            </td>

                            <td class="px-4 py-3 text-sm text-slate-700 whitespace-nowrap">
                                {{ $teacher->phone_number ?: '—' }}
                            </td>

                            <td class="px-4 py-3 text-sm text-slate-500 max-w-[180px]">
                                <span class="block truncate" title="{{ $teacher->email }}">
                                    {{ $teacher->email ?: '—' }}
                                </span>
                            </td>

                            <td class="px-4 py-3 text-sm text-slate-700 whitespace-nowrap">
                                {{ $teacher->parishGroup->name ?? '—' }}
                            </td>

                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-semibold
                                    {{ $teacher->is_active
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-slate-200 text-slate-600' }}">
                                    {{ $teacher->is_active ? 'Hoạt động' : 'Đã nghỉ' }}
                                </span>
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    <x-tooltip content="Xem chi tiết">
                                        <a href="{{ route('catechists.show', $teacher->id) }}"
                                            class="p-2 hover:bg-slate-100 text-slate-600 rounded-lg transition-all">
                                            <x-icon name="eye" />
                                        </a>
                                    </x-tooltip>

                                    <x-tooltip content="Chỉnh sửa">
                                        <a href="{{ route('catechists.edit', $teacher->id) }}"
                                            class="p-2 hover:bg-slate-100 text-slate-600 rounded-lg transition-all">
                                            <x-icon name="edit" />
                                        </a>
                                    </x-tooltip>

                                    <x-tooltip content="Xóa">
                                        <button type="button"
                                            @click="$dispatch('open-confirm', {
                                                message: 'Xóa giáo lý viên này sẽ xóa luôn tài khoản đăng nhập. Bạn chắc chắn?',
                                                wireMethod: 'delete({{ $teacher->id }})'
                                            })"
                                            class="p-2 hover:bg-red-50 text-red-500 rounded-lg transition-all">
                                            <x-icon name="trash" />
                                        </button>
                                    </x-tooltip>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($teachers->hasPages())
            <div class="mac-hairline-t">
                <x-pagination :paginator="$teachers" :per-page-options="[10, 15, 25, 50]" />
            </div>
            @endif

            @else
            <x-stats.page-empty
                :panel="false"
                tone="primary"
                :title="($search || $filterParishGroup || $filterGender || $filterActive) ? 'Không tìm thấy kết quả' : 'Chưa có giáo lý viên'"
                :description="($search || $filterParishGroup || $filterGender || $filterActive) ? 'Thử đổi bộ lọc hoặc từ khóa tìm kiếm' : 'Thêm giáo lý viên đầu tiên hoặc import từ Excel'">
                <x-slot name="icon">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </x-slot>
                @if(!($search || $filterParishGroup || $filterGender || $filterActive))
                <x-button as="a" href="{{ route('catechists.create') }}" variant="primary">
                    <x-icon name="plus" />
                    Thêm giáo lý viên
                </x-button>
                @endif
            </x-stats.page-empty>
            @endif
        </x-mac-panel>
    </div>
</div>
