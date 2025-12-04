@extends('frontend.layout.main')

{{-- SEO --}}
@section('title', config('settings.web_name'))
@section('meta_description', config('settings.meta_description'))

@section('main') 
<div class="bg-body-tertiary p-4">
<div class="container">
    <h2 class="text-center mb-4">HƯỚNG DẪN IMPORT DANH SÁCH LỚP</h2>
    <h5 class="text-center text-muted">MỤC VỤ QUẢN LÝ GIÁO LÝ</h5>

    <hr>

    <h4>A. CHUẨN BỊ FILE DANH SÁCH CHUẨN</h4>
    <p>Để tránh lỗi khi import hoặc phải chỉnh sửa nhiều, cần cẩn thận chuẩn bị file danh sách.</p>

    <ol>
        <li>Download file mẫu danh sách Thiếu nhi và Giáo lý viên (link tải ở hệ thống).</li>
        <li>Xử lý và copy danh sách lớp vào file mẫu, đúng theo các cột Excel quy định.</li>
    </ol>

    <p><strong>Một vài lưu ý:</strong></p>
    <ul>
        <li><b>Tách họ và tên đệm:</b> Nếu chưa tách, hãy tách riêng hai cột (xem hướng dẫn 
            <a href="https://www.ketoan.biz/2016/06/thu-thuat-tach-gop-ho-ten-trong-excel.html#google_vignette" target="_blank">tại đây</a>).
        </li>
        <li><b>Cột ngày sinh:</b> Đưa về định dạng <code>Text</code> bằng hàm 
            <code>=TEXT(A1; "dd/mm/yyyy")</code> 
            (<a href="https://www.youtube.com/shorts/qgNWN8RHb5Q" target="_blank">Xem video hướng dẫn</a>).
        </li>
        <li><b>Giới tính:</b> Cần điền đầy đủ để thống kê.</li>
        <li><b>Số điện thoại:</b> Nên có để phụ huynh tra cứu kết quả học tập và đi lễ.</li>
    </ul>

    <h4 class="mt-4">B. IMPORT DỮ LIỆU</h4>
    <ol>
        <li>Đăng nhập tài khoản tại <a href="https://mvqlgiaoxu.org" target="_blank">https://mvqlgiaoxu.org</a>.</li>
        <li>Vào menu <b>TÍNH NĂNG THIẾU NHI → Import lớp</b> → chọn Năm học → tải file → Import.</li>
        <li>Vào menu <b>TÍNH NĂNG THIẾU NHI → Import Giáo viên</b> → chọn Giáo phận, Giáo hạt, Giáo xứ → tải file → Import.</li>
    </ol>

    <h4 class="mt-4">C. MỘT VÀI THIẾT LẬP TRONG ADMIN</h4>
    <ol>
        <li>Vào trang <a href="https://mvqlgiaoxu.org/admin" target="_blank">Admin</a>.</li>
        <li><b>Tạo Khối/Ngành:</b> Vào Quản lý giáo lý → Khối → Thêm mới → Điền thông tin → Lưu.</li>
        <li><b>Sắp xếp lớp vào Khối:</b> Vào Quản lý giáo lý → Lớp → Sửa → chọn Giáo phận, Giáo hạt, Giáo xứ, Khối (Năm học), Thời gian 2 học kỳ → Lưu.</li>
    </ol>

    <p class="mt-4 text-success fw-bold">Xong!</p>
</div>
</div>

<script src="{{mix('js/char.js')}}"></script>
<script src="{{mix('js/apexcharts.min.js')}}"></script>
@endsection


