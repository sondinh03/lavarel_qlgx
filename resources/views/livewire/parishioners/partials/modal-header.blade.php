<div class="flex items-center justify-between px-6 py-4 border-b bg-gradient-to-r from-primary-50 to-white flex-shrink-0">
    <h2 class="text-base font-bold text-slate-900">{{ $title }}</h2>
    <button wire:click="$set('{{ $close }}', false)" class="text-slate-400 hover:text-slate-600 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
</div>