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
                description="Giải thích toàn bộ cơ chế: loại điểm, cách tính TB học kỳ / cả năm, chuyên cần từ điểm danh, xếp loại và xuất Excel."
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
                    <strong>Ban quản trị</strong> (parish_admin / catechism_admin): cấu hình loại điểm và luôn được sửa điểm.
                    <strong>GLV thuần</strong>: chỉ <em>xem</em> điểm lớp được phân công — không nhập/sửa.
                    <strong>GLV có «Quản lý điểm toàn giáo xứ»</strong> (quyền hỗ trợ quản trị): nhập/sửa điểm <em>mọi lớp</em> trong xứ.
                    Điều kiện chung: GLV (kể cả có quyền hỗ trợ) phải <strong>đang được phân công</strong> vào ít nhất một lớp
                    trong năm học hiện tại — chưa phân công thì không xem/nhập được gì.
                </p>
            </x-inline-tip>
            <x-inline-tip>
                Cần sẵn: năm học đang dùng, lớp có học sinh đã ghi danh, ít nhất một <strong>loại điểm</strong> đang bật,
                và (nếu muốn GLV nhập giúp) đã cấp quyền «Quản lý điểm toàn giáo xứ» trên hồ sơ GLV.
            </x-inline-tip>
            <x-inline-tip>
                Điểm chuyên cần lấy từ
                <a href="{{ route('help.attendance') }}" class="font-semibold text-primary-700 underline">điểm danh</a>
                theo trạng thái hiệu lực (gồm KP suy luận sau giờ chốt). Xem kỹ mục 5 bên dưới.
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
                    <p class="font-semibold text-primary-900 text-xs sm:text-sm">Cách tính điểm</p>
                </div>
                <div class="hidden sm:flex text-slate-300 px-1">→</div>
                <div class="sm:hidden text-center text-slate-300 text-xs">↓</div>
                <div class="flex-1 rounded-xl bg-primary-50 border border-primary-100 px-3 py-2.5 text-center">
                    <p class="text-[11px] text-primary-600 font-medium">Bước 4</p>
                    <p class="font-semibold text-primary-900 text-xs sm:text-sm">Nhập &amp; xuất</p>
                </div>
            </div>
        </x-mac-panel>

        {{-- Bước 1 --}}
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

        {{-- Bước 2 --}}
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
                    <li>Lưu. Có thể tắt/bật từng loại điểm: cột đã tắt không hiện trên bảng điểm và không tính vào trung bình học tập.</li>
                </ol>

                <div class="rounded-xl bg-slate-50/80 border border-black/[0.04] p-3 space-y-2">
                    <p class="font-semibold text-slate-800">Công thức trung bình học tập</p>
                    <p class="text-slate-600">
                        <strong>TB học tập</strong> = tổng (điểm × hệ số) / tổng hệ số của các cột <em>đã có điểm</em> và đang bật.
                    </p>
                    <ul class="list-disc ml-5 space-y-1 text-slate-600">
                        <li>Cột đã tắt: bỏ hoàn toàn.</li>
                        <li>Cột thường (15 phút, 45 phút, khảo kinh…) thiếu điểm: bỏ qua cột đó (không vào tử số lẫn mẫu số).</li>
                        <li>Cột <strong>Giữa kỳ</strong> hoặc <strong>Cuối kỳ</strong> đang bật mà chưa có điểm: chưa chốt được TB học tập (ô TB hiện «—»).</li>
                    </ul>
                </div>
            </div>
        </x-mac-panel>

        {{-- Bước 3 --}}
        <x-mac-panel :overflow="true">
            <div class="px-4 lg:px-6 py-4 mac-hairline-b bg-white/40 flex items-center gap-3">
                <span class="w-8 h-8 rounded-full bg-primary-600 text-white text-sm font-bold flex items-center justify-center flex-shrink-0">3</span>
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Cách tính điểm trung bình học kỳ &amp; cả năm</h2>
                    <p class="text-xs text-slate-500">Màn: <span class="font-medium text-slate-700">Kết quả học tập</span> · tab Cách tính điểm</p>
                </div>
            </div>
            <div class="p-4 lg:p-6 space-y-4 text-sm text-slate-700 leading-relaxed">
                <p>Giáo xứ tự chọn tỉ lệ ba thành phần của điểm trung bình học kỳ:</p>
                <div class="rounded-xl bg-slate-50/80 border border-black/[0.04] p-4 space-y-2">
                    <p><strong>TB học kỳ</strong> = TB học tập × %HT + chuyên cần học × %CC học + chuyên cần lễ × %CC lễ</p>
                    <p><strong>TB cả năm</strong> = TB học kỳ 1 × %HK1 + TB học kỳ 2 × %HK2</p>
                    <p class="text-xs text-slate-500">
                        Chỉ cộng các thành phần có tỉ lệ &gt; 0. Ba tỉ lệ học kỳ phải cộng đúng 100%; hai tỉ lệ học kỳ cũng phải cộng đúng 100%.
                    </p>
                </div>

                <ol class="list-decimal list-inside space-y-2">
                    <li>Chọn <strong>năm học</strong> rồi mở tab <strong>Cách tính điểm</strong>.</li>
                    <li>Chọn phạm vi: <strong>toàn giáo xứ</strong> trong năm học đó, hoặc <strong>riêng một khối</strong> (ghi đè cấu hình toàn xứ).</li>
                    <li>Nhập tỉ lệ ba thành phần và tỉ lệ hai học kỳ.</li>
                    <li>Đặt <strong>tỉ lệ quy đổi vắng có phép</strong>, ví dụ 50% = tính bằng nửa buổi có mặt.</li>
                    <li>Xem khung <strong>xem trước</strong> trên một học sinh thật của lớp đang chọn, rồi bấm <strong>Lưu cách tính</strong>.</li>
                </ol>

                <div class="rounded-xl bg-slate-50/80 border border-black/[0.04] p-3 space-y-2">
                    <p class="font-semibold text-slate-800">Khi nào ô TB hiện «—»?</p>
                    <ul class="list-disc ml-5 space-y-1 text-slate-600">
                        <li>Thành phần có tỉ lệ &gt; 0 đang thiếu dữ liệu (ví dụ thiếu giữa kỳ/cuối kỳ, hoặc chưa có buổi nào được điểm danh cho chuyên cần).</li>
                        <li>Trỏ chuột vào dấu «—» để xem lý do cụ thể.</li>
                        <li>Thành phần có tỉ lệ = 0 thì bỏ qua hoàn toàn — thiếu cũng không sao.</li>
                        <li>TB cả năm chỉ có khi <strong>cả hai học kỳ</strong> đều đã có TB.</li>
                    </ul>
                </div>

                <x-inline-tip>
                    Cấu hình lưu theo từng năm học nên <strong>điểm các năm trước không đổi</strong>.
                    Mặc định là 100% điểm trung bình học tập và cả năm chia đôi hai kỳ — giữ nguyên cách tính cũ nếu chưa cấu hình thêm chuyên cần.
                </x-inline-tip>
            </div>
        </x-mac-panel>

        {{-- Bước chuyên cần chi tiết --}}
        <x-mac-panel :overflow="true">
            <div class="px-4 lg:px-6 py-4 mac-hairline-b bg-white/40 flex items-center gap-3">
                <span class="w-8 h-8 rounded-full bg-slate-700 text-white text-sm font-bold flex items-center justify-center flex-shrink-0">★</span>
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Điểm chuyên cần lấy từ điểm danh thế nào?</h2>
                    <p class="text-xs text-slate-500">Áp dụng cho chuyên cần học và chuyên cần lễ</p>
                </div>
            </div>
            <div class="p-4 lg:p-6 space-y-3 text-sm text-slate-700 leading-relaxed">
                <div class="rounded-xl bg-slate-50/80 border border-black/[0.04] p-4 space-y-2">
                    <p>
                        <strong>Điểm chuyên cần</strong> = 10 × (số buổi có mặt + tỉ lệ quy đổi × số buổi vắng có phép)
                        / số buổi đã điểm danh
                    </p>
                    <p class="text-xs text-slate-500">Làm tròn 1 chữ số thập phân. Tính riêng theo học kỳ và theo loại buổi (học / lễ).</p>
                </div>

                <p class="font-semibold text-slate-800">Buổi nào vào mẫu số?</p>
                <ul class="list-disc ml-5 space-y-1 text-slate-600">
                    <li>Buổi thuộc đúng học kỳ, đúng loại (học hoặc lễ), <strong>không bị hủy</strong>.</li>
                    <li>Buổi đã có ít nhất một học sinh được điểm danh.</li>
                    <li>Với từng học sinh: chỉ tính các buổi từ ngày xếp lớp trở đi.</li>
                </ul>

                <p class="font-semibold text-slate-800">Buổi nào KHÔNG vào mẫu số?</p>
                <ul class="list-disc ml-5 space-y-1 text-slate-600">
                    <li>Buổi <strong>đã hủy</strong>.</li>
                    <li>Buổi <strong>chưa ai được điểm danh</strong> (cả lớp trống) — tránh trừ oan khi GLV quên điểm hoặc lớp chưa học.</li>
                    <li>Ô còn «chưa điểm danh» của buổi <strong>chưa khóa và chưa tới giờ chốt</strong> — chưa kết luận, chưa tính.</li>
                </ul>

                <p class="font-semibold text-slate-800">KP suy luận có vào điểm không?</p>
                <p class="text-slate-600">
                    Có. Khi buổi đã khóa hoặc đã qua giờ chốt của ngày buổi đó, và đã có ít nhất một em được điểm danh,
                    những em còn trống được coi là <strong>vắng không phép</strong>: vào mẫu số nhưng không được cộng điểm buổi đó.
                    Hệ thống <strong>không ghi</strong> bản ghi KP này vào database — chỉ suy luận khi đọc.
                    Chi tiết xem
                    <a href="{{ route('help.attendance') }}" class="font-semibold text-primary-700 underline">Hướng dẫn điểm danh → Giờ chốt</a>.
                </p>

                <div class="overflow-x-auto rounded-xl border border-black/[0.06]">
                    <table class="min-w-full text-xs sm:text-sm">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th class="px-3 py-2 text-left font-semibold">Trạng thái hiệu lực</th>
                                <th class="px-3 py-2 text-left font-semibold">Vào mẫu số?</th>
                                <th class="px-3 py-2 text-left font-semibold">Cộng điểm buổi?</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/[0.04] text-slate-700">
                            <tr>
                                <td class="px-3 py-2">Có mặt (✓)</td>
                                <td class="px-3 py-2">Có</td>
                                <td class="px-3 py-2">1 buổi</td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2">Vắng có phép (P)</td>
                                <td class="px-3 py-2">Có</td>
                                <td class="px-3 py-2">Theo tỉ lệ quy đổi (vd 0,5)</td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2">Vắng không phép / KP suy luận</td>
                                <td class="px-3 py-2">Có</td>
                                <td class="px-3 py-2">0</td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2">Chưa điểm danh (chưa tới giờ chốt)</td>
                                <td class="px-3 py-2">Không</td>
                                <td class="px-3 py-2">—</td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2">Buổi hủy / chưa ai điểm danh</td>
                                <td class="px-3 py-2">Không</td>
                                <td class="px-3 py-2">—</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </x-mac-panel>

        {{-- Bước 4 --}}
        <x-mac-panel :overflow="true">
            <div class="px-4 lg:px-6 py-4 mac-hairline-b bg-white/40 flex items-center gap-3">
                <span class="w-8 h-8 rounded-full bg-primary-600 text-white text-sm font-bold flex items-center justify-center flex-shrink-0">4</span>
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Nhập điểm, xếp loại &amp; xuất Excel</h2>
                    <p class="text-xs text-slate-500">Màn: <span class="font-medium text-slate-700">Kết quả học tập</span> · tab Bảng điểm</p>
                </div>
            </div>
            <div class="p-4 lg:p-6 space-y-4 text-sm text-slate-700 leading-relaxed">
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
                    <li>Hệ thống tự tính TB và xếp loại.</li>
                </ol>

                <div class="rounded-xl bg-slate-50/80 border border-black/[0.04] p-3 space-y-2">
                    <p class="font-semibold text-slate-800">Thang xếp loại (cố định toàn hệ thống)</p>
                    <ul class="list-disc ml-5 space-y-1 text-slate-600">
                        <li><strong>Xuất sắc</strong>: từ 9,5 đến 10</li>
                        <li><strong>Giỏi</strong>: từ 8,0 đến dưới 9,5</li>
                        <li><strong>Khá</strong>: từ 6,5 đến dưới 8,0</li>
                        <li><strong>Trung bình</strong>: từ 5,0 đến dưới 6,5</li>
                        <li><strong>Yếu</strong>: từ 3,5 đến dưới 5,0</li>
                        <li><strong>Kém</strong>: dưới 3,5</li>
                    </ul>
                    <p class="text-xs text-slate-500">
                        Học sinh chưa đủ dữ liệu để tính TB thì chưa được xếp loại.
                        Có thể lọc theo xếp loại hoặc tìm tên ở ô tìm kiếm phía trên bảng.
                    </p>
                </div>

                <p class="font-semibold text-slate-800">Xuất Excel</p>
                <ul class="list-disc ml-5 space-y-1 text-slate-600">
                    <li>
                        <strong>Xuất bảng điểm</strong> — file toàn giáo xứ, mỗi lớp một sheet.
                        Có đủ cột loại điểm, TB học tập, chuyên cần học/lễ (nếu tỉ lệ &gt; 0), TB học kỳ, TB cả năm và xếp loại.
                        Tỉ lệ đang áp dụng được ghi trên tiêu đề cột và dòng đầu file.
                    </li>
                    <li>
                        <strong>Xuất thống kê / phân phối</strong> — workbook 3 sheet (Tổng quan, Phân phối, Chi tiết)
                        cho HK1, HK2 và cả năm; luôn lấy toàn giáo xứ theo năm học đang chọn.
                    </li>
                    <li>
                        Quản trị viên có thể xem
                        <a href="{{ route('scores.edit-logs') }}" class="font-semibold text-primary-700 underline">Nhật ký sửa điểm</a>.
                    </li>
                </ul>
            </div>
        </x-mac-panel>

        {{-- FAQ --}}
        <x-mac-panel class="p-4 lg:p-6 space-y-4">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Câu hỏi thường gặp</p>
            <div class="space-y-4 text-sm text-slate-700">
                <div>
                    <p class="font-semibold text-slate-900">GLV báo «không sửa được điểm»?</p>
                    <p class="mt-1 text-slate-600 leading-relaxed">
                        Kiểm tra lần lượt: (1) GLV đã được <strong>phân công lớp trong năm học hiện tại</strong> chưa —
                        chưa phân công thì mọi quyền đều không có hiệu lực;
                        (2) GLV đã được cấp «Quản lý điểm toàn giáo xứ» chưa — GLV thuần không nhập được.
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
                        Kiểm tra hai chỗ: hệ số từng loại điểm (TB học tập = tổng (điểm × hệ số) / tổng hệ số các cột đã có điểm)
                        và tỉ lệ ba thành phần ở tab <strong>Cách tính điểm</strong>. Dòng công thức ngay trên bảng điểm cho biết
                        lớp đang dùng tỉ lệ nào. Với chuyên cần, kiểm tra thêm buổi đã điểm danh / giờ chốt / buổi hủy.
                    </p>
                </div>
                <div>
                    <p class="font-semibold text-slate-900">Cột TB hiện dấu «—» dù đã nhập điểm?</p>
                    <p class="mt-1 text-slate-600 leading-relaxed">
                        Một thành phần có tỉ lệ lớn hơn 0 đang thiếu dữ liệu: thiếu điểm giữa kỳ/cuối kỳ, hoặc kỳ đó chưa có buổi nào
                        được điểm danh (cho chuyên cần). Trỏ chuột vào dấu «—» sẽ thấy lý do cụ thể.
                    </p>
                </div>
                <div>
                    <p class="font-semibold text-slate-900">Chuyên cần thấp dù nhiều em mới vắng vài buổi?</p>
                    <p class="mt-1 text-slate-600 leading-relaxed">
                        Kiểm tra các buổi đã qua giờ chốt hoặc đã khóa: ô trống được suy luận thành KP và vẫn vào mẫu số.
                        Buổi chưa ai điểm danh thì không trừ — nhưng buổi đã điểm một phần rồi để trống sẽ bị tính KP.
                        Xem
                        <a href="{{ route('help.attendance') }}" class="font-semibold text-primary-700 underline">Hướng dẫn điểm danh</a>.
                    </p>
                </div>
                <div>
                    <p class="font-semibold text-slate-900">Không thấy tab Cấu hình loại điểm / Cách tính điểm?</p>
                    <p class="mt-1 text-slate-600 leading-relaxed">
                        Chỉ quản trị giáo lý / quản trị xứ mới thấy. GLV (kể cả có quyền hỗ trợ) chỉ dùng tab Bảng điểm.
                    </p>
                </div>
                <div>
                    <p class="font-semibold text-slate-900">Muốn một GLV nhập điểm giúp ban quản trị?</p>
                    <p class="mt-1 text-slate-600 leading-relaxed">
                        Quản trị xứ vào <strong>Giáo lý viên → sửa GLV</strong>, bật <strong>«Quản lý điểm toàn giáo xứ»</strong>
                        trong mục Quyền hỗ trợ quản trị. GLV đó sẽ nhập/sửa được điểm mọi lớp trong xứ ngay sau khi được cấp quyền.
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
            <a href="{{ route('help.attendance') }}"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-white/80 hover:bg-white
                       text-slate-700 text-sm font-semibold rounded-xl border border-black/[0.06]
                       transition shadow-mac-sm">
                ← Hướng dẫn điểm danh
            </a>
        </div>

    </div>
</div>
