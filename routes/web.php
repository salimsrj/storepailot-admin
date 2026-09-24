<?php

use App\Http\Controllers\Admin\AiSettingController;
use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\ConversationController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\SiteController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\UsageEventController;
use App\Http\Controllers\Admin\UsagePeriodController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WebhookEventController;
use App\Http\Controllers\Merchant\Auth\LoginController as MerchantLoginController;
use App\Http\Controllers\Merchant\CredentialsController;
use App\Http\Controllers\Merchant\DashboardController as MerchantDashboardController;
use App\Http\Controllers\Merchant\PlanController as MerchantPlanController;
use App\Http\Controllers\Merchant\ProfileController;
use App\Http\Controllers\Signup\EmailVerificationController;
use App\Http\Controllers\Signup\SignupController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/', [SignupController::class, 'create'])->name('signup.create');
    Route::post('/signup', [SignupController::class, 'store'])
        ->middleware('throttle:signup')
        ->name('signup.store');

    Route::get('/login', [MerchantLoginController::class, 'create'])->name('login');
    Route::post('/login', [MerchantLoginController::class, 'store'])
        ->middleware('throttle:merchant-login')
        ->name('login.store');
});

Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::middleware(['auth', 'merchant'])->group(function (): void {
    Route::post('/logout', [MerchantLoginController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', MerchantDashboardController::class)->name('dashboard');
    Route::get('/dashboard/profile', [ProfileController::class, 'edit'])->name('dashboard.profile.edit');
    Route::put('/dashboard/profile', [ProfileController::class, 'update'])->name('dashboard.profile.update');
    Route::get('/dashboard/plans', [MerchantPlanController::class, 'index'])->name('dashboard.plans.index');
    Route::post('/dashboard/plans', [MerchantPlanController::class, 'store'])->name('dashboard.plans.store');
    Route::get('/dashboard/credentials', CredentialsController::class)->name('dashboard.credentials');

    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])
        ->middleware('throttle:6,1')
        ->name('verification.notice');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('login', [AdminLoginController::class, 'create'])->name('login');
        Route::post('login', [AdminLoginController::class, 'store'])
            ->middleware('throttle:admin-login')
            ->name('login.store');
    });

    Route::middleware(['auth', 'admin'])->group(function (): void {
        Route::post('logout', [AdminLoginController::class, 'destroy'])->name('logout');
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::resource('users', UserController::class);
        Route::resource('plans', PlanController::class);
        Route::resource('subscriptions', SubscriptionController::class);
        Route::resource('sites', SiteController::class);
        Route::patch('sites/{site}/settings', [SiteController::class, 'updateSettings'])->name('sites.settings');
        Route::post('sites/{site}/token', [SiteController::class, 'rotateToken'])->name('sites.token');
        Route::resource('conversations', ConversationController::class)->only(['index', 'show', 'update', 'destroy']);
        Route::get('usage-periods', [UsagePeriodController::class, 'index'])->name('usage-periods.index');
        Route::get('usage-events', [UsageEventController::class, 'index'])->name('usage-events.index');
        Route::get('webhooks', [WebhookEventController::class, 'index'])->name('webhooks.index');
        Route::get('webhooks/{webhook}', [WebhookEventController::class, 'show'])->name('webhooks.show');
        Route::get('settings/ai', [AiSettingController::class, 'edit'])->name('settings.ai');
        Route::put('settings/ai', [AiSettingController::class, 'update'])->name('settings.ai.update');
    });
});
