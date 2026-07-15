<?php

use Illuminate\Support\Facades\Route;

use Illuminate\Pagination\Paginator;

use App\Http\Controllers\SlugController;

// Offline route for PWA
Route::get('/offline', function () {
    return view('offline');
});

use App\Http\Controllers\GiaDinhController;
use App\Http\Controllers\GiaoPhanController;
use App\Http\Controllers\UsersImportController;
use App\Http\Controllers\UsersExportController;
use App\Http\Controllers\BitichExportController;
use App\Http\Controllers\LyLichCaNhanExportController;
use App\Http\Controllers\GiayGioiThieuGiaoLyHonPhoiExportController;
use App\Http\Controllers\GiayGioiThieuHonPhoiExportController;
use App\Http\Controllers\GiayDieuTraHonPhoiExportController;
use App\Http\Controllers\SoGiaDinhCongGiaoExportController;
use App\Http\Controllers\RaoHonPhoiNuController;
use App\Http\Controllers\KQRaoHonPhoiNuController;
use App\Http\Controllers\RaoHonPhoiNamController;
use App\Http\Controllers\KQRaoHonPhoiNamController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\LopController;
use App\Http\Controllers\LopHocController;
use App\Http\Controllers\DiLeController;
use App\Http\Controllers\KhaoKinhController;
use App\Http\Controllers\KetQuaController;
use App\Http\Controllers\KQController;
use App\Http\Controllers\KetQuaDiHocController;
use App\Http\Controllers\KetQuaDiLeController;
use App\Http\Controllers\KetQuaKhaoKinhController;
use App\Http\Controllers\QRLopController;
use App\Http\Controllers\ThuGioiThieuExportController;
use App\Http\Controllers\LopImportController;
use App\Http\Controllers\TeacherImportController;
use App\Http\Controllers\KetQuaExportController;
use App\Http\Controllers\HonPhoiExportController;
use App\Http\Controllers\StudentImportController;
use App\Http\Controllers\FamilyRegisterImportController;
use App\Http\Controllers\ParishionerLyLichExportController;
use App\Http\Controllers\ParishionerDonXinRuaToiExportController;
use App\Http\Controllers\ParishionerPhieuBaoTuExportController;
use App\Http\Controllers\ParishionerGioiThieuGiaoLyDuTongExportController;
use App\Http\Controllers\ParishionerChungChiBiTichExportController;
use App\Http\Controllers\MarriageAnnouncementGioiThieuHonPhoiExportController;
use App\Http\Controllers\StudentQrController;
use App\Http\Livewire\Attendance\AttendanceQr as AttendanceAttendanceQr;
use App\Http\Livewire\Attendance\AttendanceStatistics;
use App\Http\Livewire\Attendance\SessionManager;
use App\Http\Livewire\AttendanceManager;
use App\Http\Livewire\CatechismClass\CatechismClassList;
use App\Http\Livewire\ClassStudentManager;
use App\Http\Livewire\Dashboard\AdminDashboard;
use App\Http\Livewire\Dashboard\CatechistDashboard;
use App\Http\Livewire\Dashboard\ParishionerDashboard;
use App\Http\Livewire\Family\FamilyDetail;
use App\Http\Livewire\Family\FamilyEdit;
use App\Http\Livewire\Family\FamilyList;
use App\Http\Livewire\MarriageAnnouncement\MarriageAnnouncementEdit;
use App\Http\Livewire\MarriageAnnouncement\MarriageAnnouncementList;
use App\Http\Livewire\MarriageAnnouncement\MarriageAnnouncementShow;
use App\Http\Livewire\MarriageAnnouncement\MarriageCreateFromAnnouncement;
use App\Http\Livewire\Group\GroupAttendance;
use App\Http\Livewire\Group\GroupManager;
use App\Http\Livewire\Group\GroupMemberManager;
use App\Http\Livewire\Group\GroupSessionManager;
use App\Http\Livewire\Holy\HolyManager;
use App\Http\Livewire\Landing;
use App\Http\Livewire\Account\AccountSettings;
use App\Http\Livewire\Parish\ParishSettings;
use App\Http\Livewire\Notifications\NotificationIndex;
use App\Http\Livewire\Lop\AssignTeacher;
use App\Http\Livewire\Lop\LopDetail;
use App\Http\Livewire\ModuleSelect;
use App\Http\Livewire\NamHoc\CopyNamHoc;
use App\Http\Livewire\NamHoc\NamHocManager;
use App\Http\Livewire\Parish\ParishChild;
use App\Http\Livewire\Parish\ParishGroup;
use App\Http\Livewire\Parish\ParishGroupManager;
use App\Http\Livewire\ParishAdmin\ParishAdminSelfRegistration;
use App\Http\Livewire\Parishioners\AssociationManager;
use App\Http\Livewire\Parishioners\ParishionerCreate;
use App\Http\Livewire\Parishioners\ParishionerRegistrationList;
use App\Http\Livewire\Parishioners\ParishionerRegistrationShow;
use App\Http\Livewire\Parishioners\ParishionerSelfRegistration;
use App\Http\Livewire\Parishioners\ParishionerShow;
use App\Http\Livewire\Parishioners\ParishionerStatistics;
use App\Http\Livewire\Parishioners\ParishionersManager;
use App\Http\Livewire\Parishioners\FamilyRegisterImportPreview;
use App\Http\Livewire\Parishioners\SacramentsManager;
use App\Http\Livewire\Score\ScoreEditLogList;
use App\Http\Livewire\Score\ScoreManager;
use App\Http\Livewire\Score\ScoreStatistics;
use App\Http\Livewire\Student\PrintCards;
use App\Http\Livewire\Student\StudentDetail;
use App\Http\Livewire\Student\StudentEdit;
use App\Http\Livewire\Student\StudentImportPreview;
use App\Http\Livewire\Student\StudentListNew;
use App\Http\Livewire\Student\StudentStatistics;
use App\Http\Livewire\Teacher\TeacherDetail;
use App\Http\Livewire\Teacher\TeacherEdit;
use App\Http\Livewire\Teacher\TeacherImportPreview;
use App\Http\Livewire\Teacher\TeacherManager;
use Illuminate\Support\Facades\Auth;

