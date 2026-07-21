# ARCHITECTURE — Kiến trúc & luật nghiệp vụ

Tài liệu dành cho lập trình viên tiếp quản. Mục tiêu: hiểu hệ thống được tổ chức thế nào và
**những luật nghiệp vụ ngầm không nhìn code là thấy ngay**. Đọc kèm `README-DEV.md` (cài đặt,
chạy local) và `DEPLOYMENT.md` (vận hành trên cPanel).

> Cập nhật lần cuối: 07/2026

---

## 1. Tổng quan

Hệ thống quản lý giáo xứ (QLGX) gồm **2 phân hệ** dùng chung một đăng nhập:

- **Giáo lý**: năm học, lớp giáo lý, học sinh, giáo lý viên (GLV), điểm danh (thủ công + QR), nhập điểm.
- **Giáo dân**: hồ sơ giáo dân, gia đình, bí tích, hôn phối, rao hôn phối, thống kê.

Sau khi đăng nhập, người dùng vào `/select-module` (`ModuleSelect`) để chọn phân hệ — danh sách
phân hệ hiện ra tùy vai trò. GLV thuần (không kiêm quản trị) dùng layout riêng có bottom-nav
(`User::usesCatechistLayout()`).

**Stack**: Laravel 8, Livewire 2, Backpack CRUD 4.1 (chỉ super admin), Spatie Permission,
Spatie Backup, Telescope, Maatwebsite Excel, Simple QrCode. Không dùng queue
(`QUEUE_CONNECTION=sync`).

### Hai "thế hệ" code cùng tồn tại

| | Cũ (legacy) | Mới (chính) |
|---|---|---|
| UI | Backpack CRUD tại `/admin` | Livewire tại các route tiếng Việt (`/diem-danh`, `/nhap-diem`, ...) |
| Ai dùng | chỉ `super_admin` | tất cả vai trò còn lại |
| Model giáo dân | `GiaoDan` → bảng `parishioners` | `Parishioner` → bảng `parishioners_new` |
| Model gia đình | `GiaDinh` → bảng `family` | `Family` → bảng `families` |
| Model học sinh | `Student` | `StudentNew` → bảng `students` |

Khi sửa nghiệp vụ, gần như luôn làm ở phần **Livewire + model mới**. Phần Backpack/legacy giữ
nguyên để super admin thao tác dữ liệu thô và quản lý lookup tables.

---

## 2. Vai trò & phân quyền

### 2.1. Các vai trò (Spatie roles)

| Role | Ý nghĩa | Phạm vi |
|------|---------|---------|
| `super_admin` | Quản trị hệ thống, vào được Backpack `/admin` | Toàn hệ thống, mọi giáo xứ |
| `parish_admin` | Quản trị giáo xứ | Cả 2 phân hệ, trong giáo xứ của mình |
| `catechism_admin` | Quản trị phân hệ giáo lý | Chỉ giáo lý, trong giáo xứ |
| `parishioner_admin` | Quản trị phân hệ giáo dân | Chỉ giáo dân, trong giáo xứ |
| `catechist` | Giáo lý viên | Lớp được phân công (xem mục 3) |

Helper trên `app/Models/User.php` — dùng các hàm này thay vì gọi `hasRole` rải rác:

- `isSuperAdmin()`, `isParishAdmin()`, `isCatechist()`, `isCatechismAdmin()`, `isParishionerAdmin()`
- `canManageCatechism()` = super_admin | parish_admin | catechism_admin
- `canManageParishioners()` = super_admin | parish_admin | parishioner_admin
- `canManage()` = một trong 4 role quản trị
- `usesCatechistLayout()` = catechist **và không** `canManage()`

Role được gán ở: Backpack user CRUD (`User::booted` sync từ request), tạo tài khoản GLV
(`CreateCatechistAccount`, `TeacherEdit`, `ImportTeacherAction` → `catechist`), duyệt đăng ký
quản trị xứ (`ApproveParishAdminRegistrationAction` → `parish_admin`).

### 2.2. Hai quyền nâng cao của GLV

Hằng số trong `app/Support/CatechistPermissions.php`:

| Permission | Ý nghĩa |
|------------|---------|
| `manage_parish_scores` | Nhập/sửa điểm cho **toàn giáo xứ** (không chỉ lớp mình) |
| `edit_parish_students` | Sửa hồ sơ học sinh **toàn giáo xứ** |

- Chỉ `super_admin` / `parish_admin` được cấp (`CatechistAccess::canGrantElevatedPermissions`),
  thao tác trong màn hình sửa GLV (`TeacherEdit`).
