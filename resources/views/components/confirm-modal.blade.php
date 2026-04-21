{{-- resources/views/components/confirm-modal.blade.php --}}
@props([
'title' => 'Xác nhận',
'message' => '',
'onConfirm' => null,
])

<div x-data="{ open: @entangle($attributes->wire('model')) }"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center">

    {{-- Overlay --}}
    <div class="absolute inset-0 bg-black/40" @click="open = false"></div>

    {{-- Modal --}}
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6">

        {{-- Title --}}
        <h2 class="text-lg font-bold text-slate-900">
            {{ $title ?? 'Xác nhận' }}
        </h2>

        {{-- Message --}}
        <p class="mt-2 text-sm text-slate-600">
            {{ $message ?? 'Bạn có chắc chắn thực hiện hành động này?' }}
        </p>

        {{-- Actions --}}
        <div class="mt-6 flex justify-end gap-2">

            <button @click="open = false"
                class="px-4 py-2 rounded-xl border border-slate-200 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Hủy
            </button>

            <button
                @click="open = false; Livewire.emit('{{ $onConfirm }}')"
                class="px-4 py-2 bg-red-500 text-white rounded-xl text-sm font-medium hover:bg-red-600 transition-all">
                Xác nhận
            </button>

        </div>
    </div>
</div>