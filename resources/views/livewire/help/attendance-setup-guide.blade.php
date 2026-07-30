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
                description="Giải thích toàn bộ cơ chế: tạo buổi, đánh dấu, giờ chốt, khóa/hủy, xuất Excel và ảnh hưởng tới điểm chuyên cần."
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
                    <strong>Phiên điểm danh</strong> = tạo / khóa / hủy / mở lại các <em>buổi</em>, và cài giờ chốt.
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
                    <p class="font-semibold text-primary-900 text-xs sm:text-sm">Giờ chốt &amp; khóa</p>
                </div>
            </div>
        </x-mac-panel>

        {{-- Bước 1 --}}
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
                    Học sinh ghi danh <em>sau</em> ngày buổi học sẽ không bị suy luận vắng cho các buổi trước ngày xếp lớp.
                </x-inline-tip>
            </div>
        </x-mac-panel>

        {{-- Bước 2 --}}
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
                    <li>Chọn <strong>năm học → khối → lớp</strong> (cùng lớp sẽ điểm sau). Có thể tạo theo khối hoặc toàn xứ tùy quyền.</li>
                    <li>Bấm <strong>Tạo phiên mới</strong>.</li>
                    <li>Chọn chế độ:
                        <ul class="mt-2 ml-5 list-disc space-y-1 text-slate-600">
                            <li><strong>Theo ngày</strong> — một buổi cụ thể</li>
                            <li><strong>Theo tuần</strong> — nhiều buổi theo lịch tuần trong khoảng ngày</li>
                            <li><strong>Tùy chọn</strong> — chọn từng ngày</li>
                        </ul>
                    </li>
                    <li>Chọn loại buổi (<strong>Đi học</strong> / <strong>Đi lễ</strong>), rồi lưu.</li>
                </ol>

                <div class="rounded-xl bg-slate-50/80 border border-black/[0.04] p-3 space-y-2">
                    <p class="font-semibold text-slate-800">Trạng thái buổi</p>
                    <ul class="list-disc ml-5 space-y-1 text-slate-600">
                        <li><strong>Đang mở</strong> — được điểm danh / quét QR / sửa.</li>
                        <li><strong>Đã khóa</strong> — chốt sớm: không sửa/quét thêm; muốn sửa thì <strong>Mở lại</strong>.</li>
                        <li><strong>Đã hủy</strong> — lớp nghỉ: không tính vào báo cáo vắng và <strong>không vào điểm chuyên cần</strong>.</li>
                    </ul>
                </div>

                <x-inline-tip tone="amber">
                    Giáo lý viên thuần thường <strong>không</strong> tạo phiên — chỉ quản trị,
                    hoặc GLV được cấp quyền hỗ trợ <strong>Tạo phiên điểm danh</strong>
                    (cấp khi sửa GLV → Quyền hỗ trợ quản trị) — gồm cả phiên học sinh và buổi GLV.
                    GLV không có quyền này vào thẳng trang Điểm danh sau khi đã có buổi.
                </x-inline-tip>
            </div>
        </x-mac-panel>

        {{-- Bước 3 --}}
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
                    <li>Đánh dấu từng học sinh. Có thể dùng «✓ Tất cả» trên cột buổi.</li>
                    <li>Lưu thay đổi (nút lưu / thanh lưu trên mobile).</li>
                </ol>

                <div class="rounded-xl bg-slate-50/80 border border-black/[0.04] p-3 space-y-2">
                    <p class="font-semibold text-slate-800">Ký hiệu trên lưới</p>
                    <ul class="list-disc ml-5 space-y-1 text-slate-600">
                        <li><strong>✓</strong> — có mặt</li>
                        <li><strong>P</strong> — vắng có phép (có thể kèm lý do)</li>
                        <li><strong>✕</strong> — vắng không phép (đã ghi tay)</li>
                        <li><strong>KP</strong> — vắng không phép <em>được suy luận</em> sau giờ chốt / khi khóa (chưa ghi vào DB; vẫn sửa được nếu buổi chưa khóa)</li>
                        <li>Ô trống / «?» — chưa điểm danh và buổi <em>chưa tới giờ chốt</em>, chưa khóa</li>
                    </ul>
                </div>

                <x-inline-tip>
                    Nếu thấy tip «chưa có buổi» — quay lại bước 2 tạo phiên, rồi chọn lại lớp trên trang này.
                    Quét QR toàn xứ cũng ghi cùng loại trạng thái; không cần nút «Hoàn thành» trên trang QR.
                </x-inline-tip>
            </div>
        </x-mac-panel>

        {{-- Bước 4 --}}
        <x-mac-panel :overflow="true">
            <div class="px-4 lg:px-6 py-4 mac-hairline-b bg-white/40 flex items-center gap-3">
                <span class="w-8 h-8 rounded-full bg-primary-600 text-white text-sm font-bold flex items-center justify-center flex-shrink-0">4</span>
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Giờ chốt &amp; khóa phiên</h2>
                    <p class="text-xs text-slate-500">Cách hệ thống kết luận «chưa điểm danh» thành KP</p>
                </div>
            </div>
            <div class="p-4 lg:p-6 space-y-4 text-sm text-slate-700 leading-relaxed">
                <div class="rounded-xl bg-slate-50/80 border border-black/[0.04] p-3 space-y-2">
                    <p class="font-semibold text-slate-800">Ý tưởng chính</p>
                    <ul class="list-disc ml-5 space-y-1 text-slate-600">
                        <li>Hệ thống <strong>không tự ghi</strong> bản ghi vắng vào database khi tới giờ chốt.</li>
                        <li>Mỗi lần xem lưới, xuất Excel, thống kê hay tính điểm chuyên cần, hệ thống <strong>đọc lại</strong> và suy luận trạng thái hiệu lực.</li>
                        <li>Điểm danh bù sau giờ chốt (buổi còn mở) thì số liệu tự đúng lại — không cần xóa gì thêm.</li>
                    </ul>
                </div>

                <p class="font-semibold text-slate-800">Điều kiện suy luận KP</p>
                <p>Một em chưa có bản ghi sẽ được tính <strong>vắng không phép (KP)</strong> khi <strong>cả hai</strong> điều kiện sau đúng:</p>
                <ol class="list-decimal list-inside space-y-2">
                    <li>Buổi đó đã có <strong>ít nhất một học sinh</strong> được điểm danh (có mặt / CP / KP thật).</li>
                    <li>Buổi đã <strong>khóa</strong>, <strong>hoặc</strong> đã qua <strong>giờ chốt của ngày buổi đó</strong> (mặc định 20:00).</li>
                </ol>

                <div class="overflow-x-auto rounded-xl border border-black/[0.06]">
                    <table class="min-w-full text-xs sm:text-sm">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th class="px-3 py-2 text-left font-semibold">Tình huống</th>
                                <th class="px-3 py-2 text-left font-semibold">Ô chưa điểm danh</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/[0.04] text-slate-700">
                            <tr>
                                <td class="px-3 py-2">Chưa khóa, chưa tới giờ chốt ngày buổi đó</td>
                                <td class="px-3 py-2">Chưa điểm danh (ô trống / ?)</td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2">Đã qua giờ chốt ngày buổi đó, và đã có ít nhất 1 em được điểm</td>
                                <td class="px-3 py-2 font-semibold text-red-700">KP (suy luận)</td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2">Buổi đã khóa, và đã có ít nhất 1 em được điểm</td>
                                <td class="px-3 py-2 font-semibold text-red-700">KP (suy luận)</td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2">Cả lớp chưa ai được điểm danh</td>
                                <td class="px-3 py-2">Không suy KP — tránh trừ oan cả lớp</td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2">Buổi đã hủy</td>
                                <td class="px-3 py-2">Không tính báo cáo / chuyên cần</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <p class="font-semibold text-slate-800">Cài giờ chốt</p>
                <ol class="list-decimal list-inside space-y-2">
                    <li>Ban quản trị vào
                        <a href="{{ route('session.index', ['tab' => 'settings']) }}" class="font-semibold text-primary-700 underline">Phiên điểm danh</a>
                        → tab <strong>Cài đặt điểm danh</strong>.
                    </li>
                    <li>Chọn <strong>giờ chốt số liệu</strong> (mặc định <strong>20:00</strong>). Giờ này luôn áp dụng khi đọc số liệu — không còn công tắc bật/tắt.</li>
                    <li>Nên đặt muộn hơn buổi cuối trong ngày của giáo xứ.</li>
                </ol>

                <p class="font-semibold text-slate-800">Khóa tay (tuỳ chọn)</p>
                <ol class="list-decimal list-inside space-y-2">
                    <li>Vào <strong>Phiên điểm danh</strong>.</li>
                    <li>Với buổi đã điểm xong, bấm <strong>Khóa</strong> để kết luận số liệu ngay, không cần đợi giờ chốt.</li>
                    <li>Muốn sửa lại: bấm <strong>Mở lại</strong>, rồi chỉnh trên trang Điểm danh và lưu.</li>
                </ol>

                <x-inline-tip tone="amber">
                    <p class="font-semibold text-sm mb-1">Cảnh báo vận hành</p>
                    <p class="text-amber-800/90">
                        Nếu buổi đã khóa hoặc đã qua giờ chốt mà <strong>chưa có ai được điểm danh</strong>,
                        trang Điểm danh sẽ hiện tip màu amber. Hệ thống không tự tính cả lớp là KP.
                        Hãy điểm danh ít nhất một em, hoặc <strong>hủy buổi</strong> nếu lớp nghỉ.
                    </p>
                </x-inline-tip>

                <x-inline-tip>
                    Quét QR toàn xứ: cứ quét bình thường trong buổi. Đến giờ chốt, các lớp đã có người quét sẽ coi phần còn lại là KP —
                    không cần nút «Hoàn thành» trên trang QR. Nhật ký điểm danh không ghi dòng «chốt tự động» vì hệ thống không sửa dữ liệu của em nào.
                </x-inline-tip>
            </div>
        </x-mac-panel>

        {{-- Xuất Excel --}}
        <x-mac-panel :overflow="true">
            <div class="px-4 lg:px-6 py-4 mac-hairline-b bg-white/40 flex items-center gap-3">
                <span class="w-8 h-8 rounded-full bg-slate-700 text-white text-sm font-bold flex items-center justify-center flex-shrink-0">5</span>
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Xuất Excel &amp; ảnh hưởng điểm chuyên cần</h2>
                    <p class="text-xs text-slate-500">Cùng một quy tắc trạng thái hiệu lực</p>
                </div>
            </div>
            <div class="p-4 lg:p-6 space-y-3 text-sm text-slate-700 leading-relaxed">
                <p class="font-semibold text-slate-800">Xuất từ trang Điểm danh</p>
                <ul class="list-disc ml-5 space-y-1 text-slate-600">
                    <li><strong>Xuất bảng điểm danh</strong> — đủ học sinh và các buổi trong khoảng lọc; dùng trạng thái hiệu lực (có KP suy luận).</li>
                    <li><strong>Xuất học sinh vắng</strong> — chỉ liệt kê học sinh có CP/KP trong khoảng ngày; chỉ giữ các buổi có ít nhất một CP/KP.
                        Buổi chưa tới giờ chốt (chỉ còn ô trống) không đưa vào danh sách vắng.</li>
                </ul>

                <p class="font-semibold text-slate-800 mt-3">Liên hệ điểm chuyên cần</p>
                <ul class="list-disc ml-5 space-y-1 text-slate-600">
                    <li>Điểm chuyên cần học / lễ lấy từ cùng trạng thái hiệu lực trên.</li>
                    <li>Buổi <strong>hủy</strong> và buổi <strong>chưa ai điểm danh</strong> không vào mẫu số.</li>
                    <li>KP suy luận <strong>có</strong> vào mẫu số và không được cộng điểm buổi đó.</li>
                    <li>Chi tiết công thức xem
                        <a href="{{ route('help.scores') }}" class="font-semibold text-primary-700 underline">Hướng dẫn nhập điểm</a>.
                    </li>
                </ul>
            </div>
        </x-mac-panel>

        {{-- FAQ --}}
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
                    <p class="font-semibold text-slate-900">Giờ chốt 20:00 nghĩa là gì?</p>
                    <p class="mt-1 text-slate-600 leading-relaxed">
                        Từ giờ đã cấu hình của <em>ngày buổi đó</em> (mặc định 20:00), với buổi đã có ít nhất một người điểm danh,
                        những em chưa được điểm danh sẽ tính là KP trên lưới, Excel và điểm chuyên cần.
                        Hệ thống không tự ghi vắng vào từng em và không tự khóa buổi.
                        Buổi chưa ai điểm danh thì bỏ qua. Đổi giờ tại
                        <a href="{{ route('session.index', ['tab' => 'settings']) }}" class="font-semibold text-primary-700 underline">Phiên điểm danh → Cài đặt điểm danh</a>.
                    </p>
                </div>
                <div>
                    <p class="font-semibold text-slate-900">Ô hiện «KP» nhưng chưa bấm lưu — có ghi DB không?</p>
                    <p class="mt-1 text-slate-600 leading-relaxed">
                        Không. Đó là trạng thái suy luận khi đọc. Chỉ khi bạn chọn trạng thái khác (✓ / P / ✕) và bấm Lưu
                        thì hệ thống mới tạo hoặc cập nhật bản ghi thật.
                    </p>
                </div>
                <div>
                    <p class="font-semibold text-slate-900">Buổi cũ còn mở, nay đã qua nhiều ngày — có bị KP không?</p>
                    <p class="mt-1 text-slate-600 leading-relaxed">
                        Có, nếu buổi đó đã có người điểm danh và đã qua giờ chốt của <em>ngày buổi đó</em>.
                        Giờ chốt không chỉ áp dụng cho hôm nay.
                    </p>
                </div>
                <div>
                    <p class="font-semibold text-slate-900">Điểm đi học và đi lễ khác nhau thế nào?</p>
                    <p class="mt-1 text-slate-600 leading-relaxed">
                        Là hai loại buổi / tab riêng. Tạo phiên đúng loại, rồi chọn đúng tab trên trang Điểm danh.
                        Điểm chuyên cần học và chuyên cần lễ cũng tính riêng từ hai loại này.
                    </p>
                </div>
                <div>
                    <p class="font-semibold text-slate-900">Lớp nghỉ thì làm gì?</p>
                    <p class="mt-1 text-slate-600 leading-relaxed">
                        Vào Phiên điểm danh và <strong>hủy buổi</strong>. Buổi hủy không vào mẫu số chuyên cần và không xuất trong danh sách vắng.
                        Đừng để buổi trống không điểm danh nếu lớp thực sự nghỉ.
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
            <a href="{{ route('help.scores') }}"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-white/80 hover:bg-white
                       text-slate-700 text-sm font-semibold rounded-xl border border-black/[0.06]
                       transition shadow-mac-sm">
                Hướng dẫn nhập điểm →
            </a>
        </div>

    </div>
</div>
