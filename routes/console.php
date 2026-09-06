<?php

use App\Jobs\CleanupExpiredConversations;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new CleanupExpiredConversations)
    ->daily()
    ->withoutOverlapping(120)
    ->name('cleanup-expired-conversations');
