# Plan: Import dữ liệu Sổ Gia Đình Công Giáo vào hệ thống quản lý giáo dân

> File này dùng để đưa vào Cursor làm ngữ cảnh (context) cho AI thực hiện từng task theo thứ tự.
> Database: MySQL. Các bảng `parishioners_new`, `sacraments`, `marriages` đã tồn tại (xem schema bên dưới).

---

## 0. Bối cảnh & Schema hiện có

### Bảng `parishioners_new` (đã có)
```sql
id bigint UN AI PK
last_name varchar(100)
first_name varchar(100)
gender enum('male','female')
birthday date
birth_order tinyint
saint_id bigint UN
phone varchar(20)
email varchar(255)
cccd varchar(20)
avatar_path varchar(255)
note text
parish_id bigint UN
deanery_id bigint UN
diocese_id bigint UN
parish_area_id bigint UN
ethnic tinyint
career tinyint
education_level tinyint
specialist_level tinyint
catechism_level tinyint
catechism_major varchar(100)
position tinyint
language tinyint
holy_order_status tinyint
is_new_convert tinyint(1)
is_included_in_stats tinyint(1)
married tinyint
level tinyint
status tinyint(1)
death_date date
death_book_number varchar(20)
death_place varchar(255)
burial_place varchar(255)
permanent_ward_id int UN
permanent_province varchar(255)
permanent_residence varchar(255)
temporary_ward_id int UN
temporary_province varchar(255)
temporary_residence varchar(255)
origin varchar(255)
father_name varchar(255)
mother_name varchar(255)
joined_date date
transferred_from bigint UN
transferred_date date
is_active tinyint(1)
left_reason varchar(255)
created_at timestamp
updated_at timestamp
father_id bigint UN
mother_id bigint UN
family_id bigint UN
family_role enum('husband','wife','child','other')
```

### Bảng `sacraments` (đã có)
```sql
id bigint UN AI PK
parishioner_id bigint UN
type enum('baptism','communion','confirmation','anointing','holy_orders')
received_date date
certificate_number varchar(50)
book_number int UN
giver varchar(100)
sponsor varchar(100)
parish_id bigint UN
parish_name varchar(100)
deanery_id bigint UN
diocese_id bigint UN
note text
created_at timestamp
updated_at timestamp
```

### Bảng `marriages` (đã có)
```sql
id bigint UN AI PK
husband_id bigint UN
wife_id bigint UN
married_date date
certificate_number varchar(50)
parish_id bigint UN
parish_name varchar(100)
place_ward_id int UN
place_province varchar(100)
status enum('valid','invalid','widowed','divorced')
witness_1 varchar(100)
witness_2 varchar(100)
priest_witness varchar(100)
note text
created_at timestamp
updated_at timestamp
```

### Quyết định kiến trúc đã chốt
- `marriages` để **riêng**, không gộp vào `sacraments`.
- `sacraments.type` **không có** `'marriage'` — bỏ qua, không cần ALTER.
- 1 gia đình (cha+mẹ+con) gắn với 1 `family_id` chung. Khi 1 người con kết hôn, người đó trở thành `husband`/`wife` của **một `family_id` mới**.
- Cha/mẹ ưu tiên dùng `father_id`/`mother_id` (FK) nếu đã có record; chỉ dùng `father_name`/`mother_name` (text) khi người đó chưa/không có trong hệ thống.

### Việc cần xác nhận/bổ sung trước khi code (Task 1 sẽ làm rõ)
- `parishioners_new` **thiếu cột `birth_place`** (nơi sinh) — cần ALTER TABLE thêm cột.
- Cần bảng danh mục `saints` (id, name) để map `saint_id`. Kiểm tra xem đã tồn tại chưa.
- Cần bảng danh mục `parishes` (giáo xứ) — kiểm tra `parish_id` đang trỏ vào đâu.

---

## 1. Mục tiêu tổng thể

