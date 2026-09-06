<?php

namespace App\Support;

use Illuminate\Support\Facades\Context;
use Illuminate\Support\Str;

class RequestId
{
    public static function current(): string
    {
        $existing = Context::getHidden('request_id');

        if (is_string($existing) && $existing !== '') {
            return $existing;
        }

        $generated = (string) Str::uuid();
        Context::addHidden('request_id', $generated);

        return $generated;
    }

    public static function remember(string $requestId): string
    {
        Context::addHidden('request_id', $requestId);

        return $requestId;
    }
}
