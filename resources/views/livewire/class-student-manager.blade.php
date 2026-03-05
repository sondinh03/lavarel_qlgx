<div>

    <div class="flex justify-between mb-4">
        <input type="text"
            wire:model.debounce.500ms="search"
            placeholder="Tìm học sinh..."
            class="border rounded px-3 py-2">

        <button wire:click="openAddModal"
            class="bg-blue-600 text-white px-4 py-2 rounded">
            Ghi danh
        </button>
    </div>

    <table class="w-full border">
        <thead>
            <tr class="bg-gray-100">
                <th class="p-2">Mã</th>
                <th class="p-2">Họ tên</th>
                <th class="p-2">Hành động</th>
            </tr>
        </thead>
        <tbody>
            @foreach($this->currentStudents as $student)
            <tr>
                <td class="p-2">{{ $student->student_code }}</td>
                <td class="p-2">{{ $student->full_name }}</td>
                <td class="p-2">
                    <button wire:click="removeStudent({{ $student->id }})"
                        class="text-red-600">
                        Xóa
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @if($showAddModal)
    <div class="fixed inset-0 bg-black/50 flex items-center justify-center">

        <div class="bg-white p-6 rounded w-2/3">

            <input type="text"
                wire:model.debounce.500ms="modalSearch"
                placeholder="Tìm học sinh..."
                class="border rounded px-3 py-2 w-full mb-4">

            <div class="max-h-80 overflow-y-auto">
                @foreach($this->availableStudents as $student)
                <div>
                    <label>
                        <input type="checkbox"
                            wire:model="studentsToAdd"
                            value="{{ $student->id }}">
                        {{ $student->full_name }}
                    </label>
                </div>
                @endforeach
            </div>

            <div class="mt-4 flex justify-end gap-2">
                <button wire:click="closeAddModal"
                    class="px-4 py-2 border rounded">
                    Hủy
                </button>

                <button wire:click="addStudents"
                    class="px-4 py-2 bg-blue-600 text-white rounded">
                    Lưu
                </button>
            </div>

        </div>
    </div>
    @endif

</div>