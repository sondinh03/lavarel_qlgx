# DEPLOYMENT — Vận hành & triển khai lên cPanel

Runbook cho người vận hành hệ thống QLGX trên hosting cPanel. Đọc kèm `README-DEV.md` (chạy local) và
`HANDOVER-SECRETS.md` (thông tin đăng nhập thật — bàn giao ngoài git).

> Cập nhật lần cuối: 07/2026

---

## 1. Kiến trúc triển khai hiện tại

- Hosting **cPanel** (shared hosting), PHP + MySQL của hosting.
- **Document root cố định là `public_html`** (không đổi được như VPS). Vì vậy dự án được tách làm 2 phần:
  - **Mã nguồn ứng dụng** đặt **ngoài** `public_html`, tại thư mục **`~/mvgiaoxu`** — chứa `app/`, `bootstrap/`, `vendor/`, `storage/`, `.env`, ...
  - **Nội dung thư mục `public/` của Laravel** được đặt **trong** `public_html` (các file `index.php`, `.htaccess`, `js/`, `css/`, `assets/`, ...).
  - File **`public_html/index.php` đã được sửa đường dẫn** để trỏ ngược ra thư mục mã nguồn (xem mục 1.1).
- **Không có queue worker** (`QUEUE_CONNECTION=sync`) — mọi job/mail chạy ngay trong request.
- **Có cron scheduler** (bắt buộc — xem mục 5): backup DB hằng ngày + dọn Telescope.
- Email gửi qua **SMTP của hosting** (tài khoản email tạo trong cPanel).

> Điền đường dẫn thật vào `HANDOVER-SECRETS.md`: thư mục mã nguồn (`APP_DIR`) và thư mục web (`public_html`).

### 1.1. `public_html/index.php` đã chỉnh

Bản gốc trong repo (`public/index.php`) dùng đường dẫn tương đối `__DIR__.'/../'`. Trên server, vì
`public_html` tách khỏi mã nguồn (`~/mvgiaoxu`), file `index.php` trong `public_html` được sửa các
dòng require để trỏ sang thư mục mã nguồn:

```php
// public_html/index.php
if (file_exists($maintenance = __DIR__.'/../mvgiaoxu/storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../mvgiaoxu/vendor/autoload.php';

$app = require_once __DIR__.'/../mvgiaoxu/bootstrap/app.php';
```

> **QUAN TRỌNG:** File này chỉ tồn tại/khác biệt **trên server**. Đừng để `git pull` hay upload đè
> làm mất phần sửa đường dẫn. Nếu deploy có đè `index.php`, phải chỉnh lại 2 dòng require ngay sau đó.
> Nên **ghi lại nội dung `index.php` thật của server** vào `HANDOVER-SECRETS.md`.

### 1.2. Sơ đồ thư mục trên server

```
/home/<user>/
├── mvgiaoxu/                  ← MÃ NGUỒN (ngoài web, không truy cập trực tiếp qua URL)
│   ├── app/ bootstrap/ config/ database/ resources/ routes/ vendor/
│   ├── storage/               ← log, cache, file upload gốc, bản backup
│   └── .env                   ← cấu hình + BÍ MẬT production
└── public_html/               ← DOCUMENT ROOT (web)
    ├── index.php              ← đã sửa require trỏ sang ../mvgiaoxu
    ├── .htaccess
    ├── js/ css/ assets/       ← asset build từ `npm run prod` (đặt Ở ĐÂY)
    └── mix-manifest.json
```

## 2. Yêu cầu môi trường trên server

| Mục | Giá trị |
|-----|---------|
| PHP | 8.0+ (chọn trong cPanel → MultiPHP Manager), bật extension: `pdo_mysql`, `mbstring`, `gd`, `zip`, `bcmath`, `fileinfo`, `curl`, `openssl` |
| MySQL | 5.7+ / MariaDB tương đương |
| Composer | có sẵn trên hosting hoặc chạy `composer.phar` |
| Node.js | **không bắt buộc trên server** — xem mục 3.2 về build asset |

## 3. Triển khai bản cập nhật (deploy)

### 3.1. Chuẩn bị ở máy dev (trước khi đưa lên server)

```bash
# 1. Đảm bảo test phân quyền xanh
php artisan test tests/Feature/CatechistAuthorizationMatrixTest.php tests/Feature/CatechistLivewireScopeTest.php

# 2. Build asset production
npm run prod
```

### 3.2. Đưa code lên server

Hosting cPanel thường **không có Node.js**, nên asset phải được build sẵn ở máy dev (`npm run prod`)
rồi đưa lên. Vì bố trí tách 2 thư mục (mục 1), cần đưa **đúng phần vào đúng chỗ**:

| Nội dung | Đưa lên đâu |
|----------|-------------|
| `app/`, `config/`, `database/`, `resources/`, `routes/`, `bootstrap/`, `composer.*` | Thư mục mã nguồn (`~/mvgiaoxu`) |
| Asset đã build: `public/js`, `public/css`, `public/assets`, `public/mix-manifest.json` | Vào `public_html/` |
| `.env`, `storage/`, `vendor/` | **KHÔNG đè** — giữ nguyên trên server |
| `public_html/index.php` (đã sửa require) | **KHÔNG đè** — xem cảnh báo mục 1.1 |

