<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ClasseController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LecturerController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\VenueController;
use App\Http\Controllers\LevelController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

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

Route::get('/', function () {
    return Inertia('Welcome');
})->middleware('guest')->name('home');


/* ---------- Admin Route ------------ */

Route::prefix('admin')->group(function () {

    Route::middleware('guest')->group(function () {

        Route::get('/login', [AdminController::class, 'showLoginForm'])->name('admin.show.login');

        Route::post('/login', [AdminController::class, 'login'])->name('admin.login');

        Route::get('forgot-password', [AdminController::class, 'showForgotPasswordForm'])
            ->name('admin.password.request');

        Route::post('forgot-password', [AdminController::class, 'requestPassword'])
            ->name('admin.password.email');

        Route::get('reset-password/{token}', [AdminController::class, 'showResetPasswordForm'])
            ->name('admin.password.reset');

        Route::post('reset-password', [AdminController::class, 'storePassword'])
            ->name('admin.password.store');
    });

    Route::middleware('admin')->group(function () {

        Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');

        Route::get('/profile', [AdminController::class, 'edit'])->name('admin.profile.edit');
        Route::patch('/profile', [AdminController::class, 'update'])->name('admin.profile.update');
        Route::put('password', [AdminController::class, 'updatePassword'])->name('admin.password.update');

        Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');

        // setup routes
        Route::prefix('setup')->group(function () {
            // admins routes
            Route::prefix('admin')->group(function () {
                Route::get('/', [AdminController::class, 'showAdmin'])->name('admin.index');
                Route::get('/fetch', [AdminController::class, 'fetch'])->name('admin.fetch');
                Route::post('/', [AdminController::class, 'store'])->name('admin.store');
                Route::post('/edit-profile', [AdminController::class, 'editProfile'])->name('admin.edit.profile');
                Route::put('/{user}/update-profile', [AdminController::class, 'updateProfile'])->name('admin.update.profile');
                Route::put('/{user}/update-password', [AdminController::class, 'updateAdminPassword'])->name('admin.update.password');
                Route::post('/destroy', [AdminController::class, 'destroy'])->name('admin.destroy');
            });
            // end of admins routes

            // lecturer routes
            Route::prefix('lecturer')->group(function () {
                Route::get('/', [LecturerController::class, 'showLecturer'])->name('lecturer.index');
                Route::get('/fetch', [LecturerController::class, 'fetch'])->name('lecturer.fetch');
                Route::post('/', [LecturerController::class, 'store'])->name('lecturer.store');
                Route::post('/import', [LecturerController::class, 'import'])->name('lecturer.import');
                Route::post('/edit-profile', [LecturerController::class, 'editProfile'])->name('lecturer.edit.profile');
                Route::put('/{user}/update-profile', [LecturerController::class, 'updateProfile'])->name('lecturer.update.profile');
                Route::put('/{user}/update-password', [LecturerController::class, 'updateLecturerPassword'])->name('lecturer.update.password');
                Route::post('/destroy', [LecturerController::class, 'destroy'])->name('lecturer.destroy');
            });
            // end of lecturer routes

            // student routes
            Route::prefix('student')->group(function () {
                Route::get('/', [StudentController::class, 'showStudent'])->name('student.index');
                Route::get('/fetch', [StudentController::class, 'fetch'])->name('student.fetch');
                Route::get('/fetch-level', [StudentController::class, 'fetchLevel'])->name('student.fetch.level');
                Route::get('/fetch-section', [StudentController::class, 'fetchSection'])->name('student.fetch.section');
                Route::post('/', [StudentController::class, 'store'])->name('student.store');
                Route::post('/import', [StudentController::class, 'import'])->name('student.import');
                Route::post('/edit-profile', [StudentController::class, 'editProfile'])->name('student.edit.profile');
                Route::put('/{user}/update-profile', [StudentController::class, 'updateProfile'])->name('student.update.profile');
                Route::put('/{user}/update-password', [StudentController::class, 'updateStudentPassword'])->name('student.update.password');
                Route::post('/destroy', [StudentController::class, 'destroy'])->name('student.destroy');
            });
            // end of student routes

            // dept routes
            Route::prefix('department')->group(function () {
                Route::get('/', [DepartmentController::class, 'index'])->name('department.index');
                Route::get('/fetch', [DepartmentController::class, 'fetch'])->name('department.fetch');
                Route::post('/', [DepartmentController::class, 'store'])->name('department.store');
                Route::post('/edit', [DepartmentController::class, 'edit'])->name('department.edit');
                Route::put('/{department}/update', [DepartmentController::class, 'update'])->name('department.update');
                Route::post('/destroy', [DepartmentController::class, 'destroy'])->name('department.destroy');
            });
            // end of dept routes

            // level routes
            Route::prefix('level')->group(function () {
                Route::get('/', [LevelController::class, 'index'])->name('level.index');
                Route::get('/fetch', [LevelController::class, 'fetch'])->name('level.fetch');
                Route::post('/', [LevelController::class, 'store'])->name('level.store');
                Route::post('/edit', [LevelController::class, 'edit'])->name('level.edit');
                Route::put('/{level}/update', [LevelController::class, 'update'])->name('level.update');
                Route::post('/destroy', [LevelController::class, 'destroy'])->name('level.destroy');
            });
            // end of level routes

            // section routes
            Route::prefix('section')->group(function () {
                Route::get('/', [SectionController::class, 'index'])->name('section.index');
                Route::get('/fetch', [SectionController::class, 'fetch'])->name('section.fetch');
                Route::post('/', [SectionController::class, 'store'])->name('section.store');
                Route::post('/edit', [SectionController::class, 'edit'])->name('section.edit');
                Route::put('/{section}/update', [SectionController::class, 'update'])->name('section.update');
                Route::post('/destroy', [SectionController::class, 'destroy'])->name('section.destroy');
            });
            // end of section routes

            // class routes
            Route::prefix('class')->group(function () {
                Route::get('/', [ClasseController::class, 'index'])->name('classe.index');
                Route::get('/fetch', [ClasseController::class, 'fetch'])->name('classe.fetch');
                Route::post('/', [ClasseController::class, 'store'])->name('classe.store');
                Route::post('/edit', [ClasseController::class, 'edit'])->name('classe.edit');
                Route::post('/edit-assign', [ClasseController::class, 'editAssign'])->name('classe.assign.edit');
                Route::post('/store-assign', [ClasseController::class, 'storeAssign'])->name('classe.assign.store');
                Route::post('/edit-course-assign', [ClasseController::class, 'editAssignCourse'])->name('classe.assign.course.edit');
                Route::post('/store-course-assign', [ClasseController::class, 'storeAssignCourse'])->name('classe.assign.course.store');
                Route::put('/{classe}/update', [ClasseController::class, 'update'])->name('classe.update');
                Route::post('/destroy', [ClasseController::class, 'destroy'])->name('classe.destroy');
            });
            // end of class routes

            // course routes
            Route::prefix('course')->group(function () {
                Route::get('/', [CourseController::class, 'index'])->name('course.index');
                Route::get('/fetch', [CourseController::class, 'fetch'])->name('course.fetch');
                Route::post('/', [CourseController::class, 'store'])->name('course.store');
                Route::post('/edit', [CourseController::class, 'edit'])->name('course.edit');
                Route::post('/edit-assign', [CourseController::class, 'editAssign'])->name('course.assign.edit');
                Route::post('/store-assign', [CourseController::class, 'storeAssign'])->name('course.assign.store');
                Route::put('/{course}/update', [CourseController::class, 'update'])->name('course.update');
                Route::post('/destroy', [CourseController::class, 'destroy'])->name('course.destroy');
            });
            // end of course routes

            // venue routes
            Route::prefix('venue')->group(function () {
                Route::get('/', [VenueController::class, 'index'])->name('venue.index');
                Route::get('/fetch', [VenueController::class, 'fetch'])->name('venue.fetch');
                Route::post('/', [VenueController::class, 'store'])->name('venue.store');
                Route::post('/edit', [VenueController::class, 'edit'])->name('venue.edit');
                Route::put('/{venue}/update', [VenueController::class, 'update'])->name('venue.update');
                Route::post('/destroy', [VenueController::class, 'destroy'])->name('venue.destroy');
            });
            // end of venue routes

        });
        // end of setup routes

    });
});