<!-- @section('main')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">

        {{-- Header --}}
        <div class="text-center mb-8">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">
                HƯỚNG DẪN IMPORT DANH SÁCH LỚP
            </h2>
            <p class="mt-2 text-sm sm:text-base text-gray-600 font-medium">
                MỤC VỤ QUẢN LÝ GIÁO LÝ
            </p>
        </div>

        <div class="h-px bg-gray-200 rounded-full"></div>

        {{-- Section A --}}
        <div class="mt-10 bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sm:p-8">
            <h4 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                <span class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-sm font-bold">A</span>
                CHUẨN BỊ FILE DANH SÁCH CHUẨN
            </h4>
            <p class="text-gray-700 leading-relaxed mb-5">
                Để tránh lỗi khi import hoặc phải chỉnh sửa nhiều, cần cẩn thận chuẩn bị file danh sách.
            </p>

            <ol class="space-y-3 ml-5">
                <li class="flex items-start gap-3">
                    <span class="flex-shrink-0 w-6 h-6 bg-blue-500 text-white rounded-full flex items-center justify-center text-xs font-bold">1</span>
                    <span class="text-gray-700">Download file mẫu danh sách Thiếu nhi và Giáo lý viên (link tải ở hệ thống).</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="flex-shrink-0 w-6 h-6 bg-blue-500 text-white rounded-full flex items-center justify-center text-xs font-bold">2</span>
                    <span class="text-gray-700">Xử lý và copy danh sách lớp vào file mẫu, đúng theo các cột Excel quy định.</span>
                </li>
            </ol>

            <div class="mt-6 p-4 bg-amber-50 border border-amber-200 rounded-xl">
                <p class="font-semibold text-amber-800 mb-2">Một vài lưu ý:</p>
                <ul class="space-y-2 ml-5 text-gray-700">
                    <li class="flex items-start gap-2">
                        <span class="text-amber-600">•</span>
                        <span><strong>Tách họ và tên đệm:</strong> Nếu chưa tách, hãy tách riêng hai cột (xem hướng dẫn
                            <a href="https://www.ketoan.biz/2016/06/thu-thuat-tach-gop-ho-ten-trong-excel.html#google_vignette"
                                target="_blank" class="text-blue-600 hover:text-blue-800 underline">tại đây</a>).
                        </span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-amber-600">•</span>
                        <span><strong>Cột ngày sinh:</strong> Đưa về định dạng <code class="bg-white px-2 py-0.5 rounded text-xs font-mono">Text</code> bằng hàm
                            <code class="bg-white px-2 py-0.5 rounded text-xs font-mono">=TEXT(A1; "dd/mm/yyyy")</code>
                            (<a href="https://www.youtube.com/shorts/qgNWN8RHb5Q" target="_blank" class="text-blue-600 hover:text-blue-800 underline">Xem video hướng dẫn</a>).
                        </span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-amber-600">•</span>
                        <span><strong>Giới tính:</strong> Cần điền đầy đủ để thống kê.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-amber-600">•</span>
                        <span><strong>Số điện thoại:</strong> Nên có để phụ huynh tra cứu kết quả học tập và đi lễ.</span>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Section B --}}
        <div class="mt-6 bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sm:p-8">
            <h4 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                <span class="w-8 h-8 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-sm font-bold">B</span>
                IMPORT DỮ LIỆU
            </h4>
            <ol class="space-y-3 ml-5">
                <li class="flex items-start gap-3">
                    <span class="flex-shrink-0 w-6 h-6 bg-green-500 text-white rounded-full flex items-center justify-center text-xs font-bold">1</span>
                    <span class="text-gray-700">Đăng nhập tài khoản tại
                        <a href="https://mvqlgiaoxu.org" target="_blank" class="text-blue-600 hover:text-blue-800 underline font-medium">
                            https://mvqlgiaoxu.org
                        </a>.
                    </span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="flex-shrink-0 w-6 h-6 bg-green-500 text-white rounded-full flex items-center justify-center text-xs font-bold">2</span>
                    <span class="text-gray-700">Vào menu <strong>TÍNH NĂNG THIẾU NHI → Import lớp</strong> → chọn Năm học → tải file → Import.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="flex-shrink-0 w-6 h-6 bg-green-500 text-white rounded-full flex items-center justify-center text-xs font-bold">3</span>
                    <span class="text-gray-700">Vào menu <strong>TÍNH NĂNG THIẾU NHI → Import Giáo viên</strong> → chọn Giáo phận, Giáo hạt, Giáo xứ → tải file → Import.</span>
                </li>
            </ol>
        </div>

        {{-- Section C --}}
        <div class="mt-6 bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sm:p-8">
            <h4 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                <span class="w-8 h-8 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center text-sm font-bold">C</span>
                MỘT VÀI THIẾT LẬP TRONG ADMIN
            </h4>
            <ol class="space-y-3 ml-5">
                <li class="flex items-start gap-3">
                    <span class="flex-shrink-0 w-6 h-6 bg-purple-500 text-white rounded-full flex items-center justify-center text-xs font-bold">1</span>
                    <span class="text-gray-700">Vào trang
                        <a href="https://mvqlgiaoxu.org/admin" target="_blank" class="text-blue-600 hover:text-blue-800 underline font-medium">
                            Admin
                        </a>.
                    </span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="flex-shrink-0 w-6 h-6 bg-purple-500 text-white rounded-full flex items-center justify-center text-xs font-bold">2</span>
                    <span class="text-gray-700"><strong>Tạo Khối/Ngành:</strong> Vào Quản lý giáo lý → Khối → Thêm mới → Điền thông tin → Lưu.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="flex-shrink-0 w-6 h-6 bg-purple-500 text-white rounded-full flex items-center justify-center text-xs font-bold">3</span>
                    <span class="text-gray-700"><strong>Sắp xếp lớp vào Khối:</strong> Vào Quản lý giáo lý → Lớp → Sửa → chọn Giáo phận, Giáo hạt, Giáo xứ, Khối (Năm học), Thời gian 2 học kỳ → Lưu.</span>
                </li>
            </ol>
        </div>

        {{-- Footer Success --}}
        <div class="mt-8 text-center">
            <p class="text-lg font-bold text-green-600 animate-pulse">
                Xong!
            </p>
        </div>

    </div>
</div>

{{-- Scripts (giữ nguyên) --}}
<script src="{{mix('js/char.js')}}"></script>
<script src="{{mix('js/apexcharts.min.js')}}"></script>
@endsection -->