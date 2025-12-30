<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="mx-auto max-w-7xl space-y-5">

        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[
            [
                'label' => 'Trang chủ',
                'url' => route('home'),
            ],
            [
                'label' => 'Quản lý năm học',
                'url' => route('nam-hoc'),
                'icon' => '<svg class=\'w-4 h-4\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z\'/></svg>'
            ],
        ]" separator="arrow" />

        {{-- Toast --}}
        <div role="status" aria-live="polite">
            @if (session()->has('message'))
            <x-toast-notification type="success" :duration="3500">
                {{ session('message') }}
            </x-toast-notification>
            @endif
        </div>

        {{-- Main Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

            {{-- Header
            <x-page-header
                title="Cài đặt năm học"
                description="Quản lý danh sách năm học trong giáo xứ"
                icon="calendar"
                gradient="blue"
                :count="count($namHocs)"
                count-label="Năm học" />

                 --}}

            <div class="p-6 border-b border-slate-200 bg-gradient-to-br from-indigo-50 to-white">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-indigo-500 rounded-xl flex items-center justify-center shadow-sm">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>

                        <div>
                            <h1 class="text-2xl font-bold text-slate-900">Quản lý năm học</h1>
                            <p class="text-sm text-slate-600 mt-1">
                                Danh sách các năm học đã được tạo trong hệ thống
                            </p>
                        </div>
                    </div>

                    <div class="text-right">
                        <div class="text-3xl font-bold text-indigo-600">{{ $namHocs->count() }}</div>
                        <div class="text-xs text-slate-600 font-medium">Năm học</div>
                    </div>
                </div>
            </div>

            {{-- Action --}}
            <div class="px-6 py-4 flex justify-end border-b border-slate-200 bg-slate-50/70">
                <button
                    wire:click="create"
                    class="inline-flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-xl
               text-sm font-semibold hover:bg-indigo-700 active:scale-95 transition-all shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4v16m8-8H4" />
                    </svg>
                    Thêm năm học
                </button>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="w-full border-separate border-spacing-0">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <x-table-header>Tên năm học</x-table-header>
                            <x-table-header class="text-center">Học kỳ I</x-table-header>
                            <x-table-header class="text-center">Học kỳ II</x-table-header>
                            <x-table-header class="text-center">HK hiện tại</x-table-header>
                            <x-table-header class="text-center">Trạng thái</x-table-header>
                            <x-table-header class="text-center">Thao tác</x-table-header>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse ($namHocs as $nh)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4 font-semibold text-slate-900">
                                {{ $nh->name }}
                            </td>

                            <td class="px-6 py-4 text-center text-sm">
                                {{ optional($nh->start_date_one)->format('d/m/Y') }}
                                →
                                {{ optional($nh->end_date_one)->format('d/m/Y') }}
                            </td>

                            <td class="px-6 py-4 text-center text-sm">
                                {{ optional($nh->start_date_two)->format('d/m/Y') }}
                                →
                                {{ optional($nh->end_date_two)->format('d/m/Y') }}
                            </td>

                            <td class="px-6 py-4 text-center">
                                @if ($nh->current_semester)
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700">
                                    HK {{ $nh->current_semester }}
                                </span>
                                @else
                                <span class="text-slate-400">—</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-center">
                                @if ($nh->status)
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-700">
                                    Hoạt động
                                </span>
                                @else
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-slate-200 text-slate-600">
                                    Lưu trữ
                                </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-center">
                                <div class="inline-flex items-center gap-3 text-sm font-medium">
                                    <button wire:click="edit({{ $nh->id }})"
                                        class="text-indigo-600 hover:text-indigo-800">
                                        Sửa
                                    </button>

                                    <span class="text-slate-300">|</span>

                                    <button wire:click="toggleStatus({{ $nh->id }})"
                                        class="text-orange-600 hover:text-orange-800">
                                        {{ $nh->status ? 'Lưu trữ' : 'Kích hoạt' }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <x-empty-state
                            icon="calendar"
                            :colspan="6"
                            title="Chưa có năm học"
                            description="Hãy tạo năm học đầu tiên cho giáo xứ">
                        </x-empty-state>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Modal --}}
        @if ($showForm)
        @include('livewire.nam-hoc.form')
        @endif
    </div>
</div>