- **Quan trọng**: có permission chưa đủ — còn phải có phân công năm hiện tại (mục 3).

### 2.3. Multi-tenancy theo giáo xứ

Trait `App\Traits\BelongsToParish` gắn global scope `App\Scopes\ParishScope`: mọi user không
phải `super_admin` tự động bị lọc `parish_id = auth()->user()->parish_id`. Scope bị bỏ qua khi
chạy console/seeder. Hầu hết bảng chính đều có cột `parish_id`.

### 2.4. Policies

Đăng ký trong `AuthServiceProvider`, nằm ở `app/Policies/`:

- `CatechismClassPolicy`, `StudentPolicy`, `StudentScorePolicy`, `AttendanceSessionPolicy`,
  `ScoreTypePolicy`, `SchoolYearPolicy` — phân hệ giáo lý; phần liên quan GLV đều ủy quyền
  qua `CatechistAccess`.
- `ParishionerPolicy`, `FamilyPolicy`, `MarriageAnnouncementPolicy`, `ParishGroupPolicy`,
  `AssociationPolicy` — phân hệ giáo dân; mutate cần `canManageParishioners()` cùng giáo xứ.
- **GLV bên giáo dân chỉ được XEM chi tiết hồ sơ giáo dân** — không danh sách, không thống kê,
  không gia đình/hôn phối, không export.

---

## 3. Luật nghiệp vụ ngầm — PHẢI ĐỌC trước khi sửa phân quyền

Đây là những luật đã chốt với người dùng, được test khóa lại trong
`tests/Feature/CatechistAuthorizationMatrixTest.php` và `tests/Feature/CatechistLivewireScopeTest.php`.
**Chạy 2 file test này trước mỗi lần deploy.**

### 3.1. GLV chưa phân công năm hiện tại = không thao tác được gì

- GLV **không có phân công lớp nào trong năm học hiện tại** (kể cả tài khoản năm cũ, GLV đã nghỉ)
  vẫn đăng nhập được, nhưng **không thao tác được bất cứ gì** trong phân hệ giáo lý: không điểm
  danh, không quét QR, không xem thống kê, dropdown lớp trống, dashboard hiện banner giải thích.
- Luật này áp cho **cả 2 quyền nâng cao**: có `manage_parish_scores` / `edit_parish_students`
  mà không có phân công năm nay thì quyền cũng vô hiệu. Nhờ đó quản trị xứ **không cần thu hồi
  quyền thủ công** khi GLV nghỉ — chỉ cần không phân công ở năm mới.
- `Teacher.is_active = false` cũng chặn tương tự (GLV nghỉ hẳn).

### 3.2. GLV đã có phân công → điểm danh được TOÀN GIÁO XỨ

GLV chỉ cần được phân công **ít nhất 1 lớp** trong năm hiện tại là điểm danh được **tất cả các
lớp trong giáo xứ** (không chỉ lớp mình). Đây là quyết định nghiệp vụ có chủ đích (GLV hỗ trợ
điểm danh chéo lớp), đừng "sửa bug" thành chỉ-lớp-mình.

Ma trận tóm tắt:

| GLV | Điểm danh | Nhập điểm | Sửa học sinh |
|-----|-----------|-----------|--------------|
| Không phân công năm nay | ✗ | ✗ | ✗ |
| Có phân công, không quyền nâng cao | ✓ toàn xứ | chỉ xem điểm lớp mình | chỉ xem |
| Có phân công + `manage_parish_scores` | ✓ toàn xứ | ✓ toàn xứ (khi cửa nhập điểm mở) | — |
| Có phân công + `edit_parish_students` | ✓ toàn xứ | — | ✓ toàn xứ |

### 3.3. `CatechistAccess` là cổng trung tâm

`app/Services/CatechistAccess.php` — **mọi** kiểm tra quyền GLV phải đi qua đây, đừng tự ráp
`isCatechist() && ...` trong component. Các method chính:

| Method | Logic |
|--------|-------|
| `teacherFor(User)` | Tìm bản ghi `Teacher` đang `is_active` của user (khớp `parish_id`) |
| `hasActiveAssignmentThisYear(User)` | Có ≥1 phân công active trong năm học hiện tại (memoized) |
| `canOperateCatechism(User)` | Quản trị: luôn true. GLV: true khi có phân công năm nay. **Cổng chính** |
| `canManageParishScores(User)` | Quản trị, hoặc GLV + permission + phân công năm nay |
| `canEditParishStudents(User)` | Tương tự với `edit_parish_students` |
| `assignedClassIds(User, $yearId)` | Lớp được phân công: `class_teachers` active, lớp active cùng xứ; năm khớp qua `namhoc_id` **hoặc** `classes.school_year_id` (dữ liệu cũ chỉ có 1 trong 2) |
| `canViewClass / canViewStudent / canViewScoresForClass / canEnterScoresForClass` | Kiểm tra chi tiết theo đối tượng |
| `restrictClassQuery` | Thu hẹp query lớp theo phân công (quản trị/quyền nâng cao: không thu hẹp) |

