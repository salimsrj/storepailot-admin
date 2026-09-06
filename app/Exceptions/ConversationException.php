<?php

namespace App\Exceptions;

class ConversationException extends CommercePilotException
{
    public static function notFound(): self
    {
        return new self('The conversation could not be found.', 'conversation_not_found', 404);
    }

    public static function forbidden(): self
    {
        return new self('This conversation does not belong to the authenticated site.', 'conversation_forbidden', 403);
    }
}
