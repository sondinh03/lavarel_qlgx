<?php

/**
 * Sinh Hướng dẫn Mục vụ Quản lý Thiếu nhi — bản V3 (cập nhật từ V2).
 * Chạy: php docs/generate-vgiaoxu-guide-v3.php
 *
 * Xuất: docs/HUONG_DAN_VGIAOXU_ORG_V3.docx
 * (Có thể Save As PDF từ Microsoft Word nếu cần file .pdf)
 */

require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\SimpleType\Jc;

Settings::setOutputEscapingEnabled(true);

$phpWord = new PhpWord();
$phpWord->setDefaultFontName('Times New Roman');
$phpWord->setDefaultFontSize(12);

$phpWord->addTitleStyle(1, ['bold' => true, 'size' => 16, 'color' => '1E3A2F'], ['spaceBefore' => 360, 'spaceAfter' => 160]);
$phpWord->addTitleStyle(2, ['bold' => true, 'size' => 14, 'color' => '245C3A'], ['spaceBefore' => 280, 'spaceAfter' => 120]);
$phpWord->addTitleStyle(3, ['bold' => true, 'size' => 12, 'color' => '2F6B45'], ['spaceBefore' => 200, 'spaceAfter' => 80]);

$section = $phpWord->addSection([
    'marginTop'    => 1134,
    'marginBottom' => 1134,
    'marginLeft'   => 1418,
    'marginRight'  => 1134,
]);

$footer = $section->addFooter();
$footer->addPreserveText(
    'mvgiaoxu.org — Hướng dẫn Mục vụ Quản lý Thiếu nhi V3 | Trang {PAGE} / {NUMPAGES}',
    ['size' => 9, 'italic' => true, 'color' => '666666'],
    ['alignment' => Jc::CENTER]
);

$addPara = function (string $text, array $font = [], array $para = []) use ($section) {
    $section->addText(
        $text,
        array_merge(['size' => 12], $font),
        array_merge(['spaceAfter' => 120, 'alignment' => Jc::BOTH], $para)
    );
};

$addBullet = function (string $text) use ($section) {
    $section->addListItem($text, 0, ['size' => 12], null, ['spaceAfter' => 60]);
};

$addNote = function (string $text) use ($section) {
    $section->addText(
        'Lưu ý: ' . $text,
        ['italic' => true, 'size' => 11, 'color' => '5B4B00'],
        ['spaceBefore' => 80, 'spaceAfter' => 160]
    );
};

$addNew = function (string $text) use ($section) {
    $section->addText(
        'Mới (V3): ' . $text,
        ['bold' => true, 'size' => 11, 'color' => '0B5CAB'],
        ['spaceBefore' => 80, 'spaceAfter' => 120]
    );
};

// ===== BÌA =====
$section->addTextBreak(2);
$section->addText(
    'Lưu hành nội bộ ' . date('Y'),
    ['size' => 11, 'color' => '666666'],
    ['alignment' => Jc::CENTER, 'spaceAfter' => 200]
);
$section->addText(
    'HƯỚNG DẪN SỬ DỤNG',
    ['bold' => true, 'size' => 22, 'color' => '30B653'],
    ['alignment' => Jc::CENTER, 'spaceAfter' => 120]
);
$section->addText(
    'MỤC VỤ QUẢN LÝ THIẾU NHI',
    ['bold' => true, 'size' => 18, 'color' => '1E3A2F'],
    ['alignment' => Jc::CENTER, 'spaceAfter' => 200]
);
$section->addText(
    'https://mvgiaoxu.org',
    ['size' => 14, 'color' => '245C3A'],
    ['alignment' => Jc::CENTER, 'spaceAfter' => 200]
);
$section->addText(
    'Bản V3 — cập nhật từ V2',
    ['bold' => true, 'size' => 13],
    ['alignment' => Jc::CENTER, 'spaceAfter' => 80]
);
$section->addText(
    'Cập nhật: ' . date('d/m/Y'),
    ['size' => 11, 'color' => '666666'],
    ['alignment' => Jc::CENTER, 'spaceAfter' => 200]
);
$section->addText(
    'Giải đáp thắc mắc: mời vào nhóm Zalo MVGIAOXU.ORG',
    ['size' => 11, 'italic' => true],
    ['alignment' => Jc::CENTER]
);

