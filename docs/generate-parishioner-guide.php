<?php

/**
 * Sinh file Word: Hướng dẫn sử dụng dành cho Giáo dân / Phụ huynh
 * Chạy: php docs/generate-parishioner-guide.php
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
    'QLGX — Hướng dẫn Giáo dân / Phụ huynh | Trang {PAGE} / {NUMPAGES}',
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
    'Dành cho Giáo dân / Phụ huynh',
    ['bold' => true, 'size' => 16],
    ['alignment' => Jc::CENTER, 'spaceAfter' => 400]
);
$section->addText(
    'Tài liệu hướng dẫn các thao tác công khai: tra cứu kết quả giáo lý của con, khai báo sổ gia đình, xem hồ sơ khi được gửi link.',
    ['size' => 12, 'italic' => true],
    ['alignment' => Jc::CENTER, 'spaceAfter' => 200]
);
$section->addText(
    'Không cần tài khoản đăng nhập · Tiếng Việt',
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
    '1. Giới thiệu',
    '2. Tra cứu kết quả giáo lý của con (trang chủ)',
    '3. Đăng ký / khai báo sổ gia đình',
    '4. Xem hồ sơ giáo dân (khi có link)',
    '5. Những việc cần nhờ văn phòng xứ',
    '6. Câu hỏi thường gặp',
    '7. Liên hệ hỗ trợ',
] as $item) {
    $section->addText($item, ['size' => 12], ['spaceAfter' => 80]);
}
$section->addPageBreak();

// ===== 1 =====
$section->addTitle('1. Giới thiệu', 1);
$addPara('Hệ thống QLGX hỗ trợ giáo dân và phụ huynh một số thao tác trên website mà không cần tạo tài khoản đăng nhập.');
$addPara('Bạn có thể:');
$addBullet('Tra cứu hồ sơ giáo lý, điểm danh và kết quả học tập của con bằng số điện thoại phụ huynh.');
$addBullet('Khai báo / đăng ký sổ gia đình trực tuyến (chờ giáo xứ duyệt).');
$addBullet('Xem hồ sơ giáo dân nếu văn phòng xứ gửi đường dẫn xem.');
$addNote('Nút “Đăng nhập” trên website dành cho Quản trị xứ, Giáo lý viên và nhân sự quản trị — không phải cổng đăng nhập riêng của giáo dân thường.');

// ===== 2 =====
$section->addTitle('2. Tra cứu kết quả giáo lý của con (trang chủ)', 1);

$section->addTitle('2.1. Điều kiện để tra cứu được', 2);
$addPara('Số điện thoại bạn nhập phải là số đã được ghi nhận khi nhập học / trong hồ sơ học sinh (SĐT phụ huynh). Nếu chưa cập nhật SĐT trong hệ thống, cần nhờ văn phòng xứ bổ sung rồi mới tra cứu được.');

$section->addTitle('2.2. Các bước thực hiện', 2);
$addBullet('Mở trang chủ website giáo xứ / hệ thống QLGX (thường là địa chỉ trang chủ / ).');
$addBullet('Nhập số điện thoại phụ huynh (khoảng 9–15 chữ số, không cần dấu cách).');
$addBullet('Xác nhận / tìm kiếm theo hướng dẫn trên màn hình.');
$addBullet('Nếu cùng một SĐT gắn với nhiều học viên, chọn đúng con cần xem.');
$addBullet('Xem các phần thông tin: Hồ sơ, Điểm danh, Kết quả học tập.');

$section->addTitle('2.3. Các thông tin thường thấy', 2);
$addBullet('Hồ sơ: tên thánh, họ tên, ngày sinh, thông tin bố/mẹ, giáo xứ / giáo họ, lớp…');
$addBullet('Điểm danh: tình hình đi học / đi lễ trong năm học hiện tại (tùy dữ liệu xứ đã điểm danh).');
$addBullet('Kết quả học tập: điểm theo năm học / lớp / học kỳ (nếu xứ đã nhập điểm).');
$addNote('Nếu không thấy dữ liệu: kiểm tra lại đúng SĐT đã đăng ký với xứ; hoặc liên hệ văn phòng xứ / kênh hỗ trợ.');

// ===== 3 =====
$section->addTitle('3. Đăng ký / khai báo sổ gia đình', 1);

$section->addTitle('3.1. Mục đích', 2);
$addPara('Giáo dân tự gửi thông tin hộ gia đình lên hệ thống để văn phòng xứ / Quản trị giáo dân xem xét và cập nhật sổ. Đây không phải đăng ký tài khoản đăng nhập.');

$section->addTitle('3.2. Mở form đăng ký', 2);
$addBullet('Từ trang chủ, chọn Đăng ký sổ gia đình (hoặc đường dẫn dạng /dang-ky-giao-dan).');
$addBullet('Nếu được gửi link theo từng giáo xứ, mở đúng link đó để khỏi chọn nhầm xứ.');

$section->addTitle('3.3. Các bước trên form (4 bước)', 2);

$section->addTitle('Bước 1 — Thông tin hộ gia đình', 3);
$addBullet('Chọn giáo xứ (bắt buộc nếu form có nhiều xứ).');
$addBullet('Xem mã gia đình (hệ thống có thể tự tạo để theo dõi).');
$addBullet('Điền tên hộ, giáo họ, địa chỉ, tỉnh/thành, xã/phường (theo mức độ bắt buộc trên màn hình).');

$section->addTitle('Bước 2 — Thành viên trong hộ', 3);
$addPara('Cần ít nhất một thành viên. Với mỗi người, thường điền:');
$addBullet('Họ đệm, Tên, Giới tính (thường bắt buộc).');
$addBullet('Vai trò trong hộ: Chồng / Vợ / Con / Khác.');
$addBullet('Tên thánh, ngày/nơi sinh, thứ tự con, cha/mẹ, hội đoàn, CCCD, bí tích… (theo hướng dẫn trên form, có thể tùy chọn).');

$section->addTitle('Bước 3 — Hôn phối (nếu có)', 3);
$addPara('Có thể bỏ qua nếu không khai. Nếu thêm bản ghi hôn phối, điền đủ các mục bắt buộc (ví dụ thông tin chồng/vợ) theo form.');

$section->addTitle('Bước 4 — Gửi yêu cầu', 3);
$addBullet('Nhập số điện thoại liên hệ (bắt buộc).');
$addBullet('Chọn người đại diện đăng ký trong hộ (bắt buộc theo form).');
$addBullet('Gửi yêu cầu và giữ lại mã theo dõi / mã gia đình hiện ra sau khi gửi thành công.');
$addNote('Yêu cầu cần Quản trị giáo dân / Quản trị xứ duyệt. Gửi quá nhiều lần trong thời gian ngắn có thể bị giới hạn (ví dụ theo địa chỉ mạng). Hãy điền đúng và gửi một lần sạch sẽ.');

$section->addTitle('3.4. Sau khi gửi', 2);
$addBullet('Ghi lại mã theo dõi để hỏi văn phòng xứ khi cần.');
$addBullet('Chờ thông báo / phản hồi từ giáo xứ (duyệt hoặc yêu cầu bổ sung).');
$addBullet('Không dùng form này để “đăng nhập” lần sau — đây chỉ là phiếu khai báo.');

// ===== 4 =====
$section->addTitle('4. Xem hồ sơ giáo dân (khi có link)', 1);
$addPara('Một số hồ sơ giáo dân có thể xem qua đường dẫn dạng /giao-dan/{số-id} mà không cần đăng nhập.');
$addBullet('Chỉ mở link do văn phòng xứ hoặc người có trách nhiệm gửi cho bạn.');
$addBullet('Có thể xem các mục như thông tin cơ bản, sinh hoạt xứ, bí tích, hôn phối, gia đình… (tùy dữ liệu đã nhập).');
$addBullet('Khách xem thường không sửa, không xóa, không xuất giấy tờ nội bộ — các thao tác đó dành cho nhân sự có tài khoản quản trị.');
$addNote('Không chia sẻ lung tung link hồ sơ vì có thể chứa thông tin cá nhân mục vụ.');

// ===== 5 =====
$section->addTitle('5. Những việc cần nhờ văn phòng xứ', 1);
$addPara('Các việc sau giáo dân / phụ huynh không tự làm trên cổng công khai — hãy liên hệ văn phòng xứ hoặc Quản trị:');
$addBullet('Sửa sai sót hồ sơ giáo dân / học sinh đã có trong sổ.');
$addBullet('Cập nhật số điện thoại phụ huynh để tra cứu giáo lý.');
$addBullet('In thẻ học sinh, cấp / đổi thẻ QR điểm danh.');
$addBullet('Nhập / chỉnh điểm, điểm danh hộ (do GLV / quản trị thao tác).');
$addBullet('Rao hôn phối, xuất lý lịch / giấy tờ hành chính theo quy định xứ.');
$addBullet('Xin tài khoản Quản trị xứ (chỉ khi được giao nhiệm vụ quản trị — có form đăng ký riêng, phải được duyệt).');

// ===== 6 =====
$section->addTitle('6. Câu hỏi thường gặp', 1);

$section->addTitle('6.1. Không tra cứu được bằng SĐT?', 2);
$addPara('Kiểm tra đúng số đã đăng ký với xứ; thử bỏ số 0 đầu hoặc ngược lại theo hướng dẫn; nếu vẫn không được, nhờ văn phòng kiểm tra hồ sơ học sinh đã gắn SĐT chưa.');

$section->addTitle('6.2. Thấy nhiều học viên cùng một SĐT?', 2);
$addPara('Chọn đúng tên con cần xem. Nhiều con có thể dùng chung SĐT phụ huynh.');

$section->addTitle('6.3. Đã gửi đăng ký sổ nhưng chưa thấy trên xứ?', 2);
$addPara('Yêu cầu đang chờ duyệt. Liên hệ văn phòng xứ kèm mã theo dõi để được kiểm tra.');

$section->addTitle('6.4. Có cần tạo mật khẩu để xem điểm danh của con không?', 2);
$addPara('Không. Phụ huynh tra cứu trên trang chủ bằng SĐT. Không dùng tài khoản Giáo lý viên / Quản trị trừ khi được giao nhiệm vụ.');

$section->addTitle('6.5. Có phải đăng ký quản trị xứ để khai sổ gia đình không?', 2);
$addPara('Không. Đăng ký sổ gia đình là form công khai. Đăng ký quản trị xứ là xin quyền quản trị hệ thống — khác mục đích.');

// ===== 7 =====
$section->addTitle('7. Liên hệ hỗ trợ', 1);
$addPara('Khi cần giúp đỡ:');
$addBullet('Ưu tiên liên hệ văn phòng giáo xứ (nơi lưu sổ và duyệt đăng ký).');
$addBullet('Trên trang chủ thường có khối “Cần hỗ trợ?” với điện thoại, email hoặc Zalo do đơn vị vận hành cấu hình.');
$addPara('Khi liên hệ, nên nêu rõ: giáo xứ, họ tên, SĐT đã dùng để tra cứu / đăng ký, mã theo dõi (nếu có), và mô tả ngắn vấn đề.');

$section->addTextBreak(2);
$section->addText(
    '— Hết tài liệu hướng dẫn dành cho Giáo dân / Phụ huynh —',
    ['italic' => true, 'size' => 11, 'color' => '666666'],
    ['alignment' => Jc::CENTER]
);

$out = __DIR__ . '/Huong-dan-su-dung-Giao-dan-Phu-huynh.docx';
IOFactory::createWriter($phpWord, 'Word2007')->save($out);

echo "Created: {$out}\n";
