@section('topbar')
<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => route('parish-admin.dashboard')],
    ['label' => 'Trợ giúp'],
    ['label' => 'Hướng dẫn điểm danh'],
]" />
@endsection

<div class="min-h-screen bg-apple-gray p-2 sm:p-4 lg:p-6"
    style="min-height: calc(100vh - 56px - var(--bottom-offset));">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="mx-auto max-w-3xl space-y-5">

        <x-mac-panel :overflow="true">
            <x-page-header
                title="Hướng dẫn điểm danh"
                description="Chuẩn bị phiên (buổi), rồi điểm danh đi học / đi lễ trên trang Điểm danh. Làm lần lượt các bước bên dưới."
                icon-type="attendance">
                <x-slot name="actions">
                    <a href="{{ route('attendance.show') }}"
                        class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold
                               text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        ← Sang Điểm danh
                    </a>
                </x-slot>
            </x-page-header>
        </x-mac-panel>

        <x-mac-panel class="p-4 lg:p-6 space-y-3">
            <x-inline-tip tone="amber">
                <p class="font-semibold text-sm mb-1">Hai màn hình khác nhau</p>
                <p class="text-amber-800/90">
                    <strong>Phiên điểm danh</strong> = tạo / khóa / xóa các <em>buổi</em>.
                    <strong>Điểm danh</strong> = đánh dấu có mặt / vắng cho từng học sinh theo buổi đã tạo.
                    Chưa có buổi thì trang Điểm danh sẽ trống — đó là bình thường.
                </p>
            </x-inline-tip>
            <x-inline-tip>
                Cần sẵn: năm học đang dùng, lớp học, và học sinh đã được <strong>ghi danh</strong> vào lớp.
            </x-inline-tip>
            <x-inline-tip tone="amber">
                <p class="font-semibold text-sm mb-1">Ai điểm danh được?</p>
                <p class="text-amber-800/90">
                    <strong>Ban quản trị</strong>: mọi lớp, mọi năm học.
                    <strong>GLV đã được phân công</strong> vào ít nhất một lớp trong năm học hiện tại: điểm danh được
                    <em>mọi lớp</em> trong xứ (hỗ trợ điểm danh chéo lớp).
                    <strong>GLV chưa được phân công</strong> trong năm học hiện tại (kể cả tài khoản từ năm cũ):
                    đăng nhập được nhưng <em>không thao tác gì</em> — trang sẽ hiện thông báo «Bạn chưa được phân công lớp trong năm học này».
                </p>
            </x-inline-tip>
        </x-mac-panel>

        <x-mac-panel class="p-4 lg:p-6">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-3">Luồng tổng quát</p>
            <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-1 text-sm">
                <div class="flex-1 rounded-xl bg-primary-50 border border-primary-100 px-3 py-2.5 text-center">
                    <p class="text-[11px] text-primary-600 font-medium">Bước 1</p>
                    <p class="font-semibold text-primary-900 text-xs sm:text-sm">Chuẩn bị lớp &amp; HS</p>
                </div>
                <div class="hidden sm:flex text-slate-300 px-1">→</div>
                <div class="sm:hidden text-center text-slate-300 text-xs">↓</div>
                <div class="flex-1 rounded-xl bg-primary-50 border border-primary-100 px-3 py-2.5 text-center">
                    <p class="text-[11px] text-primary-600 font-medium">Bước 2</p>
                    <p class="font-semibold text-primary-900 text-xs sm:text-sm">Tạo phiên / buổi</p>
                </div>
                <div class="hidden sm:flex text-slate-300 px-1">→</div>
                <div class="sm:hidden text-center text-slate-300 text-xs">↓</div>
                <div class="flex-1 rounded-xl bg-primary-50 border border-primary-100 px-3 py-2.5 text-center">
                    <p class="text-[11px] text-primary-600 font-medium">Bước 3</p>
                    <p class="font-semibold text-primary-900 text-xs sm:text-sm">Điểm danh</p>
                </div>
                <div class="hidden sm:flex text-slate-300 px-1">→</div>
                <div class="sm:hidden text-center text-slate-300 text-xs">↓</div>
                <div class="flex-1 rounded-xl bg-primary-50 border border-primary-100 px-3 py-2.5 text-center">
                    <p class="text-[11px] text-primary-600 font-medium">Bước 4</p>
                    <p class="font-semibold text-primary-900 text-xs sm:text-sm">Khóa phiên (tuỳ chọn)</p>
                </div>
            </div>
        </x-mac-panel>

        <x-mac-panel :overflow="true">
            <div class="px-4 lg:px-6 py-4 mac-hairline-b bg-white/40 flex items-center gap-3">
                <span class="w-8 h-8 rounded-full bg-primary-600 text-white text-sm font-bold flex items-center justify-center flex-shrink-0">1</span>
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Chuẩn bị lớp và học sinh</h2>
                    <p class="text-xs text-slate-500">Điều kiện trước khi tạo buổi</p>
                </div>
            </div>
            <div class="p-4 lg:p-6 space-y-3 text-sm text-slate-700 leading-relaxed">
                <ol class="list-decimal list-inside space-y-2">
                    <li>Có <strong>năm học</strong> đang hoạt động (xem
                        <a href="{{ route('school-years.index') }}" class="font-semibold text-primary-700 underline">Quản lý năm học</a>).
                    </li>
                    <li>Có <strong>lớp</strong> trong năm đó (Hệ thống → Lớp học).</li>
                    <li>Học sinh đã được <strong>ghi danh</strong> vào lớp (
                        <a href="{{ route('students.index') }}" class="font-semibold text-primary-700 underline">Học sinh</a>).
                    </li>
                </ol>
                <x-inline-tip>
                    Lớp chưa có học sinh thì vẫn tạo được phiên, nhưng trang Điểm danh sẽ báo «Lớp chưa có học sinh».
                </x-inline-tip>
            </div>
        </x-mac-panel>

        <x-mac-panel :overflow="true">
            <div class="px-4 lg:px-6 py-4 mac-hairline-b bg-white/40 flex items-center gap-3">
                <span class="w-8 h-8 rounded-full bg-primary-600 text-white text-sm font-bold flex items-center justify-center flex-shrink-0">2</span>
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Tạo phiên (buổi) điểm danh</h2>
                    <p class="text-xs text-slate-500">Màn: <span class="font-medium text-slate-700">Phiên điểm danh</span> · Hệ thống</p>
                </div>
            </div>
            <div class="p-4 lg:p-6 space-y-3 text-sm text-slate-700 leading-relaxed">
                <ol class="list-decimal list-inside space-y-2">
                    <li>Mở
                        <a href="{{ route('session.index') }}" class="font-semibold text-primary-700 underline">Phiên điểm danh</a>.
                    </li>
                    <li>Chọn <strong>năm học → khối → lớp</strong> (cùng lớp sẽ điểm sau).</li>
                    <li>Bấm <strong>Tạo phiên mới</strong>.</li>
                    <li>Chọn chế độ:
                        <ul class="mt-2 ml-5 list-disc space-y-1 text-slate-600">
                            <li><strong>Theo ngày</strong> — một buổi cụ thể</li>
                            <li><strong>Theo tuần</strong> — nhiều buổi theo lịch tuần trong khoảng ngày</li>
                            <li><strong>Tùy chọn</strong> — chọn từng ngày</li>
                        </ul>
                    </li>
                    <li>Chọn loại buổi (đi học / đi lễ nếu hệ thống hỗ trợ), giờ bắt đầu–kết thúc nếu cần, rồi lưu.</li>
                </ol>
                <x-inline-tip tone="amber">
                    Giáo lý viên thuần thường <strong>không</strong> tạo phiên — chỉ quản trị viên / người có quyền quản lý.
                    GLV vào thẳng trang Điểm danh sau khi đã có buổi.
                </x-inline-tip>
            </div>
        </x-mac-panel>

        <x-mac-panel :overflow="true">
            <div class="px-4 lg:px-6 py-4 mac-hairline-b bg-white/40 flex items-center gap-3">
                <span class="w-8 h-8 rounded-full bg-primary-600 text-white text-sm font-bold flex items-center justify-center flex-shrink-0">3</span>
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Điểm danh trên trang Điểm danh</h2>
                    <p class="text-xs text-slate-500">Màn: <span class="font-medium text-slate-700">Điểm danh</span> · Giáo lý</p>
                </div>
            </div>
            <div class="p-4 lg:p-6 space-y-3 text-sm text-slate-700 leading-relaxed">
                <ol class="list-decimal list-inside space-y-2">
                    <li>Mở
                        <a href="{{ route('attendance.show') }}" class="font-semibold text-primary-700 underline">Điểm danh</a>.
                    </li>
                    <li>Chọn cùng <strong>năm học → khối → lớp</strong> (và học kỳ nếu có).</li>
                    <li>Chuyển tab <strong>Đi học</strong> / <strong>Đi lễ</strong> nếu cần.</li>
                    <li>Đánh dấu từng học sinh (có mặt, vắng có phép, vắng không phép…). Có thể dùng «✓ Tất cả» trên cột buổi.</li>
                    <li>Lưu thay đổi (nút lưu / thanh lưu trên mobile).</li>
                </ol>
                <x-inline-tip>
                    Nếu thấy tip «chưa có buổi» — quay lại bước 2 tạo phiên, rồi chọn lại lớp trên trang này.
                </x-inline-tip>
            </div>
        </x-mac-panel>

        <x-mac-panel :overflow="true">
            <div class="px-4 lg:px-6 py-4 mac-hairline-b bg-white/40 flex items-center gap-3">
                <span class="w-8 h-8 rounded-full bg-primary-600 text-white text-sm font-bold flex items-center justify-center flex-shrink-0">4</span>
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Khóa phiên (tuỳ chọn)</h2>
                    <p class="text-xs text-slate-500">Tránh sửa nhầm sau khi điểm xong</p>
                </div>
            </div>
            <div class="p-4 lg:p-6 space-y-3 text-sm text-slate-700 leading-relaxed">
                <ol class="list-decimal list-inside space-y-2">
                    <li>Vào lại <strong>Phiên điểm danh</strong>.</li>
                    <li>Với buổi đã điểm xong, bấm khóa phiên.</li>
                    <li>Buổi đã khóa không cho sửa điểm trên trang Điểm danh (trừ khi mở lại).</li>
                </ol>
                <p class="text-slate-600">
                    Nhật ký sửa điểm danh nằm ở <strong>Hệ thống → Nhật ký điểm danh</strong> (nếu bạn có quyền xem).
                </p>
            </div>
        </x-mac-panel>

        <x-mac-panel class="p-4 lg:p-6 space-y-4">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Câu hỏi thường gặp</p>
            <div class="space-y-4 text-sm text-slate-700">
                <div>
                    <p class="font-semibold text-slate-900">Trang Điểm danh trống dù đã chọn lớp?</p>
                    <p class="mt-1 text-slate-600 leading-relaxed">
                        Thường là chưa tạo buổi. Kiểm tra tip màu amber và mở Phiên điểm danh để tạo.
                    </p>
                </div>
                <div>
                    <p class="font-semibold text-slate-900">Không thấy nút Tạo phiên mới?</p>
                    <p class="mt-1 text-slate-600 leading-relaxed">
                        Cần chọn năm học trước; nút bị tắt nếu chưa chọn năm. Tài khoản GLV thuần có thể không có quyền tạo.
                    </p>
                </div>
                <div>
                    <p class="font-semibold text-slate-900">GLV báo «Bạn chưa được phân công lớp trong năm học này»?</p>
                    <p class="mt-1 text-slate-600 leading-relaxed">
                        GLV đó chưa có phân công lớp trong năm học đang vận hành (thường gặp với tài khoản từ năm học cũ).
                        Ban quản trị vào <strong>Lớp giáo lý → Phân công GLV</strong> (icon GLV ở cột Thao tác) để gán GLV
                        vào lớp của năm hiện tại; sau đó GLV tải lại trang là điểm danh được (mọi lớp trong xứ).
                    </p>
                </div>
                <div>
                    <p class="font-semibold text-slate-900">Điểm đi học và đi lễ khác nhau thế nào?</p>
                    <p class="mt-1 text-slate-600 leading-relaxed">
                        Là hai loại buổi / tab riêng. Tạo phiên đúng loại, rồi chọn đúng tab trên trang Điểm danh.
                    </p>
                </div>
            </div>
        </x-mac-panel>

        <div class="flex flex-wrap gap-3 justify-center pb-4">
            <a href="{{ route('session.index') }}"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-500 hover:bg-primary-600
                       text-white text-sm font-semibold rounded-xl transition shadow-mac-sm">
                Mở Phiên điểm danh
            </a>
            <a href="{{ route('attendance.show') }}"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-white/80 hover:bg-white
                       text-slate-700 text-sm font-semibold rounded-xl border border-black/[0.06]
                       transition shadow-mac-sm">
                Sang Điểm danh
            </a>
        </div>

    </div>
</div>