Paginator::useBootstrap();

// ========== GUEST ==========
Route::middleware('redirect.auth.dashboard')->group(function () {
    Route::get('/', Landing::class)->name('landing');
});

Route::get('/dang-ky-giao-dan', ParishionerSelfRegistration::class)
    ->name('parishioners.register.public');
Route::get('/dang-ky-giao-dan/{parish}', ParishionerSelfRegistration::class)
    ->whereNumber('parish')
    ->name('parishioners.register.public.parish');

Route::get('/dang-ky-quan-tri-xu', ParishAdminSelfRegistration::class)
    ->name('parish-admin.register.public');
Route::get('/dang-ky-quan-tri-xu/{parish}', ParishAdminSelfRegistration::class)
    ->whereNumber('parish')
    ->name('parish-admin.register.public.parish');

Route::get('/select-module', ModuleSelect::class)
    ->middleware('auth')
    ->name('module.select');

Auth::routes();

Route::middleware('auth')->group(function () {

    Route::get('/tai-khoan', AccountSettings::class)
        ->name('account.settings');

    Route::get('/thong-bao', NotificationIndex::class)
        ->name('notifications.index');

    Route::get('/thong-tin-giao-xu', ParishSettings::class)
        ->middleware('role:parish_admin')
        ->name('parish.settings');

    Route::get('/dashboard', function () {
        $user = auth()->user();

        if ($user?->isSuperAdmin()) {
            return redirect('/admin/dashboard');
        }
        return redirect()->route('module.select');
    })->name('dashboard');

    // ── Module Giáo lý (URL tiếng Việt, không prefix) ─────────────────
    Route::get('/parish-admin-dashboard', AdminDashboard::class)
        ->middleware('role:parish_admin|catechism_admin')
        ->name('parish-admin.dashboard');

    Route::get('/bang-dieu-khien', CatechistDashboard::class)
        ->middleware('role:catechist')
        ->name('catechist.dashboard');

    Route::middleware('role:parish_admin|catechism_admin|catechist')->group(function () {
        Route::get('/diem-danh', AttendanceManager::class)
            ->name('attendance.show');

        Route::get('/diem-danh/thong-ke', AttendanceStatistics::class)
            ->name('attendance.statistics');

        Route::get('/diem-danh/qr', AttendanceAttendanceQr::class)
            ->name('attendance.qr');

        Route::prefix('hoc-sinh')->name('students.')->group(function () {
            Route::get('/', StudentListNew::class)->name('index');
            Route::get('/thong-ke', StudentStatistics::class)->name('statistics');
            Route::get('/qr/{token}', [StudentQrController::class, 'show'])
                ->where('token', '[0-9a-fA-F-]{36}')
                ->name('qr-image');

            Route::middleware('role:parish_admin|catechism_admin')->group(function () {
                Route::get('/tao', StudentEdit::class)->name('create');
                Route::get('/nhap', StudentImportPreview::class)->name('import');
                Route::get('/nhap/mau', [StudentImportController::class, 'template'])->name('import.template');
                Route::get('/in-the', PrintCards::class)->name('print-cards');
                Route::get('/{id}/sua', StudentEdit::class)->name('edit')->whereNumber('id');
            });

            Route::get('/{id}', StudentDetail::class)->name('show')->whereNumber('id');
        });

        Route::get('/diem-so', ScoreManager::class)->name('scores.index');
    });

    Route::middleware('role:parish_admin|catechism_admin')->group(function () {
        Route::prefix('lop-hoc')->name('classes.')->group(function () {
            Route::get('/', CatechismClassList::class)->name('index');
            Route::get('/{id}', LopDetail::class)->name('show')->whereNumber('id');
            Route::get('/{id}/hoc-sinh', ClassStudentManager::class)->name('students')->whereNumber('id');
            Route::get('/{id}/giao-ly-vien', AssignTeacher::class)->name('catechists')->whereNumber('id');
        });

        Route::get('/thong-bao-giao-ly', \App\Http\Livewire\Catechism\CatechistAnnouncementComposer::class)
            ->name('catechism.announcements');

        Route::prefix('giao-ly-vien')->name('catechists.')->group(function () {
            Route::get('/', TeacherManager::class)->name('index');
            Route::get('/tao', TeacherEdit::class)->name('create');
            Route::get('/nhap', TeacherImportPreview::class)->name('import');
            Route::get('/nhap/mau', [TeacherImportController::class, 'template'])->name('import.template');
            Route::get('/{id}/sua', TeacherEdit::class)->name('edit')->whereNumber('id');
            Route::get('/{id}', TeacherDetail::class)->name('show')->whereNumber('id');
        });

        Route::get('/phien-diem-danh', SessionManager::class)
            ->name('session.index');

        Route::get('/diem-danh/nhat-ky', \App\Http\Livewire\Attendance\AttendanceEditLogList::class)
            ->name('attendance.edit-logs');

        Route::prefix('diem-so')->name('scores.')->group(function () {
            Route::get('/thong-ke', ScoreStatistics::class)->name('statistics');
            Route::get('/nhat-ky', ScoreEditLogList::class)->name('edit-logs');
        });

        Route::prefix('nam-hoc')->name('school-years.')->group(function () {
            Route::get('/', NamHocManager::class)->name('index');
            Route::get('/sao-che', CopyNamHoc::class)->name('copy');
        });
    });

    // Redirect URL cũ → tiếng Việt
    Route::redirect('/catechist-dashboard', '/bang-dieu-khien', 301);
    Route::redirect('/attendance', '/diem-danh', 301);
    Route::redirect('/attendance/statistics', '/diem-danh/thong-ke', 301);
    Route::redirect('/attendance/qr', '/diem-danh/qr', 301);
    Route::redirect('/students', '/hoc-sinh', 301);
    Route::redirect('/students/statistics', '/hoc-sinh/thong-ke', 301);
    Route::redirect('/students/create', '/hoc-sinh/tao', 301);
    Route::redirect('/studentss/import', '/hoc-sinh/nhap', 301);
    Route::redirect('/studentss/download-template', '/hoc-sinh/nhap/mau', 301);
    Route::redirect('/studentss/print-cards', '/hoc-sinh/in-the', 301);
    Route::redirect('/classes', '/lop-hoc', 301);
    Route::redirect('/catechists', '/giao-ly-vien', 301);
    Route::redirect('/catechists/import', '/giao-ly-vien/nhap', 301);
    Route::redirect('/catechists/download-template', '/giao-ly-vien/nhap/mau', 301);
    Route::redirect('/session', '/phien-diem-danh', 301);
    Route::redirect('/scores', '/diem-so', 301);
    Route::redirect('/scores/statistics', '/diem-so/thong-ke', 301);
    Route::redirect('/school-years', '/nam-hoc', 301);
    Route::redirect('/school-years/copy', '/nam-hoc/sao-che', 301);

    // Redirect /giao-ly/* (URL trung gian) → URL mới
    Route::redirect('/giao-ly', '/parish-admin-dashboard', 301);
    Route::redirect('/giao-ly/bang-dieu-khien', '/bang-dieu-khien', 301);
    Route::redirect('/giao-ly/diem-danh', '/diem-danh', 301);
    Route::redirect('/giao-ly/hoc-sinh', '/hoc-sinh', 301);
    Route::redirect('/giao-ly/lop-hoc', '/lop-hoc', 301);
    Route::redirect('/giao-ly/giao-ly-vien', '/giao-ly-vien', 301);
    Route::redirect('/giao-ly/nam-hoc', '/nam-hoc', 301);

    Route::get('/students/qr/{token}', function (string $token) {
        return redirect("/hoc-sinh/qr/{$token}", 301);
    })->where('token', '[0-9a-fA-F-]{36}');

    Route::get('/students/{id}/edit', function (int $id) {
        return redirect("/hoc-sinh/{$id}/sua", 301);
    })->whereNumber('id');

    Route::get('/students/{id}', function (int $id) {
        return redirect("/hoc-sinh/{$id}", 301);
    })->whereNumber('id');

    Route::get('/classes/{id}/students', function (int $id) {
        return redirect("/lop-hoc/{$id}/hoc-sinh", 301);
    })->whereNumber('id');

    Route::get('/classes/{id}/catechists', function (int $id) {
        return redirect("/lop-hoc/{$id}/giao-ly-vien", 301);
    })->whereNumber('id');

    Route::get('/classes/{id}', function (int $id) {
        return redirect("/lop-hoc/{$id}", 301);
    })->whereNumber('id');

    Route::middleware('role:parish_admin|parishioner_admin|catechist')->prefix('giao-dan')->group(function () {
        Route::get('/', ParishionerDashboard::class)
            ->name('parishioners.dashboard');

        Route::get('/danh-sach', ParishionersManager::class)
            ->name('parishioners.index');

        Route::get('/thong-ke', ParishionerStatistics::class)
            ->name('parishioners.statistics');

        Route::get('/tao', ParishionerCreate::class)
            ->middleware('role:parish_admin|parishioner_admin')
            ->name('parishioners.create');

        Route::get('/bi-tich', SacramentsManager::class)
            ->name('parishioners.sacrament');

        Route::middleware('role:parish_admin|parishioner_admin')->group(function () {
            Route::get('/nhap', FamilyRegisterImportPreview::class)
                ->name('parishioners.import');

            Route::get('/nhap/mau', [FamilyRegisterImportController::class, 'template'])
                ->name('parishioners.import.template');

            Route::get('/dang-ky', ParishionerRegistrationList::class)
                ->name('parishioners.registrations.index');

            Route::get('/dang-ky/{registration}', ParishionerRegistrationShow::class)
                ->name('parishioners.registrations.show');
        });

        Route::get('/{parishioner}/xuat-ly-lich', ParishionerLyLichExportController::class)
            ->name('parishioners.export-lylich');

        Route::get('/{parishioner}/xuat-don-xin-rua-toi', ParishionerDonXinRuaToiExportController::class)
            ->name('parishioners.export-don-xin-rua-toi');

        Route::get('/{parishioner}/xuat-giay-bao-tu', ParishionerPhieuBaoTuExportController::class)
            ->name('parishioners.export-phieu-bao-tu');

        Route::get('/{parishioner}/xuat-giay-gioi-thieu-giao-ly-du-tong', ParishionerGioiThieuGiaoLyDuTongExportController::class)
            ->name('parishioners.export-gioi-thieu-giao-ly-du-tong');

        Route::get('/{parishioner}/xuat-chung-chi-bi-tich', ParishionerChungChiBiTichExportController::class)
            ->name('parishioners.export-chung-chi-bi-tich');

        Route::get('/{parishioner}/sua', function (\App\Models\Parishioner $parishioner) {
            return redirect()->route('parishioners.show', ['parishioner' => $parishioner], 301);
        })->name('parishioners.edit');
    });

    Route::middleware('role:parish_admin|parishioner_admin|catechist')->prefix('gia-dinh')->name('families.')->group(function () {
        Route::get('/tao', FamilyEdit::class)
            ->middleware('role:parish_admin|parishioner_admin')
            ->name('create');

        Route::get('/', FamilyList::class)
            ->name('index');

        Route::get('/{family}/xuat-so-gia-dinh', \App\Http\Controllers\FamilySoGiaDinhExportController::class)
            ->name('export-sogiadinh');

        Route::get('/{id}/sua', FamilyEdit::class)
            ->middleware('role:parish_admin|parishioner_admin')
            ->name('edit');

        Route::get('/{id}', FamilyDetail::class)
            ->name('show');
    });

    Route::middleware('role:parish_admin|parishioner_admin|catechist')->prefix('rao-hon-phoi')->name('marriage-announcements.')->group(function () {
        Route::get('/', MarriageAnnouncementList::class)
            ->name('index');

        Route::get('/tao', MarriageAnnouncementEdit::class)
            ->middleware('role:parish_admin|parishioner_admin')
            ->name('create');

        Route::get('/{id}/sua', MarriageAnnouncementEdit::class)
            ->middleware('role:parish_admin|parishioner_admin')
            ->name('edit');

        Route::get('/{id}/hon-phoi/tao', MarriageCreateFromAnnouncement::class)
            ->middleware('role:parish_admin|parishioner_admin')
            ->name('create-marriage');

        Route::get('/{id}/xuat-giay-gioi-thieu-hon-phoi', MarriageAnnouncementGioiThieuHonPhoiExportController::class)
            ->middleware('role:parish_admin|parishioner_admin')
            ->name('export-gioi-thieu-hon-phoi');

        Route::get('/{id}', MarriageAnnouncementShow::class)
            ->name('show');
    });

    Route::middleware('role:parish_admin|parishioner_admin')->group(function () {
        Route::get('/ten-thanh', HolyManager::class)
            ->name('holy-names.index');

        Route::get('/giao-ho', ParishGroupManager::class)
            ->name('parish-group.index');

        Route::get('/hoi-doan', AssociationManager::class)
            ->name('associations.index');

        Route::get('/groups', GroupManager::class)
            ->name('groups.index');

        Route::get('/{groupId}/members', GroupMemberManager::class)
            ->name('groups.members');

        Route::get('/{groupId}/sessions', GroupSessionManager::class)
            ->name('groups.sessions');

        Route::get('/{groupId}/sessions/{sessionId}/attendance', GroupAttendance::class)
            ->name('groups.attendance');
    });
});