$section->addPageBreak();

// ===== GIỚI THIỆU =====
$section->addTitle('Ứng dụng này có những gì?', 1);
$addPara('Ứng dụng Mục vụ quản lý thiếu nhi giúp quý Cha và Ban giáo lý quản lý thiếu nhi trong giáo xứ. Các câu hỏi thường gặp:');
$addBullet('Sao chép / chuyển năm học nhanh chóng?');
$addBullet('Import danh sách thiếu nhi bằng Excel, báo biểu tải về, cập nhật theo lớp?');
$addBullet('Phân công GLV vào lớp và tạo tài khoản để GLV điểm danh?');
$addBullet('Điểm danh bằng quét QR hoặc tích thủ công?');
$addBullet('Nhập điểm, tùy chỉnh loại điểm / hệ số theo học kỳ?');
$addBullet('Điểm danh Giáo lý viên (đi dạy / đi lễ / họp) và hội nhóm (Ca đoàn, Tông đồ…)?');
$addBullet('Phụ huynh tra cứu kết quả / điểm danh của các em?');
$addNew('Cấp quyền hỗ trợ cho GLV (Trưởng/Phó): tạo phiên điểm danh học sinh và GLV, sửa hồ sơ học sinh toàn xứ, nhập điểm toàn xứ, điểm danh GLV.');
$addNew('Thêm / đổi ảnh học sinh hoặc GLV nhanh ngay trên danh sách (chụp ảnh hoặc chọn từ thư viện trên điện thoại).');
$addPara('Tài liệu V3 giữ cấu trúc V2 và bổ sung các thao tác mới theo phiên bản hiện tại của hệ thống.');

$section->addPageBreak();

// ===== MỤC LỤC =====
$section->addTitle('MỤC LỤC', 1);
$toc = [
    'I. Tạo năm học mới và sao chép chuyển năm học kế tiếp',
    'II. Quản lý danh sách học sinh',
    'III. Quản lý danh sách Giáo lý viên (kèm quyền hỗ trợ quản trị)',
    'IV. Điểm danh học sinh và phiên điểm danh',
    'V. Điểm danh Giáo lý viên',
    'VI. Quản lý điểm',
    'VII. Quản lý hội nhóm',
    'VIII. Phụ huynh tra cứu kết quả',
    'IX. Câu hỏi thường gặp (bổ sung V3)',
];
foreach ($toc as $i => $item) {
    $section->addText($item, ['size' => 12], ['spaceAfter' => 80]);
}

$section->addPageBreak();

// ===== I =====
$section->addTitle('I. TẠO NĂM HỌC MỚI VÀ SAO CHÉP CHUYỂN NĂM HỌC KẾ TIẾP', 1);

$section->addTitle('1. Tạo năm học mới', 2);
$addPara('B1: Vào Menu Hệ thống → Năm học → Thêm năm học.');
$addPara('B2: Điền đầy đủ thông tin năm học → Lưu. (Cần vào Trang chủ → Làm mới để cập nhật năm học mới nếu chưa thấy ngay.)');

$section->addTitle('2. Sao chép năm học', 2);
$addPara('B1: Vào Menu Hệ thống → Năm học → Sao chép năm học và lần lượt làm theo hướng dẫn trên màn hình.');
$addBullet('Chọn Năm nguồn → Xem xác nhận → Xác nhận copy.');
$addBullet('Xếp học sinh ngay: Lớp đích (năm mới) ← lớp năm trước → Chọn tất cả (hoặc bỏ chọn em không lên lớp) → Xếp học sinh vào lớp.');
$addBullet('Tiếp tục xếp các lớp khác cho đến khi hoàn tất.');
$addNote('Khi đã tạo và sao chép năm học mới xong thì vào năm học cũ, nhấn Lưu trữ để dữ liệu chỉ hiển thị năm học hiện tại. Muốn tra cứu năm cũ thì bỏ lưu trữ tạm thời rồi lưu trữ lại sau.');

$section->addPageBreak();

// ===== II =====
$section->addTitle('II. QUẢN LÝ DANH SÁCH HỌC SINH', 1);

$section->addTitle('1. Chuẩn bị danh sách học sinh các lớp', 2);
$section->addTitle('1.1. Download file mẫu', 3);
$addPara('B1: Vào Menu Giáo lý → Quản lý học sinh → Khác → Import Excel.');
$addPara('B2: Nhấn Tải file mẫu để tải file về.');

