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
use App\Http\Livewire\Attendance\AttendanceQr as AttendanceAttendanceQr;
use App\Http\Livewire\Attendance\SessionManager;
use App\Http\Livewire\AttendanceManager;
use App\Http\Livewire\CatechismClass\CatechismClassList;
use App\Http\Livewire\ClassStudentManager;
use App\Http\Livewire\Dashboard\AdminDashboard;
use App\Http\Livewire\Dashboard\CatechistDashboard;
use App\Http\Livewire\Holy\HolyManager;
use App\Http\Livewire\Landing;
use App\Http\Livewire\Lop\AssignTeacher;
use App\Http\Livewire\Lop\LopDetail;
use App\Http\Livewire\ModuleSelect;
use App\Http\Livewire\NamHoc\CopyNamHoc;
use App\Http\Livewire\NamHoc\NamHocManager;
use App\Http\Livewire\Parish\ParishChild;
use App\Http\Livewire\Parish\ParishGroup;
use App\Http\Livewire\Parish\ParishGroupManager;
use App\Http\Livewire\Parishioners\ParishionerShow;
use App\Http\Livewire\Parishioners\ParishionersManager;
use App\Http\Livewire\Parishioners\SacramentsManager;
use App\Http\Livewire\Score\ScoreManager;
use App\Http\Livewire\Student\PrintCards;
use App\Http\Livewire\Student\StudentDetail;
use App\Http\Livewire\Student\StudentEdit;
use App\Http\Livewire\Student\StudentImportPreview;
use App\Http\Livewire\Student\StudentListNew;
use App\Http\Livewire\Teacher\TeacherImportPreview;
use App\Http\Livewire\Teacher\TeacherManager;
use Illuminate\Support\Facades\Auth;

Paginator::useBootstrap();

// ========== GUEST ==========
Route::middleware('redirect.auth.dashboard')->group(function () {
    Route::get('/', Landing::class)->name('landing');
});

Route::get('/select-module', ModuleSelect::class)
    ->middleware('auth')
    ->name('module.select');

Auth::routes();

Route::middleware('auth')->group(function () {
    Route::get('/parish-admin-dashboard', AdminDashboard::class)
        ->name('parish-admin.dashboard');

    Route::get('/catechist-dashboard', CatechistDashboard::class)
        ->name('catechist.dashboard');

    Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user?->isCatechist()) {
        return redirect()->route('catechist.dashboard');
    }

    if ($user?->isParishAdmin()) {
        return redirect()->route('parish-admin.dashboard');
    }

    if ($user?->isSuperAdmin()) {
        return redirect('/admin/dashboard');
    }

    abort(403);
})->middleware('auth')->name('dashboard');

    Route::get('/parishioner', ParishionersManager::class)
        ->name('parishioners.index');
    Route::get('/parishioner/sacrament', SacramentsManager::class)
        ->name('parishioners.sacrament');
    Route::get('/parishioners/{parishioner}', ParishionerShow::class)
        ->name('parishioners.show');

    Route::middleware('role:parish_admin|catechist')->group(function () {

        Route::get('/attendance', AttendanceManager::class)
            ->name('attendance.show');

        Route::get('/attendance/qr', AttendanceAttendanceQr::class)
            ->name('attendance.qr');

        Route::get('/students', StudentListNew::class)
            ->name('students.index');

        Route::get('/students/create', StudentEdit::class)
            ->middleware('role:parish_admin')        
            ->name('students.create');

        Route::get('/students/{id}', StudentDetail::class)
            ->name('students.show');
    });

    Route::middleware('role:parish_admin')->group(function () {
        Route::get('/classes', CatechismClassList::class)
            ->name('classes.index');

        Route::get('/classes/{id}', LopDetail::class)
            ->name('classes.show');

        Route::get('/classes/{id}/students', ClassStudentManager::class)
            ->name('classes.students');

        Route::get('/classes/{id}/catechists', AssignTeacher::class)
            ->name('classes.catechists');

        Route::get('/studentss/import', StudentImportPreview::class)
            ->name('students.import');

        Route::get('/studentss/download-template', [StudentImportController::class, 'template'])
            ->name('students.import.template');

        Route::get('/students/{id}/edit', StudentEdit::class)
            ->name('students.edit');

        Route::get('/studentss/print-cards', PrintCards::class)
            ->name('students.print-cards');

        Route::get('/catechists/download-template', [TeacherImportController::class, 'template'])
            ->name('catechists.import.template');

        Route::get('/catechists/import', TeacherImportPreview::class)
            ->name('catechists.import');

        Route::get('/catechists', TeacherManager::class)
            ->name('catechists.index');

        // ── Others ────────────────────────────────────────────
        Route::get('/session', SessionManager::class)
            ->name('session.index');

        Route::get('/scores', ScoreManager::class)
            ->name('scores.index');

        Route::get('/school-years', NamHocManager::class)
            ->name('school-years.index');

        Route::get('/school-years/copy', CopyNamHoc::class)
            ->name('school-years.copy');

        Route::get('/holy-names', HolyManager::class)
            ->name('holy-names.index');

        Route::get('/parish-group', ParishGroupManager::class)
            ->name('parish-group.index');
    });
});


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