// Hồ sơ giáo dân — công khai (không cần đăng nhập)
Route::get('/giao-dan/{parishioner}', ParishionerShow::class)
    ->name('parishioners.show');

Route::get('{slug}', [SlugController::class, 'make']);

Route::post('search', [GiaoPhanController::class, 'getSearch'])->name('search');

Route::get('giadinh/export/', [GiaDinhController::class, 'export']);

Route::get('/import', [UsersImportController::class, 'show']);
Route::post('/import', [UsersImportController::class, 'store'])->name('import');

Route::get('/export', [UsersExportController::class, 'show']);
Route::post('/export', [UsersExportController::class, 'store'])->name('export');

Route::get('{slug}/lylichcanhan={id}', [LyLichCaNhanExportController::class, 'store']);
Route::get('{slug}/bitich={id}', [BitichExportController::class, 'store']);
Route::get('{slug}/giaygioithieugiaolyhonphoi={id}', [GiayGioiThieuGiaoLyHonPhoiExportController::class, 'store']);
Route::get('{slug}/giaygioithieuhonphoi={id}', [GiayGioiThieuHonPhoiExportController::class, 'store']);
Route::get('{slug}/giaydieutrahonphoi={id}', [GiayDieuTraHonPhoiExportController::class, 'store']);
Route::get('{slug}/sogiadinhconggiao={id}', [SoGiaDinhCongGiaoExportController::class, 'store']);
Route::get('{slug}/raohonphoinu={id}', [RaoHonPhoiNuController::class, 'store']);
Route::get('{slug}/kqraohonphoinu={id}', [KQRaoHonPhoiNuController::class, 'store']);
Route::get('{slug}/raohonphoinam={id}', [RaoHonPhoiNamController::class, 'store']);
Route::get('{slug}/kqraohonphoinam={id}', [KQRaoHonPhoiNamController::class, 'store']);