$section->addTitle('1.2. Chỉnh sửa file Excel và copy vào file mẫu', 3);
$addPara('Lưu ý khi chỉnh file:');
$addBullet('Xem file mẫu có những cột nào; tách cột Tên Thánh, Họ tên đệm và Tên nếu cần.');
$addBullet('Sửa từ viết tắt: Tên Thánh, Tên Đệm, Giáo họ…');
$addBullet('Cột Ngày sinh (dd/mm/yyyy) và Số điện thoại (10 số, có số 0 đầu) về định dạng Text (có thể copy qua Notepad rồi paste lại Excel).');
$addBullet('Thêm cột Giới tính Nam/Nữ để dễ thống kê.');
$addBullet('Chỉnh thứ tự cột giống file mẫu trước khi copy (nhớ xóa dòng mẫu thừa trước khi paste).');
$addBullet('Lưu tên lớp rõ ràng để dễ Import.');
$addPara('Video hướng dẫn chỉnh file mẫu: https://drive.google.com/file/d/1b0vDdieD1Vf0S0MHcOOb01C7cYnO23-o/view?usp=sharing');

$section->addTitle('2. Import danh sách lớp', 2);
$section->addTitle('2.1. Nếu chưa thêm Giáo họ (và có tài khoản Quản lý giáo dân)', 3);
$addPara('B1: Vào module Quản lý Giáo dân.');
$addPara('B2: Menu Hệ thống → Giáo họ: thêm tất cả tên Giáo họ trong xứ (kể cả giáo họ thuộc xứ khác nhưng có trong danh sách HS).');
$addPara('B3: Trở lại module Quản lý Giáo lý.');

$section->addTitle('2.2. Nếu đã thêm các Giáo họ', 3);
$addPara('B1: Menu Hệ thống → Lớp học: thêm các lớp thuộc khối của năm học đang dùng.');
$addPara('B2: Menu Giáo lý → Quản lý học sinh → Khác → Import Excel.');
$addPara('B3: Chọn Khối – Lớp → Choose File (file Excel đã copy theo mẫu).');
$addPara('B4: Kéo xuống cuối, nếu không báo lỗi thì Xác nhận Import; còn lỗi thì sửa file rồi làm lại.');

$section->addTitle('3. Thêm ảnh học sinh', 2);
$addPara('Cách 1 — từ form sửa (như V2):');
$addPara('B1: Menu Giáo lý → Quản lý học sinh → biểu tượng Chỉnh sửa.');
$addPara('B2: Chọn hình ảnh trên máy hoặc chụp trực tiếp trên điện thoại → Lưu.');
$addNew('Cách 2 — nhanh trên danh sách: tại danh sách học sinh, bấm vào avatar học sinh → chọn ảnh (Camera / Thư viện / File do hệ điều hành đề xuất) → hệ thống lưu ngay. GLV chỉ làm được nếu được cấp quyền «Sửa thông tin học sinh toàn giáo xứ».');

$section->addTitle('4. In thẻ học sinh', 2);
$addPara('B1: Menu Giáo lý → Quản lý học sinh → Chọn Lớp → Khác → In thẻ.');
$addPara('B2: Tải thẻ xuống hoặc in trực tiếp (thẻ vĩnh viễn hoặc thẻ theo năm, tùy lựa chọn trên màn hình).');

$section->addTitle('5. Cách cập nhật danh sách học sinh', 2);
$addPara('B1: Menu Giáo lý → Quản lý học sinh → Chọn Khối–Lớp → Export Excel (file có cột Mã học sinh ở cuối).');
$addPara('B2: Cập nhật thông tin trên file vừa export, copy các cột tương ứng sang file mẫu → Lưu.');
$addPara('B3: Import Excel lại như bước Import danh sách → Xác nhận Import.');

$section->addPageBreak();

// ===== III =====
$section->addTitle('III. QUẢN LÝ DANH SÁCH GIÁO LÝ VIÊN', 1);

