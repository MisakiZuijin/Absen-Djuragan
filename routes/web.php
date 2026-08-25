<?php

use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\InternController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\AdminMapsController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LogActivityController;
use App\Http\Controllers\DiscountTimeController;
use App\Http\Controllers\SettingShiftController;
use App\Http\Controllers\SettingOfficeController;
use App\Http\Controllers\SettingSchoolController;
use App\Http\Controllers\SettingHolidayController;
use App\Http\Controllers\SettingProjectController;
use App\Http\Controllers\SettingDivisionController;
use App\Http\Controllers\BroadcastController;
use App\Http\Controllers\OutsiderDashboardController;
use App\Http\Controllers\AssistantAdminController;
use App\Http\Controllers\HandRaiseController;
use App\Http\Controllers\OutsiderController;
use App\Http\Controllers\AdminIzinKeluarController;
use App\Http\Controllers\AdminIzinShalatController;
use App\Http\Controllers\AdminIzinToiletController;
use App\Http\Controllers\HrMonitoringController;
use App\Http\Controllers\PrayerController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\NotificationStreamController;
use App\Http\Controllers\LateAbsenceController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Platform Check
Route::get('/platform-check', function () {
    $agent = new Agent();
    return ['platform' => $agent->platform(), 'browser' => $agent->browser()];
})->name('platform-check');

// Authentication Routes
Route::controller(AuthController::class)->group(function () {
    Route::get('/', 'loginView')->name('login.view');
    Route::post('/loginAction', 'loginAction')->name('login.action');
    Route::get('/logout', 'logoutAction')->name('logout.action');
    Route::get('/user/create', 'registerView')->name('register.view');
    Route::post('/user/action/create', 'insertUser')->name('register.action');
    Route::get('/user/forget-password', 'forgetPasswordView')->name('forget-password.view');
    Route::post('/user/action/forget-password', 'forgetPasswordAction')->name('forget-password.action');
    Route::get('/user/verify/otp', 'validationOptView')->name('validation.view');
    Route::get('/user/change-password/{jwt}', 'changePasswordView')->name('change-password.view');
    Route::post('/user/action/change-password/{jwt}', 'changePasswordAction')->name('change-password.action');
    Route::get('/user/notif/success', 'successView')->name('notif.success.view');
});

// User Routes (Intern)
Route::prefix('user')->middleware('role:3')->group(function () {
    Route::controller(UserController::class)->group(function () {
        Route::get('/home', 'userView')->name('user.home');
        Route::get('/home/attendance/change', 'attendanceChangeView')->name('user.attendance.change.view');
        Route::post('/add-permit-reason-user', 'addPermitPresence')->name('attendance.addPermitPresenceUser');
        Route::post('/whatsapp-notification/toggle', 'toggleWhatsappNotification')->name('user.whatsapp.toggle');
        Route::post('/whatsapp-notification/test', 'testWhatsappNotification')->name('user.whatsapp.test');
        Route::post('/permit/start', 'startPermit')->name('user.permit.start');
        Route::post('/permit/end', 'endPermit')->name('user.permit.end');
    });

    Route::controller(AttendanceController::class)->group(function () {
        Route::post('/home/action/presence/{stage}', 'actionPresence')->name('home.action.presence');
    });

    Route::controller(LogActivityController::class)->group(function () {
        Route::post('/home/action/log-activity', 'logActivityAction')->name('home.logActivity.action');
        Route::post('/home/action/update/log-activity', 'updateLogActivityAction')->name('home.logActivity.update.action');
        Route::get('/home/history-activity', 'historyActivityView')->name('home.historyActivity');
    });

    Route::controller(InternController::class)->group(function () {
        Route::post('/raise-hand/toggle', 'raiseHandToggle')->name('intern.raisehand.toggle');
        Route::post('/prayer/toggle', 'prayerToggle')->name('intern.prayer.toggle');
    });

    Route::controller(PrayerController::class)->group(function () {
        Route::post('/prayer/start', 'startPrayer')->name('prayer.start');
        Route::post('/prayer/end', 'endPrayer')->name('prayer.end');
        Route::get('/prayer/status', 'getPrayerStatus')->name('prayer.status');
        Route::get('/prayer/history', 'getPrayerHistory')->name('prayer.history');
        Route::post('/prayer/cancel', 'cancelPrayer')->name('prayer.cancel');
    });

    Route::controller(HrMonitoringController::class)->group(function(){
        Route::get('/monitor/toilet', 'monitorToilet')->name('hr.monitor.toilet');
        Route::get('/monitor/prayer', 'monitorPrayer')->name('hr.monitor.prayer');
        Route::get('/monitor/toilet/data', 'getToiletData')->name('hr.monitor.toilet.data');
        Route::get('/monitor/prayer/data', 'getPrayerData')->name('hr.monitor.prayer.data');
        Route::get('/toilet/history/{intern}', 'toiletHistoryDetail')->name('hr.toilet.history.detail');
        Route::get('/prayer/history/{intern}', 'prayerHistoryDetail')->name('hr.prayer.history.detail');
    });

    Route::controller(ScheduleController::class)->group(function () {
        Route::get('/ganti-jam', 'showChangeTimePage')->name('user.change-time.index');
    });
});

