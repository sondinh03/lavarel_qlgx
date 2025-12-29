<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="mx-auto max-w-7xl space-y-5">

        {{-- BREADCRUMB --}}
        <x-breadcrumb :items="[
            ['label' => 'Trang chủ', 'url' => route('home')],
            ['label' => 'Quản lý khối']
        ]" />

        {{-- Toast Notifications --}}
        <div role="status" aria-live="polite" aria-atomic="true">
            @if(session()->has('message'))
            <x-toast-notification type="success" :duration="3500">
                {{ session('message') }}
            </x-toast-notification>
            @endif
        </div>

        {{-- Header + Selector + Create button --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">

            <div class="flex items-center gap-3">
                <label for="selectedNamHoc" class="font-medium">Năm học:</label>
                <select wire:model="selectedNamHoc" id="selectedNamHoc" class="rounded-md border-gray-300">
                    @foreach($namHocs as $nh)
                    <option value="{{ $nh->id }}">{{ $nh->name }}</option>
                    @endforeach
                </select>
            </div>

            @if($parish_id)
            <button wire:click="create"
                class="bg-purple-600 text-white px-5 py-2.5 rounded-xl font-semibold hover:bg-purple-700">
                Thêm khối
            </button>
            @endif
        </div>

        {{-- Blocks Table --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <x-loading.overlay wire-target="selectedNamHoc,perPage,resetForm,save,toggleStatus" mode="centered">
                Đang tải danh sách khối...
            </x-loading.overlay>

            <div class="overflow-x-auto">
                <table class="w-full border-separate border-spacing-0">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <x-table-header>STT</x-table-header>
                            <x-table-header>Tên khối</x-table-header>
                            <x-table-header>Trạng thái</x-table-header>
                            <x-table-header>Thao tác</x-table-header>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($blocks as $index => $block)
                        <tr>
                            <td class="px-4 py-2">{{ $blocks->firstItem() + $index }}</td>
                            <td class="px-4 py-2">{{ $block->name }}</td>
                            <td class="px-4 py-2">
                                <button wire:click="toggleStatus({{ $block->id }})"
                                    class="px-3 py-1 rounded-lg {{ $block->status ? 'bg-green-500 text-white' : 'bg-red-500 text-white' }}">
                                    {{ $block->status ? 'Hoạt động' : 'Đóng' }}
                                </button>
                            </td>
                            <td class="px-4 py-2">
                                <button wire:click="edit({{ $block->id }})" class="text-blue-600 hover:underline">Sửa</button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-gray-500">Chưa có khối nào</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($blocks && $blocks->hasPages())
            <div class="p-4">
                <x-pagination :paginator="$blocks" :per-page-options="$perPageOptions" />
            </div>
            @endif
        </div>
    </div>
</div>