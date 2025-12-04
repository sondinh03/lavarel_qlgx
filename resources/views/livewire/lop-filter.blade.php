<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">

    <div class="mx-auto max-w-7xl space-y-5">

        {{-- Header Card --}}
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-purple-500 rounded-xl flex items-center justify-center shadow-sm">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-slate-900">Quản lý lớp học</h1>
                        <p class="text-sm text-slate-600 mt-1">Danh sách các lớp học trong năm</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter Card --}}
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Năm học Dropdown --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Năm học</label>
                    <select wire:model.live="selectedNamHoc"
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                        {{--<option value="">-- Chọn năm học --</option>--}}
                        @foreach($namHocs as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Khối Dropdown --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Khối</label>
                    <select wire:model.live="selectedKhoi"
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                        @if(!$selectedNamHoc) disabled @endif>
                        <option value="">-- Tất cả khối --</option>
                        @if($khois)
                        @foreach($khois as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                        @endif
                    </select>
                </div>

                {{-- Reset Button --}}
                <div class="flex items-end">
                    <button wire:click="resetFilters"
                        type="button"
                        class="w-full px-4 py-3 bg-slate-100 border border-slate-200 rounded-xl hover:bg-slate-200 active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span class="font-semibold text-slate-900">Đặt lại</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Statistics Card --}}
        @if($selectedNamHoc && $lops)
        <div class="bg-gradient-to-r from-purple-500 to-indigo-600 rounded-2xl p-6 shadow-sm text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium">Tổng số lớp</p>
                    <p class="text-4xl font-bold mt-1">{{ $lops->count() }}</p>
                </div>
                <div class="w-16 h-16 bg-white/20 rounded-xl flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
            </div>
        </div>
        @endif

        {{-- Class Table --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-separate border-spacing-0">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">STT</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Mã lớp</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Tên lớp</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Khối</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Sĩ số</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Giáo lý viên</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($lops as $index => $lop)
                        <tr class="hover:bg-slate-50 transition-colors">
                            {{-- STT --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-900">
                                {{ $index + 1 }}
                            </td>

                            {{-- Mã lớp --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-purple-600 font-semibold">
                                {{ $lop->symbol ?? '-' }}
                            </td>

                            {{-- Tên lớp --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-900">
                                {{ $lop->name }}
                            </td>

                            {{-- Khối --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">
                                    {{ $lop->blockRelation->name ?? 'N/A' }}
                                </span>
                            </td>

                            {{-- Sĩ số --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <span class="font-semibold">{{ $lop->students_count ?? 0 }}</span>
                                </div>
                            </td>

                            {{-- Giáo lý viên --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">
                                {{ $lop->teacher_name ?? 'Chưa phân công' }}
                            </td>

                            {{-- Thao tác --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    {{-- Xem chi tiết --}}
                                    <!-- <a href="{{ route('lop.show', $lop->id) }}" -->
                                    <a href="{{$lop->slug}}"
                                        class="p-2 hover:bg-blue-50 text-blue-600 rounded-lg active:scale-95 transition-all"
                                        title="Xem chi tiết">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>

                                    {{-- Sửa --}}
                                    <a href="{{ route('lop.edit', $lop->id) }}"
                                        class="p-2 hover:bg-orange-50 text-orange-600 rounded-lg active:scale-95 transition-all"
                                        title="Chỉnh sửa">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>

                                    {{-- Danh sách học sinh --}}
                                    {{--<a href="{{ route('lop.students', $lop->id) }}"
                                    class="p-2 hover:bg-green-50 text-green-600 rounded-lg active:scale-95 transition-all"
                                    title="Danh sách học sinh">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    </a>--}}
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                <svg class="w-12 h-12 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <p class="font-medium">Không tìm thấy lớp học nào</p>
                                <p class="text-sm mt-1">Vui lòng chọn năm học để xem danh sách lớp</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>