<?php

namespace App\Events;

use App\Models\Site;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UsageLimitReached
{
    use Dispatchable, SerializesModels;

    public function __construct(public Site $site) {}
}