/* ---------- End of Admin Route ------------ */

/* ---------- Student Route ------------ */

Route::prefix('student')->group(function () {

    Route::middleware('guest')->group(function () {

        Route::get('/login', [StudentController::class, 'showLoginForm'])->name('student.show.login');

        Route::post('/login', [StudentController::class, 'login'])->name('student.login');

        Route::get('forgot-password', [StudentController::class, 'showForgotPasswordForm'])
            ->name('student.password.request');

        Route::post('forgot-password', [StudentController::class, 'requestPassword'])
            ->name('student.password.email');

        Route::get('reset-password/{token}', [StudentController::class, 'showResetPasswordForm'])
            ->name('student.password.reset');

        Route::post('reset-password', [StudentController::class, 'storePassword'])
            ->name('student.password.store');
    });

    Route::middleware('student')->group(function () {

        Route::get('/attendance', [StudentController::class, 'index'])->name('student.dashboard');

        Route::get('/profile', [StudentController::class, 'edit'])->name('student.profile.edit');
        Route::patch('/profile', [StudentController::class, 'update'])->name('student.profile.update');
        Route::put('password', [StudentController::class, 'updatePassword'])->name('student.password.update');

        Route::post('/logout', [StudentController::class, 'logout'])->name('student.logout');

        Route::get('/attendance-sessions', [StudentController::class, 'sessions'])->name('student.sessions');
        Route::get('/attendance-sessions/fetch', [StudentController::class, 'fetchSessions'])->name('student.fetch.sessions');
        Route::post('/attendance-sessions/sign', [StudentController::class, 'signSession'])->name('student.sessions.sign');
    });
});