Route::get('{slug}/lophoc={id}', [LopHocController::class, 'show']);
Route::post('submit-info', [LopHocController::class, 'submitInfo'])->name('my-form');
Route::post('submit-infohk2', [LopHocController::class, 'submitInfoHk2'])->name('my-formhk2');

Route::get('{slug}/dile={id}', [DiLeController::class, 'index']);
Route::post('submit-info-dilehk1', [DiLeController::class, 'submitInfohk1'])->name('my-form-dilehk1');
Route::post('submit-info-dilehk2', [DiLeController::class, 'submitInfohk2'])->name('my-form-dilehk2');

Route::get('{slug}/khaokinh={id}', [KhaoKinhController::class, 'index']);
Route::post('submit-info-khaokinhhk1', [KhaoKinhController::class, 'submitInfohk1'])->name('my-form-khaokinhhk1');
Route::post('submit-info-khaokinhhk2', [KhaoKinhController::class, 'submitInfohk2'])->name('my-form-khaokinhhk2');

Route::post('submit-infos', [LopController::class, 'submitInfo'])->name('my-form-diemthi');

Route::post('submit-info-diemthi', [LopController::class, 'submitInfoUpdate'])->name('my-form-updatediem');

Route::get('{slug}/ketqua={id}', [KetQuaController::class, 'index']);

