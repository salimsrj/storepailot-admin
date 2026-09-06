<?php

namespace App\AI\Agent;

use App\Models\Conversation;
use App\Models\Site;
use App\Models\SiteSetting;
use App\Models\Visitor;

readonly class AgentContext
{
    public function __construct(
        public Site $site,
        public SiteSetting $settings,
        public Visitor $visitor,
        public Conversation $conversation,
        public string $message,
    ) {}
}
