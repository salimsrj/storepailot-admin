<?php

use App\Http\Controllers\Admin\AiSettingController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\ConversationController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\SiteController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\UsageEventController;
use App\Http\Controllers\Admin\UsagePeriodController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WebhookEventController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('admin.login'));

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('login', [LoginController::class, 'create'])->name('login');
        Route::post('login', [LoginController::class, 'store'])
            ->middleware('throttle:admin-login')
            ->name('login.store');
    });

    Route::middleware(['auth', 'admin'])->group(function (): void {
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
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