Route::post('submit-dihoc', [StudentController::class, 'submitInfoDiHoc'])->name('my-form-dihoc');

Route::post('submit-dile', [StudentController::class, 'submitInfoDiLe'])->name('my-form-dile');

Route::post('submit-qr', [LopController::class, 'submitInfoQr'])->name('my-form-qr');

Route::post('submit-kq', [KQController::class, 'submitInfoKQ'])->name('my-form-kq');

Route::get('{slug}/ketqua={id}', [KetQuaController::class, 'index']);

Route::get('{slug}/ketquadihoc={id}', [KetQuaDiHocController::class, 'index']);

Route::get('{slug}/ketquadile={id}', [KetQuaDiLeController::class, 'index']);

Route::get('{slug}/ketquakhaokinh={id}', [KetQuaKhaoKinhController::class, 'index']);

Route::post('submit-qrlop', [QRLopController::class, 'submitInfoQRLop'])->name('my-form-qrlop');

Route::get('{slug}/thugioithieu={id}', [ThuGioiThieuExportController::class, 'store']);

Route::get('/import-lop', [LopImportController::class, 'show']);
Route::post('/import-lop', [LopImportController::class, 'store'])->name('importlop');

Route::get('/import-teacher', [TeacherImportController::class, 'show']);
Route::post('/import-teacher', [TeacherImportController::class, 'store'])->name('importgv');

Route::get('/export-ketqua', [KetQuaExportController::class, 'show']);
Route::post('/export-ketqua', [KetQuaExportController::class, 'store'])->name('exportketqua');


Route::get('/export-honphoi', [HonPhoiExportController::class, 'show']);
Route::post('/export-honphoi', [HonPhoiExportController::class, 'store'])->name('exporthonphoi');


Route::post('submit-dihockq', [KQController::class, 'submitInfoKQdihoc'])->name('my-form-kqdihoc');
Route::post('submit-dilekq', [KQController::class, 'submitInfoKQdile'])->name('my-form-kqdile');
