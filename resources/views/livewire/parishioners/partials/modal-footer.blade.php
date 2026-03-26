<div class="flex items-center justify-end gap-3 px-6 py-4 border-t bg-slate-50 flex-shrink-0">
    <button wire:click="$set('{{ $close }}', false)"
        class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition">
        Hủy
    </button>
    <button wire:click="{{ $save }}" wire:loading.attr="disabled" wire:target="{{ $save }}"
        class="px-6 py-2 text-sm font-medium text-white bg-primary-600 rounded-xl hover:bg-primary-700 transition disabled:opacity-60">
        <span wire:loading.remove wire:target="{{ $save }}">Lưu</span>
        <span wire:loading wire:target="{{ $save }}">Đang lưu...</span>
    </button>
</div>