<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">

    {{--<x-loading-indicator target="selectedNamHoc,selectedKhoi,resetFilters" />--}}

    <div class="mx-auto max-w-7xl space-y-5">
        @if (session()->has('message'))
        <x-toast-notification type="success" :duration="3000">
            {{ session('message') }}
        </x-toast-notification>
        @endif

        @if (session()->has('error'))
        <x-toast-notification type="error" :duration="4000">
            {{ session('error') }}
        </x-toast-notification>
        @endif

        @if (session()->has('warning'))
        <x-toast-notification type="warning" :duration="3500">
            {{ session('warning') }}
        </x-toast-notification>
        @endif

        {{-- Combined Header + Filter + Stats Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            {{-- Header Section --}}
            <!-- <div class="bg-gradient-to-r from-purple-500 to-indigo-600 p-6">
                <div class="flex items-center justify-between text-white">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold">Quản lý lớp học</h1>
                            <p class="text-purple-100 text-sm mt-0.5">Danh sách các lớp học trong năm</p>
                        </div>
                    </div>
                    @if($selectedNamHoc && $lops)
                    <div class="text-right">
                        <p class="text-purple-100 text-sm font-medium">Tổng số lớp</p>
                        <p class="text-3xl font-bold">{{ $lops->count() }}</p>
                    </div>
                    @endif
                </div>
            </div> -->

            <x-page-header
                title="Quản lý lớp học"
                description="Danh sách các lớp học trong năm"
                icon="class"
                gradient="purple"
                :count="$selectedNamHoc && $lops ? $lops->count() : null"
                count-label="Tổng số lớp" />

            {{-- Filter Section --}}
            <div class="p-6 bg-slate-50">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{-- Năm học Dropdown --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Năm học</label>
                        <select wire:model.live="selectedNamHoc"
                            class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                            @foreach($namHocs as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Khối Dropdown --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Khối</label>
                        <select wire:model.live="selectedKhoi"
                            class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                            @if(!$selectedNamHoc) disabled @endif>
                            <option value="">-- Tất cả khối --</option>
                            @if($khois)
                            @foreach($khois as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                            @endif
                        </select>
                    </div>

                    {{-- Reset Button --}}
                    <div class="flex items-end">
                        <button wire:click="resetFilters"
                            type="button"
                            class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span class="font-semibold text-slate-900">Đặt lại</span>
                        </button>
                    </div>
                </div>

                {{-- Loading Indicator 
                <div wire:loading wire:target="selectedNamHoc,selectedKhoi,resetFilters"
                    class="mt-4">
                    <div class="bg-purple-50 border border-purple-200 rounded-xl p-3 flex items-center gap-3">
                        <svg class="animate-spin h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-purple-700 font-medium">Đang tải dữ liệu...</span>
                    </div>
                </div>
                --}}
            </div>

            {{-- Filter Section
            <x-filter-section :show-reset="true">
                <x-select-input 
                    label="Năm học"
                    wire-model="selectedNamHoc"
                    :options="$namHocs"
                    :required="true" />

                <x-select-input 
                    label="Khối"
                    wire-model="selectedKhoi"
                    :options="array_merge([''=>'-- Tất cả khối --'], $khois ?? [])"
                    :disabled="!$selectedNamHoc" />
            </x-filter-section>
             --}}
        </div>

        {{-- Class Table --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-separate border-spacing-0">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <!-- <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">STT</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Mã lớp</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Tên lớp</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Khối</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Sĩ số</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Giáo lý viên</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Thao tác</th> -->

                            <x-table-header>STT</x-table-header>
                            <!-- <x-table-header
                                sortable
                                sort-field="symbol"
                                :current-sort="$sortField ?? ''"
                                :sort-direction="$sortDirection ?? 'asc'">
                                Mã lớp
                            </x-table-header>
                            <x-table-header
                                sortable
                                sort-field="name"
                                :current-sort="$sortField ?? ''"
                                :sort-direction="$sortDirection ?? 'asc'">
                                Tên lớp
                            </x-table-header> -->
                            <x-table-header>Mã lớp</x-table-header>
                            <x-table-header>Tên lớp</x-table-header>
                            <x-table-header>Khối</x-table-header>
                            <!-- <x-table-header align="center">Sĩ số</x-table-header> -->
                            <x-table-header>Sĩ số</x-table-header>
                            <x-table-header>Giáo lý viên</x-table-header>
                            <x-table-header>Thao tác</x-table-header>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($lops as $index => $lop)
                        <tr class="hover:bg-slate-50 transition-colors">
                            {{-- STT --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-900">
                                {{ $index + 1 }}
                            </td>

                            {{-- Mã lớp --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-purple-600 font-semibold">
                                {{ $lop->symbol ?? '-' }}
                            </td>

                            {{-- Tên lớp --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-900">
                                {{ $lop->name }}
                            </td>

                            {{-- Khối --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">
                                    {{ $lop->blockRelation->name ?? 'N/A' }}
                                </span>
                            </td>

                            {{-- Sĩ số --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <span class="font-semibold">{{ $lop->students_count ?? 0 }}</span>
                                </div>
                            </td>

                            {{-- Giáo lý viên --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">
                                @if($lop->has_teacher)
                                <div x-data="{ open: false }" class="relative">
                                    {{-- Tên GLV chủ nhiệm (người đầu tiên) --}}
                                    <button
                                        @mouseover="open = true"
                                        @mouseleave="open = false"
                                        class="flex items-center gap-2 font-medium text-slate-900 hover:text-purple-600 transition-colors focus:outline-none">

                                        <span class="block max-w-48 leading-tight">
                                            <span class="inline-block">{{ $lop->teacher_names[0] ?? 'GLV' }}</span>
                                        </span>

                                        @if($lop->teacher_count > 1)
                                        <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-semibold text-purple-700 bg-purple-100 rounded-full shrink-0">
                                            +{{ $lop->teacher_count - 1 }}
                                        </span>
                                        @endif
                                    </button>

                                    <div
                                        x-show="open"
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 transform scale-95"
                                        x-transition:enter-end="opacity-100 transform scale-100"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100 transform scale-100"
                                        x-transition:leave-end="opacity-0 transform scale-95"
                                        x-cloak
                                        class="absolute left-0 top-full mt-2 w-auto max-w-xs p-4 bg-white rounded-xl shadow-xl border border-slate-200 z-20">

                                        <div class="font-semibold text-slate-900 mb-3 flex items-center gap-2">
                                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                            </svg>
                                            Giáo lý viên phụ trách ({{ $lop->teacher_count }} người)
                                        </div>

                                        <div class="space-y-2">
                                            @foreach($lop->teacher_names as $index => $name)
                                            <div class="flex items-center gap-3 text-sm">
                                                <div class="w-2 h-2 {{ $index === 0 ? 'bg-purple-600' : 'bg-slate-400' }} rounded-full"></div>
                                                <span class="{{ $index === 0 ? 'font-semibold text-purple-900' : 'text-slate-700' }}">
                                                    {{ trim($name) }}
                                                </span>
                                                @if($index === 0)
                                                <span class="text-xs font-medium text-purple-600 bg-purple-100 px-2 py-0.5 rounded-full">
                                                    Chủ nhiệm
                                                </span>
                                                @endif
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @else
                                <span class="inline-flex items-center gap-1.5 py-1.5 px-3 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Chưa phân công
                                </span>
                                @endif
                            </td>

                            {{-- Thao tác --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    {{-- Xem chi tiết --}}
                                    <a href="{{ route('lop.show', $lop->id) }}"
                                        class="p-2 hover:bg-blue-50 text-blue-600 rounded-lg active:scale-95 transition-all"
                                        title="Xem chi tiết">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>

                                    {{-- Sửa --}}
                                    {{-- @if($isAdmin) --}}
                                    <a href="{{ route('lop.edit', $lop->id) }}"
                                        class="p-2 hover:bg-orange-50 text-orange-600 rounded-lg active:scale-95 transition-all"
                                        title="Chỉnh sửa">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    {{-- @endif --}}

                                    {{-- Danh sách học sinh --}}
                                    <a href="{{ $lop->slug_url }}"
                                        class="p-2 hover:bg-green-50 text-green-600 rounded-lg active:scale-95 transition-all"
                                        title="Danh sách học sinh">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>

                        @empty
                        <x-empty-state
                            icon="class"
                            :colspan="7"
                            :title="$selectedNamHoc ? 'Không tìm thấy lớp học' : 'Chưa chọn năm học'"
                            :description="!$selectedNamHoc ? 'Vui lòng chọn năm học để xem danh sách lớp' : ($selectedKhoi ? 'Không có lớp nào trong khối này' : 'Chưa có lớp học nào trong năm học này')">
                            @if($isAdmin && $selectedNamHoc)
                            <a href="{{ route('lop.create') }}"
                                class="mt-4 bg-purple-600 text-white px-6 py-2.5 rounded-xl font-semibold hover:bg-purple-700 active:scale-95 transition-all flex items-center gap-2 shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Tạo lớp học mới
                            </a>
                            @endif
                        </x-empty-state>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($lops->hasPages())
            <x-pagination :paginator="$lops" :per-page-options="[10, 15, 25, 50]" />
            @endif
        </div>
    </div>
</div>

@push('scripts')
{{-- Alpine.js cho toast notifications --}}
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

<div
    x-data="{ show: false, x: 0, y: 0, content: '' }"
    x-show="show"
    x-transition
    x-cloak
    @mousemove.window="if(show) { x = $event.pageX + 15; y = $event.pageY + 15 }"
    :style="'position:absolute; left:'+x+'px; top:'+y+'px;'"
    class="z-[9999] px-4 py-3 bg-white rounded-xl shadow-xl border border-slate-200 text-sm">
    <div x-html="content"></div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('tooltip', {
            show: false
        });

        window.addEventListener('tooltip-show', e => {
            const tooltip = document.querySelector('[x-data]');
            tooltip.__x.$data.content = e.detail.content;
            tooltip.__x.$data.show = true;
        });

        window.addEventListener('tooltip-hide', () => {
            const tooltip = document.querySelector('[x-data]');
            tooltip.__x.$data.show = false;
        });
    });
</script>