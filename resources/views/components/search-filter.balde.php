<div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
    <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">

        {{-- 1. SEARCH INPUT --}}
        <div class="relative flex-1 w-full">
            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text"
                   id="search-box"
                   placeholder="Tìm tên, mã HS, tên thánh..."
                   class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl 
                          text-slate-900 placeholder-slate-500
                          focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                   value="{{ request('search') }}">
        </div>

        {{-- 2. FILTER BUTTON --}}
        <div class="relative">
            <button type="button"
                    id="filter-btn"
                    class="flex items-center gap-2 px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl 
                           hover:bg-slate-100 active:scale-98 transition-all">
                <svg class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                <span class="font-semibold text-slate-900">Lọc</span>
                @if(request('guardian'))
                    <span class="ml-1 px-2 py-0.5 bg-blue-500 text-white text-xs font-bold rounded-full">1</span>
                @endif
            </button>

            {{-- 3. DROPDOWN --}}
            <div id="filter-menu"
                 class="hidden absolute right-0 top-full mt-2 w-64 bg-white rounded-xl border border-slate-200 shadow-lg z-20">
                <div class="px-4 py-3 bg-slate-50 border-b border-slate-200">
                    <div class="text-xs font-bold text-slate-600 uppercase">Giáo họ</div>
                </div>
                @foreach($guardians as $g)
                    <button type="button"
                            data-guardian="{{ $g }}"
                            class="filter-option w-full px-4 py-3 text-left hover:bg-slate-50 active:bg-slate-100 
                                   border-b border-slate-100 last:border-b-0
                                   {{ request('guardian') == $g ? 'bg-blue-50' : '' }}">
                        <span class="text-sm {{ request('guardian') == $g ? 'font-bold text-blue-600' : 'text-slate-900' }}">
                            {{ $g }}
                        </span>
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- 4. ACTIVE FILTER CHIPS --}}
    @if(request('search') || request('guardian'))
        <div class="mt-4 pt-4 border-t border-slate-200 flex flex-wrap items-center gap-2">
            <span class="text-sm font-semibold text-slate-600">Đang lọc:</span>

            @if(request('search'))
                <span class="inline-flex items-center gap-2 bg-blue-100 text-blue-700 px-3 py-1.5 rounded-full text-sm font-semibold">
                    "{{ request('search') }}"
                    <button type="button" class="remove-search hover:text-blue-900">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </span>
            @endif

            @if(request('guardian'))
                <span class="inline-flex items-center gap-2 bg-green-100 text-green-700 px-3 py-1.5 rounded-full text-sm font-semibold">
                    {{ request('guardian') }}
                    <button type="button" class="remove-guardian hover:text-green-900">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </span>
            @endif

            <button type="button" id="clear-all" class="ml-auto text-sm text-blue-600 font-semibold hover:text-blue-700">
                Xóa tất cả
            </button>
        </div>
    @endif
</div>

{{-- Overlay --}}
<div id="overlay" class="hidden fixed inset-0 z-10 bg-black bg-opacity-0"></div>