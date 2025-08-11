<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\PhoneLoginController;
use App\Http\Controllers\Auth\PhoneRegisterController;
use App\Http\Controllers\SpaceController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\TaskViewController;
use App\Http\Controllers\ProfileController;

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

// CSRF token refresh endpoint
Route::get('/csrf-token', function () {
    return response()->json(['csrf_token' => csrf_token()]);
})->name('csrf-token');

// CSRF test page
Route::get('/csrf-test', function () {
    return view('csrf-test');
})->name('csrf-test');

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
Route::get('/organizations/{organization}/spaces/{space}/archive', [App\Http\Controllers\HomeController::class, 'showSpaceArchive'])->name('spaces.archive');

// Маршруты для организаций и пространств
Route::middleware('auth')->group(function () {
    // Профиль пользователя
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    
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
    Route::put('/tasks/{task}/title', [TaskViewController::class, 'updateTitle'])->name('tasks.update.title');
    Route::put('/tasks/{task}/priority', [TaskViewController::class, 'updatePriority'])->name('tasks.update.priority');
    Route::put('/tasks/{task}/start-date', [TaskViewController::class, 'updateStartDate'])->name('tasks.update.start_date');
    Route::put('/tasks/{task}/due-date', [TaskViewController::class, 'updateDueDate'])->name('tasks.update.due_date');
    Route::put('/tasks/{task}/assignee', [TaskViewController::class, 'updateAssignee'])->name('tasks.update.assignee');
    Route::post('/tasks/{task}/upload', [TaskViewController::class, 'uploadFile'])->name('tasks.upload');
    Route::post('/tasks/{task}/upload-document', [TaskViewController::class, 'uploadDocument'])->name('tasks.upload.document');
    Route::post('/tasks/{task}/upload-multiple', [TaskViewController::class, 'uploadMultiple'])->name('tasks.upload.multiple');
    Route::post('/tasks/{task}/add-to-block', [TaskViewController::class, 'addToBlock'])->name('tasks.add.to.block');
    Route::delete('/tasks/{task}', [TaskViewController::class, 'destroy'])->name('tasks.destroy');
    Route::get('/tasks/{task}/pdf', [TaskViewController::class, 'downloadPDF'])->name('tasks.download.pdf');
    Route::post('/tasks/{task}/archive', [TaskViewController::class, 'archive'])->name('tasks.archive');
    Route::post('/tasks/{task}/unarchive', [TaskViewController::class, 'unarchive'])->name('tasks.unarchive');
    
    // Управление памятью (простая версия)
    Route::get('/memory', [App\Http\Controllers\SimpleStorageController::class, 'index'])->name('memory.index');
    Route::get('/memory/plans', [App\Http\Controllers\SimpleStorageController::class, 'plans'])->name('memory.plans');
    Route::post('/memory/plans/purchase', [App\Http\Controllers\SimpleStorageController::class, 'purchasePlan'])->name('memory.plans.purchase');
    Route::get('/memory/demo', [App\Http\Controllers\SimpleStorageController::class, 'demo'])->name('memory.demo');
    Route::post('/memory/simulate', [App\Http\Controllers\SimpleStorageController::class, 'simulate'])->name('memory.simulate');
    Route::post('/memory/reset-demo', [App\Http\Controllers\SimpleStorageController::class, 'resetDemo'])->name('memory.reset-demo');
    
    // API для управления памятью пространств
    Route::prefix('api/storage')->group(function () {
        Route::get('/spaces/{space}/stats', [App\Http\Controllers\SpaceStorageController::class, 'getSpaceStorageStats'])->name('api.storage.space.stats');
        Route::get('/spaces-stats', [App\Http\Controllers\SpaceStorageController::class, 'getUserSpacesStats'])->name('api.storage.spaces.stats');
        Route::post('/sync', [App\Http\Controllers\SpaceStorageController::class, 'syncUserStorage'])->name('api.storage.sync');
        Route::get('/tips', [App\Http\Controllers\SpaceStorageController::class, 'getOptimizationTips'])->name('api.storage.tips');
        Route::get('/spaces/{space}/analyze', [App\Http\Controllers\SpaceStorageController::class, 'analyzeSpaceTasks'])->name('api.storage.space.analyze');
        Route::post('/cleanup', [App\Http\Controllers\SpaceStorageController::class, 'cleanupArchivedTasks'])->name('api.storage.cleanup');
    });
});

// Маршруты для приглашений
Route::get('/invitations/{token}', [InvitationController::class, 'accept'])->name('invitations.accept');
