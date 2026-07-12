# Đơn xin rửa tội

File: `public/word-template/01_donxinruatoi.docx`  
Backup gốc: `01_donxinruatoi.original.docx` (tự tạo lần đầu generate)

Xuất từ trang chi tiết giáo dân → **Đơn xin rửa tội**  
Route: `parishioners.export-don-xin-rua-toi`

## Placeholder

| Biến | Ý nghĩa |
|------|---------|
| `${diocese}` `${parish}` `${parish_group}` | Tên đã có tiền tố (presenter tự thêm nếu DB thiếu) |
| `${current_parish_group}` `${current_parish}` | Cùng chuẩn hóa như trên |
| `${birth_order}` | **Nhập khi xuất** — con thứ |
| `${holy_fullname}` | **Nhập khi xuất** — tên thánh, họ tên người được rửa tội |
| `${birth_day}` `${birth_month}` `${birth_year}` | **Nhập khi xuất** — ngày sinh |
| `${birth_place}` | **Nhập khi xuất** — nơi sinh |
| `${godparent_name}` | **Nhập khi xuất** — tên thánh, họ tên người đỡ đầu |
| `${father_name}` `${mother_name}` | Từ hồ sơ xuất: người xuất + vợ/chồng (nếu là cha/mẹ), hoặc cha/mẹ liên kết |

Mẫu có **2 bản** trên cùng file (tách bằng đường kẻ).
