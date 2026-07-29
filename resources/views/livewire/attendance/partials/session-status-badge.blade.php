{{-- Badge trạng thái phiên điểm danh --}}
<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-[11px] sm:text-xs font-semibold
    {{ $session['statusClass'] ?? 'bg-slate-50/90 text-slate-500 ring-1 ring-slate-200/60 shadow-mac-sm' }}">
    @if($session['locked'] ?? false)
        <x-icon name="lock" class="w-3 h-3" />
    @elseif($session['cancelled'] ?? false)
        <x-icon name="cancel" class="w-3 h-3" />
    @else
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
    @endif
    {{ $session['statusLabel'] }}
</span>
