<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\UserCrudController;
use App\Http\Controllers\Admin\RedirectCrudController;
use App\Http\Controllers\Admin\ParishManagementCrudController;
use App\Http\Controllers\Admin\HolymanagementCrudController;
use App\Http\Controllers\Admin\EthnicmanagementCrudController;
use App\Http\Controllers\Admin\PositionmanagementCrudController;
use App\Http\Controllers\Admin\CareermanagementCrudController;
use App\Http\Controllers\Admin\LevelmanagementCrudController;
use App\Http\Controllers\Admin\LanguagemanagementCrudController;
use App\Http\Controllers\Admin\SlugCrudController;
use App\Http\Controllers\Admin\CkfinderController;
use App\Http\Controllers\Admin\ParishionersCrudController;
use App\Http\Controllers\Admin\SacramentGiverCrudController;
use App\Http\Controllers\Admin\SponsorCrudController;
use App\Http\Controllers\Admin\DioceseCrudController;
use App\Http\Controllers\Admin\DeaneryCrudController;
use App\Http\Controllers\Admin\ParishCrudController;
use App\Http\Controllers\Admin\AssociationCrudController;
use App\Http\Controllers\Admin\GiaDinhCrudController;
use App\Http\Controllers\Admin\FamilyCrudController;
use App\Http\Controllers\Admin\FamilyAreaCrudController;
use App\Http\Controllers\Admin\MarriageAnnouncementCrudController;
use App\Http\Controllers\Admin\MenuCrudController;
use App\Http\Controllers\Admin\PageCrudController;
use App\Http\Controllers\Admin\TeacherCrudController;
use App\Http\Controllers\Admin\StudentCrudController;
use App\Http\Controllers\Admin\LopCrudController;
use App\Http\Controllers\Admin\BlockCrudController;
use App\Http\Controllers\Admin\DecenCrudController;
use App\Http\Controllers\Admin\SetAdminCrudController;
use App\Http\Controllers\Admin\NamHocCrudController;

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\Base.
// Routes you generate using Backpack\Generators will be placed here.

/*
 Route::group([
    'middleware' => 'web',
    'prefix' => config('backpack.base.route_prefix'),
], function () {
    //Route::get('dashboard', [AdminController::class, 'dashboard'])->name('backpack.dashboard');
    
    Route::get('parish{slug}', ParishController::class)->name('backpack');
});
 */
Route::group([
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    //'namespace'  => 'App\Http\Controllers\Admin',
], function () { // custom admin routes        
    Route::get('/ckfinder', \App\Http\Controllers\Admin\CkfinderController::class)->name('ckfinder');
    Route::any('/ckfinder/connector', '\CKSource\CKFinderBridge\Controller\CKFinderController@requestAction')->name('ckfinder_connector');
    Route::any('/ckfinder/browser', '\CKSource\CKFinderBridge\Controller\CKFinderController@browserAction')->name('ckfinder_browser');
    Route::any('/ckfinder/examples/{example?}', '\CKSource\CKFinderBridge\Controller\CKFinderController@examplesAction')->name('ckfinder_examples');
    
    Route::crud('user', UserCrudController::class);
    Route::crud('redirect', RedirectCrudController::class);
    Route::crud('slug', SlugCrudController::class);
    Route::crud('parish-management', ParishManagementCrudController::class);
    Route::crud('careermanagement', CareermanagementCrudController::class);
    Route::crud('ethnicmanagement', EthnicmanagementCrudController::class);
    Route::crud('holymanagement', HolymanagementCrudController::class);
    Route::crud('languagemanagement', LanguagemanagementCrudController::class);
    Route::crud('levelmanagement', LevelmanagementCrudController::class);
    Route::crud('positionmanagement', PositionmanagementCrudController::class);    
        
    Route::crud('parishioners', ParishionersCrudController::class);
    Route::crud('sacrament-giver', SacramentGiverCrudController::class);
    Route::crud('sponsor', SponsorCrudController::class);
    Route::crud('diocese', DioceseCrudController::class);
    Route::crud('deanery', DeaneryCrudController::class);
    Route::crud('parish', ParishCrudController::class);
    Route::crud('association', AssociationCrudController::class);
    
    Route::crud('gia-dinh', GiaDinhCrudController::class);
    Route::crud('family', FamilyCrudController::class);
    Route::crud('family-area', FamilyAreaCrudController::class);
    Route::crud('marriage-announcement', MarriageAnnouncementCrudController::class);
    Route::crud('menu', MenuCrudController::class);
    Route::crud('page', PageCrudController::class);
    Route::crud('teacher', TeacherCrudController::class);
    Route::crud('student', StudentCrudController::class);
    Route::crud('lop', LopCrudController::class);
    Route::crud('block', BlockCrudController::class);
    
    Route::crud('decen', DecenCrudController::class);
    Route::crud('set-admin', SetAdminCrudController::class);
    Route::crud('nam-hoc', NamHocCrudController::class);
}); // this should be the absolute last line of this file