Xây dựng pipeline: **Excel mẫu nhập liệu → script validate → script import → 3 bảng MySQL**, để nhập dữ liệu từ Sổ Gia Đình Công Giáo (scan/ảnh) vào hệ thống.

---

## 2. Danh sách Task cho Cursor (làm tuần tự)

### Task 1 — Rà soát & bổ sung schema
**Yêu cầu Cursor:**
- Kết nối tới DB (hoặc đọc file migration hiện có trong repo).
- Kiểm tra xem đã có bảng `saints`, `parishes`, `deaneries`, `dioceses` chưa.
- Nếu chưa có `birth_place` trong `parishioners_new`, viết migration:
  ```sql
  ALTER TABLE parishioners_new ADD COLUMN birth_place varchar(255) NULL AFTER birthday;
  ```
- Nếu chưa có bảng `saints`, tạo migration:
  ```sql
  CREATE TABLE saints (
    id bigint UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name varchar(100) NOT NULL,
    feast_day varchar(20) NULL
  );
  ```
- Output: file migration (theo convention của framework đang dùng — Laravel/Knex/raw SQL, Cursor tự phát hiện từ repo).

**Done khi:** migration chạy được, không lỗi, schema khớp với phần "Bối cảnh" ở trên.

---

### Task 2 — Thiết kế file Excel mẫu nhập liệu
**Yêu cầu Cursor:**
- Tạo file `template_nhap_lieu.xlsx` gồm 3 sheet, mỗi sheet là mặt phẳng (flat) của 1 bảng, thêm cột phụ để liên kết khi import:

  **Sheet `parishioners`:**
  | temp_id (tự đặt, vd P001) | family_temp_id | family_role | last_name | first_name | gender | birthday | birth_place | birth_order | saint_name | father_temp_id | mother_temp_id | parish_name | note |

  **Sheet `sacraments`:**
  | parishioner_temp_id | type | received_date | certificate_number | book_number | giver | sponsor | parish_name | note |

  **Sheet `marriages`:**
  | husband_temp_id | wife_temp_id | married_date | certificate_number | parish_name | witness_1 | witness_2 | priest_witness | status | note |

- `temp_id` là mã tạm do người nhập tự đặt (vd P001, P002) để nối các sheet với nhau — KHÔNG phải `id` thật trong DB (DB sẽ tự sinh `id` khi insert).
- Thêm data validation (dropdown) cho: `gender`, `family_role`, `type`, `status`.
- Thêm 1 sheet `README` trong file Excel giải thích cách điền (đặc biệt cách dùng `temp_id` để liên kết).

**Done khi:** file Excel mở được, có dropdown đúng, có ví dụ mẫu sẵn 1-2 dòng dựa theo dữ liệu thật (gia đình Hoàng Công Luật trong sổ).

---

### Task 3 — Script validate dữ liệu Excel trước khi import
**Yêu cầu Cursor:** viết script Python (dùng `openpyxl` hoặc `pandas`) để:
- Đọc 3 sheet.
- Kiểm tra:
  - `temp_id` không trùng trong sheet `parishioners`.
  - Mọi `parishioner_temp_id` trong `sacraments` phải tồn tại trong `parishioners`.
  - Mọi `husband_temp_id`/`wife_temp_id` trong `marriages` phải tồn tại trong `parishioners`.
  - `family_role` mỗi `family_temp_id` chỉ có tối đa 1 `husband`, 1 `wife`.
  - Ngày tháng đúng định dạng, không rỗng ở các trường bắt buộc (`last_name`, `first_name`, `family_role`).
  - `saint_name` nếu điền mà không khớp danh mục `saints` → cảnh báo (không chặn, vì có thể cần tạo mới).
- Output: in ra danh sách lỗi theo dòng/sheet cụ thể, dừng import nếu có lỗi nghiêm trọng (thiếu FK), chỉ cảnh báo nếu lỗi nhẹ (saint chưa có).

**Done khi:** chạy `python validate_import.py template_nhap_lieu.xlsx` báo đúng lỗi khi cố tình nhập sai dữ liệu test.

