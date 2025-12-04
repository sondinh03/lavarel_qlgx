<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <div class="mx-auto max-w-7xl space-y-5">
        
        {{-- Toast Notifications --}}
        @if (session()->has('message'))
        <x-toast-notification type="success" :duration="3000">
            {{ session('message') }}
        </x-toast-notification>
        @endif

        @if (session()->has('error'))
        <x-toast-notification type="error" :duration="4000">
            {{ session('error') }}
        </x-toast-notification>
        @endif

        {{-- Back Button --}}
        <div>
            <a href="{{ route('ds-lop') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 active:scale-95 transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Quay lại danh sách
            </a>
        </div>

        {{-- Class Info Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            {{-- Header --}}
            <x-page-header
                :title="$lop->name"
                :description="$lop->symbol ? 'Mã lớp: ' . $lop->symbol : ''"
                icon="class"
                gradient="purple"
                :count="$statistics['total']"
                count-label="Tổng sĩ số" />

            {{-- Compact Stats - Inline --}}
            <div class="px-4 py-3 border-b border-slate-200 bg-slate-50">
                <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span class="text-slate-600">Năm học:</span>
                        <span class="font-semibold text-slate-900">{{ $namHoc->name ?? 'N/A' }}</span>
                    </div>
                    <span class="text-slate-300">•</span>
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <span class="text-slate-600">Khối:</span>
                        <span class="font-semibold text-slate-900">{{ $block->name ?? 'N/A' }}</span>
                    </div>
                    <span class="text-slate-300">•</span>
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span class="text-slate-600">Nam:</span>
                        <span class="font-semibold text-blue-600">{{ $statistics['male'] }}</span>
                    </div>
                    <span class="text-slate-300">•</span>
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span class="text-slate-600">Nữ:</span>
                        <span class="font-semibold text-pink-600">{{ $statistics['female'] }}</span>
                    </div>
                </div>
            </div>

            {{-- Teachers Section - Compact --}}
            @if($teachers && $teachers->count() > 0)
            <div class="p-4 border-b border-slate-200">
                <h3 class="text-sm font-bold text-slate-900 mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Giáo lý viên
                    <span class="text-xs font-normal text-slate-600">({{ $teachers->count() }})</span>
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    @foreach($teachers as $index => $teacher)
                    <div class="flex items-center gap-2 p-2 bg-slate-50 rounded-lg border border-slate-200">
                        <div class="w-8 h-8 {{ $index === 0 ? 'bg-purple-500' : 'bg-slate-400' }} rounded-full flex items-center justify-center flex-shrink-0">
                            <span class="text-white font-semibold text-xs">
                                {{ mb_substr($teacher->name, 0, 2) }}
                            </span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-slate-900 text-sm truncate">{{ $teacher->name }}</p>
                            @if($index === 0)
                            <span class="text-xs text-purple-600 font-medium">Chủ nhiệm</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @else
            <div class="p-4 border-b border-slate-200">
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-2 flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <p class="text-sm text-amber-900 font-medium">Chưa phân công giáo lý viên</p>
                </div>
            </div>
            @endif

            {{-- Schedule Section - Compact --}}
            @if($lop->start_date_one || $lop->end_date_one || $lop->start_date_two || $lop->end_date_two)
            <div class="p-4 border-b border-slate-200">
                <h3 class="text-sm font-bold text-slate-900 mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Lịch học
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    @if($lop->start_date_one && $lop->end_date_one)
                    <div class="flex items-center justify-between p-2 bg-slate-50 rounded-lg border border-slate-200">
                        <span class="text-xs font-semibold text-slate-700">Học kỳ 1</span>
                        <span class="text-xs text-slate-600 font-medium">
                            {{ \Carbon\Carbon::parse($lop->start_date_one)->format('d/m/Y') }} - 
                            {{ \Carbon\Carbon::parse($lop->end_date_one)->format('d/m/Y') }}
                        </span>
                    </div>
                    @endif
                    
                    @if($lop->start_date_two && $lop->end_date_two)
                    <div class="flex items-center justify-between p-2 bg-slate-50 rounded-lg border border-slate-200">
                        <span class="text-xs font-semibold text-slate-700">Học kỳ 2</span>
                        <span class="text-xs text-slate-600 font-medium">
                            {{ \Carbon\Carbon::parse($lop->start_date_two)->format('d/m/Y') }} - 
                            {{ \Carbon\Carbon::parse($lop->end_date_two)->format('d/m/Y') }}
                        </span>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Note Section - Compact --}}
            @if($lop->note)
            <div class="p-4 border-b border-slate-200">
                <h3 class="text-sm font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                    </svg>
                    Ghi chú
                </h3>
                <p class="text-slate-700 text-sm leading-relaxed">{{ $lop->note }}</p>
            </div>
            @endif

            {{-- Quick Action - Single Link --}}
            <div class="p-4 bg-white">
                <a href="{{ $slugUrl }}" 
                   class="flex items-center justify-center gap-2 w-full px-4 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl hover:from-blue-600 hover:to-blue-700 active:scale-[0.98] transition-all shadow-sm font-semibold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Xem danh sách học sinh
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush