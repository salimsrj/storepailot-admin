<?php

namespace App\Exceptions;

class ToolExecutionException extends CommercePilotException
{
    public static function failed(string $tool): self
    {
        return new self(
            'The store tool could not be completed.',
            'tool_execution_failed',
            502,
            ['tool' => $tool],
        );
    }
}
