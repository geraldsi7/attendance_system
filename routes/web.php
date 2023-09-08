<?php

use App\Http\Controllers\StudentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LecturerController;
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

/* ---------- Student Route ------------ */

Route::prefix('student')->group(function () {

    Route::middleware('guest')->group(function () {

        Route::get('/login', [StudentController::class, 'showLoginForm'])->name('student.show.login');

        Route::post('/login', [StudentController::class, 'login'])->name('student.login');
    });

    Route::middleware('student')->group(function () {

        Route::get('/dashboard', [StudentController::class, 'index'])->name('student.dashboard');

        Route::get('/profile', [StudentController::class, 'edit'])->name('student.profile.edit');
        Route::patch('/profile', [StudentController::class, 'update'])->name('student.profile.update');
        Route::put('password', [StudentController::class, 'updatePassword'])->name('student.password.update');

        Route::post('/logout', [StudentController::class, 'logout'])->name('student.logout');

        Route::get('/attendance-sessions', [StudentController::class, 'sessions'])->name('student.sessions');
        Route::get('/attendance-sessions/fetch', [StudentController::class, 'fetchSessions'])->name('student.sessions.fetch');
        Route::post('/attendance-sessions/sign', [StudentController::class, 'signSession'])->name('student.sessions.sign');
    });
});

/* ---------- End of Student Route ------------ */


/* ---------- Lecturer Route ------------ */

Route::prefix('lecturer')->group(function () {

    Route::middleware('guest')->group(function () {

        Route::get('/login', [LecturerController::class, 'showLoginForm'])->name('lecturer.show.login');

        Route::post('/login', [LecturerController::class, 'login'])->name('lecturer.login');
    });

    Route::middleware('lecturer')->group(function () {

        Route::get('/dashboard', [LecturerController::class, 'index'])->name('lecturer.dashboard');

        Route::get('/profile', [LecturerController::class, 'edit'])->name('lecturer.profile.edit');
        Route::patch('/profile', [LecturerController::class, 'update'])->name('lecturer.profile.update');
        Route::put('password', [LecturerController::class, 'updatePassword'])->name('lecturer.password.update');
        // Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        Route::post('/logout', [LecturerController::class, 'logout'])->name('lecturer.logout');
        Route::get('/courses', [LecturerController::class, 'courses'])->name('lecturer.courses');
        Route::get('/students', [LecturerController::class, 'students'])->name('lecturer.students');
        Route::get('/fetch-students', [LecturerController::class, 'fetchStudents'])->name('lecturer.fetch.students');
        Route::get('/attendance-sessions', [LecturerController::class, 'sessions'])->name('lecturer.sessions');
        Route::get('/attendance-sessions/fetch', [LecturerController::class, 'fetchSessions'])->name('lecturer.fetch.sessions');
        Route::get('/attendance-sessions/fetch-classes', [LecturerController::class, 'fetchSessionsClasses'])->name('lecturer.fetch.sessions.classes');
        Route::post('/attendance-sessions', [LecturerController::class, 'storeSession'])->name('lecturer.sessions.store');
        Route::get('/attendance-sessions/edit', [LecturerController::class, 'editSession'])->name('lecturer.sessions.edit');
        Route::post('/attendance-sessions/{session}/update', [LecturerController::class, 'updateSession'])->name('lecturer.sessions.update');
        Route::post('/attendance-sessions/publish', [LecturerController::class, 'publishSession'])->name('lecturer.sessions.publish');
        Route::post('/attendance-sessions/end', [LecturerController::class, 'endSession'])->name('lecturer.sessions.end');
        Route::post('/attendance-sessions/destroy', [LecturerController::class, 'destroySession'])->name('lecturer.sessions.destroy');
        Route::get('/attendance-sessions/show', [LecturerController::class, 'showSessionAttendance'])->name('lecturer.sessions.show.attendance');
        Route::get('/attendance-sessions/fetch-attendance', [LecturerController::class, 'fetchSessionAttendance'])->name('lecturer.sessions.attendance.fetch');
    });
});

/* ---------- End of Lecturer Route ------------ */

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