### 3.4. Cờ `assignmentBlocked` trong `BaseComponent`

`app/Http/Livewire/Base/BaseComponent.php` có sẵn:

- `public bool $assignmentBlocked` — true khi user là GLV **và** `!canOperateCatechism()`.
- Được set trong `refreshCatechistAssignmentGuard()` (gọi từ `initializeUser()` ở `mount`).

Component con chỉ cần đọc `$this->assignmentBlocked`, không tự tính lại. Các component đang
dùng: `AttendanceManager`, `AttendanceQr`, `AttendanceStatistics`, `CatechistDashboard`.

### 3.5. Sentinel `[0]` cho dropdown lớp (FilterBar)

`Filters/FilterBar` nhận `allowedClassIds` với quy ước:

- **Mảng rỗng `[]`** = không giới hạn (hiện tất cả lớp).
- **`[0]`** = ép dropdown **trống** (không có lớp id 0 thật). Dùng cho GLV chưa phân công.

Đừng đổi `[0]` thành `[]` khi refactor — sẽ mở toang dropdown cho GLV chưa phân công. Pattern
này có ở `StudentListNew`, `ScoreManager`, `AttendanceManager`.

Ngoài ra FilterBar còn 1 sentinel UI khác: **học kỳ `3`** = "giữa 2 học kỳ / hè" (không tồn tại
học kỳ 3 trong DB).

### 3.6. Cửa nhập điểm `scores_entry_open`

Bảng `parishes` có cột `scores_entry_open`. GLV có `manage_parish_scores` chỉ nhập điểm được khi
cờ này bật (quản trị xứ bật/tắt). Quản trị thì nhập được bất kể cờ.

---

## 4. Mô hình dữ liệu chính

### 4.1. Phân hệ giáo lý

```
ParishNew (parishes)
 └── NamHoc (nam_hoc)  [parish_id, status=1 active, start/end 2 học kỳ]
      └── CatechismClass (classes)  [parish_id, school_year_id, grade_level_id, is_active]
           ├── ClassTeacher (class_teachers)  [teacher_id, class_id, namhoc_id, role 1=chủ nhiệm/2=phụ, status]
           │    └── Teacher (teachers)  [parish_id, user_id, is_active]
           │         └── User (users)   [parish_id, is_active, Spatie roles]
           ├── StudentsClass (students_class)  [student_id, class_id, status 1=đang học/2=hoàn thành/3=nghỉ]
           │    ├── StudentNew (students)  [parish_id, qr_token, parishioner_id, ...]
           │    └── StudentScore (student_scores)  [student_class_id, score_type_id, score_value, attempt]
           ├── ScoreType (score_types)  [class_id, semester, type 1..5, coefficient, max_score]
           └── AttendanceSession (attendance_sessions)  [class_id, date, semester, type 1=học/2=lễ, status]
                └── AttendanceRecord (attendance_records)  [session_id, student_id, status 1=có mặt/2=phép/3=không phép]
```

Điểm cần nhớ:

- **Năm học nằm ở 2 chỗ**: `class_teachers.namhoc_id` và `classes.school_year_id`. Dữ liệu cũ
  có thể chỉ điền 1 trong 2, nên `CatechistAccess::assignedClassIds` OR cả hai.
- Điểm số treo trên **pivot** `students_class` (không trực tiếp trên student) — một học sinh
  học nhiều lớp/nhiều năm có nhiều bộ điểm.
- Có bảng log sửa đổi: `AttendanceEditLog`, `ScoreEditLog`.
- `students.qr_token` (UUID, tự sinh) dùng cho điểm danh QR.

### 4.2. Phân hệ giáo dân

```
Parishioner (parishioners_new)  [parish_id, family_id, father_id, mother_id, association_id, ...]
 ├── Family (families)          [parish_id, head_id, member_count, ...]
 ├── Sacrament (sacraments)     [parishioner_id, type: baptism/communion/confirmation/...]
 ├── Marriage (marriages)       [husband_id, wife_id, parish_id, status: valid/invalid/widowed/divorced]
 └── StudentNew.parishioner_id  ← liên kết học sinh ↔ hồ sơ giáo dân
```

`ParishGroup` (giáo họ) dùng chung cho cả 2 phân hệ (`parish_group_id` trên teacher, student,
family, parishioner).

