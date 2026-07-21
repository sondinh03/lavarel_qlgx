# MODULES — Sổ tay tính năng theo module

Tra cứu nhanh cho lập trình viên: mỗi module gồm **route → component Livewire → view → điểm cần
lưu ý**. Đọc kèm `ARCHITECTURE.md` (vai trò, luật nghiệp vụ, mô hình dữ liệu).

Quy ước trong tài liệu này:

- Component viết tắt từ `App\Http\Livewire\` (vd `Score\ScoreManager` = `app/Http/Livewire/Score/ScoreManager.php`).
- View viết tắt từ `resources/views/livewire/`.
- "Guard" là middleware `role:` của Spatie trên route (chưa tính policy bên trong component).
- "Bộ 3 giáo lý" = `parish_admin|catechism_admin|catechist`; "bộ 3 giáo dân" = `parish_admin|parishioner_admin|catechist`.

> Cập nhật lần cuối: 07/2026

---

## 0. Route công khai (không cần đăng nhập)

| URI | Component | Ghi chú |
|-----|-----------|---------|
| `/` | `Landing` | Trang chủ; phụ huynh tra cứu học sinh bằng SĐT (thông tin / điểm danh / điểm). Đã đăng nhập → redirect dashboard |
| `/dang-ky-giao-dan[/{parish}]` | `Parishioners\ParishionerSelfRegistration` | Form đăng ký giáo dân công khai |
| `/dang-ky-quan-tri-xu[/{parish}]` | `ParishAdmin\ParishAdminSelfRegistration` | Form đăng ký quản trị xứ công khai |
| `/giao-dan/{parishioner}` | `Parishioners\ParishionerShow` | Trang chi tiết giáo dân **xem công khai**; modal sửa chỉ hiện khi đăng nhập + đủ quyền |
| `/offline` | view `offline` | PWA |
| `{slug}` + các POST export/import legacy | Controllers cũ | UI slug giáo xứ đời cũ, vẫn public — cẩn thận khi dọn dẹp |

Lưu ý: `/hoc-sinh/qr/{token}` (ảnh QR) **không public** — nằm trong nhóm auth giáo lý.

---

## 1. Dashboard / chọn phân hệ

| Route | Guard | Component |
|-------|-------|-----------|
| `/select-module` (`module.select`) | auth | `ModuleSelect` |
| `/dashboard` (`dashboard`) | auth | Closure: super_admin → `/admin/dashboard`, còn lại → `module.select` |
| `/parish-admin-dashboard` | parish_admin\|catechism_admin | `Dashboard\AdminDashboard` |
| `/bang-dieu-khien` (`catechist.dashboard`) | catechist | `Dashboard\CatechistDashboard` |
| `/giao-dan` (`parishioners.dashboard`) | bộ 3 giáo dân | `Dashboard\ParishionerDashboard` |

Lưu ý:

- Ai thấy dashboard nào quyết định bởi `usesCatechistLayout()` — GLV thuần vào `CatechistDashboard`
  (bottom-nav), quản trị vào `AdminDashboard`. Mỗi dashboard tự redirect nếu vào nhầm.
- `AdminDashboard` cache thống kê 600s — sửa số liệu mà không thấy đổi thì xóa cache.
- `CatechistDashboard` hiện banner "chưa được phân công" khi `assignmentBlocked` (xem ARCHITECTURE mục 3.4).

## 2. Năm học

Guard: `parish_admin|catechism_admin`.

| Route | Component | View |
|-------|-----------|------|
| `/nam-hoc` | `NamHoc\NamHocManager` | `nam-hoc.nam-hoc-manager` |
| `/nam-hoc/sao-che` | `NamHoc\CopyNamHoc` | `nam-hoc.copy-nam-hoc` |
| `/nam-hoc/huong-dan` | `NamHoc\NamHocSetupGuide` | `nam-hoc.nam-hoc-setup-guide` |

Lưu ý:

- `CopyNamHoc` là wizard 4 bước: copy lớp (kèm tùy chọn copy `ScoreType`) rồi xếp học sinh lên lớp.
  Hỗ trợ query `?source=&target=` để nhảy thẳng bước 4.
- `NamHoc.status = 1` là năm active. Cách app xác định "năm hiện tại": xem ARCHITECTURE mục 4.3.

## 3. Lớp giáo lý

Guard: `parish_admin|catechism_admin`.

| Route | Component | View |
|-------|-----------|------|
| `/lop-hoc` | `CatechismClass\CatechismClassList` | `catechism-class.catechism-class-list` |
| `/lop-hoc/{id}` | `Lop\LopDetail` | `lop.lop-detail` |
| `/lop-hoc/{id}/hoc-sinh` | `ClassStudentManager` | `class-student-manager` |
| `/lop-hoc/{id}/giao-ly-vien` | `Lop\AssignTeacher` | `lop.assign-teacher` |

Lưu ý:

- **Phân công GLV** ở `AssignTeacher`: role pivot `ClassTeacher::ROLE_CHU_NHIEM` (1) /
  `ROLE_PHO` (2), mỗi lớp 1 chủ nhiệm. Khi phân công gửi notification `TeacherAssignedToClass`.
- Phân công chính là thứ quyết định GLV có "được thao tác năm nay" hay không (ARCHITECTURE mục 3.1).

## 4. Học sinh

Guard chung: bộ 3 giáo lý. Tạo / import / in thẻ: chỉ `parish_admin|catechism_admin`.

| Route | Component | View |
|-------|-----------|------|
| `/hoc-sinh` | `Student\StudentListNew` | `student.student-list-new` |
| `/hoc-sinh/thong-ke` | `Student\StudentStatistics` | `student.student-statistics` |
| `/hoc-sinh/{id}` | `Student\StudentDetail` | `student.student-detail` |
| `/hoc-sinh/tao`, `/hoc-sinh/{id}/sua` | `Student\StudentEdit` | `student.student-edit` |
| `/hoc-sinh/nhap` (+ `/mau`) | `Student\StudentImportPreview` + `StudentImportController` | `student.student-import-preview` |
| `/hoc-sinh/in-the` | `Student\PrintCards` | `student.print-cards` |
| `/hoc-sinh/qr/{token}` | `StudentQrController@show` | ảnh SVG QR |

Lưu ý:

- Route sửa cho phép cả catechist vì GLV có quyền `edit_parish_students` được sửa (chặn tiếp ở `StudentPolicy`).
- `StudentNew.qr_token` (UUID) tự sinh khi lưu — dùng cho thẻ QR và điểm danh QR.
- In thẻ: `?ids=` hoặc `?classId=`, 2 loại thẻ (vĩnh viễn / theo năm), nhúng QR.
- List có: liên kết/hủy liên kết hồ sơ giáo dân (`linkParishioner`), xóa hàng loạt, export Excel (`StudentExport`).
- Import qua `ImportStudentAction` + preview (`StudentPreviewImport`).

## 5. Giáo lý viên

Guard: `parish_admin|catechism_admin`.

| Route | Component | View |
|-------|-----------|------|
| `/giao-ly-vien` | `Teacher\TeacherManager` | `teacher.teacher-manager` |
| `/giao-ly-vien/{id}` | `Teacher\TeacherDetail` | `teacher.teacher-detail` |
| `/giao-ly-vien/tao`, `/{id}/sua` | `Teacher\TeacherEdit` | `teacher.teacher-edit` |
| `/giao-ly-vien/nhap` (+ `/mau`) | `Teacher\TeacherImportPreview` + `TeacherImportController` | `teacher.teacher-import-preview` |

Lưu ý:

- Tạo tài khoản đăng nhập cho GLV ngay trong `TeacherEdit`: tạo `User` + role `catechist`,
  mật khẩu mặc định từ ngày sinh (`CatechistDefaultPassword::fromBirthday`), email chuẩn hóa qua
  `UserAccountEmailResolver`.
- **Cấp 2 quyền nâng cao** (`manage_parish_scores`, `edit_parish_students`) cũng ở màn này —
  `TeacherEdit::syncElevatedPermissions`, chỉ parish_admin/super_admin thấy
  (`CatechistAccess::canGrantElevatedPermissions`).
- Import: `ImportTeacherAction` (tạo kèm tài khoản qua `CreateCatechistAccount`).

## 6. Điểm danh

Guard: bộ 3 giáo lý; riêng quản lý phiên + nhật ký chỉ `parish_admin|catechism_admin`.

| Route | Component | View |
|-------|-----------|------|
| `/diem-danh` | `AttendanceManager` | `attendance-manager` |
| `/diem-danh/qr` | `Attendance\AttendanceQr` | `attendance.attendance-qr` |
| `/diem-danh/thong-ke` | `Attendance\AttendanceStatistics` | `attendance.attendance-statistics` |
| `/phien-diem-danh` | `Attendance\SessionManager` | `attendance.session-manager` |
| `/diem-danh/nhat-ky` | `Attendance\AttendanceEditLogList` | `attendance.attendance-edit-log-list` |

Hằng số:

- Loại phiên (`AttendanceSession`): `TYPE_CLASS = 1` đi học, `TYPE_CEREMONY = 2` đi lễ.
- Trạng thái phiên: `1` đang mở, `2` đã khóa, `3` đã hủy.
- Trạng thái điểm danh (`AttendanceRecord`): `1` có mặt, `2` vắng phép, `3` vắng không phép.

Lưu ý:

- **GLV có phân công điểm danh được toàn giáo xứ** — chủ đích (ARCHITECTURE mục 3.2). GLV chưa
  phân công: `assignmentBlocked` chặn cả 3 màn, dropdown lớp nhận sentinel `[0]`.
- Lưu điểm danh qua `AttendanceService` (bulk save), gửi notification `AttendanceSessionSummary`.
- QR: tra học sinh theo `qr_token` cùng giáo xứ, chỉ ghi nhận vào phiên **đang mở**.
- Có export Excel (`AttendanceExport`) và log sửa đổi (`AttendanceEditLog`).

## 7. Nhập điểm

| Route | Guard | Component | View |
|-------|-------|-----------|------|
| `/diem-so` | bộ 3 giáo lý | `Score\ScoreManager` | `score.score-manager` |
| `/diem-so/thong-ke` | parish_admin\|catechism_admin | `Score\ScoreStatistics` | `score.score-statistics` |
| `/diem-so/nhat-ky` | parish_admin\|catechism_admin | `Score\ScoreEditLogList` | `score.score-edit-log-list` |

Lưu ý:

- Loại điểm (`ScoreType.type`): 1 khảo kinh, 2 mười lăm phút, 3 bốn lăm phút, 4 giữa kỳ, 5 cuối kỳ.
  Cấu hình loại điểm (hệ số, điểm tối đa) ngay trong modal của `ScoreManager`.
- **Cửa nhập điểm** `parishes.scores_entry_open`: bật/tắt trong `ScoreManager` (quyền admin).
  GLV có `manage_parish_scores` chỉ nhập được khi cửa mở; quản trị nhập được bất kể
  (`StudentScorePolicy`, `CatechismClassPolicy`).
- GLV thường: chỉ **xem** điểm lớp mình. GLV chưa phân công: dropdown lớp sentinel `[0]`.
- Điểm lưu trên `student_scores` treo vào pivot `students_class` (không trực tiếp student).
- Export `ScoreExport`; mọi thay đổi ghi `ScoreEditLog`.

## 8. Giáo dân

Guard xem: bộ 3 giáo dân. Tạo / import / duyệt đăng ký: `parish_admin|parishioner_admin`.

| Route | Component | View |
|-------|-----------|------|
| `/giao-dan/danh-sach` | `Parishioners\ParishionersManager` | `parishioners.parishioners-manager` |
| `/giao-dan/thong-ke` | `Parishioners\ParishionerStatistics` | `parishioners.parishioner-statistics` |
| `/giao-dan/tao` | `Parishioners\ParishionerCreate` | `parishioners.parishioner-form` |
| `/giao-dan/bi-tich` | `Parishioners\SacramentsManager` | `parishioners.sacraments-manager` |
| `/giao-dan/nhap` (+ `/mau`) | `Parishioners\FamilyRegisterImportPreview` | `parishioners.family-register-import-preview` |
| `/giao-dan/dang-ky` (+ `/{registration}`) | `Parishioners\ParishionerRegistrationList` / `...Show` | `parishioners.parishioner-registration-*` |
| `/giao-dan/{parishioner}` | `Parishioners\ParishionerShow` | `parishioners.parishioner-show` (**public**) |
| `/hoi-doan` | `Parishioners\AssociationManager` | `parishioners.association-manager` |

Export giấy tờ (controller invokable → `App\Actions\Parishioner\Export*`): lý lịch, đơn xin rửa
tội, phiếu báo tử, giới thiệu giáo lý dự tòng, chứng chỉ bí tích.

Lưu ý:

- **GLV chỉ được xem chi tiết hồ sơ** (`ParishionerShow`) — không danh sách/thống kê/export
  (luật đã chốt, ARCHITECTURE mục 2.4).
- Route `/giao-dan/{parishioner}/sua` chỉ là 301 về trang show — sửa bằng modal trong show.
- Đánh dấu qua đời trong show → notification `ParishionerMarkedDeceased`.
- Import sổ gia đình: `ImportFamilyRegisterAction`.

## 9. Gia đình

Guard xem: bộ 3 giáo dân. Tạo/sửa: `parish_admin|parishioner_admin`.

| Route | Component | View |
|-------|-----------|------|
| `/gia-dinh` | `Family\FamilyList` | `family.family-list` |
| `/gia-dinh/{id}` | `Family\FamilyDetail` | `family.family-detail` |
| `/gia-dinh/tao`, `/{id}/sua` | `Family\FamilyEdit` | `family.family-edit` |
| `/gia-dinh/{family}/xuat-so-gia-dinh` | controller → `ExportSoGiaDinhAction` | file Word |

Lưu ý: thêm/bớt thành viên, đặt vai trò trong gia đình qua `FamilyMembershipService`.

## 10. Rao hôn phối

Guard xem: bộ 3 giáo dân. Tạo/sửa/tạo hôn phối/export: `parish_admin|parishioner_admin`.

| Route | Component | View |
|-------|-----------|------|
| `/rao-hon-phoi` | `MarriageAnnouncement\MarriageAnnouncementList` | `marriage-announcement.marriage-announcement-list` |
| `/rao-hon-phoi/{id}` | `...Show` | `...-show` |
| `/rao-hon-phoi/tao`, `/{id}/sua` | `...Edit` | `...-form` |
| `/rao-hon-phoi/{id}/hon-phoi/tao` | `MarriageCreateFromAnnouncement` | `marriage-create-from-announcement` |
| `/rao-hon-phoi/{id}/xuat-giay-gioi-thieu-hon-phoi` | controller → `ExportGioiThieuHonPhoiAction` | file Word |

Lưu ý:

- Lưu rao qua `SaveMarriageAnnouncementAction` — nếu có ngăn trở, gửi notification
  `MarriageAnnouncementImpediment`.
- Sau khi rao đủ → tạo bản ghi hôn phối bằng `CreateMarriageFromAnnouncementAction`
  (có thể tạo luôn gia đình mới qua `CreateFamilyFromMarriageAction` / `MarriageService`).
- Tra cứu phục vụ form nằm ở `MarriageAnnouncementLookupService` (một phần expose qua `routes/api.php`).

## 11. Đăng ký công khai & luồng duyệt

| Luồng | Form public | Duyệt ở đâu | Action | Notification |
|-------|-------------|-------------|--------|--------------|
| Giáo dân đăng ký | `/dang-ky-giao-dan` | `/giao-dan/dang-ky` (Livewire) | `ApproveParishionerRegistrationAction` | Submitted → quản trị xứ; Approved/Rejected → người đăng ký (**chỉ database**) |
| Quản trị xứ đăng ký | `/dang-ky-quan-tri-xu` | Backpack `/admin` (**super admin duyệt**) | `ApproveParishAdminRegistrationAction` / `Reject...` | Submitted → super admin; Approved/Rejected → **database + MAIL thật** |

Lưu ý:

- Duyệt giáo dân hỗ trợ tạo 1 người hoặc cả sổ gia đình (payload v2 có members).
- Duyệt quản trị xứ tạo giáo xứ + giáo họ + user với role yêu cầu (mặc định `parish_admin`,
  config `config/parish-admin-registration.php`).
- Gửi mail duyệt được bọc `try/catch` — mail fail không làm fail việc duyệt (chủ đích).

## 12. Trợ giúp (in-app)

Guard: `parish_admin|catechism_admin`. Trang tĩnh, không có logic.

| Route | Component |
|-------|-----------|
| `/tro-giup/cai-dat-dien-thoai` | `Help\InstallAppGuide` |
| `/tro-giup/diem-danh` | `Help\AttendanceSetupGuide` |
| `/tro-giup/nhap-diem` | `Help\ScoreEntryGuide` |

Lưu ý: khi đổi luật phân quyền GLV, nhớ cập nhật cả 3 trang này **và** tài liệu Word sinh từ
`docs/generate-*.php` (đã có tiền lệ quên đồng bộ).

## 13. Thông báo (chuông)

- `/thong-bao` → `Notifications\NotificationIndex` (danh sách, đánh dấu đã đọc).
- `Notifications\NotificationBell` — component nhúng trong cả 3 layout (main / catechist /
  parishioner), **không có route riêng**.
- Dùng database notifications của Laravel; click mở theo `data.url` trong payload.

## 14. Tài khoản

- `/tai-khoan` → `Account\AccountSettings`: sửa hồ sơ + avatar (`UploadService`), đổi mật khẩu
  (yêu cầu mật khẩu hiện tại). Layout tự đổi theo vai trò.

## 15. Module phụ

| Route | Guard | Component | Ghi chú |
|-------|-------|-----------|---------|
| `/thong-tin-giao-xu` | parish_admin | `Parish\ParishSettings` | Thông tin giáo xứ, logo |
| `/giao-ho` | parish_admin\|parishioner_admin | `Parish\ParishGroupManager` | Giáo họ — dùng chung cả 2 phân hệ |
| `/ten-thanh` | — | `Holy\HolyManager` | Danh mục tên thánh |
| `/thong-bao-giao-ly` | parish_admin\|catechism_admin | `Catechism\CatechistAnnouncementComposer` | Gửi `CatechismBoardAnnouncement` tới GLV (tất cả hoặc theo khối) |
| `/groups`, `/{groupId}/members\|sessions\|attendance` | parish_admin\|parishioner_admin | `Group\*` | Nhóm sinh hoạt (không phải giáo họ), có điểm danh riêng |

Mồ côi / legacy:

- `Parish\ParishChild` được import trong `web.php` nhưng **không có route**.
- `Block\BlockManager` có view nhưng **không có route web**.
- Khối 301 redirect trong `web.php` map URL tiếng Anh cũ (`/giao-ly/*`, `/students`...) sang URL
  tiếng Việt — giữ để không gãy bookmark.
- Backpack `/admin` (super admin) là hệ CRUD riêng, route ở `routes/backpack/custom.php`.

---

## Phụ lục: helper dùng chung

| Helper | Dùng ở |
|--------|--------|
| `App\Services\CatechistAccess` | Điểm danh, học sinh, điểm số, cấp quyền nâng cao |
| `App\Services\SchoolYearResolver` | Dashboard, điểm danh, điểm số, học sinh |
| `App\Http\Livewire\Filters\FilterBar` | Bộ lọc năm/lớp/kỳ dùng chung — nhớ sentinel `[0]` (ARCHITECTURE mục 3.5) |
| `App\Services\UploadService` | Avatar tài khoản, học sinh, giáo dân |
| `notify_users()` | Điểm danh, phân công GLV, đăng ký, thông báo giáo lý, báo tử, ngăn trở hôn phối |
