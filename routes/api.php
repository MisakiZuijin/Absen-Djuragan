<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HandRaiseController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SchedulerController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\UserController;
use App\Models\AdjustableAttd;
use App\Models\Attendance;
use App\Services\ShiftService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Attendance API Routes
Route::post('/attendance/action', [AttendanceController::class, 'actionPresence']);
Route::get('/attendance', [AttendanceController::class, 'attendaceAdmin']);
Route::get('/attendance/detail', [AttendanceController::class, 'attendanceDetailAdmin']);
Route::get('/report-attendance', [AttendanceController::class, 'actionAttendaceReport']);

// Scheduler API Routes
Route::get('/scheduler', [SchedulerController::class, 'scheduleAttendance']);

// Raise Hand API Routes
Route::get('/raise-hand/latest', [HandRaiseController::class, 'latest']);

// Schedule API Routes
Route::patch('/schedule/shift/update/{id}', [ScheduleController::class, 'updateSchedule']);
Route::put('/schedule/update/single/{internId}', [ScheduleController::class, "scheduleUpdateSingle"]);
Route::post('/schedule/multi/update/{internId}', [ScheduleController::class, "scheduleUpdateMulti"]);

// Additional Attendance API Routes
Route::controller(AttendanceController::class)->group(function () {
    Route::post('/attendance/updateTime/{id}', 'updateTime');
    Route::post('/attendance/updateStatus/{id}', 'updateStatus');
    Route::post('/attendance/delete/{id}', 'delete');
    Route::post('/attendance/reset/{id}', 'reset');
    Route::post('/attendance/updatestatusattd/{id}', 'updatestatusattd');
    Route::post('/adjustable-attendance/update-status/{id}', 'updateStatusAdjustable');
    Route::post('/add-permit-reason', 'addPermitPresenceadmin');
    Route::post('/update-permit-reason', 'updatePermitPresence');
    Route::post('/detail-schedule/update', 'updateShift');
    Route::post('/intern/storeNote/{id}', 'storeNote');
    Route::post('/attendance/notify-alpha/bulk', 'sendBulkAlphaNotifications');
    Route::post('/attendance/notify-alpha/{id}', 'sendAlphaNotification');
    Route::post('/attendance/notify-permit/bulk', 'sendBulkPermitNotifications');
    Route::post('/attendance/notify-permit/{id}', 'sendPermitNotification');
    Route::post('/attendance/toilet/return', 'returnFromToilet')->middleware('auth');
});