// Admin Routes
Route::prefix('admin')->middleware('role:1')->group(function () {

    Route::controller(AdminController::class)->group(function () {
        Route::get('/home', 'homeView')->name('admin.home');
        Route::get('/report', 'reportView')->name('admin.report');
        Route::get('/presensi-otomatis', 'tabelView')->name('admin.tabel-view');
        Route::get('/report/download', 'reportDownload')->name('admin.download-report');
        Route::get('/presence', 'presenceView')->name('admin.presence');
        Route::get('/presence/detail/{intern_id}', 'prensenceDetailView')->name('admin.presence.detail');
    });

    Route::controller(NotificationStreamController::class)->group(function () {
        Route::get('/notifications/stream', 'stream')->name('admin.notifications.stream');
        Route::get('/notifications/enhanced-count', 'getEnhancedCount')->name('admin.notifications.enhanced-count');
    });

   Route::controller(AttendanceController::class)->group(function () {
    Route::get('/presence', [AdminController::class, 'presenceView'])->name('admin.presence');
    Route::get('/presence/detail/{intern_id}', [AdminController::class, 'prensenceDetailView'])->name('admin.presence.detail');
    Route::post('/attendance/updateTime/{id}', 'updateTime')->name('attendance.updateTime');
    Route::post('/attendance/updateStatus/{id}', 'updateStatus')->name('attendance.updateStatus');
    Route::post('/attendance/delete/{id}', 'delete')->name('attendance.delete');

// Ubah route untuk mendukung metode DELETE
    Route::delete('/adjustable-attendance/delete/{id}', 'deleteAdjustableAttendance')->name('adjustable-attendance.delete');
    Route::post('/adjustable-attendance/restore/{id}', 'restoreAdjustableAttendance')->name('adjustable-attendance.restore');

    Route::post('/attendance/reset/{id}', 'reset')->name('attendance.reset');
    Route::post('/attendance/updatestatusattd/{id}', 'updatestatusattd')->name('attendance.updatestatusattd');
    Route::post('/adjustable-attendance/update-status/{id}', 'updateStatusAdjustable')->name('attendance.updateStatusAdjustable');
    Route::post('/add-permit-reason', 'addPermitPresenceadmin')->name('attendance.addPermitPresenceadmin');
    Route::post('/update-permit-reason', 'updatePermitPresence')->name('attendance.updatePermitPresence');
    Route::get('/presence/location/user', 'locationUser')->name('location.user.view');
    Route::post('/detail-schedule/update', 'updateShift')->name('update.Shift');
    Route::post('/intern/storeNote/{id}', 'storeNote')->name('intern.storeNote');
    Route::get('/presence/log-activity/{internId}', 'show')->name('log-activity.show');
    Route::get('/report/{internId}/', 'reportUserPDF')->name('download-report-user.pdf');
    Route::get('/presence/report/', 'reportPDF')->name('download-report.pdf');
    Route::post('/attendance/notify-alpha/bulk', 'sendBulkAlphaNotifications')->name('admin.attendance.notify.alpha.bulk');
    Route::post('/attendance/notify-alpha/{id}', 'sendAlphaNotification')->name('admin.attendance.notify.alpha');
    Route::post('/attendance/notify-permit/bulk', 'sendBulkPermitNotifications')->name('admin.attendance.notify.permit.bulk');
    Route::post('/attendance/notify-permit/{id}', 'sendPermitNotification')->name('admin.attendance.notify.permit');
    Route::post('/attendance/toilet/return', 'returnFromToilet')->name('attendance.toilet.return')->middleware('auth');
    Route::get('/detail-auto-attendance/{id}', 'detailAutoAttendance')->name('admin.detail.autoAttd');
    Route::get('/api/attendance/detail', 'getDetail');
    Route::get('/attendance/detail', [AttendanceController::class, 'attendanceDetailAdmin']);
});

    Route::controller(ShiftController::class)->group(function () {
        Route::get('shifts/{name?}', 'index')->name('admin.shift.index');
        Route::put('shifts/{id}', 'update')->name('admin.shift.update');
        Route::get('shifts/bulk-update/form', 'showBulkUpdateForm')->name('admin.shifts.bulk-update.form');
        Route::post('shifts/bulk-update', 'bulkUpdate')->name('admin.shifts.bulk-update');
        Route::get('/api/interns-by-school/{school}', 'getInternsBySchool')->name('admin.api.interns-by-school');
        Route::post('/api/interns-by-schools', 'getInternsBySchools')->name('admin.api.interns-by-schools');
    });

    Route::controller(AdminIzinKeluarController::class)->group(function () {
        Route::get('/izin-keluar', 'index')->name('admin.izinKeluar.index');
        Route::get('/izin-keluar/duration/{permitLog}', 'getLeaveDuration')->name('admin.izinKeluar.duration');
        Route::get('/izin-keluar/history/{intern}', 'showKeluarHistoryDetail')->name('admin.keluar.history.detail');
    });

    Route::controller(AdminIzinShalatController::class)->group(function () {
        Route::get('/izin-shalat', 'index')->name('admin.izinShalat.index');
        Route::get('/izin-shalat/duration/{permitLog}', 'getPrayerDuration')->name('admin.izinShalat.duration');
        Route::get('/izin-shalat/history/{intern}', 'prayerHistoryDetail')->name('admin.prayer.history.detail');
    });

    Route::controller(AdminIzinToiletController::class)->group(function () {
        Route::get('/izin-toilet', 'index')->name('admin.izinToilet.index');
        Route::get('/izin-toilet/duration/{permitLog}', 'getToiletDuration')->name('admin.izinToilet.duration');
        Route::get('/izin-toilet/history', 'toiletHistory')->name('admin.toilet.history');
        Route::get('/izin-toilet/history/{intern}', 'toiletHistoryDetail')->name('admin.toilet.history.detail');
    });

    Route::name('admin.')->group(function () {
        Route::resource('outsiders', OutsiderController::class)->parameters(['outsiders' => 'user']);
        Route::patch('/outsiders/{user}/toggle-status', [OutsiderController::class, 'toggleStatus'])->name('outsiders.toggle-status');
        Route::patch('/outsiders/{user}/reset-password', [OutsiderController::class, 'resetPassword'])->name('outsiders.reset-password');
        Route::resource('assistant-admins', AssistantAdminController::class);
    });

    Route::get('/outsiders/presensi/dashboard', [OutsiderController::class, 'presensiDashboard'])->name('outsiders.presensi.dashboard');

    Route::controller(HandRaiseController::class)->group(function () {
        Route::get('/raise-hand', 'index')->name('admin.raiseHand.index');
        Route::get('/raise-hand/table-data', 'getTableData')->name('admin.raiseHand.tableData');
        Route::delete('/raise-hand/{id}/confirm', 'confirmAction')->name('admin.raiseHand.confirm');
        Route::get('/raise-hand/count', 'getCount')->name('admin.raiseHand.count');
        Route::get('/raise-hand/count-enhanced', 'getEnhancedCount')->name('admin.raiseHand.count-enhanced');
        Route::post('/raise-hand/{id}/quick-resolve', 'quickResolve')->name('admin.raiseHand.quick-resolve');
        Route::get('/raise-hand/poll-notifications', 'pollNotifications')->name('admin.raiseHand.poll-notifications');
        Route::get('/api/raise-hand/latest', [HandRaiseController::class, 'latest']);
    });

    Route::controller(LogActivityController::class)->group(function () {
        Route::post('/log-activity/update-status/{id}', 'updateStatus');
        Route::post('/log-activity/yesall/{date}', 'yesall');
        Route::post('/log-activity/update-isi/{id}', 'updateIsi');
    });

    Route::controller(DivisionController::class)->group(function () {
        Route::get('/division', 'divisionView')->name('admin.division');
        Route::get('/division/team/{divisionId}', 'divisionTeamView')->name('admin.division.team');
        Route::get('/divisi/edit/{userId}', 'divisionTeamEditView')->name('admin.division.edit.view');
        Route::delete('/delete/user/{internId}', 'destroy')->name('admin.intern.destroy');
        Route::post('/bulk-action', 'bulkAction')->name('bulk.action');
    });

    Route::post('/intern/update', [InternController::class, 'adminUpdateInternAction'])->name('admin.update.intern.action');

    Route::controller(SchoolController::class)->group(function () {
        Route::get('/sekolah', 'adminSchoolView')->name('admin.school.view');
        Route::get('/sekolah/anggota/{schoolId}', 'adminSchoolTeamView')->name('admin.school.team.view');
    });

    Route::controller(ScheduleController::class)->group(function () {
        Route::post('/shft/update', 'createSchedule')->name('create.Schedule');
        Route::get('/shift/update/{internId}', 'scheduleUpdateView')->name('admin.shift.schedule.update');
    });

    Route::get('/late-absence', [App\Http\Controllers\LateAbsenceController::class, 'index'])
        ->name('admin.late-absence.index');

    Route::get('/late-absence/create', [App\Http\Controllers\LateAbsenceController::class, 'create'])
        ->name('admin.late-absence.create');
    Route::post('/late-absence/store', [App\Http\Controllers\LateAbsenceController::class, 'store'])
        ->name('admin.late-absence.store');

    Route::get('/late-absence/scan', [App\Http\Controllers\LateAbsenceController::class, 'scanLateAbsences'])
        ->name('admin.late-absence.scan');

    Route::post('/late-absence/{id}/update-status', [App\Http\Controllers\LateAbsenceController::class, 'updateStatus'])
        ->name('admin.late-absence.update-status');
    Route::delete('/late-absence/{id}', [App\Http\Controllers\LateAbsenceController::class, 'destroy'])
        ->name('admin.late-absence.destroy');

    Route::post('/late-absence/bulk-update', [App\Http\Controllers\LateAbsenceController::class, 'bulkUpdateStatus'])
        ->name('admin.late-absence.bulk-update');


    Route::get('/late-absence/export-pdf', [LateAbsenceController::class, 'exportPdf'])->name('admin.late-absence.export.pdf');

    Route::get('/late-absence/{id}/details', [App\Http\Controllers\LateAbsenceController::class, 'getDetails'])
        ->name('admin.late-absence.details');

    Route::post('/discount-time', [DiscountTimeController::class, 'store'])->name('discount-time.store');

    Route::prefix('setting')->group(function () {
        Route::controller(SettingController::class)->group(function () {
            Route::get('/', 'adminSettingView')->name('admin.pengaturan.view');
            Route::get('/edit-profile', 'profileSettingView')->name('profile.pengaturan.view');
            Route::put('/edit-profile/{id}', 'updateProfile')->name('profiles.update');
            Route::post('/add-quotes', 'storeQuote')->name('quotes.store');
            Route::post('/add-quotesultah', 'storeQuoteUltah')->name('quotes.ultah.store');
            Route::delete('/quotes/{id}', 'deleteQuote')->name('quotes.delete');
            Route::get('/izin', 'managePermitSettingsView')->name('admin.pengaturan.izin.view');
            Route::post('/izin', 'updatePermitSettings')->name('admin.pengaturan.izin.update');
        });

        Route::controller(SettingProjectController::class)->group(function () {
            Route::get('/project', 'adminSettingProjectView')->name('admin.pengaturan.project');
            Route::post('/add-project', 'storeProject')->name('projects.store');
            Route::get('/projects/{id}/edit', 'edit')->name('projects.edit');
            Route::put('/projects/update/{id}', 'update')->name('projects.update');
            Route::delete('/delete-projects/{id}', 'destroy')->name('projects.destroy');
            Route::post('/projects/{id}/update-status', 'updateStatus');
            Route::post('/add-name-project', 'storeNameProject')->name('projects.nameProject');
        });

        Route::controller(SettingShiftController::class)->group(function () {
            Route::get('/shift', 'adminSettingShiftView')->name('admin.pengaturan.shift');
            Route::post('/add-shift', 'storeShift')->name('shifts.store');
            Route::put('/shift/{id}', 'updateShift')->name('shifts.update');
            Route::delete('/delete-shift/{id}', 'deleteShift')->name('shifts.delete');
        });

        Route::controller(SettingDivisionController::class)->group(function () {
            Route::get('/division', 'adminSettingDivisiView')->name('admin.pengaturan.divisi');
            Route::post('/add-division', 'storeDivision')->name('divisions.store');
            Route::put('/division/{id}', 'updateDivision')->name('divisions.update');
            Route::delete('/delete-division/{id}', 'deleteDivision')->name('divisions.delete');
        });

        Route::controller(SettingSchoolController::class)->group(function () {
            Route::get('/schooll', 'adminSettingSekolahView')->name('admin.pengaturan.sekolah');
            Route::post('/add-schooll', 'storeSchool')->name('schools.store');
            Route::put('/update-schooll/{id}', 'updateSchool')->name('schools.update');
            Route::delete('/delete-schooll/{id}', 'deleteSchool')->name('schools.delete');
        });

        Route::controller(SettingOfficeController::class)->group(function () {
            Route::get('/office', 'adminSettingKantorView')->name('admin.pengaturan.kantor');
            Route::post('/add-office', 'storeOffice')->name('offices.store');
            Route::get('/office/edit/{id}', 'showEditLocation')->name('offices.editLocation');
            Route::post('/office/{id}', 'updateOffice')->name('offices.update');
            Route::delete('/delete-office/{id}', 'deleteOffice')->name('offices.delete');
        });

        Route::controller(SettingHolidayController::class)->group(function () {
            Route::get('/holiday', 'adminSettingHolidayView')->name('admin.pengaturan.holiday');
            Route::post('/add-holiday', 'storeHoliday')->name('holidays.store');
            Route::post('/update-holiday/{id}', 'updateHoliday')->name('holidays.update');
            Route::delete('/delete-holiday/{id}', 'deleteHoliday')->name('holidays.delete');
        });

        Route::get('/office/maps', [AdminMapsController::class, 'officeMapsView'])->name('office.maps.view');
    });

    Route::controller(BroadcastController::class)->prefix('broadcasts')->group(function () {
        Route::get('/', 'index')->name('admin.pengaturan.broadcast');
        Route::post('/', 'store')->name('broadcast.store');
        Route::put('/{broadcast}', 'update')->name('broadcast.update');
        Route::delete('/{broadcast}', 'destroy')->name('broadcast.delete');
    });
});

