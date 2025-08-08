<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\PhoneLoginController;
use App\Http\Controllers\Auth\PhoneRegisterController;
use App\Http\Controllers\SpaceController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\TaskViewController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/bootstrap-test', function () {
    return view('bootstrap-test');
})->name('bootstrap-test');

// Аутентификация по номеру телефона
Route::get('/login', [PhoneLoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [PhoneLoginController::class, 'login'])->name('phone.login');
Route::post('/logout', [PhoneLoginController::class, 'logout'])->name('logout');

Route::get('/register', [PhoneRegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [PhoneRegisterController::class, 'register'])->name('phone.register');

// Старые маршруты аутентификации (оставляем для восстановления пароля)
Auth::routes(['login' => false, 'register' => false]);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/organizations/{organization}', [App\Http\Controllers\HomeController::class, 'showOrganization'])->name('organizations.show');
Route::get('/organizations/{organization}/spaces/{space}', [App\Http\Controllers\HomeController::class, 'showSpace'])->name('spaces.show');

// Маршруты для организаций и пространств
Route::middleware('auth')->group(function () {
    // Организации
    Route::post('/organizations', [OrganizationController::class, 'store'])->name('organizations.store');
    
    // Пространства
    Route::post('/spaces', [SpaceController::class, 'store'])->name('spaces.store');
    Route::post('/spaces/{space}/invite', [SpaceController::class, 'generateInviteLink'])->name('spaces.invite');
    Route::delete('/spaces/{space}/members/{user}', [SpaceController::class, 'removeMember'])->name('spaces.remove-member');
    Route::patch('/spaces/{space}/members/{user}', [SpaceController::class, 'updateMemberAccess'])->name('spaces.update-member');
    Route::post('/spaces/{space}/members/{user}/approve', [SpaceController::class, 'approveMember'])->name('spaces.approve-member');
    Route::post('/spaces/{space}/members/{user}/ban', [SpaceController::class, 'banMember'])->name('spaces.ban-member');
    
    // Задачи
    Route::get('/tasks/{task}', [TaskViewController::class, 'show'])->name('tasks.show');
    Route::put('/tasks/{task}', [TaskViewController::class, 'update'])->name('tasks.update');
    Route::put('/tasks/{task}/content', [TaskViewController::class, 'updateContent'])->name('tasks.update.content');
    Route::post('/tasks/{task}/upload', [TaskViewController::class, 'uploadFile'])->name('tasks.upload');
    Route::delete('/tasks/{task}', [TaskViewController::class, 'destroy'])->name('tasks.destroy');
});

// Маршруты для приглашений
Route::get('/invitations/{token}', [InvitationController::class, 'accept'])->name('invitations.accept');
