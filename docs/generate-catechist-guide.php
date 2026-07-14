<?php

/**
 * Sinh file Word: Hướng dẫn sử dụng dành cho Giáo lý viên
 * Chạy: php docs/generate-catechist-guide.php
 */

require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;

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
    'QLGX — Hướng dẫn Giáo lý viên | Trang {PAGE} / {NUMPAGES}',
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

// ===== BÌA =====
$section->addTextBreak(3);
$section->addText(
    'HỆ THỐNG QUẢN LÝ GIÁO XỨ (QLGX)',
    ['bold' => true, 'size' => 18, 'color' => '1E3A2F'],
    ['alignment' => Jc::CENTER, 'spaceAfter' => 200]
);
$section->addText(
    'HƯỚNG DẪN SỬ DỤNG',
    ['bold' => true, 'size' => 22, 'color' => '30B653'],
    ['alignment' => Jc::CENTER, 'spaceAfter' => 120]
);
$section->addText(
    'Dành cho Giáo lý viên (GLV)',
    ['bold' => true, 'size' => 16],
    ['alignment' => Jc::CENTER, 'spaceAfter' => 400]
);
$section->addText(
    'Tài liệu hướng dẫn các thao tác thường dùng trên điện thoại và máy tính: điểm danh, quét QR, xem học sinh, tài khoản.',
    ['size' => 12, 'italic' => true],
    ['alignment' => Jc::CENTER, 'spaceAfter' => 200]
);
$section->addText(
    'Phiên bản dành cho người dùng cuối · Tiếng Việt',
    ['size' => 11, 'color' => '666666'],
    ['alignment' => Jc::CENTER]
);
$section->addText(
    'Cập nhật: ' . date('d/m/Y'),
    ['size' => 11, 'color' => '666666'],
    ['alignment' => Jc::CENTER]
);

$section->addPageBreak();

// ===== MỤC LỤC =====
$section->addTitle('Mục lục', 1);
foreach ([
    '1. Giới thiệu vai trò Giáo lý viên',
    '2. Đăng nhập lần đầu',
    '3. Giao diện làm việc (điện thoại)',
    '4. Trang chủ',
    '5. Điểm danh',
    '6. Quét QR điểm danh',
    '7. Danh sách học sinh',
    '8. Tài khoản và thông báo',
    '9. Những việc GLV không làm trên hệ thống',
    '10. Quy trình một buổi học điển hình',
    '11. Câu hỏi thường gặp',
    '12. Liên hệ hỗ trợ',
] as $item) {
    $section->addText($item, ['size' => 12], ['spaceAfter' => 80]);
}
$section->addPageBreak();

// ===== 1 =====
$section->addTitle('1. Giới thiệu vai trò Giáo lý viên', 1);
$addPara('Tài khoản Giáo lý viên (GLV) dùng để vận hành buổi học trong phạm vi giáo xứ được gán:');
$addBullet('Điểm danh học sinh (thủ công hoặc quét QR).');
$addBullet('Xem danh sách và thông tin liên hệ học sinh.');
$addBullet('Nhận thông báo liên quan công việc.');
$addPara('Khác với Quản trị xứ / Quản trị giáo lý: GLV không tạo năm học, không tạo lớp, không phân công GLV, không nhập điểm, không quản lý giáo dân.');
$addNote('Chỉ thấy dữ liệu của giáo xứ mình. Nếu vừa có vai trò quản trị vừa GLV, giao diện và quyền sẽ theo mức quản trị (sidebar đầy đủ hơn). Tài liệu này dành cho tài khoản GLV thuần.');

// ===== 2 =====
$section->addTitle('2. Đăng nhập lần đầu', 1);

$section->addTitle('2.1. Nhận tài khoản', 2);
$addPara('Quản trị xứ (hoặc Quản trị giáo lý) tạo tài khoản GLV rồi gửi cho bạn:');
$addBullet('Thường đăng nhập bằng số điện thoại.');
$addBullet('Cũng có thể dùng email nếu tài khoản đã cấu hình email.');
$addBullet('Mật khẩu ban đầu do quản trị cung cấp (thường là mật khẩu mặc định khi import/tạo hàng loạt).');

$section->addTitle('2.2. Cách đăng nhập', 2);
$addBullet('Mở trang đăng nhập hệ thống QLGX.');
$addBullet('Nhập số điện thoại hoặc email + mật khẩu.');
$addBullet('Sau khi đăng nhập thành công, GLV thuần vào thẳng Trang chủ Giáo lý viên (không cần chọn phân hệ Giáo dân/Giáo lý).');
$addNote('Tài khoản phải được gắn giáo xứ. Nếu báo chưa gán giáo xứ, liên hệ Quản trị xứ.');