Route::middleware(['auth', 'role:1'])->group(function () {
    Route::get('/api/admin/notifications/poll', [HandRaiseController::class, 'pollNotifications'])
        ->name('admin.notifications.poll');
});

Route::middleware(['auth', 'role:5'])->prefix('outsider')->name('outsider.')->group(function () {
    Route::controller(OutsiderDashboardController::class)->group(function () {
        Route::get('/', 'index')->name('dashboard');
        Route::prefix('presensi')->group(function () {
            Route::get('/', 'index')->name('presensi.index');
            Route::get('/filter', 'filterData')->name('presensi.filter');
            Route::get('/{intern_id}', 'show')->name('presensi.show');
            Route::get('/log/{internId}', 'showLogActivity')->name('log');
        });
    });
});

Route::middleware(['auth', 'role:6'])->prefix('assistant-admin')->name('assistant.')->group(function () {
    Route::controller(AssistantAdminController::class)->group(function () {
        Route::get('/', 'dashboard')->name('dashboard');
        Route::get('/log-activity', 'logActivity')->name('logactivity');
        Route::get('/log-activity/{log}/confirm', 'confirmLogActivity')->name('logactivity.confirm');
        Route::patch('/log-activity/{id}/update', 'updateLogActivity')->name('logactivity.update');
        Route::get('/raise-hand/list', 'raiseHandList')->name('raisehand.list');
        Route::post('/raise-hand/{id}/confirm', 'confirmHandRaise')->name('raisehand.confirm');
        Route::post('/raise-hand/{id}/assign', 'confirmRaiseHandAction')->name('raisehand.assign');
        Route::get('/izin/leave', 'izinLeave')->name('izin.leave.index');
        Route::get('/izin/leave/history/{intern}', 'showLeaveHistory')->name('izin.leave.history');
        Route::get('/permit-log/leave/{permitLog}/duration', 'getLeaveDuration')->name('permit.leave.duration');
        Route::get('/izin/prayer', 'izinPrayer')->name('izin.prayer.index');
        Route::get('/izin/prayer/history/{intern}', 'showPrayerHistory')->name('izin.prayer.history');
        Route::get('/permit-log/prayer/{permitLog}/duration', 'getPrayerDuration')->name('permit.prayer.duration');
        Route::get('/izin/toilet', 'izinToilet')->name('izin.toilet.index');
        Route::get('/izin/toilet/history/{intern}', 'showToiletHistory')->name('izin.toilet.history');
        Route::get('/permit-log/toilet/{permitLog}/duration', 'getToiletDuration')->name('permit.toilet.duration');
    });
});

Route::middleware(['auth'])->group(function () {
    Route::get('/raise-hand/count', [HandRaiseController::class, 'getCount']);
});