/* ---------- End of Student Route ------------ */


/* ---------- Lecturer Route ------------ */

Route::prefix('lecturer')->group(function () {

    Route::middleware('guest')->group(function () {

        Route::get('/login', [LecturerController::class, 'showLoginForm'])->name('lecturer.show.login');

        Route::post('/login', [LecturerController::class, 'login'])->name('lecturer.login');

        Route::get('forgot-password', [LecturerController::class, 'showForgotPasswordForm'])
            ->name('lecturer.password.request');

        Route::post('forgot-password', [LecturerController::class, 'requestPassword'])
            ->name('lecturer.password.email');

        Route::get('reset-password/{token}', [LecturerController::class, 'showResetPasswordForm'])
            ->name('lecturer.password.reset');

        Route::post('reset-password', [LecturerController::class, 'storePassword'])
            ->name('lecturer.password.store');
    });

    Route::middleware('lecturer')->group(function () {

        Route::get('/attendance', [LecturerController::class, 'index'])->name('lecturer.dashboard');

        Route::get('/profile', [LecturerController::class, 'edit'])->name('lecturer.profile.edit');
        Route::patch('/profile', [LecturerController::class, 'update'])->name('lecturer.profile.update');
        Route::put('password', [LecturerController::class, 'updatePassword'])->name('lecturer.password.update');
        // Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        Route::post('/logout', [LecturerController::class, 'logout'])->name('lecturer.logout');
        Route::get('/courses', [LecturerController::class, 'courses'])->name('lecturer.courses');
        Route::get('/students', [LecturerController::class, 'students'])->name('lecturer.students');
        Route::get('/fetch-students', [LecturerController::class, 'fetchStudents'])->name('lecturer.fetch.students');

        Route::prefix('session')->group(function () {
            Route::get('/', [LecturerController::class, 'sessions'])->name('lecturer.sessions');
            Route::get('/fetch', [LecturerController::class, 'fetchSessions'])->name('lecturer.fetch.sessions');
            Route::get('/fetch-classes', [LecturerController::class, 'fetchSessionsClasses'])->name('lecturer.fetch.sessions.classes');
            Route::post('/', [LecturerController::class, 'storeSession'])->name('lecturer.sessions.store');
            Route::post('/edit', [LecturerController::class, 'editSession'])->name('lecturer.sessions.edit');
            Route::put('/{session}/update', [LecturerController::class, 'updateSession'])->name('lecturer.sessions.update');
            Route::post('/start', [LecturerController::class, 'startSession'])->name('lecturer.sessions.start');
            Route::post('/end', [LecturerController::class, 'endSession'])->name('lecturer.sessions.end');
            Route::post('/destroy', [LecturerController::class, 'destroySession'])->name('lecturer.sessions.destroy');
            Route::get('/show', [LecturerController::class, 'showSessionAttendance'])->name('lecturer.sessions.show.attendance');
            Route::get('/fetch-attendance', [LecturerController::class, 'fetchSessionAttendance'])->name('lecturer.sessions.attendance.fetch');
            Route::get('/fetch-attendance-analytics', [LecturerController::class, 'fetchAttendanceAnalytics'])->name('lecturer.attendance.fetch.analytics');
        });
    });
});

/* ---------- End of Lecturer Route ------------ */

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
