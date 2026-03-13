<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4">
    <div class="mx-auto max-w-2xl space-y-4">

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 px-5 py-4">
            <h1 class="text-xl font-bold text-slate-900">
                Xin chào, {{ auth()->user()->name }} 👋
            </h1>
            <p class="text-sm text-slate-500 mt-0.5">Giáo lý viên</p>
        </div>

        <div class="grid grid-cols-1 gap-3">
            <a href="{{ route('attendance.show') }}"
                class="flex items-center gap-4 bg-white rounded-2xl shadow-sm border border-slate-200
                      px-5 py-4 hover:bg-primary-50 hover:border-primary-200 transition-all group">
                <div class="w-11 h-11 bg-primary-100 rounded-xl flex items-center justify-center flex-shrink-0
                            group-hover:bg-primary-200 transition-colors">
                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-slate-900 group-hover:text-primary-700">Điểm danh</p>
                    <p class="text-sm text-slate-400">Điểm danh lớp học của bạn</p>
                </div>
                <svg class="w-5 h-5 text-slate-300 ml-auto group-hover:text-primary-400"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>

            <a href="{{ route('students.index') }}"
                class="flex items-center gap-4 bg-white rounded-2xl shadow-sm border border-slate-200
                      px-5 py-4 hover:bg-green-50 hover:border-green-200 transition-all group">
                <div class="w-11 h-11 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0
                            group-hover:bg-green-200 transition-colors">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-slate-900 group-hover:text-green-700">Học sinh</p>
                    <p class="text-sm text-slate-400">Danh sách học sinh lớp tôi</p>
                </div>
                <svg class="w-5 h-5 text-slate-300 ml-auto group-hover:text-green-400"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>

    </div>
</div>