<?php

/**
 * Sinh file Word: Hướng dẫn sử dụng dành cho Quản trị xứ
 * Chạy: php docs/generate-parish-admin-guide.php
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
    'QLGX — Hướng dẫn Quản trị xứ | Trang {PAGE} / {NUMPAGES}',
    ['size' => 9, 'italic' => true, 'color' => '666666'],
    ['alignment' => Jc::CENTER]
);

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
    'Dành cho Quản trị xứ',
    ['bold' => true, 'size' => 16],
    ['alignment' => Jc::CENTER, 'spaceAfter' => 400]
);
$section->addText(
    'Tài liệu hướng dẫn các thao tác thường dùng trong module Giáo lý và Giáo dân.',
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

// ===== MỤC LỤC (thủ công) =====
$section->addTitle('Mục lục', 1);
$toc = [
    '1. Giới thiệu hệ thống',
    '2. Đăng ký và đăng nhập',
    '3. Chọn phân hệ (Giáo dân / Giáo lý)',
    '4. Module Giáo lý',
    '5. Module Giáo dân',
    '6. Tài khoản và thông tin giáo xứ',
    '7. Quy trình làm việc đề xuất',
    '8. Câu hỏi thường gặp',
    '9. Liên hệ hỗ trợ',
];
foreach ($toc as $item) {
    $section->addText($item, ['size' => 12], ['spaceAfter' => 80]);
}
$section->addPageBreak();

$addPara = function (string $text, array $font = [], array $para = []) use ($section) {
    $section->addText($text, array_merge(['size' => 12], $font), array_merge(['spaceAfter' => 120, 'alignment' => Jc::BOTH], $para));
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

// ===== 1 =====
$section->addTitle('1. Giới thiệu hệ thống', 1);
$addPara('Hệ thống Quản lý giáo xứ (QLGX) giúp Quản trị xứ quản lý hai mảng chính:');
$addBullet('Giáo lý: năm học, lớp, giáo lý viên, học sinh, điểm danh, điểm số.');
$addBullet('Giáo dân: hồ sơ giáo dân, gia đình, giáo họ, hội đoàn, rao hôn phối, duyệt đăng ký.');
$addPara('Mỗi Quản trị xứ chỉ làm việc trong phạm vi giáo xứ được gắn với tài khoản của mình. Dữ liệu của giáo xứ khác không hiển thị.');
$addNote('Quản trị xứ không dùng trang quản trị tổng (Backpack /admin). Trang đó chỉ dành cho Super Admin hệ thống.');

// ===== 2 =====
$section->addTitle('2. Đăng ký và đăng nhập', 1);

$section->addTitle('2.1. Đăng ký tài khoản Quản trị xứ', 2);
$addPara('Nếu chưa có tài khoản:');
$addBullet('Vào trang Đăng ký quản trị xứ trên website.');
$addBullet('Điền thông tin theo hướng dẫn và gửi yêu cầu.');
$addBullet('Chờ Super Admin hệ thống duyệt. Sau khi được duyệt, bạn mới đăng nhập được.');

$section->addTitle('2.2. Đăng nhập', 2);
$addPara('Tại trang đăng nhập, bạn có thể dùng:');
$addBullet('Số điện thoại, hoặc');
$addBullet('Email (nếu tài khoản đã có email).');
$addPara('Nhập mật khẩu đã được cấp/thiết lập rồi chọn đăng nhập.');
$addNote('Tài khoản phải được gắn với một giáo xứ. Nếu đăng nhập báo chưa gắn giáo xứ, hãy liên hệ hỗ trợ hoặc Super Admin.');

$section->addTitle('2.3. Quên mật khẩu', 2);
$addPara('Tại trang đăng nhập, chọn chức năng quên mật khẩu (nếu có). Tài khoản chỉ đăng nhập bằng số điện thoại và không có Gmail có thể cần liên hệ hỗ trợ để được đặt lại mật khẩu.');

// ===== 3 =====
$section->addTitle('3. Chọn phân hệ (Giáo dân / Giáo lý)', 1);
$addPara('Sau khi đăng nhập thành công, hệ thống mở trang Chọn phân hệ. Quản trị xứ thường thấy hai lựa chọn:');
$addBullet('Giáo dân — quản lý hồ sơ giáo dân, gia đình, danh mục xứ…');
$addBullet('Giáo lý — quản lý năm học, lớp, GLV, học sinh, điểm danh, điểm…');
$addPara('Chọn một phân hệ để vào làm việc. Trong lúc dùng, bạn có thể quay lại “Chọn phân hệ” từ menu tài khoản (góc phải / menu người dùng) để đổi sang phân hệ còn lại.');

// ===== 4 =====
$section->addTitle('4. Module Giáo lý', 1);
$addPara('Sau khi chọn Giáo lý, sidebar (menu trái) gồm các nhóm: Trang chủ, Giáo lý, Thống kê, Tiện ích, Nhân sự, Hệ thống…');

$section->addTitle('4.1. Trang chủ Giáo lý', 2);
$addPara('Hiển thị tổng quan nhanh: năm học đang hoạt động, tình hình điểm danh, số học sinh theo khối. Dùng để nắm nhanh tình hình trong ngày/tuần.');

$section->addTitle('4.2. Năm học', 2);
$addPara('Vào Hệ thống → Năm học.');
$addBullet('Tạo năm học mới (ví dụ 2025–2026).');
$addBullet('Thiết lập / kích hoạt năm học đang dùng.');
$addBullet('Quản lý kỳ học (kỳ 1, kỳ 2) nếu hệ thống yêu cầu.');
$addBullet('Sao chép cấu trúc năm học (khi sang năm mới): copy lớp rồi xếp học sinh — giảm nhập lại từ đầu.');
$addNote('Nên kích hoạt đúng một năm học “đang dùng” để các màn hình điểm danh, điểm số, lớp học lọc đúng dữ liệu.');

$section->addTitle('4.3. Lớp học', 2);
$addPara('Vào Hệ thống → Lớp học.');
$addBullet('Tạo lớp theo năm học và khối (nếu có).');
$addBullet('Xem chi tiết lớp, xếp học sinh vào lớp.');
$addBullet('Phân công giáo lý viên cho lớp (Chủ nhiệm / Phụ trách).');
$addPara('Gợi ý thứ tự: tạo năm học → tạo lớp → gán GLV → xếp học sinh.');

$section->addTitle('4.4. Giáo lý viên (GLV)', 2);
$addPara('Vào Nhân sự → Giáo lý viên.');
$addBullet('Thêm / sửa thông tin GLV.');
$addBullet('Import danh sách từ Excel (nếu có mẫu sẵn).');
$addBullet('Tạo tài khoản đăng nhập cho GLV (thường bằng số điện thoại) để GLV tự điểm danh, xem lớp phụ trách.');
$addNote('Mật khẩu mặc định (nếu hệ thống tạo sẵn) nên được GLV đổi sau lần đăng nhập đầu. Liên hệ quản trị nếu cần reset.');

$section->addTitle('4.4.1. Cấp quyền hỗ trợ quản trị cho GLV', 3);
$addPara('Mặc định GLV chỉ điểm danh và xem lớp được phân công. Khi cần một GLV (thường là Trưởng/Phó) hỗ trợ quản trị, mở màn sửa GLV (tài khoản đã tạo) và bật ở mục "Quyền hỗ trợ quản trị":');
$addBullet('Quản lý điểm toàn giáo xứ: GLV xem, nhập và sửa điểm mọi lớp trong xứ khi cửa sổ nhập điểm đang mở.');
$addBullet('Sửa thông tin học sinh toàn giáo xứ: GLV cập nhật hồ sơ học sinh toàn xứ (không gồm tạo/xóa học sinh hay liên kết giáo dân).');
$addPara('Chỉ Quản trị xứ (và Super admin) mới cấp được các quyền này. Bỏ chọn để thu hồi bất cứ lúc nào.');
$addNote('Mọi thay đổi hồ sơ học sinh đều được ghi Nhật ký sửa học sinh (ai sửa, thời điểm, giá trị cũ/mới) để đối chiếu khi cần.');

$section->addTitle('4.5. Học sinh', 2);
$addPara('Vào Giáo lý → Quản lý học sinh.');
$addBullet('Thêm học sinh thủ công hoặc import Excel.');
$addBullet('Gán / chuyển lớp khi cần.');
$addBullet('In thẻ học sinh (nếu chức năng khả dụng).');
$addBullet('Xem thống kê học sinh.');
$addPara('Có thể xếp học sinh ngay từ màn hình lớp học (Xếp học sinh).');

$section->addTitle('4.6. Điểm danh và phiên điểm danh', 2);
$addPara('Vào Giáo lý → Điểm danh / Phiên điểm danh.');
$addBullet('Tạo hoặc chọn phiên điểm danh theo lớp / buổi.');
$addBullet('Điểm danh thủ công trên danh sách.');
$addBullet('Dùng điểm danh QR (nếu bật) để quét nhanh.');
$addBullet('Xem thống kê điểm danh theo thời gian / lớp.');

$section->addTitle('4.7. Kết quả học tập (điểm số)', 2);
$addPara('Vào Giáo lý → Kết quả học tập.');
$addBullet('Nhập / chỉnh điểm theo lớp, loại điểm, kỳ.');
$addBullet('Xem thống kê điểm, phân bố xếp loại (nếu có).');

$section->addTitle('4.8. Nhóm sinh hoạt (tiện ích)', 2);
$addPara('Vào Tiện ích → Quản lý nhóm để quản lý các nhóm ngoài lớp chính (ví dụ nhóm GLV, ca đoàn…): thành viên, buổi sinh hoạt, điểm danh nhóm nếu được hỗ trợ.');

// ===== 5 =====
$section->addTitle('5. Module Giáo dân', 1);
$addPara('Sau khi chọn Giáo dân (hoặc chuyển từ menu “Sang module Giáo dân”), sidebar gồm: Trang chủ, Giáo Dân, Gia Đình, Hệ Thống…');

$section->addTitle('5.1. Trang chủ Giáo dân', 2);
$addPara('Tổng quan số liệu giáo dân trong xứ, hỗ trợ nắm nhanh quy mô và các mục cần xử lý.');

$section->addTitle('5.2. Danh sách giáo dân', 2);
$addPara('Vào Giáo Dân → Danh sách.');
$addBullet('Tìm kiếm, lọc, thêm mới, sửa hồ sơ giáo dân.');
$addBullet('Xem chi tiết hồ sơ (thông tin cá nhân, gia đình, bí tích…).');
$addBullet('Xuất giấy tờ liên quan khi hệ thống hỗ trợ (ví dụ lý lịch / đơn rửa tội).');

$section->addTitle('5.3. Thống kê giáo dân', 2);
$addPara('Xem phân bố theo giới tính, độ tuổi, giáo họ, hội đoàn… để báo cáo mục vụ.');

$section->addTitle('5.4. Duyệt đăng ký giáo dân', 2);
$addPara('Người giáo dân có thể tự gửi yêu cầu đăng ký công khai. Quản trị xứ vào Duyệt đăng ký để:');
$addBullet('Xem yêu cầu chờ duyệt.');
$addBullet('Duyệt (tạo/ghép hồ sơ trong xứ) hoặc từ chối kèm lý do nếu cần.');

$section->addTitle('5.5. Gia đình', 2);
$addPara('Vào Gia Đình → Gia đình.');
$addBullet('Tạo / sửa hộ gia đình, gắn thành viên.');
$addBullet('Xuất sổ gia đình khi cần.');

$section->addTitle('5.6. Rao hôn phối', 2);
$addPara('Vào Gia Đình → Rao hôn phối để tạo, sửa, theo dõi các rao hôn phối; có thể tạo hồ sơ hôn phối từ rao khi quy trình xứ yêu cầu.');

$section->addTitle('5.7. Danh mục hệ thống xứ', 2);
$addPara('Vào Hệ Thống để khai báo trước khi nhập nhiều dữ liệu:');
$addBullet('Giáo họ');
$addBullet('Hội đoàn');
$addBullet('Tên thánh');
$addNote('Nên chuẩn hóa danh mục (giáo họ, tên thánh) trước khi import / nhập hàng loạt để tránh dữ liệu lộn xộn.');

// ===== 6 =====
$section->addTitle('6. Tài khoản và thông tin giáo xứ', 1);

$section->addTitle('6.1. Tài khoản cá nhân', 2);
$addPara('Trong menu người dùng, chọn Tài khoản để xem/cập nhật thông tin đăng nhập cơ bản và đổi mật khẩu (nếu được phép).');

$section->addTitle('6.2. Thông tin giáo xứ', 2);
$addPara('Quản trị xứ vào Thông tin giáo xứ để cập nhật:');
$addBullet('Tên giáo xứ, mã xứ (nếu có).');
$addBullet('Cha xứ, số điện thoại, địa chỉ.');
$addBullet('Giáo phận / giáo hạt (theo cấu hình hệ thống).');

$section->addTitle('6.3. Thông báo', 2);
$addPara('Mục Thông báo (nếu có trên menu) dùng để xem các thông báo hệ thống liên quan tới xứ hoặc tác vụ chờ xử lý.');

// ===== 7 =====
$section->addTitle('7. Quy trình làm việc đề xuất', 1);

$section->addTitle('7.1. Khi bắt đầu năm học mới (Giáo lý)', 2);
$addBullet('Kiểm tra / cập nhật Thông tin giáo xứ.');
$addBullet('Tạo và kích hoạt Năm học mới.');
$addBullet('Sao chép lớp từ năm cũ (nếu có) hoặc tạo lớp mới.');
$addBullet('Cập nhật danh sách Giáo lý viên và tài khoản đăng nhập.');
$addBullet('Phân công GLV cho từng lớp.');
$addBullet('Xếp / import học sinh vào lớp.');
$addBullet('Thiết lập phiên điểm danh; hướng dẫn GLV điểm danh (kể cả QR).');
$addBullet('Nhập điểm theo kế hoạch năm học; theo dõi thống kê.');

$section->addTitle('7.2. Khi vận hành mục vụ Giáo dân', 2);
$addBullet('Khai báo Giáo họ, Hội đoàn, Tên thánh.');
$addBullet('Nhập / import Gia đình và Giáo dân (hoặc duyệt đăng ký tự khai).');
$addBullet('Bổ sung bí tích trên hồ sơ khi có sự kiện mục vụ.');
$addBullet('Quản lý Rao hôn phối theo lịch giáo xứ.');
$addBullet('Dùng Thống kê khi cần báo cáo.');

// ===== 8 =====
$section->addTitle('8. Câu hỏi thường gặp', 1);

$section->addTitle('8.1. Không thấy menu Giáo dân hoặc Giáo lý?', 2);
$addPara('Quay lại trang Chọn phân hệ từ menu người dùng. Nếu vẫn thiếu một module, tài khoản có thể đang mang role hẹp hơn (chỉ Giáo lý hoặc chỉ Giáo dân) — liên hệ Super Admin để gán đúng role Quản trị xứ.');

$section->addTitle('8.2. GLV đăng nhập không vào được lớp?', 2);
$addPara('Kiểm tra GLV đã được phân công vào lớp thuộc năm học đang active; kiểm tra tài khoản GLV còn hiệu lực và đúng giáo xứ.');

$section->addTitle('8.3. Điểm danh / điểm số không hiện lớp?', 2);
$addPara('Kiểm tra năm học đang kích hoạt, lớp thuộc năm đó, và học sinh đã được xếp vào lớp.');

$section->addTitle('8.4. Đăng nhập bằng SĐT được không?', 2);
$addPara('Được. Hệ thống hỗ trợ đăng nhập bằng số điện thoại hoặc email tùy cách tài khoản được tạo.');

$section->addTitle('8.5. Hồ sơ giáo dân có link công khai?', 2);
$addPara('Một số đường dẫn xem hồ sơ giáo dân có thể truy cập công khai theo thiết kế hệ thống. Chỉ chia sẻ link khi thật sự cần thiết; tránh đưa thông tin nhạy cảm ra ngoài phạm vi mục vụ.');

// ===== 9 =====
$section->addTitle('9. Liên hệ hỗ trợ', 1);
$addPara('Khi gặp sự cố kỹ thuật (không đăng nhập được, thiếu quyền, lỗi import…), hãy liên hệ kênh hỗ trợ do đơn vị vận hành hệ thống cung cấp (điện thoại, email hoặc nhóm Zalo — thường hiển thị trên trang đăng nhập / quên mật khẩu).');
$addPara('Khi gửi yêu cầu hỗ trợ, nên kèm:');
$addBullet('Tên giáo xứ và số điện thoại / tài khoản đăng nhập.');
$addBullet('Mô tả ngắn thao tác đang làm và ảnh chụp màn hình lỗi (nếu có).');
$addBullet('Thời điểm xảy ra lỗi.');

$section->addTextBreak(2);
$section->addText(
    '— Hết tài liệu hướng dẫn dành cho Quản trị xứ —',
    ['italic' => true, 'size' => 11, 'color' => '666666'],
    ['alignment' => Jc::CENTER]
);

$out = __DIR__ . '/Huong-dan-su-dung-Quan-tri-xu.docx';
IOFactory::createWriter($phpWord, 'Word2007')->save($out);

echo "Created: {$out}\n";