$section->addTitle('2.3. Đổi mật khẩu ngay lần đầu', 2);
$addPara('Vào Tài khoản (biểu tượng avatar / menu) → đổi mật khẩu mới (ít nhất 8 ký tự). Không nên giữ mật khẩu mặc định lâu ngày.');

// ===== 3 =====
$section->addTitle('3. Giao diện làm việc (điện thoại)', 1);
$addPara('Giao diện GLV thiết kế ưu tiên điện thoại. Dưới màn hình thường có thanh điều hướng nhanh:');
$addBullet('Trang chủ');
$addBullet('Học sinh');
$addBullet('Quét QR (nút nổi giữa)');
$addBullet('Điểm danh');
$addBullet('Lối tắt khác (tùy phiên bản giao diện)');
$addPara('Vuốt hoặc bấm avatar để mở menu phụ: Tài khoản, Thông báo, Đăng xuất.');
$addNote('Trên máy tính vẫn dùng được các chức năng tương tự; bố cục có thể rộng hơn nhưng các mục chính không đổi.');

// ===== 4 =====
$section->addTitle('4. Trang chủ', 1);
$addPara('Trang chủ chào bạn theo tên và vai trò Giáo lý viên, đồng thời có lối tắt tới:');
$addBullet('Quét QR');
$addBullet('Điểm danh');
$addBullet('Học sinh');
$addPara('Dùng trang chủ để bắt đầu nhanh trước giờ học.');

// ===== 5 =====
$section->addTitle('5. Điểm danh', 1);

$section->addTitle('5.1. Vào trang điểm danh', 2);
$addPara('Chọn menu Điểm danh. Hệ thống hiển thị theo năm học đang hoạt động của giáo xứ (GLV không tự đổi năm học).');

$section->addTitle('5.2. Chọn lớp và buổi', 2);
$addBullet('Chọn lớp cần điểm danh. Lớp bạn được phân công thường được chọn sẵn / ưu tiên.');
$addBullet('Chọn loại buổi nếu có (ví dụ đi học / đi lễ — tùy cấu hình xứ).');
$addBullet('Kiểm tra ngày / phiên buổi trong ngày.');

$section->addTitle('5.3. Đánh dấu và lưu', 2);
$addBullet('Đánh dấu trạng thái từng học sinh trên danh sách (có mặt / vắng / muộn… tùy tùy chọn hệ thống).');
$addBullet('Nhấn Lưu để ghi nhận.');
$addBullet('Có thể xuất / xem lại điểm danh nếu chức năng khả dụng trên trang.');
$addNote('Nên lưu ngay sau khi điểm danh xong, tránh thoát giữa chừng làm mất thao tác chưa lưu.');

$section->addTitle('5.4. Thống kê điểm danh', 2);
$addPara('Từ trang điểm danh có thể mở Thống kê điểm danh để xem tỷ lệ chuyên cần theo thời gian / lớp (trong phạm vi bạn được phép xem).');

// ===== 6 =====
$section->addTitle('6. Quét QR điểm danh', 1);
$addPara('Dùng khi học sinh có thẻ QR:');
$addBullet('Vào Quét QR (nút giữa thanh điều hướng hoặc lối tắt Trang chủ).');
$addBullet('Cho phép trình duyệt / ứng dụng dùng camera khi được hỏi.');
$addBullet('Hướng camera vào mã QR trên thẻ học sinh.');
$addBullet('Hệ thống ghi nhận điểm danh nếu đã có buổi điểm danh phù hợp trong ngày cho lớp.');
$addNote('Nếu quét không nhận: kiểm tra camera, ánh sáng, thẻ còn hiệu lực, và buổi điểm danh của lớp hôm nay đã được tạo/sẵn sàng chưa. Khi cần, quay lại điểm danh thủ công.');

// ===== 7 =====
$section->addTitle('7. Danh sách học sinh', 1);
$addPara('Vào Học sinh để xem danh sách dạng thẻ / danh sách:');
$addBullet('Lọc theo lớp khi cần.');
$addBullet('Mở chi tiết học sinh: tên thánh, họ tên, ngày sinh, liên hệ, lớp…');
$addPara('Mục đích chính: gọi tên, liên hệ phụ huynh, kiểm tra đúng lớp trước / trong / sau buổi học.');
$addNote('GLV chỉ xem thông tin. Việc thêm, sửa, xóa, import học sinh hoặc in thẻ do Quản trị xứ / Quản trị giáo lý thực hiện. Nếu thấy nút Sửa/Xóa nhưng không lưu được, đó là do quyền hạn chế — hãy nhờ quản trị.');

// ===== 8 =====
$section->addTitle('8. Tài khoản và thông báo', 1);

$section->addTitle('8.1. Tài khoản', 2);
$addPara('Trong Tài khoản bạn có thể:');
$addBullet('Cập nhật họ tên hiển thị.');
$addBullet('Cập nhật email đăng nhập (nếu dùng).');
$addBullet('Đổi ảnh đại diện (nếu có).');
$addBullet('Đổi mật khẩu.');

