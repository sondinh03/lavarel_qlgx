# README-DEV — Tài liệu cho lập trình viên tiếp quản

Tài liệu này dành cho **người mới hoàn toàn** tiếp quản dự án. Mục tiêu: đọc xong là chạy được hệ thống ở máy local và hiểu bố cục tổng thể. Các tài liệu khác (kiến trúc nghiệp vụ, triển khai prod, bí mật) được tách riêng — xem mục [Tài liệu liên quan](#tài-liệu-liên-quan).

> Cập nhật lần cuối: 07/2026

---

## 1. Hệ thống này là gì

Phần mềm **Quản lý Giáo xứ (QLGX)** — ứng dụng web quản lý hoạt động của một/nhiều giáo xứ Công giáo, gồm hai mảng nghiệp vụ chính:

- **Giáo lý**: năm học, lớp giáo lý, giáo lý viên (GLV), học sinh, điểm danh (thủ công + QR), điểm số/kết quả học tập.
- **Giáo dân**: hồ sơ giáo dân, gia đình, giáo họ, hội đoàn, bí tích, hôn phối, rao hôn phối.

Hệ thống đa giáo xứ (multi-parish): mỗi tài khoản gắn với một giáo xứ (`parish_id`) và chỉ thấy dữ liệu của giáo xứ mình (trừ Super Admin).

### Vai trò người dùng (roles)

| Role | Mô tả |
|------|-------|
| `super_admin` | Quản trị toàn hệ thống, mọi giáo xứ. Dùng khu admin (Backpack). |
| `parish_admin` | Quản trị một giáo xứ (cả 2 module). |
| `catechism_admin` | Quản trị mảng Giáo lý của giáo xứ. |
| `parishioner_admin` | Quản trị mảng Giáo dân của giáo xứ. |
| `catechist` | Giáo lý viên (GLV) — điểm danh, xem học sinh; có thể được cấp thêm quyền hỗ trợ. |

> Chi tiết luật phân quyền (đặc biệt ràng buộc "GLV chưa được phân công lớp trong năm học hiện tại thì không thao tác được gì") nằm trong `ARCHITECTURE.md`. Cổng phân quyền trung tâm là `app/Services/CatechistAccess.php`.

---

## 2. Công nghệ (stack)

| Thành phần | Phiên bản / ghi chú |
|------------|---------------------|
| PHP | `^7.3 \|\| ^8.0` (khuyến nghị PHP 8.1+) |
| Laravel | `^8.75` |
| Livewire | `2.12` — phần lớn UI nghiệp vụ mới |
| Backpack for Laravel | CRUD `4.1.*` — khu quản trị `super_admin` (`/admin`) |
| Spatie Laravel Permission | qua `backpack/permissionmanager` — roles & permissions |
| MySQL | 5.7+ / 8.0 |
| Frontend build | Laravel Mix (webpack) + Tailwind CSS 3, Alpine.js, Bootstrap 5 (khu Backpack) |
| Biểu đồ | ApexCharts, Chart.js |
| Xuất file | maatwebsite/excel (Excel), phpoffice/phpword (Word), simple-qrcode (QR) |
| Debug/giám sát | Laravel Telescope (chỉ bật ở local) |

Nguồn chuẩn: `composer.json` và `package.json`.

---

## 3. Cài đặt & chạy ở local (từ số 0)

### 3.1. Yêu cầu máy

- PHP 8.1+ kèm các extension thường dùng: `pdo_mysql`, `mbstring`, `openssl`, `gd`, `zip`, `bcmath`, `fileinfo`, `curl`.
- Composer 2.
- Node.js 16+ và npm.
- MySQL đang chạy.

### 3.2. Các bước

```bash
# 1. Lấy mã nguồn
git clone <URL-repo> lavarel_qlgx
cd lavarel_qlgx

# 2. Cài PHP dependencies
composer install

# 3. Cài JS dependencies và build asset
npm install
npm run dev          # build 1 lần cho môi trường dev
# hoặc: npm run watch  (tự build lại khi sửa file)
# hoặc: npm run prod   (build tối ưu cho production)

# 4. Tạo file cấu hình môi trường
cp .env.example .env

# 5. Sinh khóa ứng dụng
php artisan key:generate
```

### 3.3. Cấu hình `.env` cho local

Mở `.env` và chỉnh tối thiểu phần database:

```env
APP_NAME="QLGX"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=quan_ly_giao_ly      # tạo sẵn database rỗng này trong MySQL
DB_USERNAME=root
DB_PASSWORD=                     # mật khẩu MySQL của bạn

QUEUE_CONNECTION=sync            # KHÔNG có queue worker — job chạy đồng bộ trong request
MAIL_MAILER=log                  # local: email chỉ ghi vào storage/logs, không gửi thật
```

> **Lưu ý bảo mật:** file `.env` ở prod chứa mật khẩu DB và SMTP thật — không bao giờ commit lên git. Xem `DEPLOYMENT.md` / tài liệu bí mật bàn giao riêng.

### 3.4. Tạo bảng & dữ liệu nền

```bash
# Tạo database rỗng trước (ví dụ trong MySQL):
#   CREATE DATABASE quan_ly_giao_ly CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

php artisan migrate
```

Các **role** (`super_admin`, `parish_admin`, `catechism_admin`, `parishioner_admin`, `catechist`) được tạo tự động qua migration (dùng `Role::findOrCreate`), nên sau `migrate` là đã có sẵn.

> **Quan trọng:** `DatabaseSeeder` hiện **rỗng** — `migrate` không tạo tài khoản đăng nhập nào. Bạn phải tự tạo một tài khoản Super Admin lần đầu (xem 3.5).

### 3.5. Tạo tài khoản Super Admin đầu tiên

Chưa có seeder cho việc này, làm thủ công qua Tinker:

```bash
php artisan tinker
```

```php
$u = \App\Models\User::create([
    'name'      => 'Super Admin',
    'email'     => 'admin@local.test',
    'password'  => \Illuminate\Support\Facades\Hash::make('password'),
    'is_active' => true,
]);
$u->assignRole('super_admin');
```

> Lưu ý: model `User` có mutator tránh hash lại mật khẩu đã hash. Khi tạo qua Tinker như trên thì dùng `Hash::make(...)` là đúng.

### 3.6. Chạy server

```bash
php artisan serve
# Mở http://localhost:8000
```

Đăng nhập bằng tài khoản vừa tạo. Super Admin sẽ được đưa về khu `/admin` (Backpack); các vai trò khác vào trang chọn module.

---

## 4. Chạy test

Test dùng database MySQL **riêng** (không phải DB dev), cấu hình trong `.env.testing`.

```bash
# Tạo database test (ví dụ ql_giao_ly_test) rồi chỉnh .env.testing cho khớp
php artisan test                                   # chạy toàn bộ
php artisan test tests/Feature/CatechistAuthorizationMatrixTest.php   # 1 file
```

Ghi chú:
- Test dùng `DatabaseTransactions` — cần một DB test đã `migrate` sẵn.
- Một số test cũ phụ thuộc dữ liệu seed sẵn (ví dụ `AttendancePageTest` cần `User::find(13)`), và vài test module Giáo dân đang fail do vấn đề schema/seed môi trường — xem `KNOWN-ISSUES.md`. Bộ test phân quyền GLV (`CatechistAuthorizationMatrixTest`, `CatechistLivewireScopeTest`) là phần đáng tin và nên giữ xanh.

---

## 5. Bố cục mã nguồn

```
app/
  Http/Livewire/        Component Livewire (UI nghiệp vụ chính)
    Base/BaseComponent.php   Lớp cha: auth, phân trang, cổng phân quyền GLV
    AttendanceManager.php    Điểm danh
    Attendance/              QR, thống kê, phiên điểm danh, nhật ký
    Score/                   Nhập điểm, thống kê, nhật ký
    Student/                 Danh sách/hồ sơ/thống kê học sinh
    Teacher/                 Quản lý GLV
    Parishioners/            Module giáo dân
    Dashboard/               Dashboard theo vai trò
    Help/                    Trang hướng dẫn trực quan (/tro-giup)
  Services/
    CatechistAccess.php      *** Cổng phân quyền GLV — đọc kỹ file này ***
    SchoolYearResolver.php   Xác định năm học đang vận hành của giáo xứ
  Policies/                  Authorization theo model (Student, Parishioner, ...)
  Actions/                   Nghiệp vụ đóng gói (duyệt/từ chối đăng ký, ...)
  Notifications/             Thông báo (database + mail)
  Models/                    Eloquent models
  Support/Helps.php          Helper toàn cục (autoload), vd notify_users()
routes/web.php               Toàn bộ route (URL tiếng Việt)
resources/views/livewire/    Blade cho component Livewire
database/migrations/         ~170 migration (gồm cả tạo role)
docs/                        Tài liệu + script sinh file hướng dẫn .docx
tests/                       Unit + Feature test
```

Khu admin cũ (`super_admin`) chạy trên **Backpack** ở prefix `/admin`; phần nghiệp vụ mới (parish_admin/GLV/giáo dân) chạy trên **Livewire** với URL tiếng Việt không prefix.

---

## 6. Các URL / route chính

Định nghĩa trong `routes/web.php`. Một số điểm vào tiêu biểu:

| URL | Mô tả | Quyền |
|-----|-------|-------|
| `/` | Trang landing | Khách |
| `/dang-ky-giao-dan` | Giáo dân tự đăng ký | Khách |
| `/dang-ky-quan-tri-xu` | Đăng ký quản trị xứ | Khách |
| `/admin` | Khu quản trị Backpack | `super_admin` |
| `/parish-admin-dashboard` | Dashboard quản trị xứ | `parish_admin`, `catechism_admin` |
| `/bang-dieu-khien` | Dashboard GLV | `catechist` |
| `/diem-danh`, `/diem-danh/qr`, `/diem-danh/thong-ke` | Điểm danh | GLV + quản trị |
| `/hoc-sinh` | Danh sách học sinh | GLV + quản trị |
| `/diem-so` | Nhập điểm | GLV + quản trị |
| `/lop-hoc`, `/giao-ly-vien`, `/nam-hoc` | Quản trị giáo lý | `parish_admin`, `catechism_admin` |
| `/tro-giup/*` | Trang hướng dẫn trực quan | quản trị giáo lý |

Có nhiều `Route::redirect` từ URL tiếng Anh cũ sang URL tiếng Việt mới (giữ tương thích link cũ).

---

## 7. Điểm cần biết trước khi sửa code

- **`QUEUE_CONNECTION=sync`**: không có hàng đợi. Email/notification gửi ngay trong request. Nếu SMTP lỗi, xem `storage/logs/laravel.log`.
- **Email khi duyệt đăng ký** được bọc trong `try/catch` + `report($e)` — duyệt vẫn thành công dù mail lỗi. Đừng nhầm "duyệt OK" là "đã gửi mail OK".
- **Notification** thường dùng cả kênh `database` và `mail`; bản ghi ở bảng `notifications`.
- **Cổng phân quyền GLV** tập trung ở `CatechistAccess`. UI chỉ hiển thị cờ `assignmentBlocked` (tính trong `BaseComponent`); enforcement thật nằm ở Service + Policy + các điểm ghi dữ liệu. Không nới lỏng Service khi chỉ muốn đổi UI.
- **Tài liệu người dùng cuối** (`docs/*.docx`) được sinh từ script PHP: chỉnh nội dung trong `docs/generate-*.php` rồi chạy `php docs/generate-<tên>.php` để tạo lại file `.docx`. Đừng sửa tay file `.docx`.
- **Telescope** chỉ nên bật ở local (`TELESCOPE_ENABLED`).

---

## 8. Lệnh hay dùng

```bash
php artisan serve                 # chạy dev server
npm run watch                     # tự build asset khi sửa
php artisan migrate               # chạy migration
php artisan migrate:fresh         # DROP hết & tạo lại (mất dữ liệu — chỉ local!)
php artisan tinker                # REPL để tạo/kiểm tra dữ liệu
php artisan optimize:clear        # xóa cache config/route/view khi "sửa mà không thấy đổi"
php artisan test                  # chạy test

# Sinh lại tài liệu hướng dẫn .docx
php docs/generate-catechist-guide.php
php docs/generate-parish-admin-guide.php
php docs/generate-parishioner-guide.php
```

---

## 9. Xử lý sự cố thường gặp

| Triệu chứng | Nguyên nhân / cách xử lý |
|-------------|--------------------------|
| Trang trắng / lỗi 500 khi mới cài | Chưa `key:generate`, sai thông tin DB, hoặc chưa `migrate`. |
| CSS/JS không có style | Chưa `npm run dev`/`prod`, hoặc cache view cũ → `php artisan optimize:clear`. |
| "Class/route not found" sau khi kéo code mới | `composer dump-autoload`, `php artisan optimize:clear`. |
| Đăng nhập xong không vào được đâu | Tài khoản chưa gán role, hoặc chưa gán `parish_id` (trừ super_admin). |
| Sửa code mà không thấy thay đổi | Cache: `php artisan optimize:clear`, và kiểm tra `npm run watch` còn chạy. |
| Email không đến (local) | Bình thường: `MAIL_MAILER=log` chỉ ghi log, không gửi. Xem `storage/logs/laravel.log`. |

---

## 10. Tài liệu liên quan

- `docs/DEPLOYMENT.md` — triển khai lên cPanel, cron, backup/restore DB, email prod, rollback. ✅
- `docs/HANDOVER-SECRETS.template.md` — **mẫu** tài liệu bí mật (cPanel, DB prod, SMTP, domain, Git). Copy thành `HANDOVER-SECRETS.md`, điền và bàn giao **ngoài git** (tên file đã nằm trong `.gitignore`). ✅
- `docs/ARCHITECTURE.md` — kiến trúc, mô hình dữ liệu, **các luật nghiệp vụ ngầm** (phân quyền GLV, năm học, quyền hỗ trợ quản trị). *(nên tạo)*
- `docs/KNOWN-ISSUES.md` — nợ kỹ thuật & test đang fail đã biết. *(nên tạo)*
- `docs/*.docx` — hướng dẫn sử dụng cho người dùng cuối (GLV, giáo dân, quản trị xứ).
- `docs/DESIGN_SYSTEM.pdf`, `docs/HUONG_DAN_VGIAOXU_ORG_V2.pdf` — tài liệu thiết kế/hướng dẫn có sẵn.

---

## 11. Liên hệ bàn giao

> Người bàn giao điền trước khi rời dự án:

- Người bàn giao (dev cũ): _______________
- Nhà cung cấp hosting / cPanel: _______________
- Tên miền & nơi quản lý DNS: _______________
- Dịch vụ email (SMTP): _______________
- Repo Git: _______________
- Người phụ trách nghiệp vụ (cha xứ / ban giáo lý) để hỏi khi cần: _______________