$section->addTitle('1. Import danh sách GLV', 2);
$addPara('B1: Menu Nhân sự → Giáo lý viên → Import GLV → Tải file mẫu.');
$addPara('B2: Nhập danh sách vào file mẫu. Cột cuối: ghi «Có» nếu muốn tạo tài khoản điểm danh ngay; để trống nếu tạo sau. Cột Ngày sinh về định dạng Text.');
$addPara('B3: Import GLV → Choose File → Xác nhận Import.');
$addNew('Có thể thêm / đổi ảnh GLV nhanh ngay trên danh sách (bấm avatar), dành cho Quản trị xứ / Quản trị giáo lý.');

$section->addTitle('2. Phân công GLV vào các lớp', 2);
$addPara('B1: Menu Hệ thống → Lớp học → hàng lớp cần phân công → biểu tượng Danh sách GLV.');
$addPara('B2: Tìm tên GLV → tích chọn; có thể đổi vai trò (Chủ nhiệm / Phụ trách).');
$addPara('B3: Lặp lại với các lớp khác.');
$addNote('GLV chỉ thao tác được trên năm học hiện tại khi đã được phân công ít nhất một lớp trong năm đó. Tài khoản năm cũ chưa phân công năm mới vẫn đăng nhập được nhưng không điểm danh / không dùng quyền hỗ trợ.');

$section->addTitle('3. Tạo tài khoản GLV điểm danh (thường dùng trên điện thoại)', 2);
$addPara('B1: Menu Nhân sự → Giáo lý viên → Sửa.');
$addPara('B2: Thêm số điện thoại hoặc email đăng nhập → tích «Tạo tài khoản đăng nhập ngay» → Cập nhật.');

$section->addTitle('4. Cấp quyền hỗ trợ quản trị cho GLV (mới V3)', 2);
$addPara('Mặc định GLV chỉ điểm danh và xem lớp được phân công. Khi cần Trưởng/Phó hỗ trợ Ban giáo lý, mở Sửa GLV (tài khoản đã tạo) → mục «Quyền hỗ trợ quản trị»:');
$addBullet('Quản lý điểm toàn giáo xứ — nhập/sửa điểm mọi lớp khi cửa sổ nhập điểm đang mở.');
$addBullet('Sửa thông tin học sinh toàn giáo xứ — cập nhật hồ sơ học sinh toàn xứ (không gồm tạo/xóa/import).');
$addBullet('Điểm danh giáo lý viên — điểm danh các buổi GLV (đi dạy / đi lễ / họp).');
$addBullet('Tạo phiên điểm danh — tạo và khóa/mở phiên điểm danh học sinh và phiên điểm danh GLV (không gồm xóa phiên).');
$addPara('Chỉ Quản trị xứ / Quản trị giáo lý mới cấp hoặc thu hồi được các quyền này. Bỏ chọn để thu hồi bất cứ lúc nào.');
$addNote('Các quyền trên chỉ có hiệu lực khi GLV còn phân công lớp trong năm học hiện tại.');

$section->addPageBreak();

// ===== IV =====
$section->addTitle('IV. ĐIỂM DANH HỌC SINH', 1);

$section->addTitle('1. Thiết lập phiên điểm danh', 2);
$addPara('B1: Vào Menu Hệ thống → Phiên điểm danh (hoặc từ trang Điểm danh bấm nút «Tạo phiên»).');
$addPara('B2: Chọn tab Học sinh. Bấm «Tạo phiên mới».');
$addPara('B3: Chọn loại (Đi học / Đi lễ), chế độ tạo (một ngày / theo tuần / tùy chọn ngày).');
$addBullet('Tạo cho toàn năm học: để trống bộ lọc Khối và Lớp.');
$addBullet('Tạo cho một khối: chọn Khối, để trống Lớp.');
$addBullet('Tạo cho từng lớp: chọn Khối – Lớp.');
$addPara('B4: Tạo phiên → kiểm tra lại bằng cách chọn từng lớp trên trang Phiên điểm danh hoặc Điểm danh.');
$addNew('GLV được cấp quyền «Tạo phiên điểm danh» cũng vào được màn Phiên điểm danh (điện thoại: nút «Tạo phiên mới» nằm cùng hàng với tab Học sinh / Giáo lý viên). Modal tạo phiên căn giữa màn hình, không chiếm full màn hình.');
$addNew('Trên trang Điểm danh, khi đã chọn lớp sẽ thấy nút «Tạo phiên» nếu tài khoản có quyền tạo phiên.');

