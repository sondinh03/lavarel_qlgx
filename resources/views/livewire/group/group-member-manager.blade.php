@section('topbar')
<x-breadcrumb :items="[
    ['label' => 'Trang chủ',    'url' => route('dashboard')],
    ['label' => 'Sinh hoạt',   'url' => '#'],
    ['label' => 'Quản lý nhóm','url' => route('groups.index')],
    ['label' => $group->name],
]" separator="arrow" />
@endsection

<div class="min-h-screen bg-slate-50 p-2 sm:p-4 lg:p-6">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="mx-auto max-w-7xl space-y-6">

        {{-- Toast --}}
        <div role="status" aria-live="polite">
            @if(session()->has('message'))
                <x-toast-notification type="success" :duration="3500">{{ session('message') }}</x-toast-notification>
            @endif
            @if(session()->has('error'))
                <x-toast-notification type="error" :duration="4000">{{ session('error') }}</x-toast-notification>
            @endif
        </div>

        {{-- Main Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
            <x-page-header
                class="rounded-t-2xl"
                title="Thành viên — {{ $group->name }}"
                :count="$members->total()">
            </x-page-header>

            {{-- Actions Bar --}}
            <div class="p-4 lg:p-6 border-b border-slate-200 bg-slate-50/70 rounded-b-2xl">
                <div class="flex flex-col gap-4">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3 flex-wrap">
                            <input
                                wire:model.debounce.300ms="search"
                                type="text"
                                placeholder="Tìm tên, SĐT..."
                                class="w-56 px-3 py-2 rounded-xl border border-slate-300 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-primary-500" />

                            <select wire:model="filterActive"
                                class="px-3 py-2 rounded-xl border border-slate-300 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="">-- Tất cả --</option>
                                <option value="1">Đang hoạt động</option>
                                <option value="0">Đã nghỉ</option>
                            </select>
                        </div>

                        <div class="flex items-center gap-2">
                            <x-button as="a" href="{{ route('groups.sessions', $group->id) }}" variant="outline">
                                <x-icon name="calendar" />
                                Buổi sinh hoạt
                            </x-button>

                            <x-button wire:click="openAddModal">
                                <x-icon name="user-plus" />
                                Thêm thành viên
                            </x-button>
                        </div>
                    </div>

                    {{-- Group info badge --}}
                    <div class="flex items-center gap-3 flex-wrap text-sm text-slate-600">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full
                                     {{ $group->type == 1 ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}
                                     text-xs font-semibold">
                            {{ $group->type_label }}
                        </span>
                        <span class="text-slate-400">·</span>
                        <span>Thành viên:
                            <strong>{{ $group->member_type === 'teacher' ? 'Giáo lý viên' : 'Học sinh' }}</strong>
                        </span>
                        @if($group->note)
                            <span class="text-slate-400">· {{ $group->note }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            @if($members->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full border-separate border-spacing-0">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <x-table-header>STT</x-table-header>
                                <x-table-header>Họ tên</x-table-header>
                                @if($group->member_type === 'teacher')
                                    <x-table-header>Số điện thoại</x-table-header>
                                    <x-table-header>Giáo họ</x-table-header>
                                @else
                                    <x-table-header>Ngày sinh</x-table-header>
                                    <x-table-header>Giáo họ</x-table-header>
                                @endif
                                <x-table-header>Vai trò</x-table-header>
                                <x-table-header>Ngày vào</x-table-header>
                                <x-table-header class="text-center">Trạng thái</x-table-header>
                                <x-table-header class="text-center">Thao tác</x-table-header>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($members as $index => $member)
                            @php $person = $member->memberable; @endphp
                            <tr class="hover:bg-slate-50 transition-colors"
                                wire:key="member-{{ $member->id }}">

                                <td class="px-4 py-4 text-sm font-semibold text-slate-500">
                                    {{ ($members->firstItem() ?? 0) + $index }}
                                </td>

                                {{-- Họ tên --}}
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full flex items-center justify-center
                                                    flex-shrink-0 text-sm font-bold
                                                    {{ $member->is_active ? 'bg-primary-100 text-primary-700' : 'bg-slate-100 text-slate-500' }}">
                                            {{ mb_substr($person?->first_name ?? '?', 0, 1) }}
                                        </div>
                                        <div>
                                            @if($person?->saint)
                                                <div class="text-xs text-slate-400">{{ $person->saint->name }}</div>
                                            @endif
                                            <div class="font-semibold text-slate-900">
                                                {{ $person?->full_name ?? '—' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                @if($group->member_type === 'teacher')
                                    <td class="px-4 py-4 text-sm text-slate-600">
                                        {{ $person?->phone_number ?? '—' }}
                                    </td>
                                    <td class="px-4 py-4 text-sm text-slate-600">
                                        {{ $person?->parishGroup?->name ?? '—' }}
                                    </td>
                                @else
                                    <td class="px-4 py-4 text-sm text-slate-600">
                                        {{ $person?->birthday?->format('d/m/Y') ?? '—' }}
                                    </td>
                                    <td class="px-4 py-4 text-sm text-slate-600">
                                        {{ $person?->parishGroup?->name ?? '—' }}
                                    </td>
                                @endif

                                {{-- Vai trò --}}
                                <td class="px-4 py-4 text-sm text-slate-600">
                                    {{ $member->role_display }}
                                </td>

                                {{-- Ngày vào --}}
                                <td class="px-4 py-4 text-sm text-slate-600">
                                    {{ $member->joined_at?->format('d/m/Y') ?? '—' }}
                                </td>

                                {{-- Trạng thái --}}
                                <td class="px-4 py-4 text-center">
                                    <button wire:click="toggleActive({{ $member->id }})"
                                        class="inline-flex items-center px-2.5 py-1 text-xs font-semibold
                                               rounded-full transition-colors cursor-pointer
                                               {{ $member->is_active
                                                   ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200'
                                                   : 'bg-slate-200 text-slate-500 hover:bg-slate-300' }}">
                                        {{ $member->is_active ? 'Hoạt động' : 'Đã nghỉ' }}
                                    </button>
                                </td>

                                {{-- Thao tác --}}
                                <td class="px-4 py-4 overflow-visible">
                                    <div class="flex items-center justify-center gap-1">
                                        <x-dropdown icon="more-vertical" align="right"
                                            variant="subtle" position="fixed">
                                            <x-dropdown-item
                                                wire:click="toggleActive({{ $member->id }})"
                                                icon="{{ $member->is_active ? 'pause' : 'play' }}">
                                                {{ $member->is_active ? 'Cho nghỉ' : 'Kích hoạt lại' }}
                                            </x-dropdown-item>

                                            <div class="h-px bg-slate-100 my-1"></div>

                                            <x-dropdown-item
                                                x-on:click="$dispatch('open-confirm', {
                                                    message: 'Xóa thành viên này khỏi nhóm?',
                                                    wireMethod: 'removeMember({{ $member->id }})'
                                                })"
                                                icon="trash"
                                                class="text-red-600 hover:bg-red-50">
                                                Xóa khỏi nhóm
                                            </x-dropdown-item>
                                        </x-dropdown>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($members->hasPages())
                <div class="p-6 border-t border-slate-200">
                    <x-pagination :paginator="$members" :per-page-options="[10, 15, 25, 50]" />
                </div>
                @endif

            @else
                <x-empty-state
                    icon="users"
                    :colspan="8"
                    title="Chưa có thành viên nào"
                    description="Thêm {{ $group->member_type === 'teacher' ? 'giáo lý viên' : 'học sinh' }} vào nhóm để bắt đầu" />
            @endif
        </div>

    </div>{{-- /max-w --}}

    {{-- ===================== MODAL THÊM THÀNH VIÊN ===================== --}}
    @if($showAddModal)
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        role="dialog" aria-modal="true" wire:click="closeAddModal">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[85vh]
                    overflow-hidden flex flex-col"
            wire:click.stop>

            {{-- Header --}}
            <div class="flex-shrink-0 p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">
                            Thêm {{ $group->member_type === 'teacher' ? 'giáo lý viên' : 'học sinh' }}
                        </h2>
                        <p class="text-sm text-slate-500 mt-1">
                            Chọn từ danh sách — chỉ hiển thị người chưa có trong nhóm
                        </p>
                    </div>
                    <button wire:click="closeAddModal" type="button"
                        class="text-slate-400 hover:text-slate-600 transition p-1">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Search --}}
            <div class="flex-shrink-0 px-6 py-4 border-b border-slate-100">
                <input
                    wire:model.debounce.300ms="modalSearch"
                    type="text"
                    placeholder="Tìm kiếm..."
                    autofocus
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300
                           focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm" />
            </div>

            {{-- Body --}}
            <div class="flex-1 overflow-y-auto">
                @if($candidates->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full border-separate border-spacing-0">
                        <thead class="bg-slate-50 sticky top-0">
                            <tr>
                                <th class="px-4 py-3 text-left w-10">
                                    {{-- Không có select all vì paginated --}}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">
                                    Họ tên
                                </th>
                                @if($group->member_type === 'teacher')
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">
                                        Số điện thoại
                                    </th>
                                @else
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">
                                        Ngày sinh
                                    </th>
                                @endif
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">
                                    Giáo họ
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($candidates as $candidate)
                            <tr class="hover:bg-slate-50 transition-colors"
                                wire:key="candidate-{{ $candidate->id }}">
                                <td class="px-4 py-3">
                                    <input type="checkbox"
                                        wire:model="selectedIds"
                                        value="{{ $candidate->id }}"
                                        class="w-4 h-4 rounded border-slate-300
                                               text-primary-600 focus:ring-primary-500">
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-primary-50 text-primary-700
                                                    flex items-center justify-center text-xs font-bold flex-shrink-0">
                                            {{ mb_substr($candidate->first_name, 0, 1) }}
                                        </div>
                                        <div>
                                            @if($candidate->saint)
                                                <div class="text-xs text-slate-400">{{ $candidate->saint->name }}</div>
                                            @endif
                                            <div class="text-sm font-semibold text-slate-900">
                                                {{ $candidate->full_name }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                @if($group->member_type === 'teacher')
                                    <td class="px-4 py-3 text-sm text-slate-600">
                                        {{ $candidate->phone_number ?? '—' }}
                                    </td>
                                @else
                                    <td class="px-4 py-3 text-sm text-slate-600">
                                        {{ $candidate->birthday?->format('d/m/Y') ?? '—' }}
                                    </td>
                                @endif
                                <td class="px-4 py-3 text-sm text-slate-600">
                                    {{ $candidate->parishGroup?->name ?? '—' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($candidates->hasPages())
                <div class="px-6 py-3 border-t border-slate-100">
                    {{ $candidates->links() }}
                </div>
                @endif

                @else
                <div class="text-center py-12">
                    <svg class="mx-auto w-12 h-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="mt-3 text-slate-500 font-medium">Không tìm thấy kết quả</p>
                    <p class="mt-1 text-sm text-slate-400">
                        Thử tìm tên khác hoặc tất cả đã có trong nhóm
                    </p>
                </div>
                @endif
            </div>

            {{-- Footer --}}
            <div class="flex-shrink-0 px-6 py-4 border-t border-slate-200 bg-slate-50
                        flex items-center justify-between">
                <span class="text-sm text-slate-600">
                    Đã chọn:
                    <span class="font-semibold text-primary-600">{{ count($selectedIds) }}</span>
                </span>
                <div class="flex gap-3">
                    <x-button wire:click="closeAddModal" variant="subtle">Hủy</x-button>
                    <x-button
                        wire:click="addMembers"
                        wire:loading.attr="disabled"
                        wire:target="addMembers"
                        :disabled="empty($selectedIds)">
                        <svg wire:loading wire:target="addMembers"
                            class="animate-spin w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <x-icon name="user-plus" />
                        Thêm vào nhóm
                    </x-button>
                </div>
            </div>

        </div>
    </div>
    @endif

</div>

@push('page-title')
<span class="text-slate-800 font-semibold text-sm">{{ $group->name }}</span>
@endpush