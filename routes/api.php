<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BankController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EmailController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\UserBalanceController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WasteTypeController;
use App\Http\Controllers\Api\WithdrawalController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;

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


Route::get('/notifications/{email}', [NotificationController::class, 'getNotifications']);
Route::get('/notifications-admin', [NotificationController::class, 'getNotificationsAdmin']);


Route::get('/dashboard/{email}', [DashboardController::class, 'getDashboardData']);
Route::get('/dashboard/admin/{email}', [DashboardController::class, 'getDashboardDataAdmin']);


Route::get('/waste-types', [WasteTypeController::class, 'getWasteTypes']);
Route::post('/waste-types', [WasteTypeController::class, 'createWasteType']);
Route::post('/waste-types/{id}', [WasteTypeController::class, 'updateWasteType']);
Route::delete('/waste-types/{id}', [WasteTypeController::class, 'deleteWasteType']);


Route::get('/schedules', [ScheduleController::class, 'getSchedules']);
Route::post('/schedules', [ScheduleController::class, 'store']);
Route::put('/schedules/{id}', [ScheduleController::class, 'updateDates']);
Route::delete('/schedules', [ScheduleController::class, 'deleteAll']);


Route::post('/profile-update/{email}', [UserController::class, 'updateProfile']);
Route::get('profile/{email}', [UserController::class, 'userProfile']);
Route::post('/profile-update/admin/{email}', [UserController::class, 'updateProfileAdmin']);
Route::get('profile/admin/{email}', [UserController::class, 'userProfileAdmin']);
Route::get('profile/detail/admin/{email}', [UserController::class, 'getUserProfile']);
Route::get('profile/list/admin', [UserController::class, 'getUserList']);
Route::get('/users/ranking', [UserController::class, 'getUserRanking']);


Route::get('/savings/{email}', [WithdrawalController::class, 'getSavingsDetails']);
Route::post('/withdrawal/{email}', [WithdrawalController::class, 'createWithdrawal']);
Route::post('/withdrawal/{id}/verify', [WithdrawalController::class, 'verifyWithdrawal']);
Route::post('/withdrawal/{id}/reject', [WithdrawalController::class, 'rejectWithdrawal']);
Route::get('/pending-withdrawals', [WithdrawalController::class, 'getWithdrawalsWithPriority']);


Route::get('/balance-history/{email}', [UserBalanceController::class, 'getBalanceAndHistory']);
Route::post('/transaction', [UserBalanceController::class, 'createTransaction']);
Route::post('/waste-transaction', [UserBalanceController::class, 'createWasteTransaction']);
Route::get('/history-transaction/{id}', [UserBalanceController::class, 'showByDateRaw']);

Route::get('/bank-balance', [BankController::class, 'index']);

Route::post('send-fcm-notification', [UserController::class, 'sendFcmNotification']);
Route::get('check/{email}/user', [UserController::class, 'checkEmailUser']);
Route::get('check/{email}/admin', [UserController::class, 'checkEmailAdmin']);


Route::get('/report/download', [ReportController::class, 'downloadReport']);
Route::get('/report/download/year', function () {
    return Excel::download(new \App\Exports\WasteRekapMultiYearExport, 'rekap_per_tahun.xlsx');
});
