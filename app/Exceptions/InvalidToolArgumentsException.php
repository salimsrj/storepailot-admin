<?php

namespace App\Exceptions;

class InvalidToolArgumentsException extends CommercePilotException
{
    public static function forTool(string $tool): self
    {
        return new self(
            'The tool arguments are invalid.',
            'invalid_tool_arguments',
            422,
            ['tool' => $tool],
        );
    }
}
