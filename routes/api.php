<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ParishManagementController;
use App\Http\Controllers\Api\ParishController;
use App\Http\Controllers\Api\ParishionersController;
use App\Http\Controllers\Api\FamilyController;
use App\Http\Controllers\Api\MotherController;
use App\Http\Controllers\Api\FatherController;
use App\Http\Controllers\Api\ChildrenController;
use App\Http\Controllers\Api\PriestController;
use App\Http\Controllers\Api\MarriageAnnouncementLookupController;
use App\Http\Controllers\Api\MarriageAnnouncementController;
use App\Http\Controllers\Api\FemaleController;
use App\Http\Controllers\Api\MaleController;
use App\Http\Controllers\Api\LopController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\StudentsController;
use App\Http\Controllers\Api\BlocksController;
use App\Http\Controllers\Api\DecenController;
use App\Http\Controllers\Api\NamHocController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/ParishManagement', [ParishManagementController::class, 'index']);
Route::get('/Parish', [ParishController::class, 'index']);
Route::get('/Parishioners', [ParishionersController::class, 'index']);
Route::get('/Family', [FamilyController::class, 'index']);
Route::get('/Mother', [MotherController::class, 'index']);
Route::get('/Father', [FatherController::class, 'index']);
Route::get('/Children', [ChildrenController::class, 'index']);
Route::get('/Priest', [PriestController::class, 'index']);
Route::prefix('marriage-announcements')->group(function () {
    Route::get('/priests', [MarriageAnnouncementLookupController::class, 'priests']);
    Route::get('/dioceses', [MarriageAnnouncementLookupController::class, 'dioceses']);
    Route::get('/deaneries', [MarriageAnnouncementLookupController::class, 'deaneries']);
    Route::get('/parishes', [MarriageAnnouncementLookupController::class, 'parishes']);
    Route::get('/parish-groups', [MarriageAnnouncementLookupController::class, 'parishGroups']);
    Route::get('/parish-managements', [MarriageAnnouncementLookupController::class, 'parishManagements']);
    Route::get('/legacy-parishes', [MarriageAnnouncementLookupController::class, 'legacyParishes']);
    Route::get('/parishioners', [MarriageAnnouncementLookupController::class, 'parishioners']);
});
Route::get('/MarriageAnnouncement', [MarriageAnnouncementController::class, 'index']);
Route::get('/Female', [FemaleController::class, 'index']);
Route::get('/Male', [MaleController::class, 'index']);
Route::get('/Lop', [LopController::class, 'index']);
Route::get('/Student', [StudentController::class, 'index']);
Route::get('/Students', [StudentsController::class, 'index']);
Route::get('/Blocks', [BlocksController::class, 'index']);
Route::get('/Decen', [DecenController::class, 'index']);
Route::get('NamHoc', [NamHocController::class, 'index']);

//Route::get('/api/children/{id}', [ChildrenController::class, 'show']);
//php artisan make:controller Api/PriestController --api