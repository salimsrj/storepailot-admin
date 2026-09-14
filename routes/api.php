<?php

use App\Http\Controllers\Api\V1\ChatController;
use App\Http\Controllers\Api\V1\ConversationController;
use App\Http\Controllers\Api\V1\EventController;
use App\Http\Controllers\Api\V1\SiteController;
use App\Http\Controllers\Api\V1\UsageController;
use App\Http\Controllers\Api\V1\WebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('webhooks/billing', [WebhookController::class, 'store'])->name('api.v1.webhooks.billing');

    Route::middleware('auth')->group(function (): void {
        Route::post('sites', [SiteController::class, 'store'])->name('api.v1.sites.store');
    });

    Route::middleware(['site', 'site.rate'])->group(function (): void {
        Route::post('chat', [ChatController::class, 'store'])->name('api.v1.chat');
        Route::post('chat/stream', [ChatController::class, 'stream'])->name('api.v1.chat.stream');
        Route::get('site', [SiteController::class, 'show'])->name('api.v1.site.show');
        Route::patch('site', [SiteController::class, 'update'])->name('api.v1.site.update');
        Route::patch('site/settings', [SiteController::class, 'updateSettings'])->name('api.v1.site.settings');
        Route::get('usage', [UsageController::class, 'show'])->name('api.v1.usage.show');

        Route::middleware('site.hmac')->group(function (): void {
            Route::post('site/token/rotate', [SiteController::class, 'rotateToken'])->name('api.v1.site.token.rotate');
            Route::post('events', [EventController::class, 'store'])->name('api.v1.events.store');
        });
    });

    // Conversation history and human handover. Separate rate profile so the
    // admin inbox and widget polling cannot exhaust the daily chat allowance.
    Route::middleware(['site', 'site.rate:poll', 'site.hmac'])->group(function (): void {
        Route::get('conversations', [ConversationController::class, 'index'])->name('api.v1.conversations.index');
        Route::get('conversations/waiting-count', [ConversationController::class, 'waitingCount'])->name('api.v1.conversations.waiting-count');
        Route::get('conversations/{conversation}', [ConversationController::class, 'show'])->name('api.v1.conversations.show');
        Route::get('conversations/{conversation}/messages', [ConversationController::class, 'messages'])->name('api.v1.conversations.messages');
        Route::post('conversations/{conversation}/takeover', [ConversationController::class, 'takeOver'])->name('api.v1.conversations.takeover');
        Route::post('conversations/{conversation}/release', [ConversationController::class, 'release'])->name('api.v1.conversations.release');
        Route::post('conversations/{conversation}/messages', [ConversationController::class, 'reply'])->name('api.v1.conversations.reply');
    });
});
