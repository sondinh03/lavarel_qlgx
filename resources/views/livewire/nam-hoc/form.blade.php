<div
    class="fixed inset-0 bg-black/40 flex items-center justify-center z-50"
    wire:click="$set('showForm', false)"
>
    <div
        class="bg-white rounded-2xl shadow-xl w-full max-w-xl p-6 space-y-4"
        wire:click.stop
    >
        <h2 class="text-lg font-semibold">
            {{ $editingId ? 'Cập nhật năm học' : 'Thêm năm học' }}
        </h2>

        {{-- Tên năm học --}}
        <div>
            <label class="block text-sm font-medium mb-1">Tên năm học</label>
            <input type="text"
                wire:model.defer="name"
                class="w-full border rounded-xl p-2 focus:ring focus:ring-purple-200">
            @error('name')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- HK I --}}
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="text-sm">Bắt đầu HK I</label>
                <input type="date"
                    wire:model.defer="start_date_one"
                    class="w-full border rounded-xl p-2">
            </div>
            <div>
                <label class="text-sm">Kết thúc HK I</label>
                <input type="date"
                    wire:model.defer="end_date_one"
                    class="w-full border rounded-xl p-2">
            </div>
        </div>

        {{-- HK II --}}
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="text-sm">Bắt đầu HK II</label>
                <input type="date"
                    wire:model.defer="start_date_two"
                    class="w-full border rounded-xl p-2">
            </div>
            <div>
                <label class="text-sm">Kết thúc HK II</label>
                <input type="date"
                    wire:model.defer="end_date_two"
                    class="w-full border rounded-xl p-2">
            </div>
        </div>

        {{-- Buttons --}}
        <div class="flex justify-end gap-2 pt-4">
            <button
                wire:click="$set('showForm', false)"
                class="px-4 py-2 rounded-xl border hover:bg-slate-50">
                Huỷ
            </button>

            <button
                wire:click="save"
                class="px-4 py-2 rounded-xl bg-purple-600 text-white hover:bg-purple-700">
                Lưu
            </button>
        </div>
    </div>
</div>
