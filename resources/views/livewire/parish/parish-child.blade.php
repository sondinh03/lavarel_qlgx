<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <div class="mx-auto max-w-7xl space-y-5">

        {{-- Header --}}
        <x-page-header
            title="Quản lý giáo họ"
            description="Danh sách các giáo họ"
            :stat-value="$parishes->count()"
            stat-label="Giáo họ"
            icon-type="parish" />

        {{-- Table --}}
        <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
            @if($parishes->count())
            <table class="w-full border-separate border-spacing-0">
                <thead class="bg-slate-50 border-b">
                    <tr>
                        <x-table-header>STT</x-table-header>
                        <x-table-header>Tên giáo họ</x-table-header>
                        <x-table-header class="text-center">Trạng thái</x-table-header>
                        <x-table-header class="text-center">Thao tác</x-table-header>
                    </tr>
                </thead>

                <tbody class="divide-y">
                    @foreach($parishes as $i => $parish)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-4">{{ $i + 1 }}</td>

                        <td class="px-6 py-4 font-semibold">
                            {{ $parish->name }}
                        </td>

                        <td class="px-6 py-4 text-center">
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full
                                {{ $parish->status
                                    ? 'bg-primary-100 text-primary-700'
                                    : 'bg-slate-200 text-slate-600' }}">
                                {{ $parish->status ? 'Hoạt động' : 'Tắt' }}
                            </span>
                        </td>

                        <td class="px-6 py-4 text-center">
                            <button wire:click="edit({{ $parish->id }})"
                                class="text-primary-600 hover:text-primary-800">
                                Sửa
                            </button>

                            @if($isAdmin)
                            <button wire:click="delete({{ $parish->id }})"
                                onclick="return confirm('Xóa giáo họ?')"
                                class="ml-3 text-red-600 hover:text-red-800">
                                Xóa
                            </button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="p-12 text-center text-slate-500">
                Chưa có giáo họ nào
            </div>
            @endif
        </div>

        {{-- Floating Add Button --}}
        <div class="fixed bottom-6 right-6">
            <button
                wire:click="create"
                class="inline-flex items-center gap-2
                    px-5 py-3 rounded-full
                    bg-primary-600 hover:bg-primary-700
                    text-white font-semibold shadow-lg
                    active:scale-95 transition-all">
                + Thêm giáo họ
            </button>
        </div>

        {{-- Modal --}}
        @if ($showForm)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50"
            wire:click="$set('showForm', false)">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md"
                wire:click.stop>

                <div class="p-6 border-b bg-primary-50">
                    <h2 class="text-lg font-bold">
                        {{ $editingId ? 'Cập nhật giáo họ' : 'Thêm giáo họ mới' }}
                    </h2>
                </div>

                <div class="p-6 space-y-4">
                    <div>
                        <label class="text-sm font-semibold">Tên giáo họ</label>
                        <input
                            wire:model.defer="name"
                            class="w-full mt-1 px-3 py-2 rounded-xl border focus:ring-2 focus:ring-primary-500">
                        @error('name')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" wire:model.defer="status">
                        <span>Hoạt động</span>
                    </div>
                </div>

                <div class="px-6 py-4 border-t flex justify-end gap-3 bg-slate-50">
                    <button wire:click="resetForm" class="px-4 py-2 rounded-xl border">
                        Huỷ
                    </button>
                    <button wire:click="save"
                        class="px-5 py-2 rounded-xl bg-primary-600 text-white">
                        Lưu
                    </button>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>