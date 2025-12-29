<?php

use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

use App\Http\Controllers\SlugController;
use App\Models\ParishManagement;
use App\Http\Controllers\ParishManagementController;

use App\Http\Controllers\HomeController;
use App\Models\Page;
use App\Http\Controllers\PageController;
use App\Http\Controllers\GiaDinhController;
use App\Http\Controllers\GiaoPhanController;
use App\Http\Controllers\UsersImportController;
use App\Http\Controllers\UsersExportController;
use App\Http\Controllers\FamilyController;
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
use App\Models\Lop;
use App\Http\Controllers\KetQuaController;
use App\Models\Student;
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
use App\Http\Livewire\AttendanceManager;
use App\Http\Livewire\Block\Block;
use App\Http\Livewire\Block\BlockManager;
use App\Http\Livewire\Home;
use App\Http\Livewire\Lop\CreateEditClassForm;
use App\Http\Livewire\Lop\LopDetail;
use App\Http\Livewire\Lop\LopForm;
use App\Http\Livewire\Lop\LopList;
use App\Http\Livewire\NamHoc\NamHocManager;

Paginator::useBootstrap();

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*
Route::get('/', function () {
    return view('welcome');
});
*/
Auth::routes();

Route::get('/', Home::class)->name('home');
Route::get('/dashboard', function () {
    return view('frontend.dashboard');
})->middleware('auth')->name('dashboard');

Route::get('/ds-lop', LopList::class)->name('ds-lop');
Route::get('/lop/create', LopForm::class)->name('lop.create');
Route::get('/lop/{id}', LopDetail::class)->name('lop.show');
Route::get('/lop/{id}/edit', LopForm::class)->name('lop.edit');
Route::get('/attendance/{classId?}', AttendanceManager::class)->name('attendance');
Route::get('/nam-hoc', NamHocManager::class)->name('nam-hoc');
Route::get(
    '/nam-hoc/khoi',
    BlockManager::class
)->name('khoi.manage');


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