---

### Task 4 — Script import vào MySQL
**Yêu cầu Cursor:** viết script Python (dùng `pandas` + `mysql-connector-python` hoặc SQLAlchemy) theo thứ tự bắt buộc:
1. Insert tất cả saint mới (nếu có) vào `saints`, lưu map `saint_name -> saint_id`.
2. Insert từng người vào `parishioners_new` theo thứ tự: **cha/mẹ trước, con sau** (vì con cần `father_id`/`mother_id`).
   - Sinh `family_id` mới (dùng UUID tạm hoặc lấy `MAX(family_id)+1`) cho mỗi `family_temp_id` chưa từng gặp.
   - Giữ map `temp_id -> id thật` trong bộ nhớ (dict) suốt quá trình chạy.
3. Insert `sacraments`, dùng map ở bước 2 để lấy `parishioner_id` thật.
4. Insert `marriages`, dùng map ở bước 2 để lấy `husband_id`/`wife_id` thật.
5. Toàn bộ chạy trong 1 transaction — lỗi ở bất kỳ bước nào thì rollback toàn bộ.
6. In log: số dòng đã insert mỗi bảng, danh sách `temp_id -> id thật` để đối chiếu.

**Done khi:** chạy thử với dữ liệu mẫu từ gia đình Hoàng Công Luật (trang 2-4 trong sổ) ra đúng 4 record `parishioners_new` (cha, mẹ, 2 con), 4+ record `sacraments`, 1-2 record `marriages`, không lỗi FK.

---

### Task 5 — (Tuỳ chọn) OCR hỗ trợ bóc tách từ ảnh scan
**Yêu cầu Cursor (chỉ làm nếu số lượng sổ lớn, > vài trăm cuốn):**
- Viết script nhận ảnh từng trang sổ (giống ảnh đã upload) → gọi Claude API (vision) hoặc Google Vision OCR → trả về JSON nháp theo đúng cấu trúc sheet ở Task 2.
- Ghi JSON nháp vào dòng mới trong Excel mẫu, đánh dấu cột `verified = false`.
- Bắt buộc người dùng mở Excel, đối chiếu ảnh gốc, sửa lỗi, đổi `verified = true` trước khi cho qua Task 3.
- **Không tự động hoá hoàn toàn** bước này — do chữ viết tay + con dấu chồng chữ rất dễ sai ở các trường tên người, ngày tháng.

**Done khi:** script chạy ra JSON nháp đúng khoảng 70-80% trường so với ảnh mẫu đã có (rửa tội, ngày sinh...), có rõ ràng cột để người kiểm tra.

---

## 3. Thứ tự chạy cho Cursor

```
Task 1 (schema) → Task 2 (excel template) → Task 3 (validate) → Task 4 (import)
                                                                      ↑
                                                  Task 5 (OCR, optional) tạo input cho Task 3
```

## 4. Lưu ý quan trọng cho Cursor khi code

- Không tạo `family_id` trùng giữa các lần chạy import khác nhau — luôn query `MAX(family_id)` từ DB thật, không hardcode.
- Khi 1 người (vd con cái) vừa là `child` trong gia đình cha mẹ, vừa là `husband`/`wife` trong gia đình riêng sau khi cưới → đây là **2 record khác nhau** trong `parishioners_new`? Hay cùng 1 `id` nhưng đổi `family_id`/`family_role`? → **Cần Cursor hỏi lại người dùng nghiệp vụ thực tế trước khi code Task 4**, vì ảnh hưởng lớn đến logic insert (1 người 1 record duy nhất, chỉ `marriages` mới tạo thêm liên kết — đây là cách hợp lý hơn, tránh trùng người).
- Toàn bộ ngày tháng trong sổ là dương lịch trừ khi ghi rõ "Âm lịch" — giữ nguyên cột `birthday` là dương lịch, có thể thêm `birthday_lunar` riêng nếu cần (không có trong yêu cầu hiện tại, bỏ qua trừ khi người dùng yêu cầu).