$section->addTitle('2. Điểm danh', 2);
$section->addTitle('2.1. Điểm danh thủ công tích chọn (máy tính và điện thoại)', 3);
$addPara('Trên máy tính:');
$addPara('Giáo lý → Điểm danh → Chọn Khối – Lớp → Đi học (hoặc Đi lễ) → chọn ngày → nên chọn Tất cả có mặt, rồi chỉnh em Vắng / Có phép → Lưu.');
$addPara('Trên điện thoại (tài khoản GLV):');
$addPara('Điểm danh → chọn lớp → Đi học / Đi lễ → chọn ngày trên thanh ngang → Tất cả có mặt → chỉnh vắng → Lưu (thanh Lưu dính dưới màn hình).');

$section->addTitle('2.2. Điểm danh soi mã QR (điện thoại, tài khoản GLV)', 3);
$addPara('B1: Đăng nhập tài khoản GLV.');
$addPara('B2: Bấm Quét QR (nút giữa thanh dưới) → cho phép Camera → soi đúng học sinh → Lưu.');

$section->addTitle('3. Xem Nhật ký điểm danh', 2);
$addPara('Menu Hệ thống → Nhật ký điểm danh (dành cho Quản trị).');

$section->addTitle('4. Xem thống kê điểm danh', 2);
$addPara('Menu Thống kê → Điểm danh: xem theo lớp, khối hoặc toàn xứ; đi học / đi lễ; theo tuần, tháng, năm hoặc khoảng thời gian.');

$section->addPageBreak();

// ===== V (NEW) =====
$section->addTitle('V. ĐIỂM DANH GIÁO LÝ VIÊN', 1);
$addNew('Mục này bổ sung rõ trong V3 (V2 chỉ nêu nhu cầu ở phần mở đầu).');

$section->addTitle('1. Ai được tạo phiên và điểm danh GLV?', 2);
$addBullet('Quản trị xứ / Quản trị giáo lý: tạo, khóa/mở, xóa phiên GLV; điểm danh GLV.');
$addBullet('GLV có quyền «Tạo phiên điểm danh»: tạo và khóa/mở phiên GLV (không xóa).');
$addBullet('GLV có quyền «Điểm danh giáo lý viên»: vào tab Giáo lý viên trên trang Điểm danh để điểm danh.');

$section->addTitle('2. Tạo phiên điểm danh GLV', 2);
$addPara('B1: Hệ thống → Phiên điểm danh → tab Giáo lý viên.');
$addPara('B2: Tạo phiên mới → chọn loại Đi dạy / Đi lễ / Họp → chọn lịch (ngày / tuần / tùy chọn) → Tạo phiên.');
$addPara('Hoặc từ trang Điểm danh → tab Giáo lý viên → nút «Tạo phiên».');

$section->addTitle('3. Điểm danh GLV', 2);
$addPara('B1: Điểm danh → chuyển tab Giáo lý viên.');
$addPara('B2: Chọn loại buổi (Đi dạy / Đi lễ / Họp) và ngày.');
$addPara('B3: Đánh dấu có mặt / vắng → Lưu.');

$section->addPageBreak();

// ===== VI (was V) =====
$section->addTitle('VI. QUẢN LÝ ĐIỂM', 1);

$section->addTitle('1. Thiết lập cấu hình loại điểm', 2);
$addPara('Cấu hình cho toàn xứ:');
$addPara('B1: Giáo lý → Kết quả học tập → Cấu hình loại điểm → chọn Học kỳ (Tất cả khối, Tất cả lớp) → Thêm loại điểm.');
$addPara('B2: Chọn toàn xứ → Loại điểm → Thứ tự → Hệ số → Lưu.');
$addPara('B3: Kiểm tra bằng cách chọn Khối – Lớp.');
$addPara('Có thể cấu hình riêng cho một Khối hoặc một Lớp theo cùng các bước, nhưng chọn phạm vi tương ứng.');

$section->addTitle('2. Nhập điểm', 2);
$addPara('B1: Giáo lý → Kết quả học tập → Bảng điểm → Chọn Khối – Lớp – Học kỳ.');
$addPara('B2: Kéo xuống cuối danh sách, chọn hiển thị 50 để thấy hết lớp → nhập điểm → Lưu tất cả.');
$addNew('GLV được cấp «Quản lý điểm toàn giáo xứ» cũng nhập được điểm mọi lớp khi cửa sổ nhập điểm đang mở.');

