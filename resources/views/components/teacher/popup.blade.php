@props(['teacherNames' => [], 'teacherCount' => 0])
<div class="font-semibold text-slate-900 mb-3 flex items-center gap-2">
    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
    </svg>
    Giáo lý viên phụ trách ({{ $teacherCount }} người)
</div>
<div class="space-y-2">
    @foreach($teacherNames as $index => $name)
    <div class="flex items-center gap-3 text-sm">
        <div class="w-2 h-2 {{ $index === 0 ? 'bg-purple-600' : 'bg-slate-400' }} rounded-full"></div>
        <span class="{{ $index === 0 ? 'font-semibold text-purple-900' : 'text-slate-700' }}">{{ trim($name) }}</span>
        @if($index === 0)
        <span class="text-xs font-medium text-purple-600 bg-purple-100 px-2 py-0.5 rounded-full">Chủ nhiệm</span>
        @endif
    </div>
    @endforeach
</div>
