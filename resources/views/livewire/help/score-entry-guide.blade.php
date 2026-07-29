@section('topbar')
<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => route('parish-admin.dashboard')],
    ['label' => 'Trợ giúp'],
    ['label' => 'Hướng dẫn nhập điểm'],
]" />
@endsection

<div class="min-h-screen bg-apple-gray p-2 sm:p-4 lg:p-6"
    style="min-height: calc(100vh - 56px - var(--bottom-offset));">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="mx-auto max-w-3xl space-y-5">

        <x-mac-panel :overflow="true">
            <x-page-header
                title="Hướng dẫn nhập điểm"
                description="Cấu hình loại điểm, cấp quyền nhập cho GLV hỗ trợ, mở cửa sổ nhập, rồi nhập điểm theo lớp và học kỳ trên trang Kết quả học tập."
                icon-type="score">
                <x-slot name="actions">
                    <a href="{{ route('scores.index') }}"
                        class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold
                               text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        ← Sang Kết quả học tập
                    </a>
                </x-slot>
            </x-page-header>
        </x-mac-panel>

        <x-mac-panel class="p-4 lg:p-6 space-y-3">
            <x-inline-tip tone="amber">
                <p class="font-semibold text-sm mb-1">Ai được nhập điểm?</p>
                <p class="text-amber-800/90">
                    <strong>Ban quản trị</strong> (parish_admin / catechism_admin): cấu hình loại điểm, mở/khóa cửa sổ nhập, luôn được sửa điểm.
                    <strong>GLV thuần</strong>: chỉ <em>xem</em> điểm lớp được phân công — không nhập/sửa.
                    <strong>GLV có «Quản lý điểm toàn giáo xứ»</strong> (quyền hỗ trợ quản trị): nhập/sửa điểm <em>mọi lớp</em> trong xứ khi cửa sổ đang mở; khi khóa thì chỉ xem như GLV thuần.
                    Điều kiện chung: GLV (kể cả có quyền hỗ trợ) phải <strong>đang được phân công</strong> vào ít nhất một lớp
                    trong năm học hiện tại — chưa phân công thì không xem/nhập được gì.
                </p>
            </x-inline-tip>
            <x-inline-tip>
                Cần sẵn: năm học đang dùng, lớp có học sinh đã ghi danh, ít nhất một <strong>loại điểm</strong> đang bật,
                và (nếu muốn GLV nhập giúp) đã cấp quyền «Quản lý điểm toàn giáo xứ» trên hồ sơ GLV.
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
                    <p class="font-semibold text-primary-900 text-xs sm:text-sm">Cấu hình loại điểm</p>
                </div>
                <div class="hidden sm:flex text-slate-300 px-1">→</div>
                <div class="sm:hidden text-center text-slate-300 text-xs">↓</div>
                <div class="flex-1 rounded-xl bg-primary-50 border border-primary-100 px-3 py-2.5 text-center">
                    <p class="text-[11px] text-primary-600 font-medium">Bước 3</p>
                    <p class="font-semibold text-primary-900 text-xs sm:text-sm">Mở cửa sổ nhập</p>
                </div>
                <div class="hidden sm:flex text-slate-300 px-1">→</div>
                <div class="sm:hidden text-center text-slate-300 text-xs">↓</div>
                <div class="flex-1 rounded-xl bg-primary-50 border border-primary-100 px-3 py-2.5 text-center">
                    <p class="text-[11px] text-primary-600 font-medium">Bước 4</p>
                    <p class="font-semibold text-primary-900 text-xs sm:text-sm">Nhập &amp; lưu điểm</p>
                </div>
            </div>
        </x-mac-panel>

        <x-mac-panel :overflow="true">
            <div class="px-4 lg:px-6 py-4 mac-hairline-b bg-white/40 flex items-center gap-3">
                <span class="w-8 h-8 rounded-full bg-primary-600 text-white text-sm font-bold flex items-center justify-center flex-shrink-0">1</span>
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Chuẩn bị lớp và học sinh</h2>
                    <p class="text-xs text-slate-500">Điều kiện trước khi nhập điểm</p>
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
                    Chưa chọn lớp trên trang Kết quả học tập thì bảng điểm trống — chọn năm học → khối → lớp → học kỳ ở bộ lọc.
                </x-inline-tip>
            </div>
        </x-mac-panel>

        <x-mac-panel :overflow="true">
            <div class="px-4 lg:px-6 py-4 mac-hairline-b bg-white/40 flex items-center gap-3">
                <span class="w-8 h-8 rounded-full bg-primary-600 text-white text-sm font-bold flex items-center justify-center flex-shrink-0">2</span>
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Cấu hình loại điểm (cột điểm)</h2>
                    <p class="text-xs text-slate-500">Màn: <span class="font-medium text-slate-700">Kết quả học tập</span> · tab Cấu hình loại điểm</p>
                </div>
            </div>
            <div class="p-4 lg:p-6 space-y-3 text-sm text-slate-700 leading-relaxed">
                <ol class="list-decimal list-inside space-y-2">
                    <li>Mở
                        <a href="{{ route('scores.index') }}" class="font-semibold text-primary-700 underline">Kết quả học tập</a>
                        (chỉ quản trị viên thấy tab cấu hình).
                    </li>
                    <li>Chọn đúng <strong>năm học → khối → lớp → học kỳ</strong>.</li>
                    <li>Chuyển tab <strong>Cấu hình loại điểm</strong>.</li>
                    <li>Bấm <strong>Thêm loại điểm</strong>. Điền tên, hệ số, điểm tối đa, thứ tự cột.</li>
                    <li>Chọn phạm vi tạo nếu cần:
                        <ul class="mt-2 ml-5 list-disc space-y-1 text-slate-600">
                            <li><strong>Theo lớp</strong> — chỉ lớp đang chọn</li>
                            <li><strong>Theo khối</strong> — áp dụng các lớp cùng khối</li>
                            <li><strong>Theo giáo xứ</strong> — áp dụng rộng hơn trong xứ</li>
                        </ul>
                    </li>
                    <li>Lưu. Có thể tắt/bật từng loại điểm: cột đã tắt không hiện trên bảng điểm và không tính vào điểm trung bình học tập.</li>
                </ol>
                <x-inline-tip tone="amber">
                    Điểm trung bình học tập của một kỳ tính theo <strong>trung bình có trọng số</strong> (hệ số từng loại điểm).
                    Đặt hệ số đúng trước khi GLV nhập nhiều sẽ tránh phải sửa lại.
                </x-inline-tip>
            </div>
        </x-mac-panel>

        <x-mac-panel :overflow="true">
            <div class="px-4 lg:px-6 py-4 mac-hairline-b bg-white/40 flex items-center gap-3">
                <span class="w-8 h-8 rounded-full bg-primary-600 text-white text-sm font-bold flex items-center justify-center flex-shrink-0">3</span>
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Cách tính điểm trung bình</h2>
                    <p class="text-xs text-slate-500">Màn: <span class="font-medium text-slate-700">Kết quả học tập</span> · tab Cách tính điểm</p>
                </div>
            </div>
            <div class="p-4 lg:p-6 space-y-3 text-sm text-slate-700 leading-relaxed">
                <p>Giáo xứ tự chọn tỉ lệ ba thành phần của điểm trung bình học kỳ:</p>
                <div class="rounded-xl bg-slate-50/80 p-4 text-sm text-slate-700 space-y-2">
                    <p><strong>TB học kỳ</strong> = trung bình học tập × %HT + chuyên cần học × %CC học + chuyên cần lễ × %CC lễ</p>
                    <p><strong>TB cả năm</strong> = TB học kỳ 1 × %HK1 + TB học kỳ 2 × %HK2</p>
                    <p><strong>Điểm chuyên cần</strong> = 10 × (số buổi có mặt + tỉ lệ quy đổi × số buổi vắng có phép) / số buổi đã điểm danh</p>
                </div>
                <ol class="list-decimal list-inside space-y-2">
                    <li>Chọn <strong>năm học</strong> rồi mở tab <strong>Cách tính điểm</strong>.</li>
                    <li>Chọn phạm vi: <strong>toàn giáo xứ</strong> trong năm học đó, hoặc <strong>riêng một khối</strong> (ghi đè cấu hình toàn xứ).</li>
                    <li>Nhập tỉ lệ ba thành phần (cộng lại đúng 100%) và tỉ lệ hai học kỳ (cộng lại đúng 100%).</li>
                    <li>Đặt <strong>tỉ lệ quy đổi vắng có phép</strong>, ví dụ 50% = tính bằng nửa buổi có mặt.</li>
                    <li>Xem khung <strong>xem trước</strong> tính trên một học sinh thật của lớp đang chọn, rồi bấm <strong>Lưu cách tính</strong>.</li>
                </ol>
                <x-inline-tip>
                    Cấu hình lưu theo từng năm học nên <strong>điểm các năm trước không đổi</strong>.
                    Mặc định là 100% điểm trung bình học tập và cả năm chia đôi hai kỳ — giữ nguyên cách tính cũ.
                </x-inline-tip>
                <x-inline-tip tone="amber">
                    Buổi bị hủy và buổi GLV chưa điểm danh <strong>không</strong> vào mẫu số, nên học sinh không bị trừ oan.
                    Buổi đã được <strong>chốt tự động</strong> (cuối ngày) sẽ có đủ bản ghi: người chưa điểm danh thành vắng không phép.
                    Nếu một thành phần có tỉ lệ lớn hơn 0 mà chưa có dữ liệu, cột TB hiển thị dấu «—» kèm lý do khi trỏ chuột vào.
                </x-inline-tip>
            </div>
        </x-mac-panel>

        <x-mac-panel :overflow="true">
            <div class="px-4 lg:px-6 py-4 mac-hairline-b bg-white/40 flex items-center gap-3">
                <span class="w-8 h-8 rounded-full bg-primary-600 text-white text-sm font-bold flex items-center justify-center flex-shrink-0">4</span>
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Mở / khóa cửa sổ nhập điểm</h2>
                    <p class="text-xs text-slate-500">Cho phép hoặc dừng GLV hỗ trợ sửa điểm</p>
                </div>
            </div>
            <div class="p-4 lg:p-6 space-y-3 text-sm text-slate-700 leading-relaxed">
                <ol class="list-decimal list-inside space-y-2">
                    <li>Trên trang Kết quả học tập, xem banner phía trên:
                        <ul class="mt-2 ml-5 list-disc space-y-1 text-slate-600">
                            <li>Màu xanh = <strong>Đang mở nhập/sửa điểm</strong></li>
                            <li>Màu amber = <strong>Đang khóa</strong> (GLV chỉ xem)</li>
                        </ul>
                    </li>
                    <li>Ban quản trị bấm <strong>Mở nhập điểm</strong> khi đến kỳ nhập; bấm <strong>Khóa nhập điểm</strong> khi xong.</li>
                    <li>Ban quản trị <strong>luôn</strong> sửa được điểm, kể cả khi cửa sổ đang khóa.</li>
                </ol>
                <x-inline-tip>
                    Cửa sổ áp dụng theo giáo xứ — mở một lần thì mọi GLV đã được cấp «Quản lý điểm toàn giáo xứ» đều nhập được
                    điểm <strong>mọi lớp</strong> trong xứ. GLV thuần (chưa cấp quyền) vẫn chỉ xem, dù cửa sổ đang mở.
                </x-inline-tip>
            </div>
        </x-mac-panel>

        <x-mac-panel :overflow="true">
            <div class="px-4 lg:px-6 py-4 mac-hairline-b bg-white/40 flex items-center gap-3">
                <span class="w-8 h-8 rounded-full bg-primary-600 text-white text-sm font-bold flex items-center justify-center flex-shrink-0">5</span>
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Nhập điểm trên bảng điểm</h2>
                    <p class="text-xs text-slate-500">Màn: <span class="font-medium text-slate-700">Kết quả học tập</span> · tab Bảng điểm</p>
                </div>
            </div>
            <div class="p-4 lg:p-6 space-y-3 text-sm text-slate-700 leading-relaxed">
                <ol class="list-decimal list-inside space-y-2">
                    <li>Mở
                        <a href="{{ route('scores.index') }}" class="font-semibold text-primary-700 underline">Kết quả học tập</a>
                        → tab <strong>Bảng điểm</strong>.
                    </li>
                    <li>Chọn <strong>năm học → khối → lớp → học kỳ</strong>.
                        GLV có quyền hỗ trợ chọn được mọi lớp trong xứ; GLV thuần chỉ xem lớp được phân công.
                    </li>
                    <li>Nhập điểm từng ô theo cột loại điểm. Điểm không vượt quá điểm tối đa của cột.</li>
                    <li>Bấm <strong>Lưu tất cả</strong> để ghi nhận (đừng chỉ thoát trang).</li>
                    <li>Hệ thống tự tính điểm trung bình và xếp loại (Xuất sắc, Giỏi, Khá…).</li>
                </ol>
                <x-inline-tip>
                    Có thể lọc theo xếp loại hoặc tìm tên học sinh ở ô tìm kiếm phía trên bảng.
                    Quản trị viên có thể <strong>Xuất Excel</strong> và xem
                    <a href="{{ route('scores.edit-logs') }}" class="font-semibold text-primary-700 underline">Nhật ký sửa điểm</a>.
                </x-inline-tip>
                <x-inline-tip>
                    File Excel xuất cả năm và có đủ hạng mục điểm của từng kỳ: các cột loại điểm,
                    <strong>trung bình học tập</strong>, <strong>chuyên cần học</strong>, <strong>chuyên cần lễ</strong>,
                    trung bình học kỳ, trung bình cả năm và xếp loại. Tỉ lệ đang áp dụng được ghi ngay trên tiêu đề cột
                    và dòng đầu file.
                </x-inline-tip>
            </div>
        </x-mac-panel>

        <x-mac-panel class="p-4 lg:p-6 space-y-4">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Câu hỏi thường gặp</p>
            <div class="space-y-4 text-sm text-slate-700">
                <div>
                    <p class="font-semibold text-slate-900">GLV báo «không sửa được điểm»?</p>
                    <p class="mt-1 text-slate-600 leading-relaxed">
                        Kiểm tra lần lượt: (1) GLV đã được <strong>phân công lớp trong năm học hiện tại</strong> chưa —
                        chưa phân công thì mọi quyền đều không có hiệu lực;
                        (2) GLV đã được cấp «Quản lý điểm toàn giáo xứ» chưa — GLV thuần không nhập được;
                        (3) banner cửa sổ đang khóa hay mở — nếu khóa thì mở «Mở nhập điểm» rồi bảo GLV tải lại trang.
                    </p>
                </div>
                <div>
                    <p class="font-semibold text-slate-900">Bảng điểm không có cột nào?</p>
                    <p class="mt-1 text-slate-600 leading-relaxed">
                        Chưa có loại điểm đang bật cho lớp/học kỳ đó. Vào tab Cấu hình loại điểm và thêm cột.
                    </p>
                </div>
                <div>
                    <p class="font-semibold text-slate-900">Điểm trung bình khác kỳ vọng?</p>
                    <p class="mt-1 text-slate-600 leading-relaxed">
                        Kiểm tra hai chỗ: hệ số từng loại điểm (trung bình học tập = tổng (điểm × hệ số) / tổng hệ số các cột đã có điểm)
                        và tỉ lệ ba thành phần ở tab <strong>Cách tính điểm</strong>. Dòng công thức ngay trên bảng điểm cho biết
                        lớp đang dùng tỉ lệ nào.
                    </p>
                </div>
                <div>
                    <p class="font-semibold text-slate-900">Cột TB hiện dấu «—» dù đã nhập điểm?</p>
                    <p class="mt-1 text-slate-600 leading-relaxed">
                        Một thành phần có tỉ lệ lớn hơn 0 đang thiếu dữ liệu: thiếu điểm giữa kỳ/cuối kỳ, hoặc kỳ đó chưa có buổi nào
                        được điểm danh. Trỏ chuột vào dấu «—» sẽ thấy lý do cụ thể.
                    </p>
                </div>
                <div>
                    <p class="font-semibold text-slate-900">Không thấy tab Cấu hình loại điểm?</p>
                    <p class="mt-1 text-slate-600 leading-relaxed">
                        Chỉ quản trị giáo lý / quản trị xứ mới thấy. GLV (kể cả có quyền hỗ trợ) chỉ dùng tab Bảng điểm.
                    </p>
                </div>
                <div>
                    <p class="font-semibold text-slate-900">Muốn một GLV nhập điểm giúp ban quản trị?</p>
                    <p class="mt-1 text-slate-600 leading-relaxed">
                        Quản trị xứ vào <strong>Giáo lý viên → sửa GLV</strong>, bật <strong>«Quản lý điểm toàn giáo xứ»</strong>
                        trong mục Quyền hỗ trợ quản trị. Sau đó mở cửa sổ nhập điểm — GLV đó sẽ nhập/sửa được điểm mọi lớp trong xứ.
                        Lưu ý: GLV đó phải đang được phân công vào ít nhất một lớp trong năm học hiện tại thì quyền mới có hiệu lực.
                    </p>
                </div>
            </div>
        </x-mac-panel>

        <div class="flex flex-wrap gap-3 justify-center pb-4">
            <a href="{{ route('scores.index') }}"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-500 hover:bg-primary-600
                       text-white text-sm font-semibold rounded-xl transition shadow-mac-sm">
                Mở Kết quả học tập
            </a>
            <a href="{{ route('scores.edit-logs') }}"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-white/80 hover:bg-white
                       text-slate-700 text-sm font-semibold rounded-xl border border-black/[0.06]
                       transition shadow-mac-sm">
                Nhật ký sửa điểm
            </a>
        </div>

    </div>
</div>
