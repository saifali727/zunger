<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\{UserController, ReportController};
use App\Models\{Post,User,ActivityLog};
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
    return redirect('/login');
});

Route::get('/dashboard', function () {
    $videos = count(Post::all());
    $users = count(User::all());
    $activites = ActivityLog::all();
    return view('admin.layouts.dashboard', compact('videos','users','activites'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    //users
    Route::get('/users_index', [UserController::class, 'index'])->name('users_index');
    Route::get('get_all_users', [UserController::class, 'student_index'])->name('get_all_users');
    Route::get('/get_all_users_students', [UserController::class, 'student_datatable'])->name('get_all_users_students');
    Route::get('admin/user/status/{id}', [UserController::class, 'user_status']);
    Route::get('admin/user/delete/{id}', [UserController::class, 'user_delete']);

    //reports

    Route::get('reports', [ReportController::class, 'reports_index'])->name('reports');
    Route::get('get_all_reports', [ReportController::class, 'reports_datatable'])->name('get_all_reports');
    Route::get('admin/post/delete/{id}/{report_id}', [ReportController::class, 'post_delete']);
    Route::get('admin/report/delete/{id}', [ReportController::class, 'report_delete']);
    Route::get('admin/event/status/{id}', [ReportController::class, 'events_status']);
    Route::get('admin/user/banned/{id}/{report_id}', [ReportController::class, 'user_banned']);

    Route::get('solved_reports', [ReportController::class, 'solved_reports'])->name('solved_reports');
    Route::get('get_solved_reports', [ReportController::class, 'get_solved_reports'])->name('get_solved_reports');





    // logout
    Route::get('/logout', function () {
        Auth::logout();
        return redirect('/login');
    });
});

require __DIR__.'/auth.php';
