@props(['lop', 'index', 'paginator'])
<tr class="hover:bg-slate-50 transition-colors" role="row" wire:key="lop-{{ $lop->id }}">
    {{-- STT --}}
    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-900" role="cell">
        {{ ($paginator->firstItem() ?? 0) + $index }}
    </td>

    {{-- Mã lớp --}}
    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-purple-600 font-semibold" role="cell">
        {{ $lop->symbol ?? '-' }}
    </td>

    {{-- Tên lớp --}}
    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-900" role="cell">
        {{ $lop->name }}
    </td>

    {{-- Khối --}}
    <td class="px-6 py-4 whitespace-nowrap" role="cell">
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">{{ $lop->blockRelation->name ?? 'N/A' }}</span>
    </td>

    {{-- Sĩ số --}}
    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700" role="cell">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span class="font-semibold">{{ $lop->students_count ?? 0 }}</span>
        </div>
    </td>

    {{-- Giáo lý viên --}}
    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700" role="cell">
        @if($lop->has_teacher)
        <div x-data="{ open: false }" class="relative">
            <button @mouseover="open = true" @mouseleave="open = false" class="flex items-center gap-2 font-medium text-slate-900 hover:text-purple-600 transition-colors focus:outline-none">
                <span class="block max-w-48 leading-tight"><span class="inline-block">{{ $lop->teacher_names[0] ?? 'GLV' }}</span></span>
                @if($lop->teacher_count > 1)
                <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-semibold text-purple-700 bg-purple-100 rounded-full shrink-0">+{{ $lop->teacher_count - 1 }}</span>
                @endif
            </button>

            <div x-show="open" x-transition x-cloak class="absolute left-0 top-full mt-2 w-auto max-w-xs p-4 bg-white rounded-xl shadow-xl border border-slate-200 z-20">
                <x-teacher.popup :teacherNames="$lop->teacher_names" :teacherCount="$lop->teacher_count" />
            </div>
        </div>
        @else
        <span class="inline-flex items-center gap-1.5 py-1.5 px-3 rounded-full text-xs font-medium bg-amber-50 text-amber-900">Chưa phân công</span>
        @endif
    </td>

    {{-- Thao tác --}}
    <td class="px-6 py-4 whitespace-nowrap" role="cell">
        <div class="flex items-center gap-2">
            <a href="{{ route('lop.show', $lop->id) }}" class="p-2 hover:bg-blue-50 text-blue-600 rounded-lg active:scale-95 transition-all" title="Xem chi tiết" aria-label="Xem chi tiết lớp {{ $lop->name }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            </a>
            <a href="{{ route('lop.edit', $lop->id) }}" class="p-2 hover:bg-orange-50 text-orange-600 rounded-lg active:scale-95 transition-all" title="Chỉnh sửa" aria-label="Chỉnh sửa lớp {{ $lop->name }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </a>
            <a href="{{ $lop->slug_url }}" class="p-2 hover:bg-green-50 text-green-600 rounded-lg active:scale-95 transition-all" title="Danh sách học sinh" aria-label="Xem danh sách học sinh lớp {{ $lop->name }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </a>
        </div>
    </td>
</tr>
