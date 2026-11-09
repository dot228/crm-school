<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\HomeworkSubmissionController;
use App\Http\Controllers\LessonContentController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\StudentAvailabilityController;
use App\Http\Controllers\TeacherAvailabilityController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TariffPlanController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WayForPayController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('dashboard'));

Route::post('wayforpay/callback', [WayForPayController::class, 'callback'])->name('wayforpay.callback');

Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::get('/two-factor/verify',  [TwoFactorController::class, 'showVerify'])->name('two-factor.verify');
Route::post('/two-factor/verify', [TwoFactorController::class, 'verify'])->name('two-factor.verify.submit');

Route::middleware(['auth', '2fa'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/two-factor/setup',            [TwoFactorController::class, 'showSetup'])->name('two-factor.setup');
    Route::post('/two-factor/enable',          [TwoFactorController::class, 'enable'])->name('two-factor.enable');
    Route::get('/two-factor/manage',           [TwoFactorController::class, 'manage'])->name('two-factor.manage');
    Route::post('/two-factor/disable',         [TwoFactorController::class, 'disable'])->name('two-factor.disable');
    Route::post('/two-factor/regenerate-codes',[TwoFactorController::class, 'regenerateCodes'])->name('two-factor.regenerate');

    Route::middleware('role:1,2')->group(function () {
        Route::post('users/{id}/block', [UserController::class, 'block'])->name('users.block');
        Route::resource('users',       UserController::class);
        Route::resource('groups',      GroupController::class);
        Route::resource('lessons',     LessonController::class);
        Route::resource('enrollments', EnrollmentController::class);
        Route::resource('payments',    PaymentController::class);

        Route::prefix('lessons/{lesson}/contents')->name('lessons.contents.')->group(function () {
            Route::get('create',              [LessonContentController::class, 'create'])->name('create');
            Route::post('/',                  [LessonContentController::class, 'store'])->name('store');
            Route::get('{lessonContent}/edit',[LessonContentController::class, 'edit'])->name('edit');
            Route::put('{lessonContent}',     [LessonContentController::class, 'update'])->name('update');
            Route::delete('{lessonContent}',  [LessonContentController::class, 'destroy'])->name('destroy');
        });
    });

    Route::middleware('role:1,2,3')->group(function () {
        Route::resource('schedules', ScheduleController::class);
        Route::post('schedules/{schedule}/status', [ScheduleController::class, 'updateStatus'])->name('schedules.updateStatus');
    });

    Route::middleware('role:3')->group(function () {
        Route::get('teacher/availability',         [TeacherAvailabilityController::class, 'index'])->name('teacher.availability');
        Route::post('teacher/availability',        [TeacherAvailabilityController::class, 'store'])->name('teacher.availability.store');
        Route::get('teacher/students',             [TeacherController::class, 'students'])->name('teacher.students');
        Route::get('teacher/students/{id}',        [TeacherController::class, 'studentShow'])->name('teacher.students.show');
        Route::get('teacher/lessons',                         [TeacherController::class, 'lessons'])->name('teacher.lessons');
        Route::get('teacher/lessons/{lesson}/homework',       [TeacherController::class, 'homeworkEdit'])->name('teacher.lessons.homework.edit');
        Route::post('teacher/lessons/{lesson}/homework',      [TeacherController::class, 'homeworkUpdate'])->name('teacher.lessons.homework.update');
        Route::get('teacher/homework',             [HomeworkSubmissionController::class, 'index'])->name('teacher.homework.index');
        Route::get('teacher/homework/{homeworkSubmission}', [HomeworkSubmissionController::class, 'show'])->name('teacher.homework.show');
        Route::post('teacher/homework/{homeworkSubmission}/review', [HomeworkSubmissionController::class, 'review'])->name('teacher.homework.review');
    });

    Route::middleware('role:4')->group(function () {
        Route::get('student/schedule',                    [StudentController::class, 'schedule'])->name('student.schedule');
        Route::get('student/homework',                          [StudentController::class, 'homework'])->name('student.homework');
        Route::get('student/homework/{submission}',             [StudentController::class, 'homeworkShow'])->name('student.homework.show');
        Route::post('student/homework/{submission}/submit',     [StudentController::class, 'submitHomework'])->name('student.homework.submit');
        Route::get('student/payments',                          [StudentController::class, 'payments'])->name('student.payments');
        Route::post('student/payments/pay',                     [StudentController::class, 'payTariff'])->name('student.payments.pay');
        Route::post('student/payments/topup-money',             [StudentController::class, 'topupMoney'])->name('student.payments.topup');
        Route::post('student/payments/wayforpay',               [WayForPayController::class, 'initiate'])->name('wayforpay.initiate');
        Route::post('student/payments/wayforpay-money',         [WayForPayController::class, 'initiateMoneyTopup'])->name('wayforpay.money');
        Route::get('student/availability',                       [StudentAvailabilityController::class, 'index'])->name('student.availability');
        Route::post('student/availability',                      [StudentAvailabilityController::class, 'store'])->name('student.availability.store');
    });

    Route::get('/profile',       [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit',   [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile',        [ProfileController::class, 'update'])->name('profile.update');

    Route::middleware('role:1')->group(function () {
        Route::resource('courses',     CourseController::class);
        Route::resource('tariff-plans', TariffPlanController::class)->except('show');
        Route::get('settings/zoom',       [SettingController::class, 'zoom'])->name('settings.zoom');
        Route::post('settings/zoom',      [SettingController::class, 'updateZoom'])->name('settings.zoom.update');
        Route::get('settings/wayforpay',  [SettingController::class, 'wayforpay'])->name('settings.wayforpay');
        Route::post('settings/wayforpay', [SettingController::class, 'updateWayforpay'])->name('settings.wayforpay.update');
    });
});
