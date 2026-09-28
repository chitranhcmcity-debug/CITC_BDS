<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ModuleController as AdminModuleController;
use App\Http\Controllers\Admin\PropertyController as AdminPropertyController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Member\DashboardController as MemberDashboardController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

// Redirect only safe GET/HEAD requests. Route::redirect() registers an ANY
// route, which would intercept the compatibility POST endpoints below.
Route::get('/trang-chu/pricing', fn () => redirect('/bang-gia', 301));
Route::get('/nguoi-dung/dang-nhap', fn () => redirect('/dang-nhap', 301));
Route::get('/nguoi-dung/dang-ky', fn () => redirect('/dang-ky', 301));
Route::get('/nguoi-dung/register', fn () => redirect('/dang-ky', 301));
Route::get('/nguoi-dung/dashboard', fn () => redirect('/trang-ca-nhan', 301));

// Public Frontend Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/bang-gia', [HomeController::class, 'pricing'])->name('pricing');
Route::get('/lien-he', [ContactController::class, 'index'])->name('contact.index');
Route::post('/lien-he', [ContactController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('contact.store');

Route::get('/du-an/{slug}', [ProjectController::class, 'detail'])->name('project.detail');
Route::get('/tin-tuc/{slug}', [NewsController::class, 'detail'])->name('news.detail');

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/dang-nhap', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/dang-nhap', [AuthController::class, 'login'])->middleware('throttle:6,1');
    Route::get('/dang-ky', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/dang-ky', [AuthController::class, 'register'])->middleware('throttle:5,1');

    // Compatibility routes for legacy POST submissions
    Route::post('/nguoi-dung/dang-nhap', [AuthController::class, 'login'])->middleware('throttle:6,1');
    Route::post('/nguoi-dung/dang-ky', [AuthController::class, 'register'])->middleware('throttle:5,1');
    Route::post('/nguoi-dung/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
});

// Email verification must stay public: recipients can open the link in a
// browser where they are not signed in yet.
Route::get('/nguoi-dung/verify-email-notice', [AuthController::class, 'showVerifyEmailNotice'])
    ->name('verification.notice');
Route::get('/verify-email', [AuthController::class, 'verifyEmail'])
    ->name('verification.verify');
Route::get('/nguoi-dung/verify-email/{token}', [AuthController::class, 'verifyEmail'])
    ->where('token', '[A-Za-z0-9]+');
Route::post('/nguoi-dung/resend-verify', [AuthController::class, 'resendVerificationEmail'])
    ->middleware('throttle:3,1')
    ->name('verification.send');

// Authenticated Member Routes
Route::middleware('auth')->group(function () {
    Route::post('/dang-xuat', [AuthController::class, 'logout'])->name('logout');
    Route::get('/trang-ca-nhan', [MemberDashboardController::class, 'index'])->name('member.dashboard');

    // Compatibility routes for legacy POST/GET member actions
    Route::post('/nguoi-dung/dang-xuat', [AuthController::class, 'logout']);
    Route::prefix('nguoi-dung')->group(function () {
        // ... more member routes (favorite, chat, etc.)
    });
});

// Admin Panel Routes
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('admin.index');
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/du-an', [AdminPropertyController::class, 'index'])->name('admin.properties.index');
    Route::get('/danh-muc', [AdminCategoryController::class, 'index'])->name('admin.categories.index');

    // Keep every sidebar destination valid while the remaining legacy modules
    // are migrated. Explicit allow-listing prevents this from masking bad URLs.
    Route::get('/{module}', [AdminModuleController::class, 'show'])
        ->whereIn('module', [
            'tin-tuc', 'contact', 'wallet', 'live-chat', 'nguoi-dung',
            'bang-gia', 'bao-cao', 'cai-dat', 'roles', 'system-log',
        ])
        ->name('admin.module');
});
