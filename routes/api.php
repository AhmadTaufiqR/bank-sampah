<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmailController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WasteTypeController;
use App\Http\Controllers\Api\WithdrawalController;
use App\Http\Controllers\DashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:api');

Route::post('register', [AuthController::class, 'authRegister']);
Route::post('login', [AuthController::class, 'authLogin']);
Route::post('login-admin', [AuthController::class, 'authLoginAdmin']);
Route::post('register-admin', [AuthController::class, 'authRegisterAdmin']);


Route::get('/news/latest', [NewsController::class, 'getNewsList']);
Route::get('/news/{id}', [NewsController::class, 'getNewsDetail']);
Route::post('/news', [NewsController::class, 'createNews']);
Route::put('/news/{id}', [NewsController::class, 'updateNews']);
Route::delete('/news/{id}', [NewsController::class, 'deleteNews']);


Route::get('/notifications/{username}', [NotificationController::class, 'getNotifications']);


Route::get('/dashboard/{username}', [DashboardController::class, 'getDashboardData']);


Route::get('/waste-types', [WasteTypeController::class, 'getWasteTypes']);
Route::post('/waste-types', [WasteTypeController::class, 'createWasteType']);
Route::put('/waste-types/{id}', [WasteTypeController::class, 'updateWasteType']);
Route::delete('/waste-types/{id}', [WasteTypeController::class, 'deleteWasteType']);


Route::get('/schedules', [ScheduleController::class, 'getSchedules']);
Route::put('/schedules/{id}', [ScheduleController::class, 'updateSchedule']);


Route::put('/profile/{username}', [UserController::class, 'updateProfile']);


Route::get('/savings/{username}', [WithdrawalController::class, 'getSavingsDetails']);
Route::post('/withdrawal/{username}', [WithdrawalController::class, 'createWithdrawal']);
Route::put('/withdrawal/{id}/verify', [WithdrawalController::class, 'verifyWithdrawal']);
Route::get('/pending-withdrawals', [WithdrawalController::class, 'getPendingWithdrawals']);


Route::post('/forgot-password/user', [EmailController::class, 'sendOtpForUser']);
Route::post('/forgot-password/admin', [EmailController::class, 'sendOtpForAdmin']);
