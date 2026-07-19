@section('topbar')
<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => route('parish-admin.dashboard')],
    ['label' => 'Năm học', 'url' => route('school-years.index')],
    ['label' => 'Cấu hình năm học mới'],
]" />
@endsection

<div class="min-h-screen bg-apple-gray p-2 sm:p-4 lg:p-6"
    style="min-height: calc(100vh - 56px - var(--bottom-offset));"
    x-data="{ mode: '{{ $hasExistingYears ? 'renew' : 'first' }}' }">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="mx-auto max-w-3xl space-y-5">

        <x-mac-panel :overflow="true">
            <x-page-header
                title="Cấu hình năm học mới"
                description="Chọn tình huống của bạn, rồi làm lần lượt các bước. Mỗi bước ghi rõ màn hình và nút cần bấm."
                icon-type="schoolYear">
                <x-slot name="actions">
                    <a href="{{ route('school-years.index') }}"
                        class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold
                               text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        ← Về danh sách năm học
                    </a>
                </x-slot>
            </x-page-header>
        </x-mac-panel>

        {{-- Chọn tình huống --}}
        <x-mac-panel class="p-4 lg:p-6">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-3">Bạn đang ở tình huống nào?</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <button type="button"
                    @click="mode = 'first'"
                    class="text-left rounded-2xl border px-4 py-3.5 transition shadow-mac-sm"
                    :class="mode === 'first'
                        ? 'border-primary-300 bg-primary-50/80 ring-1 ring-primary-200'
                        : 'border-black/[0.06] bg-white/60 hover:bg-white'">
                    <p class="text-sm font-semibold text-slate-900">Lần đầu sử dụng hệ thống</p>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        Chưa có năm học / chưa có lớp từ năm trước. Tạo từ đầu.
                    </p>
                </button>
                <button type="button"
                    @click="mode = 'renew'"
                    class="text-left rounded-2xl border px-4 py-3.5 transition shadow-mac-sm"
                    :class="mode === 'renew'
                        ? 'border-primary-300 bg-primary-50/80 ring-1 ring-primary-200'
                        : 'border-black/[0.06] bg-white/60 hover:bg-white'">
                    <p class="text-sm font-semibold text-slate-900">Mở năm học mới</p>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        Đã có năm cũ với lớp. Tạo năm mới rồi sao chép cấu trúc lớp.
                    </p>
                </button>
            </div>
        </x-mac-panel>

        {{-- ═══════════════ LẦN ĐẦU ═══════════════ --}}
        <div x-show="mode === 'first'" x-cloak class="space-y-5">

            <x-mac-panel class="p-4 lg:p-6">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-3">Luồng lần đầu</p>
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-1 text-sm">
                    <div class="flex-1 rounded-xl bg-primary-50 border border-primary-100 px-3 py-2.5 text-center">
                        <p class="text-[11px] text-primary-600 font-medium">Bước 1</p>
                        <p class="font-semibold text-primary-900 text-xs sm:text-sm">Tạo &amp; kích hoạt năm</p>
                    </div>
                    <div class="hidden sm:flex text-slate-300 px-1">→</div>
                    <div class="sm:hidden text-center text-slate-300 text-xs">↓</div>
                    <div class="flex-1 rounded-xl bg-primary-50 border border-primary-100 px-3 py-2.5 text-center">
                        <p class="text-[11px] text-primary-600 font-medium">Bước 2</p>
                        <p class="font-semibold text-primary-900 text-xs sm:text-sm">Tạo lớp học</p>
                    </div>
                    <div class="hidden sm:flex text-slate-300 px-1">→</div>
                    <div class="sm:hidden text-center text-slate-300 text-xs">↓</div>
                    <div class="flex-1 rounded-xl bg-primary-50 border border-primary-100 px-3 py-2.5 text-center">
                        <p class="text-[11px] text-primary-600 font-medium">Bước 3</p>
                        <p class="font-semibold text-primary-900 text-xs sm:text-sm">Ghi danh học sinh</p>
                    </div>
                    <div class="hidden sm:flex text-slate-300 px-1">→</div>
                    <div class="sm:hidden text-center text-slate-300 text-xs">↓</div>
                    <div class="flex-1 rounded-xl bg-primary-50 border border-primary-100 px-3 py-2.5 text-center">
                        <p class="text-[11px] text-primary-600 font-medium">Bước 4</p>
                        <p class="font-semibold text-primary-900 text-xs sm:text-sm">Gán giáo lý viên</p>
                    </div>
                </div>
            </x-mac-panel>

            <x-mac-panel :overflow="true">
                <div class="px-4 lg:px-6 py-4 mac-hairline-b bg-white/40 flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full bg-primary-600 text-white text-sm font-bold flex items-center justify-center flex-shrink-0">1</span>
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">Tạo và kích hoạt năm học đầu tiên</h2>
                        <p class="text-xs text-slate-500">Màn: <span class="font-medium text-slate-700">Quản lý năm học</span></p>
                    </div>
                </div>
                <div class="p-4 lg:p-6 space-y-4 text-sm text-slate-700">
                    <ol class="list-decimal list-inside space-y-2 leading-relaxed">
                        <li>Vào menu <strong>Hệ thống → Năm học</strong>.</li>
                        <li>Bấm <strong>Thêm năm học</strong>, nhập tên (vd. <code class="px-1.5 py-0.5 bg-slate-100 rounded text-xs">2026 – 2027</code>) và thời gian hai học kỳ nếu có.</li>
                        <li>Bấm <strong>Lưu</strong>.</li>
                        <li>Trong danh sách, mở menu ⋮ của năm vừa tạo → chọn <strong>Kích hoạt năm học</strong> (dashboard chỉ hoạt động khi đã có năm đang kích hoạt).</li>
                    </ol>
                    <x-inline-tip tone="amber">
                        <strong>Lưu ý:</strong> Lần đầu <em>không dùng</em> Sao chép cấu trúc lớp — vì chưa có năm nguồn có lớp để copy.
                    </x-inline-tip>
                    <a href="{{ route('school-years.index') }}"
                        class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold
                               text-white bg-primary-600 hover:bg-primary-700 rounded-xl transition shadow-mac-sm">
                        <x-icon name="plus" />
                        Mở Quản lý năm học
                    </a>
                </div>
            </x-mac-panel>

            <x-mac-panel :overflow="true">
                <div class="px-4 lg:px-6 py-4 mac-hairline-b bg-white/40 flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full bg-primary-600 text-white text-sm font-bold flex items-center justify-center flex-shrink-0">2</span>
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">Tạo lớp học thủ công</h2>
                        <p class="text-xs text-slate-500">Màn: <span class="font-medium text-slate-700">Hệ thống → Lớp học</span></p>
                    </div>
                </div>
                <div class="p-4 lg:p-6 space-y-4 text-sm text-slate-700">
                    <ol class="list-decimal list-inside space-y-2 leading-relaxed">
                        <li>Vào menu <strong>Hệ thống → Lớp học</strong>.</li>
                        <li>Bấm <strong>Thêm lớp học</strong>, chọn năm học vừa tạo, nhập tên lớp (vd. Khai tâm 1, Xưng tội…).</li>
                        <li>Lưu và lặp lại cho các lớp còn lại.</li>
                    </ol>
                    <a href="{{ route('classes.index') }}"
                        class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold
                               text-primary-700 bg-primary-50 hover:bg-primary-100 rounded-xl transition shadow-mac-sm">
                        Mở Quản lý lớp học
                    </a>
                </div>
            </x-mac-panel>

            <x-mac-panel :overflow="true">
                <div class="px-4 lg:px-6 py-4 mac-hairline-b bg-white/40 flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full bg-primary-600 text-white text-sm font-bold flex items-center justify-center flex-shrink-0">3</span>
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">Ghi danh học sinh vào lớp</h2>
                        <p class="text-xs text-slate-500">Màn: <span class="font-medium text-slate-700">Giáo lý → Học sinh</span> → nút <strong>Ghi danh</strong></p>
                    </div>
                </div>
                <div class="p-4 lg:p-6 space-y-4 text-sm text-slate-700">
                    <ol class="list-decimal list-inside space-y-2 leading-relaxed">
                        <li>Vào menu <strong>Giáo lý → Học sinh</strong>.</li>
                        <li>Chọn đúng <strong>năm học</strong> và <strong>lớp</strong> cần ghi danh (nút Ghi danh chỉ bật khi đã chọn lớp).</li>
                        <li>Bấm nút <strong>Ghi danh</strong> — mở modal <em>Ghi danh học sinh</em>.</li>
                    </ol>

                    <div class="space-y-3">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Trong modal — chọn 1 trong 3 tab</p>
                        <div class="rounded-xl border border-black/[0.06] bg-white/60 shadow-mac-sm p-3">
                            <p class="text-xs font-semibold text-primary-700 mb-1">Tab « Học sinh có sẵn »</p>
                            <ul class="text-xs text-slate-600 space-y-1 list-disc list-inside">
                                <li>Dùng khi học sinh đã có trong hệ thống nhưng chưa thuộc lớp này.</li>
                                <li>Tìm theo tên / lọc năm sinh → tick học sinh → bấm <strong>Thêm vào lớp</strong>.</li>
                            </ul>
                        </div>
                        <div class="rounded-xl border border-black/[0.06] bg-white/60 shadow-mac-sm p-3">
                            <p class="text-xs font-semibold text-primary-700 mb-1">Tab « Tạo mới »</p>
                            <p class="text-xs text-slate-600">Chuyển sang form tạo học sinh mới (đã gắn sẵn lớp đang chọn). Điền thông tin rồi lưu.</p>
                        </div>
                        <div class="rounded-xl border border-black/[0.06] bg-white/60 shadow-mac-sm p-3">
                            <p class="text-xs font-semibold text-primary-700 mb-1">Tab « Import giáo dân »</p>
                            <ul class="text-xs text-slate-600 space-y-1 list-disc list-inside">
                                <li>Chọn giáo dân chưa có hồ sơ học sinh → bấm <strong>Import</strong> để tạo hồ sơ và ghi danh vào lớp.</li>
                            </ul>
                        </div>
                    </div>

                    <x-inline-tip tone="slate">
                        Cần ghi danh hàng loạt từ Excel: trên trang Học sinh, mở menu <strong>Khác → Import Excel</strong> (vẫn phải chọn lớp trước).
                    </x-inline-tip>

                    <a href="{{ route('students.index') }}"
                        class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold
                               text-primary-700 bg-primary-50 hover:bg-primary-100 rounded-xl transition shadow-mac-sm">
                        Mở trang Học sinh
                    </a>
                </div>
            </x-mac-panel>

            <x-mac-panel :overflow="true">
                <div class="px-4 lg:px-6 py-4 mac-hairline-b bg-white/40 flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full bg-primary-600 text-white text-sm font-bold flex items-center justify-center flex-shrink-0">4</span>
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">Gán giáo lý viên (tuỳ chọn nhưng nên làm sớm)</h2>
                        <p class="text-xs text-slate-500">Màn: <span class="font-medium text-slate-700">Hệ thống → Giáo lý viên</span> và trang chi tiết lớp</p>
                    </div>
                </div>
                <div class="p-4 lg:p-6 space-y-4 text-sm text-slate-700">
                    <ol class="list-decimal list-inside space-y-2 leading-relaxed">
                        <li>Thêm giáo lý viên tại <strong>Hệ thống → Giáo lý viên</strong> (hoặc import).</li>
                        <li>Vào từng lớp → gán giáo lý viên phụ trách lớp đó.</li>
                    </ol>
                    <p class="text-xs text-slate-500">
                        Sau các bước trên, hệ thống sẵn sàng cho điểm danh, điểm số và các chức năng khác trong năm học.
                    </p>
                    <a href="{{ route('catechists.index') }}"
                        class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold
                               text-primary-700 bg-primary-50 hover:bg-primary-100 rounded-xl transition shadow-mac-sm">
                        Mở Giáo lý viên
                    </a>
                </div>
            </x-mac-panel>

            <div class="flex flex-col sm:flex-row gap-3 pb-2">
                <a href="{{ route('school-years.index') }}"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold
                           text-white bg-primary-600 hover:bg-primary-700 rounded-xl transition shadow-mac-sm">
                    Bắt đầu: Tạo năm học đầu tiên
                </a>
                <button type="button"
                    @click="mode = 'renew'"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold
                           text-slate-700 bg-white/80 hover:bg-white border border-black/[0.06] rounded-xl transition shadow-mac-sm">
                    Đã có năm cũ? Xem mở năm mới →
                </button>
            </div>
        </div>

        {{-- ═══════════════ MỞ NĂM MỚI ═══════════════ --}}
        <div x-show="mode === 'renew'" x-cloak class="space-y-5">

            <x-mac-panel class="p-4 lg:p-6">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-3">Luồng mở năm mới</p>
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-1 text-sm">
                    <div class="flex-1 rounded-xl bg-primary-50 border border-primary-100 px-3 py-2.5 text-center">
                        <p class="text-[11px] text-primary-600 font-medium">Màn 1</p>
                        <p class="font-semibold text-primary-900 text-xs sm:text-sm">Quản lý năm học</p>
                        <p class="text-[11px] text-primary-700 mt-0.5">Tạo năm mới</p>
                    </div>
                    <div class="hidden sm:flex text-slate-300 px-1">→</div>
                    <div class="sm:hidden text-center text-slate-300 text-xs">↓</div>
                    <div class="flex-1 rounded-xl bg-primary-50 border border-primary-100 px-3 py-2.5 text-center">
                        <p class="text-[11px] text-primary-600 font-medium">Màn 2</p>
                        <p class="font-semibold text-primary-900 text-xs sm:text-sm">Sao chép cấu trúc lớp</p>
                        <p class="text-[11px] text-primary-700 mt-0.5">Copy lớp (bước 1–3)</p>
                    </div>
                    <div class="hidden sm:flex text-slate-300 px-1">→</div>
                    <div class="sm:hidden text-center text-slate-300 text-xs">↓</div>
                    <div class="flex-1 rounded-xl bg-primary-50 border border-primary-100 px-3 py-2.5 text-center">
                        <p class="text-[11px] text-primary-600 font-medium">Màn 3</p>
                        <p class="font-semibold text-primary-900 text-xs sm:text-sm">Ghi danh học sinh</p>
                        <p class="text-[11px] text-primary-700 mt-0.5">Trang Học sinh → Ghi danh</p>
                    </div>
                </div>
            </x-mac-panel>

            <x-mac-panel :overflow="true">
                <div class="px-4 lg:px-6 py-4 mac-hairline-b bg-white/40 flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full bg-primary-600 text-white text-sm font-bold flex items-center justify-center flex-shrink-0">1</span>
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">Tạo năm học mới</h2>
                        <p class="text-xs text-slate-500">Màn: <span class="font-medium text-slate-700">Quản lý năm học</span></p>
                    </div>
                </div>
                <div class="p-4 lg:p-6 space-y-4 text-sm text-slate-700">
                    <ol class="list-decimal list-inside space-y-2 leading-relaxed">
                        <li>Vào menu <strong>Hệ thống → Năm học</strong>.</li>
                        <li>Bấm nút <strong>Thêm năm học</strong>.</li>
                        <li>Điền <strong>Tên năm học</strong> (vd. <code class="px-1.5 py-0.5 bg-slate-100 rounded text-xs">2026 – 2027</code>) và thời gian Học kỳ I / II nếu có.</li>
                        <li>Bấm <strong>Lưu</strong>. Hệ thống hiện thông báo thành công và quay về danh sách năm học.</li>
                    </ol>
                    <x-inline-tip tone="amber">
                        <strong>Lưu ý:</strong> Sau khi tạo xong bạn <em>vẫn ở trang danh sách năm học</em> — chưa tự chuyển sang màn sao chép. Tiếp tục bước 2 bên dưới.
                    </x-inline-tip>
                    <a href="{{ route('school-years.index') }}"
                        class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold
                               text-white bg-primary-600 hover:bg-primary-700 rounded-xl transition shadow-mac-sm">
                        <x-icon name="plus" />
                        Mở Quản lý năm học
                    </a>
                </div>
            </x-mac-panel>

            <x-mac-panel :overflow="true">
                <div class="px-4 lg:px-6 py-4 mac-hairline-b bg-white/40 flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full bg-primary-600 text-white text-sm font-bold flex items-center justify-center flex-shrink-0">2</span>
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">Sao chép cấu trúc lớp</h2>
                        <p class="text-xs text-slate-500">Màn: <span class="font-medium text-slate-700">Sao chép cấu trúc lớp</span> (wizard bước 1 → 2 → 3)</p>
                    </div>
                </div>
                <div class="p-4 lg:p-6 space-y-4 text-sm text-slate-700">
                    <p>Từ danh sách năm học, bấm nút <strong>Sao chép cấu trúc lớp</strong> (hoặc menu ⋮ của năm đích → cùng mục).</p>
                    <div class="space-y-3">
                        <div class="rounded-xl border border-black/[0.06] bg-white/60 shadow-mac-sm p-3">
                            <p class="text-xs font-semibold text-primary-700 mb-1">Wizard — Bước 1: Chọn năm</p>
                            <ul class="text-xs text-slate-600 space-y-1 list-disc list-inside">
                                <li><strong>Năm nguồn:</strong> năm cũ đã có lớp (vd. 2025–2026).</li>
                                <li><strong>Năm đích:</strong> năm vừa tạo ở bước 1 (vd. 2026–2027).</li>
                                <li>Tuỳ chọn copy loại điểm nếu cần, rồi sang bước xác nhận.</li>
                            </ul>
                        </div>
                        <div class="rounded-xl border border-black/[0.06] bg-white/60 shadow-mac-sm p-3">
                            <p class="text-xs font-semibold text-primary-700 mb-1">Wizard — Bước 2: Xác nhận</p>
                            <p class="text-xs text-slate-600">Xem trước danh sách lớp sẽ được tạo, rồi bấm xác nhận copy.</p>
                        </div>
                        <div class="rounded-xl border border-black/[0.06] bg-white/60 shadow-mac-sm p-3">
                            <p class="text-xs font-semibold text-primary-700 mb-1">Wizard — Bước 3: Hoàn tất</p>
                            <p class="text-xs text-slate-600">Hệ thống báo đã copy xong. Tiếp tục ghi danh học sinh ở bước sau (trang Học sinh).</p>
                        </div>
                    </div>
                    <a href="{{ route('school-years.copy') }}"
                        class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold
                               text-primary-700 bg-primary-50 hover:bg-primary-100 rounded-xl transition shadow-mac-sm">
                        <x-icon name="copy" />
                        Mở Sao chép cấu trúc lớp
                    </a>
                </div>
            </x-mac-panel>

            <x-mac-panel :overflow="true">
                <div class="px-4 lg:px-6 py-4 mac-hairline-b bg-white/40 flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full bg-primary-600 text-white text-sm font-bold flex items-center justify-center flex-shrink-0">3</span>
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">Ghi danh học sinh vào lớp năm mới</h2>
                        <p class="text-xs text-slate-500">Màn: <span class="font-medium text-slate-700">Giáo lý → Học sinh</span> → nút <strong>Ghi danh</strong></p>
                    </div>
                </div>
                <div class="p-4 lg:p-6 space-y-4 text-sm text-slate-700">
                    <ol class="list-decimal list-inside space-y-2 leading-relaxed">
                        <li>Vào <strong>Giáo lý → Học sinh</strong>, chọn <strong>năm học mới</strong> và từng <strong>lớp đích</strong>.</li>
                        <li>Bấm <strong>Ghi danh</strong> để mở modal.</li>
                        <li>Dùng tab phù hợp (xem bên dưới), rồi lặp lại cho các lớp còn lại.</li>
                    </ol>

                    <div class="space-y-3">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Modal Ghi danh học sinh</p>
                        <div class="rounded-xl border border-black/[0.06] bg-white/60 shadow-mac-sm p-3">
                            <p class="text-xs font-semibold text-primary-700 mb-1">« Học sinh có sẵn »</p>
                            <p class="text-xs text-slate-600">Chọn học sinh đã có (vd. từ năm cũ / chưa xếp lớp) → <strong>Thêm vào lớp</strong>.</p>
                        </div>
                        <div class="rounded-xl border border-black/[0.06] bg-white/60 shadow-mac-sm p-3">
                            <p class="text-xs font-semibold text-primary-700 mb-1">« Tạo mới »</p>
                            <p class="text-xs text-slate-600">Tạo hồ sơ học sinh mới gắn với lớp đang chọn.</p>
                        </div>
                        <div class="rounded-xl border border-black/[0.06] bg-white/60 shadow-mac-sm p-3">
                            <p class="text-xs font-semibold text-primary-700 mb-1">« Import giáo dân »</p>
                            <p class="text-xs text-slate-600">Import giáo dân chưa có hồ sơ học sinh vào lớp.</p>
                        </div>
                    </div>

                    <x-inline-tip tone="amber">
                        <strong>Lưu ý:</strong> Phải chọn lớp trước — nếu chưa chọn, nút Ghi danh bị khóa và hiện gợi ý « Vui lòng chọn lớp trước ».
                    </x-inline-tip>

                    <p class="text-xs text-slate-500">
                        Tuỳ chọn: wizard sao chép còn bước xếp học sinh theo lớp nguồn/đích; hoặc
                        <strong>Khác → Import Excel</strong> trên trang Học sinh để ghi danh hàng loạt.
                    </p>

                    <x-inline-tip tone="slate">
                        Nhớ <strong>Kích hoạt</strong> năm mới (menu ⋮ trên danh sách năm học) khi muốn làm việc trên năm đó.
                    </x-inline-tip>

                    <a href="{{ route('students.index') }}"
                        class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold
                               text-primary-700 bg-primary-50 hover:bg-primary-100 rounded-xl transition shadow-mac-sm">
                        Mở trang Học sinh
                    </a>
                </div>
            </x-mac-panel>

            <div class="flex flex-col sm:flex-row gap-3 pb-2">
                <a href="{{ route('school-years.index') }}"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold
                           text-white bg-primary-600 hover:bg-primary-700 rounded-xl transition shadow-mac-sm">
                    Bắt đầu: Tạo năm học
                </a>
                <a href="{{ route('school-years.copy') }}"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold
                           text-slate-700 bg-white/80 hover:bg-white border border-black/[0.06] rounded-xl transition shadow-mac-sm">
                    <x-icon name="copy" />
                    Đã có năm mới → Sao chép lớp
                </a>
            </div>
        </div>

        {{-- FAQ --}}
        <x-mac-panel :overflow="true">
            <div class="px-4 lg:px-6 py-4 mac-hairline-b bg-white/40">
                <h2 class="text-base font-semibold text-slate-900">Câu hỏi thường gặp</h2>
            </div>
            <div class="p-4 lg:p-6 space-y-4 text-sm">
                <div>
                    <p class="font-semibold text-slate-900">Lần đầu có cần sao chép cấu trúc lớp không?</p>
                    <p class="text-slate-600 mt-1 text-xs leading-relaxed">Không. Chưa có năm nguồn thì tạo lớp thủ công ở <strong>Lớp học</strong>. Chỉ sao chép khi đã có năm cũ có lớp.</p>
                </div>
                <div>
                    <p class="font-semibold text-slate-900">Tạo năm xong có tự sang màn sao chép không?</p>
                    <p class="text-slate-600 mt-1 text-xs leading-relaxed">Không. Bạn ở lại danh sách năm học; hãy bấm <strong>Sao chép cấu trúc lớp</strong> nếu đang mở năm mới từ năm cũ.</p>
                </div>
                <div>
                    <p class="font-semibold text-slate-900">Sao chép có mang học sinh sang không?</p>
                    <p class="text-slate-600 mt-1 text-xs leading-relaxed">Không. Copy lớp chỉ tạo cấu trúc lớp (và loại điểm nếu chọn). Học sinh ghi danh riêng bằng nút <strong>Ghi danh</strong> trên trang Học sinh.</p>
                </div>
                <div>
                    <p class="font-semibold text-slate-900">Nút Ghi danh bị khóa?</p>
                    <p class="text-slate-600 mt-1 text-xs leading-relaxed">Cần chọn cụ thể một <strong>lớp</strong> (không chỉ năm học / khối). Sau đó nút Ghi danh mới bật và mở modal.</p>
                </div>
                <div>
                    <p class="font-semibold text-slate-900">Tại sao dashboard báo chưa có năm học kích hoạt?</p>
                    <p class="text-slate-600 mt-1 text-xs leading-relaxed">Cần tạo năm học rồi chọn <strong>Kích hoạt năm học</strong> trong menu ⋮. Chỉ năm đang kích hoạt mới dùng cho điểm danh / điểm số.</p>
                </div>
            </div>
        </x-mac-panel>

    </div>
</div>