**Cách A — Git trên server (khuyến nghị nếu hosting có Git Version Control):**

```bash
cd ~/mvgiaoxu           # thư mục mã nguồn
git pull origin main
# Sau đó đồng bộ asset đã build sang public_html (vì public/ nằm trong mã nguồn,
# nhưng web root là public_html):
cp -r ~/mvgiaoxu/public/js ~/mvgiaoxu/public/css ~/mvgiaoxu/public/assets ~/public_html/
cp ~/mvgiaoxu/public/mix-manifest.json ~/public_html/
```

**Cách B — Upload thủ công:** nén dự án (trừ `vendor/`, `node_modules/`, `.env`, `storage/`),
upload qua File Manager. Giải nén phần mã nguồn vào `~/mvgiaoxu`, còn nội dung `public/` copy
vào `public_html`. **Không đè `.env`, `storage/`, và `public_html/index.php`.**

> Lưu ý: mỗi lần đổi asset (chạy lại `npm run prod`) đều phải copy lại `js/css/assets/mix-manifest.json`
> sang `public_html`, nếu không giao diện sẽ dùng asset cũ hoặc lỗi thiếu `mix-manifest.json`.

### 3.3. Các lệnh sau khi code đã lên server

Chạy trong cPanel → Terminal (hoặc SSH), tại **thư mục mã nguồn** (`~/mvgiaoxu`), không phải trong `public_html`:

```bash
cd ~/mvgiaoxu

# 1. Cài dependencies PHP (KHÔNG cài gói dev trên prod)
composer install --no-dev --optimize-autoloader

# 2. Bật chế độ bảo trì (tránh người dùng thao tác giữa chừng)
php artisan down

# 3. Chạy migration
php artisan migrate --force

# 4. Xóa & build lại cache
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Mở lại trang
php artisan up
```

> Mẹo terminal cPanel: gõ `Ctrl + R` rồi nhập từ khóa để tìm nhanh lệnh cũ trong lịch sử bash,
> hoặc `history | grep artisan`.
>
> Ảnh trong `storage/app/public` hiển thị qua web nhờ symlink `public/storage`. Với bố trí này,
> symlink cần nằm ở `public_html/storage` trỏ tới `~/mvgiaoxu/storage/app/public`. Nếu ảnh upload
> không hiện, tạo lại symlink: `ln -s ~/mvgiaoxu/storage/app/public ~/public_html/storage`
> (một số shared hosting chặn symlink — khi đó phải cấu hình khác, ghi lại vào KNOWN-ISSUES).

### 3.4. Kiểm tra sau deploy (smoke test ~3 phút)

1. Mở trang chủ — không lỗi 500.
2. Đăng nhập 1 tài khoản quản trị xứ → mở `/diem-danh`, `/hoc-sinh`.
3. Đăng nhập 1 tài khoản GLV → dashboard hiện bình thường.
4. Xem `storage/logs/laravel.log` không có exception mới.

## 4. File `.env` production — các điểm khác local

```env
APP_ENV=production
APP_DEBUG=false                  # BẮT BUỘC false trên prod
APP_URL=https://<ten-mien>

DB_*                             # theo HANDOVER-SECRETS.md

MAIL_MAILER=smtp                 # mail thật qua SMTP hosting
MAIL_HOST=mail.<ten-mien>        # hoặc localhost tùy hosting
MAIL_PORT=465                    # 465 (SSL) hoặc 587 (TLS)
MAIL_USERNAME=<email-da-tao-trong-cPanel>
MAIL_PASSWORD=<mat-khau-email>
MAIL_ENCRYPTION=ssl              # ssl cho 465, tls cho 587
MAIL_FROM_ADDRESS=<email-cung-domain>   # PHẢI cùng domain, tránh vào spam

QUEUE_CONNECTION=sync
TELESCOPE_ENABLED=false          # không bật Telescope trên prod
```

Sau khi sửa `.env` phải chạy `php artisan config:cache` thì thay đổi mới có hiệu lực
(vì config đã được cache).

## 5. Cron — BẮT BUỘC cấu hình

Laravel scheduler cần một cron duy nhất chạy mỗi phút. Trong cPanel → **Cron Jobs** thêm:

```
* * * * * cd /home/<user>/mvgiaoxu && php artisan schedule:run >> /dev/null 2>&1
```

> Trỏ vào **thư mục mã nguồn** (nơi có file `artisan`), không phải `public_html`.

Scheduler hiện chạy các việc sau (định nghĩa trong `app/Console/Kernel.php`):

| Giờ | Lệnh | Việc |
|-----|------|------|
| 04:00 hằng ngày | `backup:clean` | Xóa bản backup cũ theo chính sách giữ |
| 05:00 hằng ngày | `backup:run` | **Backup database + file** |
| hằng ngày | `telescope:prune` | Dọn dữ liệu debug Telescope |

