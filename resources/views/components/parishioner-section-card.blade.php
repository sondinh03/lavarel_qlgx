@props(['title', 'editAction' => null])

<div class="bg-slate-50 rounded-2xl border border-slate-100 overflow-hidden">
    <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between gap-2">
        <h3 class="text-sm font-semibold text-slate-800">{{ $title }}</h3>
        @if($editAction && auth()->user()?->canManageParishioners())
        <button type="button" wire:click="{{ $editAction }}"
            class="text-xs font-semibold text-primary-600 hover:text-primary-700 transition">
            Chỉnh sửa
        </button>
        @endif
    </div>
    <div class="divide-y divide-slate-100">
        {{ $slot }}
    </div>
</div>