### 4.3. Xác định "năm học hiện tại"

Có **2 cơ chế**, đừng nhầm:

1. `App\Services\SchoolYearResolver::resolve($parishId)` — cơ chế chính, trả về
   `OperatingSchoolYear` có **phase** (`semester_1`, `semester_2`, `between_semesters`, `summer`).
   Ưu tiên năm active có hôm nay trong khoảng; fallback năm đã bắt đầu/vừa kết thúc.
2. `NamHoc::active()->current()` — đơn giản hơn (hôm nay trong `[start_date_one, end_date_two]`),
   dùng trong `CatechistAccess::currentSchoolYearId`.

---

## 5. Các tầng ứng dụng

| Tầng | Vị trí | Ghi chú |
|------|--------|---------|
| Livewire components | `app/Http/Livewire/` | UI chính. Kế thừa `Base/BaseComponent` |
| Backpack CRUD | `app/Http/Controllers/Admin/*CrudController.php` | Chỉ super_admin (`CheckIfAdmin`), routes ở `routes/backpack/custom.php` |
| Services | `app/Services/` | `CatechistAccess`, `SchoolYearResolver`, `AttendanceService`, `MarriageService`, `ParishionerStatsService`, ... |
| Policies | `app/Policies/` | Đăng ký ở `AuthServiceProvider` |
| Actions | `app/Actions/` | Duyệt/từ chối đăng ký, export Word/PDF, import Excel, gửi thông báo |
| Exports/Imports | `app/Exports/`, `app/Imports/` | Maatwebsite Excel |
| Notifications | `app/Notifications/` | Không có `app/Mail` — mail đi qua Notification (`MailMessage`) |

Livewire theo module: `Attendance/`, `Score/`, `Student/`, `Teacher/`, `CatechismClass/` + `Lop/`,
`NamHoc/`, `Parishioners/`, `Family/`, `MarriageAnnouncement/`, `Dashboard/`, `Help/` (trang trợ
giúp in-app), `Filters/FilterBar` (bộ lọc dùng chung).

Routes: `routes/web.php` dùng URL tiếng Việt (`/diem-danh`, `/nhap-diem`, `/hoc-sinh`,
`/giao-dan`, `/tro-giup/...`). Middleware đáng chú ý: `auth`, Spatie `role:...`,
`redirect.auth.dashboard`, Backpack `admin`.

### Gửi mail & thông báo

- `ParishAdminRegistrationApproved/Rejected`: **database + mail** (mail thật duy nhất ngoài reset password).
- Các notification còn lại (đăng ký giáo dân, phân công GLV, tổng kết điểm danh, rao hôn phối,
  báo tử...): chỉ **database** (chuông thông báo trong app).
- Lưu ý: chỗ duyệt đăng ký có `try/catch` nuốt lỗi gửi mail — duyệt vẫn thành công dù mail fail
  (chủ đích, tránh chặn nghiệp vụ vì SMTP).

### Tác vụ định kỳ (`app/Console/Kernel.php`)

| Giờ | Lệnh |
|-----|------|
| 04:00 | `backup:clean` |
| 05:00 | `backup:run` (Spatie Backup → disk `storage/backups`) |
| hằng ngày | `telescope:prune` |

Cần cron `schedule:run` trên server (xem `DEPLOYMENT.md` mục 5). Lệnh custom:
`php artisan qlgx:normalize-catechist-login-emails`.

---

## 6. Những chỗ "đừng đụng vào" nếu chưa hiểu

1. **Sentinel `[0]`** trong FilterBar (mục 3.5) — trông như bug, là chủ đích.
2. **GLV điểm danh toàn xứ** (mục 3.2) — chủ đích, không phải thiếu filter.
3. **Mutator password trên `User`** — tự hash; đừng `bcrypt()` trước khi gán, sẽ hash 2 lần.
4. **`try/catch` quanh gửi mail duyệt đăng ký** — chủ đích (mục 5).
5. **Năm học ở 2 cột** (`namhoc_id` + `school_year_id`) — phải OR cả hai khi query phân công.
6. **`public_html/index.php` trên server đã sửa require** — xem `DEPLOYMENT.md` mục 1.1;
   đừng để deploy đè mất.
7. **Test suite có một số test fail sẵn** không liên quan logic mới (`AttendanceServiceTest`,
   `ImportFamilyRegisterActionTest`, `MarriageServiceTest`, `AttendancePageTest`) — do dữ liệu
   seed/schema môi trường test (vd `User::find(13)`, cột `deid` thiếu default). Mốc chuẩn để tin
   là 2 file test phân quyền ở mục 3.