> Nếu cron chưa cấu hình thì **backup tự động không chạy** — kiểm tra ngay sau khi tiếp quản.

## 6. Backup & Restore

### 6.1. Backup tự động

- Dùng gói **Backpack BackupManager** (nền Spatie laravel-backup), chạy qua cron ở mục 5.
- File backup nằm trong `storage/app/<APP_NAME>/` (file `.zip` chứa dump SQL + file).
- Xem/tải bản backup trong khu admin: đăng nhập `super_admin` → `/admin` → mục **Backups**.

### 6.2. Backup thủ công trước thao tác rủi ro

```bash
php artisan backup:run                  # backup qua ứng dụng
# hoặc dump trực tiếp:
mysqldump -u <user> -p <database> > backup-$(date +%Y%m%d).sql
```

Ngoài ra cPanel có **Backup / Backup Wizard** để tải toàn bộ home + database — nên tải một bản
về máy định kỳ (hosting có thể mất).

### 6.3. Restore database

```bash
mysql -u <user> -p <database> < backup-YYYYMMDD.sql
```

Hoặc dùng phpMyAdmin → Import. Sau restore chạy `php artisan optimize:clear`.

## 7. Email production — kiểm tra & sự cố

- Ứng dụng gửi email khi: duyệt/từ chối đăng ký quản trị xứ, đăng ký giáo dân, thông báo hệ thống.
- **Lưu ý code:** phần gửi mail khi duyệt đăng ký được bọc `try/catch` — duyệt vẫn thành công dù
  mail lỗi; lỗi chỉ nằm trong `storage/logs/laravel.log`.
- Kiểm tra mail đã rời server chưa: cPanel → **Email → Track Delivery**, lọc theo địa chỉ người nhận.
  `Delivered` = máy chủ nhận đã nhận; `Failed` = bấm vào xem lý do.
- Mail hay vào spam / bị từ chối: cPanel → **Email Deliverability** → kiểm tra SPF/DKIM của domain,
  bấm **Repair** nếu có cảnh báo. Đảm bảo `MAIL_FROM_ADDRESS` cùng domain với tài khoản SMTP.
- Dấu vết trong ứng dụng: bảng `notifications` (kênh database) ghi lại các notification đã chạy.

## 8. Rollback khi deploy hỏng

```bash
php artisan down

# Nếu deploy bằng git:
git log --oneline -5                    # tìm commit ổn định trước đó
git checkout <commit-on-dinh> -- .      # hoặc: git reset --hard <commit-on-dinh>

composer install --no-dev --optimize-autoloader
php artisan optimize:clear && php artisan config:cache
php artisan up
```

- Migration của bản mới thường **không cần rollback** nếu chỉ thêm bảng/cột (code cũ bỏ qua chúng).
  Chỉ khi migration phá vỡ dữ liệu mới cần restore DB từ backup (mục 6.3).
- Nếu deploy bằng upload thủ công: giải nén lại bản nén cũ (luôn giữ bản nén trước khi đè).

## 9. Sự cố thường gặp trên prod

| Triệu chứng | Xử lý |
|-------------|-------|
| Lỗi 500 sau deploy | Xem `storage/logs/laravel.log`; thường do quên `composer install` hoặc cache cũ → `php artisan optimize:clear && php artisan config:cache` |
| Trang hiện code PHP / tải file `index.php` | PHP handler sai trong MultiPHP, hoặc `public_html/index.php` bị đè mất phần sửa require (mục 1.1) |
| Lỗi "Failed opening required ... vendor/autoload.php" | `public_html/index.php` trỏ sai đường dẫn tới thư mục mã nguồn — sửa lại 2 dòng require (mục 1.1) |
| Ảnh upload không hiển thị | Thiếu/hỏng symlink `public_html/storage` → tạo lại (xem cuối mục 3.3) |
| Sửa `.env` không có tác dụng | Quên `php artisan config:cache` |
| Giao diện vỡ sau deploy | Quên `npm run prod` ở máy dev trước khi đưa code lên (asset cũ/thiếu `mix-manifest.json`) |
| Email không đến người dùng | Mục 7 — Track Delivery + Email Deliverability + log Laravel |
| Backup không thấy chạy | Cron mục 5 chưa cấu hình, hoặc đường dẫn/php trong cron sai |
| Hết dung lượng hosting | Dọn `storage/logs`, bản backup cũ trong `storage/app`, và kiểm tra `telescope_entries` trong DB |

## 10. Việc định kỳ cho người vận hành

| Tần suất | Việc |
|----------|------|
| Hằng tuần | Liếc `storage/logs/laravel.log` xem có exception lặp lại |
| Hằng tháng | Tải 1 bản backup về máy; kiểm tra dung lượng hosting |
| Trước năm học mới | Phối hợp ban giáo lý: tạo năm học, sao chép lớp (`/nam-hoc/sao-che`), phân công GLV — GLV chưa phân công trong năm mới sẽ không thao tác được (ràng buộc an toàn, xem `ARCHITECTURE.md`) |
| Khi có người nghỉ | Vô hiệu hóa tài khoản (`is_active`) thay vì xóa |
