{{-- Bộ lọc + hành động của tab đang mở --}}
<div class="p-4 lg:p-6 mac-hairline-b bg-white/30">
    @if($isCatechist && !($canBrowseAllScoreClasses ?? false))
    <div class="flex flex-col gap-4">
        <livewire:filters.filter-bar
            :parish-id="$parishId"
            :show-nam-hoc="false"
            :show-khoi="false"
            :show-lop="true"
            :show-ky="true"
            :selected-nam-hoc="$selectedNamHoc"
            :selected-khoi="$selectedKhoi"
            :selected-lop="$selectedLop"
            :selected-ky="$selectedSemester"
            :allowed-class-ids="$scoreFilterAllowedClassIds ?? []" />

        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            <x-search-input
                wire-model="search"
                placeholder="Tìm học sinh..."
                debounce="400ms"
                class="max-w-md flex-1" />
        </div>
    </div>
    @else
    <div class="flex flex-col gap-4">
        <div class="flex-1 min-w-0">
            <livewire:filters.filter-bar
                :parish-id="$parishId"
                :show-nam-hoc="true"
                :show-khoi="true"
                :show-lop="true"
                :show-ky="true"
                :selected-nam-hoc="$selectedNamHoc"
                :selected-khoi="$selectedKhoi"
                :selected-lop="$selectedLop"
                :selected-ky="$selectedSemester" />
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <x-search-input
                wire-model="search"
                placeholder="Tìm học sinh..."
                debounce="400ms"
                class="max-w-md" />

            <div class="flex items-center gap-2 flex-wrap justify-end">
                @if($activeTab === 'scores')
                @if($canManageScoreConfig)
                <x-button
                    as="a"
                    href="{{ route('scores.statistics', ['namHoc' => $selectedNamHoc, 'khoi' => $selectedKhoi, 'lop' => $selectedLop, 'semester' => $selectedSemester]) }}"
                    variant="outline">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Thống kê
                </x-button>
                <x-button as="a" href="{{ route('scores.edit-logs') }}" variant="outline">
                    Nhật ký sửa
                </x-button>
                @endif
                @if($canEditScores)
                <x-button wire:click="saveAllScores" variant="primary">
                    <x-icon name="save" />
                    Lưu
                </x-button>
                @endif
                @if($canManageScoreConfig)
                <x-button wire:click="exportScores" variant="outline">
                    <x-icon name="file-export" />
                    Xuất Excel
                </x-button>
                @endif
                @endif
                @if($activeTab === 'config' && $canManageScoreConfig)
                <x-button wire:click="createScoreType" variant="primary">
                    <x-icon name="plus" />
                    Thêm loại điểm
                </x-button>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
