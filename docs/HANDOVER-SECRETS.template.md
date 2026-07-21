# HANDOVER-SECRETS — Tài liệu bí mật bàn giao (MẪU)

> **CÁCH DÙNG:** Copy file này thành `HANDOVER-SECRETS.md`, điền đầy đủ, rồi bàn giao **trực tiếp**
> cho người tiếp quản (USB, trình quản lý mật khẩu, in giấy...).
>
> **TUYỆT ĐỐI KHÔNG COMMIT bản đã điền lên git.** Tên file `HANDOVER-SECRETS.md` đã được thêm vào
> `.gitignore` để tránh commit nhầm. Chỉ file mẫu này (`.template.md`, không chứa bí mật) nằm trong repo.
>
> Sau khi bàn giao xong, người tiếp quản nên **đổi toàn bộ mật khẩu** bên dưới.

---

## 1. Hosting cPanel

| Mục | Giá trị |
|-----|---------|
| Nhà cung cấp hosting | |
| URL đăng nhập cPanel | |
| Username cPanel | |
| Mật khẩu cPanel | |
| Gói hosting / ngày gia hạn | |
| Tài khoản thanh toán (email đăng ký với nhà cung cấp) | |

## 2. Tên miền & DNS

| Mục | Giá trị |
|-----|---------|
| Tên miền chính | |
| Nơi mua / quản lý domain (registrar) | |
| Tài khoản đăng nhập registrar | |
| DNS quản lý ở đâu (registrar / Cloudflare / cPanel) | |
| Ngày hết hạn domain | |
| Chứng chỉ SSL (AutoSSL cPanel / Let's Encrypt / mua riêng) | |

## 3. Database production

| Mục | Giá trị |
|-----|---------|
| DB_HOST | |
| DB_PORT | 3306 |
| DB_DATABASE | |
| DB_USERNAME | |
| DB_PASSWORD | |
| Cách truy cập (phpMyAdmin trong cPanel / remote MySQL) | |

## 4. Email (SMTP)

| Mục | Giá trị |
|-----|---------|
| MAIL_MAILER | smtp |
| MAIL_HOST | |
| MAIL_PORT | (465 SSL / 587 TLS) |
| MAIL_USERNAME (địa chỉ email gửi) | |
| MAIL_PASSWORD | |
| MAIL_ENCRYPTION | |
| MAIL_FROM_ADDRESS | |
| Hộp thư này tạo ở đâu (cPanel → Email Accounts / dịch vụ ngoài) | |

> Kiểm tra email gửi thành công: cPanel → **Track Delivery** (xem `docs/DEPLOYMENT.md` mục 7).

## 5. Mã nguồn (Git)

| Mục | Giá trị |
|-----|---------|
| URL repository | |
| Nền tảng (GitLab / GitHub) | |
| Tài khoản owner | |
| Người cần được cấp quyền tiếp | |
| Branch production | |
| Deploy key / token (nếu server pull trực tiếp) | |

## 6. Đường dẫn trên server

| Mục | Giá trị |
|-----|---------|
| Thư mục chứa mã nguồn — ngoài web | `~/mvgiaoxu` (điền đường dẫn đầy đủ `/home/<user>/mvgiaoxu`) |
| Document root của domain | `~/public_html` (chứa nội dung thư mục `public/` + `index.php` đã sửa) |
| PHP version đang chọn trong cPanel (MultiPHP) | |
| File `.env` production nằm ở | (gốc thư mục mã nguồn) |
| Nội dung 2 dòng require đã sửa trong `public_html/index.php` | (dán nguyên văn để khôi phục khi cần) |

## 7. Tài khoản trong ứng dụng

| Mục | Giá trị |
|-----|---------|
| Tài khoản `super_admin` (email) | |
| Mật khẩu | |
| Các tài khoản `parish_admin` quan trọng | |

## 8. Dịch vụ ngoài khác (nếu có)

| Dịch vụ | Tài khoản | Ghi chú |
|---------|-----------|---------|
| Cloudflare | | |
| Google (reCAPTCHA, Analytics...) | | |
| Khác | | |

## 9. Bản sao `.env` production

> Dán toàn bộ nội dung file `.env` trên server vào đây (hoặc đính kèm file riêng):

```env
# ...dán .env production tại đây...
```

## 10. Liên hệ

| Vai trò | Tên | SĐT / Email |
|---------|-----|-------------|
| Dev cũ (người bàn giao) | | |
| Người phụ trách nghiệp vụ (cha xứ / ban giáo lý) | | |
| Hỗ trợ của nhà cung cấp hosting | | |

---

*Ngày bàn giao: ____ / ____ / ______ — Chữ ký hai bên (nếu cần):*
