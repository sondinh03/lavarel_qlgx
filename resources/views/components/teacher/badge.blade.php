@props(['name', 'isChuNhiem' => false, 'size' => '8'])
<div class="flex items-center gap-2 p-2 bg-slate-50 rounded-lg border border-slate-200">
    <div class="w-{{ $size }} h-{{ $size }} {{ $isChuNhiem ? 'bg-purple-500' : 'bg-slate-400' }} rounded-full flex items-center justify-center flex-shrink-0">
        <span class="text-white font-semibold text-xs">{{ mb_substr($name, 0, 2) }}</span>
    </div>
    <div class="flex-1 min-w-0">
        <p class="font-medium text-slate-900 text-sm truncate">{{ $name }}</p>
        @if($isChuNhiem)
        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-purple-100 text-purple-700 border border-purple-200">Chủ nhiệm</span>
        @endif
    </div>
</div>
