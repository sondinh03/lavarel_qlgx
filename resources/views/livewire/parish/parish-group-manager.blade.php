<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="mx-auto max-w-7xl space-y-5">

        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[
            ['label' => 'Trang chủ', 'url' => route('dashboard')],
            ['label' => 'Quản lý giáo họ', 'url' => route('parish-group.index')],
        ]" separator="arrow" />

        {{-- Toast --}}
        <div role="status" aria-live="polite">
            @if (session()->has('message'))
            <x-toast-notification type="success" :duration="3500">
                {{ session('message') }}
            </x-toast-notification>
            @endif

            @if (session()->has('error'))
            <x-toast-notification type="error" :duration="4000">
                {{ session('error') }}
            </x-toast-notification>
            @endif

            @if (session()->has('warning'))
            <x-toast-notification type="warning" :duration="4000">
                {{ session('warning') }}
            </x-toast-notification>
            @endif
        </div>

        {{-- Main Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

            {{-- Header --}}
            <x-page-header
                title="Quản lý giáo họ"
                description="Danh sách các giáo họ trong giáo xứ"
                :stat-value="$groups->count()"
                stat-label="Giáo họ"
                icon-type="parish">
            </x-page-header>

            {{-- Actions Bar --}}
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/70">
                <div class="flex justify-end">
                    <x-action-button wire="create" icon="plus">
                        Thêm giáo họ
                    </x-action-button>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            @if ($groups->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full border-separate border-spacing-0">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <x-table-header>STT</x-table-header>
                            <x-table-header>Tên giáo họ</x-table-header>
                            <x-table-header class="text-center">Học sinh</x-table-header>
                            <x-table-header class="text-center">Trạng thái</x-table-header>
                            <x-table-header class="text-center">Thao tác</x-table-header>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @foreach ($groups as $index => $group)
                        <tr class="hover:bg-slate-50 transition-colors"
                            wire:key="group-{{ $group->id }}">

                            {{-- STT --}}
                            <td class="px-6 py-4 text-sm text-slate-500">
                                {{ $index + 1 }}
                            </td>

                            {{-- Tên --}}
                            <td class="px-6 py-4">
                                <span class="font-semibold text-slate-900">
                                    {{ $group->name }}
                                </span>
                            </td>

                            {{-- Học sinh --}}
                            <td class="px-6 py-4 text-center">
                                <div class="inline-flex items-center gap-1.5 text-sm text-slate-700">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span class="font-semibold">
                                        {{ $group->students_count ?? $group->students()->count() }}
                                    </span>
                                </div>
                            </td>

                            {{-- Trạng thái --}}
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full
                                            {{ $group->status
                                                ? 'bg-primary-100 text-primary-700'
                                                : 'bg-slate-200 text-slate-600' }}">
                                    {{ $group->status ? 'Hoạt động' : 'Tắt' }}
                                </span>
                            </td>

                            {{-- Thao tác --}}
                            <td class="px-6 py-4 text-center">
                                <div class="inline-flex items-center gap-3">

                                    {{-- Sửa --}}
                                    <x-table-action
                                        wire="edit({{ $group->id }})"
                                        icon="edit">
                                        Sửa
                                    </x-table-action>

                                    <span class="text-slate-300">|</span>

                                    {{-- Toggle Status --}}
                                    <x-table-action
                                        wire="toggleStatus({{ $group->id }})"
                                        :icon="$group->status ? 'archive' : 'check'"
                                        :color="$group->status ? 'warning' : 'success'"
                                        :loading="true"
                                        debounce="500">
                                        {{ $group->status ? 'Tắt' : 'Bật' }}
                                    </x-table-action>

                                    {{-- Xóa — chỉ khi không có học sinh --}}
                                    @if (!$group->students_count)
                                    <span class="text-slate-300">|</span>

                                    <x-table-action
                                        wire="delete({{ $group->id }})"
                                        icon="trash"
                                        color="danger"
                                        :confirm="true"
                                        confirm-message="Xóa giáo họ {{ $group->name }}?"
                                        :loading="true">
                                        Xóa
                                    </x-table-action>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="p-12 text-center">
                <svg class="mx-auto w-16 h-16 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <p class="mt-4 text-lg text-slate-500">Chưa có giáo họ nào</p>
                <button wire:click="create"
                    class="mt-4 px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition-all">
                    + Thêm giáo họ đầu tiên
                </button>
            </div>
            @endif
        </div>

        {{-- Modal Form --}}
        @if ($showForm)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="group-modal-title"
            wire:click="closeModal">

            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden flex flex-col"
                wire:click.stop>

                {{-- Header --}}
                <div class="p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                    <h2 id="group-modal-title" class="text-xl font-bold text-slate-900">
                        {{ $editingId ? 'Cập nhật giáo họ' : 'Thêm giáo họ mới' }}
                    </h2>
                </div>

                {{-- Body --}}
                <div class="p-6 space-y-5">

                    {{-- Error Summary --}}
                    @if ($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-500 rounded-xl p-4">
                        <ul class="space-y-1 text-sm text-red-700">
                            @foreach ($errors->all() as $error)
                            <li>• {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    {{-- Tên giáo họ --}}
                    <x-form-input
                        label="Tên giáo họ"
                        name="name"
                        wire:model.defer="name"
                        placeholder="VD: Giáo họ Thánh Giuse..."
                        required />

                    {{-- Trạng thái --}}
                    <div class="border border-slate-200 rounded-xl p-4">
                        <div class="flex items-center gap-3">
                            <input
                                id="group-status"
                                type="checkbox"
                                wire:model.defer="status"
                                class="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                            <label for="group-status" class="text-sm font-semibold text-slate-900 cursor-pointer">
                                Kích hoạt giáo họ
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end gap-3">
                    <x-action-button wire="closeModal" variant="secondary">
                        Huỷ
                    </x-action-button>

                    <x-action-button wire="save" icon="save" :loading="true">
                        {{ $editingId ? 'Cập nhật' : 'Thêm mới' }}
                    </x-action-button>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Loading --}}
<div wire:loading class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 flex items-center gap-3">
        <svg class="animate-spin h-6 w-6 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span class="text-gray-700">Đang xử lý...</span>
    </div>
</div>