$section->addTitle('3. Xem Nhật ký sửa điểm', 2);
$addPara('Menu Hệ thống → Nhật ký sửa điểm.');

$section->addTitle('4. Xem thống kê điểm', 2);
$addPara('Menu Thống kê → Điểm số: thống kê theo lớp, khối hoặc toàn xứ.');

$section->addPageBreak();

// ===== VII (was VI) =====
$section->addTitle('VII. QUẢN LÝ HỘI NHÓM', 1);

$section->addTitle('1. Tạo nhóm và buổi điểm danh', 2);
$addPara('Dùng để điểm danh hội nhóm ngoài lớp chính (Ca đoàn thiếu nhi, Tông đồ đội trưởng…).');
$addPara('B1: Menu Tiện ích → Quản lý nhóm → Thêm nhóm mới.');
$addPara('B2: Đặt tên nhóm → Loại nhóm → Thêm nhóm.');
$addPara('B3: Cột Thành viên → Thêm thành viên → tích chọn → Thêm vào nhóm.');
$addPara('B4: Buổi sinh hoạt → Tạo buổi mới → chọn lịch theo tuần hoặc ngày → Tạo buổi.');

$section->addTitle('2. Cách điểm danh', 2);
$addPara('B1: Tiện ích → Quản lý nhóm → Buổi sinh hoạt.');
$addPara('B2: Chọn đúng ngày → biểu tượng Điểm danh.');
$addPara('B3: Chọn Tất cả có mặt → chỉnh người vắng / có phép.');

$section->addPageBreak();

// ===== VIII (was VII) =====
$section->addTitle('VIII. PHỤ HUYNH TRA CỨU KẾT QUẢ', 1);
$addPara('B1: Mở https://mvgiaoxu.org → nhập số điện thoại (trong danh sách thiếu nhi) → Tra cứu.');
$addPara('B2: Xem kết quả tra cứu (điểm danh / học tập tùy dữ liệu đã nhập).');

$section->addPageBreak();

// ===== IX FAQ =====
$section->addTitle('IX. CÂU HỎI THƯỜNG GẶP (BỔ SUNG V3)', 1);

$section->addTitle('1. GLV báo chưa được phân công lớp?', 2);
$addPara('Phân công GLV vào ít nhất một lớp của năm học đang dùng (Lớp học → biểu tượng GLV). Sau đó GLV tải lại trang.');

$section->addTitle('2. Không thấy nút Tạo phiên?', 2);
$addPara('Cần quyền Quản trị giáo lý/xứ, hoặc GLV được cấp «Tạo phiên điểm danh» và còn phân công năm hiện tại. Trên trang Điểm danh phải đã chọn lớp (tab học sinh).');

$section->addTitle('3. GLV tạo được phiên GLV không?', 2);
$addPara('Có — nếu có quyền «Tạo phiên điểm danh». Vào Phiên điểm danh → tab Giáo lý viên (hoặc từ Điểm danh → tab Giáo lý viên → Tạo phiên). Xóa phiên vẫn chỉ dành cho quản trị.');

$section->addTitle('4. Ai sửa được hồ sơ học sinh toàn xứ?', 2);
$addPara('Quản trị, hoặc GLV có quyền «Sửa thông tin học sinh toàn giáo xứ». Mọi lần sửa đều ghi Nhật ký sửa học sinh.');

$section->addTextBreak(1);
$addPara('Mọi thắc mắc xin gửi vào nhóm Zalo MVGIAOXU.ORG.');
$addPara('Video chỉnh file danh sách học sinh mẫu: https://drive.google.com/file/d/1b0vDdieD1Vf0S0MHcOOb01C7cYnO23-o/view?usp=sharing');

$section->addTextBreak(2);
$section->addText(
    '— Hết tài liệu V3 —',
    ['italic' => true, 'size' => 11, 'color' => '666666'],
    ['alignment' => Jc::CENTER]
);

$outDocx = __DIR__ . '/HUONG_DAN_VGIAOXU_ORG_V3.docx';
IOFactory::createWriter($phpWord, 'Word2007')->save($outDocx);
echo "Created: {$outDocx}\n";
