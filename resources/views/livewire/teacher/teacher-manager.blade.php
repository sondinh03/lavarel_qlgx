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
                                label="Quyền hỗ trợ"
                                wire:model="filterPermission"
                                :value="$filterPermission"
                                :options="$permissionFilterOptions"
                                placeholder="-- Tất cả quyền --" />

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
                            <x-button as="a" href="{{ route('catechists.create') }}" variant="primary">
                                <x-icon name="plus" />
                                Thêm giáo lý viên
                            </x-button>
                            <x-button as="a" href="{{ route('catechists.assign') }}" variant="outline">
                                <x-icon name="catechists" />
                                Phân công giảng dạy
                            </x-button>
                            <x-dropdown label="Khác" icon="grid" align="right" position="fixed">
                                <x-dropdown-item wire:click="export" icon="download">
                                    Xuất Excel
                                </x-dropdown-item>
                                <x-dropdown-item as="a" href="{{ route('catechists.import') }}" icon="upload">
                                    Import Excel
                                </x-dropdown-item>
                            </x-dropdown>
                        </div>
                    </div>
                </div>
            </div>

            @php $showQuickAvatar = $canQuickUploadAvatars ?? false; @endphp

            @if($teachers->count() > 0)
            <div class="px-4 lg:px-6 py-3 mac-hairline-b space-y-2">
                <x-inline-tip>
                    Chọn checkbox từng giáo lý viên (hoặc chọn cả trang) để <strong>In thẻ</strong>,
                    <strong>Đánh dấu đã nghỉ</strong> hoặc <strong>Xóa</strong> hàng loạt.
                </x-inline-tip>
                @if($showQuickAvatar)
                <p class="text-xs text-slate-500 px-0.5">
                    Chạm máy ảnh → chọn <strong>Chụp ảnh</strong> hoặc <strong>Thư viện</strong>.
                </p>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50/50 mac-hairline-b">
                        <tr>
                            <x-table-header class="w-12 text-center">
                                <x-checkbox wire:model="selectAll" />
                            </x-table-header>
                            <x-table-header class="w-12">STT</x-table-header>
                            <x-table-header class="w-14">Ảnh</x-table-header>
                            <x-table-header>Tên thánh</x-table-header>
                            <x-table-header>Họ đệm</x-table-header>
                            <x-table-header
                                :sortable="true"
                                sort-field="first_name"
                                :current-sort="$sortField"
                                :sort-direction="$sortDirection">
                                Tên
                            </x-table-header>
                            <x-table-header>Giới tính</x-table-header>
                            <x-table-header
                                :sortable="true"
                                sort-field="birthday"
                                :current-sort="$sortField"
                                :sort-direction="$sortDirection">
                                Ngày sinh
                            </x-table-header>
                            <x-table-header>Điện thoại</x-table-header>
                            <x-table-header>Giáo họ</x-table-header>
                            <x-table-header class="text-center">Trạng thái</x-table-header>
                            <x-table-header class="text-center w-28">Thao tác</x-table-header>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04]">
                        @foreach($teachers as $index => $teacher)
                        <tr class="hover:bg-black/[0.03] transition-colors" wire:key="teacher-{{ $teacher->id }}">

                            <td class="px-4 py-3 text-center">
                                <x-checkbox wire:model="selectedTeachers" value="{{ $teacher->id }}" />
                            </td>

                            <td class="px-4 py-3 text-sm text-slate-500">
                                {{ ($teachers->firstItem() ?? 0) + $index }}
                            </td>

                            <td class="px-4 py-3">
                                <x-quick-avatar-upload
                                    :id="$teacher->id"
                                    :name="trim(($teacher->last_name ?? '') . ' ' . ($teacher->first_name ?? ''))"
                                    :initials="mb_substr($teacher->last_name ?? '', 0, 1) . mb_substr($teacher->first_name ?? '', 0, 1)"
                                    :src="$teacher->avatar_path ? $teacher->avatar_url : null"
                                    wire-target="quickAvatars.{{ $teacher->id }}"
                                    :enabled="$showQuickAvatar"
                                    size="md"
                                    variant="slate"
                                    input-prefix="quick-avatar-teacher" />
                            </td>

                            <td class="px-4 py-3 text-sm text-slate-900 whitespace-nowrap">
                                {{ $teacher->saint->name ?? '—' }}
                            </td>

                            <td class="px-4 py-3 text-sm text-slate-900 whitespace-nowrap">
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
                :title="($search || $filterParishGroup || $filterPermission || $filterActive) ? 'Không tìm thấy kết quả' : 'Chưa có giáo lý viên'"
                :description="($search || $filterParishGroup || $filterPermission || $filterActive) ? 'Thử đổi bộ lọc hoặc từ khóa tìm kiếm' : 'Thêm giáo lý viên đầu tiên hoặc import từ Excel'">
                <x-slot name="icon">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </x-slot>
                @if(!($search || $filterParishGroup || $filterPermission || $filterActive))
                <x-button as="a" href="{{ route('catechists.create') }}" variant="primary">
                    <x-icon name="plus" />
                    Thêm giáo lý viên
                </x-button>
                @endif
            </x-stats.page-empty>
            @endif
        </x-mac-panel>
    </div>

    @if(count($selectedTeachers) > 0)
    <div class="fixed right-0 z-40 px-3 sm:px-4"
        style="left: var(--sidebar-current, 0px); bottom: calc(var(--bottom-offset, 0px) + env(safe-area-inset-bottom, 0px) + 0.75rem);">
        <div class="mx-auto max-w-7xl">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3
                px-4 py-3 rounded-2xl
                bg-white/90 backdrop-blur-xl border border-black/[0.06] shadow-mac">
                <span class="text-sm font-semibold text-slate-800">
                    Đã chọn <span class="text-primary-700">{{ count($selectedTeachers) }}</span> giáo lý viên
                </span>
                <div class="flex items-center gap-2 flex-wrap justify-end">
                    <x-button wire:click="printSelected" variant="primary" size="sm">
                        <x-icon name="printer" />
                        In thẻ
                    </x-button>
                    <x-button
                        variant="warning"
                        size="sm"
                        wire="markSelectedInactive"
                        confirm="Đánh dấu các giáo lý viên đã chọn là đã nghỉ? Họ sẽ không còn ở trạng thái hoạt động.">
                        <x-icon name="archive" />
                        Đánh dấu đã nghỉ
                    </x-button>
                    <x-button
                        variant="danger"
                        size="sm"
                        wire="deleteSelected"
                        confirm="Xóa các giáo lý viên đã chọn sẽ xóa luôn tài khoản đăng nhập. Bạn chắc chắn?">
                        <x-icon name="trash" />
                        Xóa
                    </x-button>
                    <x-button type="button" variant="subtle" size="sm" wire:click="$set('selectedTeachers', [])">
                        Bỏ chọn
                    </x-button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