$section->addTitle('8.2. Thông báo', 2);
$addPara('Chuông thông báo / mục Thông báo dùng để xem các thông tin hệ thống gửi tới (ví dụ tóm tắt liên quan điểm danh, tùy cấu hình). Nên kiểm tra định kỳ.');

// ===== 9 =====
$section->addTitle('9. Những việc GLV không làm trên hệ thống', 1);
$addPara('Để tránh nhầm lẫn với trang quản trị, GLV không thực hiện các việc sau (nhờ Quản trị xứ / Quản trị giáo lý):');
$addBullet('Tạo / sửa / kích hoạt năm học.');
$addBullet('Tạo lớp, xếp lớp hàng loạt, phân công GLV.');
$addBullet('Tạo tài khoản GLV khác, import danh sách GLV.');
$addBullet('Thêm / sửa / xóa / import học sinh; in thẻ hàng loạt.');
$addBullet('Nhập điểm / xem quản lý kết quả học tập (module điểm số của quản trị).');
$addBullet('Quản lý cấu hình phiên điểm danh nâng cao của toàn xứ (nếu chỉ hiện trên trang quản trị).');
$addBullet('Quản lý giáo dân, gia đình, giáo họ, hội đoàn.');
$addBullet('Sửa Thông tin giáo xứ.');

// ===== 10 =====
$section->addTitle('10. Quy trình một buổi học điển hình', 1);
$addBullet('Đăng nhập bằng SĐT hoặc email.');
$addBullet('Vào Điểm danh → chọn đúng lớp → kiểm tra buổi hôm nay.');
$addBullet('Trong buổi: Quét QR và/hoặc điểm danh tay → Lưu.');
$addBullet('Khi cần gọi phụ huynh: vào Học sinh → chi tiết → xem số điện thoại.');
$addBullet('Kết thúc: kiểm tra lại danh sách điểm danh đã lưu.');
$addBullet('Đăng xuất nếu dùng máy dùng chung.');

// ===== 11 =====
$section->addTitle('11. Câu hỏi thường gặp', 1);

$section->addTitle('11.1. Đăng nhập không được?', 2);
$addPara('Kiểm tra đúng SĐT/email, mật khẩu; nhờ quản trị xác nhận tài khoản còn hiệu lực và đã gắn giáo xứ.');

$section->addTitle('11.2. Không thấy lớp của mình?', 2);
$addPara('Nhờ quản trị kiểm tra bạn đã được phân công vào lớp thuộc năm học đang hoạt động.');

$section->addTitle('11.3. Quét QR báo lỗi / không ghi nhận?', 2);
$addPara('Kiểm tra camera, thẻ QR, và buổi điểm danh của lớp trong ngày. Thử điểm danh thủ công rồi báo quản trị nếu lỗi lặp lại.');

$section->addTitle('11.4. Muốn nhập điểm số?', 2);
$addPara('Tài khoản GLV thuần không vào được màn nhập điểm. Liên hệ Quản trị giáo lý / Quản trị xứ.');

$section->addTitle('11.5. Quên mật khẩu?', 2);
$addPara('Dùng chức năng quên mật khẩu nếu tài khoản có email nhận được; nếu chỉ dùng SĐT, liên hệ Quản trị xứ hoặc kênh hỗ trợ hệ thống để được đặt lại.');

$section->addTitle('11.6. Thấy nút Sửa học sinh nhưng không lưu được?', 2);
$addPara('Đúng với quyền GLV: chỉ xem. Cần chỉnh thông tin thì nhờ quản trị cập nhật hồ sơ.');

// ===== 12 =====
$section->addTitle('12. Liên hệ hỗ trợ', 1);
$addPara('Khi gặp sự cố:');
$addBullet('Ưu tiên hỏi Quản trị xứ / Quản trị giáo lý (người tạo tài khoản và phân công lớp).');
$addBullet('Nếu lỗi kỹ thuật toàn hệ thống: dùng kênh hỗ trợ trên trang đăng nhập (điện thoại, email, Zalo — nếu đơn vị vận hành có cấu hình).');
$addPara('Khi báo lỗi, nên gửi kèm: tên giáo xứ, SĐT đăng nhập, lớp đang dạy, mô tả ngắn và ảnh chụp màn hình.');

$section->addTextBreak(2);
$section->addText(
    '— Hết tài liệu hướng dẫn dành cho Giáo lý viên —',
    ['italic' => true, 'size' => 11, 'color' => '666666'],
    ['alignment' => Jc::CENTER]
);

$out = __DIR__ . '/Huong-dan-su-dung-Giao-ly-vien.docx';
IOFactory::createWriter($phpWord, 'Word2007')->save($out);

echo "Created: {$out}\n";
