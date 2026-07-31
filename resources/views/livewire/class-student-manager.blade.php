<div class="min-h-screen bg-apple-gray p-2 sm:p-4 lg:p-6"
    style="min-height: calc(100vh - 56px - var(--bottom-offset));">
    <div class="mx-auto max-w-7xl space-y-5">

        <x-mac-panel :overflow="true">
            <div class="p-4 lg:p-6 mac-hairline-b bg-white/30">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <x-search-input
                        wire-model="search"
                        placeholder="Tìm học sinh..."
                        debounce="500ms"
                        class="max-w-md" />

                    <x-button wire:click="openAddModal" variant="primary">
                        <x-icon name="user-plus" />
                        Ghi danh
                    </x-button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50/50 mac-hairline-b">
                        <tr>
                            <x-table-header>Mã</x-table-header>
                            <x-table-header>Họ tên</x-table-header>
                            <x-table-header class="text-center w-28">Thao tác</x-table-header>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04]">
                        @forelse($this->currentStudents as $student)
                        <tr class="hover:bg-black/[0.03] transition-colors" wire:key="cs-{{ $student->id }}">
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $student->student_code }}</td>
                            <td class="px-4 py-3 text-sm font-semibold text-slate-900">{{ $student->full_name }}</td>
                            <td class="px-4 py-3 text-center">
                                <x-button
                                    type="button"
                                    variant="danger"
                                    size="sm"
                                    wire="removeStudent({{ $student->id }})"
                                    confirm="Gỡ học sinh {{ $student->full_name }} khỏi lớp?">
                                    Xóa
                                </x-button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-12 text-center text-slate-400">
                                Chưa có học sinh trong lớp.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-mac-panel>

        @if($showAddModal)
        <div class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50 p-4"
            role="dialog" aria-modal="true"
            wire:click="closeAddModal">
            <div class="bg-white/90 backdrop-blur-xl rounded-2xl border border-black/[0.06] shadow-mac
                w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col"
                @click.stop>

                <div class="flex-shrink-0 px-6 py-5 border-b border-black/[0.06]">
                    <h2 class="text-xl font-semibold tracking-tight text-slate-900">Ghi danh học sinh</h2>
                    <p class="text-sm text-slate-500 mt-1">Chọn học sinh để thêm vào lớp</p>
                </div>

                <div class="flex-1 overflow-y-auto p-6 space-y-4">
                    <input type="text"
                        wire:model.debounce.500ms="modalSearch"
                        placeholder="Tìm học sinh..."
                        class="w-full h-11 px-4 rounded-xl border border-black/[0.06] bg-white/80 text-sm
                            shadow-mac-sm focus:outline-none focus:ring-2 focus:ring-primary-500/25">

                    <div class="max-h-80 overflow-y-auto space-y-2">
                        @forelse($this->availableStudents as $student)
                        <label class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-50 cursor-pointer">
                            <input type="checkbox"
                                wire:model="studentsToAdd"
                                value="{{ $student->id }}"
                                class="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                            <span class="text-sm text-slate-800">{{ $student->full_name }}</span>
                        </label>
                        @empty
                        <p class="text-sm text-slate-400 text-center py-8">Không có học sinh phù hợp</p>
                        @endforelse
                    </div>
                </div>

                <div class="flex-shrink-0 px-6 py-4 border-t border-black/[0.06] bg-slate-50/70 flex justify-end gap-4">
                    <x-button variant="secondary" wire:click="closeAddModal">
                        Hủy
                    </x-button>
                    <x-button variant="primary" wire:click="addStudents"
                        :disabled="empty($studentsToAdd)">
                        <x-icon name="save" />
                        Lưu
                    </x-button>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
