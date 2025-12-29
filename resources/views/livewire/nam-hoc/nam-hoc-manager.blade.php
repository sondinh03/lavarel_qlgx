<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="mx-auto max-w-7xl space-y-5">

        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[
            ['label' => 'Trang chủ', 'url' => route('home')],
            ['label' => 'Cài đặt hệ thống'],
            ['label' => 'Năm học']
        ]" />

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

            {{-- Header --}}
            <x-page-header
                title="Cài đặt năm học"
                description="Quản lý danh sách năm học trong giáo xứ"
                icon="calendar"
                gradient="blue"
                :count="count($namHocs)"
                count-label="Năm học" />

            {{-- Action --}}
            <div class="px-6 pb-4 flex justify-end">
                <button
                    wire:click="create"
                    class="inline-flex items-center gap-2 bg-blue-600 text-white px-5 py-2.5 rounded-xl font-semibold
                           hover:bg-blue-700 active:scale-95 transition-all shadow-lg hover:shadow-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                            <td class="px-6 py-3 font-medium">
                                {{ $nh->name }}
                            </td>

                            <td class="px-6 py-3 text-center text-sm">
                                {{ optional($nh->start_date_one)->format('d/m/Y') }}
                                →
                                {{ optional($nh->end_date_one)->format('d/m/Y') }}
                            </td>

                            <td class="px-6 py-3 text-center text-sm">
                                {{ optional($nh->start_date_two)->format('d/m/Y') }}
                                →
                                {{ optional($nh->end_date_two)->format('d/m/Y') }}
                            </td>

                            <td class="px-6 py-3 text-center">
                                @if ($nh->current_semester)
                                <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded-lg">
                                    HK {{ $nh->current_semester }}
                                </span>
                                @else
                                <span class="text-slate-400">—</span>
                                @endif
                            </td>

                            <td class="px-6 py-3 text-center">
                                @if ($nh->status)
                                <span class="px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded-lg">
                                    Hoạt động
                                </span>
                                @else
                                <span class="px-2 py-1 text-xs bg-slate-200 text-slate-600 rounded-lg">
                                    Lưu trữ
                                </span>
                                @endif
                            </td>

                            <td class="px-6 py-3 text-center space-x-3 text-sm">
                                <button wire:click="edit({{ $nh->id }})"
                                    class="text-blue-600 hover:underline">
                                    Sửa
                                </button>

                                <button wire:click="toggleStatus({{ $nh->id }})"
                                    class="text-orange-600 hover:underline">
                                    {{ $nh->status ? 'Lưu trữ' : 'Kích hoạt' }}
                                </button>
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