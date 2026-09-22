<?php

namespace App\AI\Agent;

class CustomerLanguage
{
    /**
     * Classify the message by Unicode script.
     *
     * Banglish uses Latin letters and cannot be distinguished from English.
     *
     * @return 'bn'|'hi'|'en'
     */
    public static function detect(string $message): string
    {
        if (preg_match('/\p{Bengali}/u', $message) === 1) {
            return 'bn';
        }

        if (preg_match('/\p{Devanagari}/u', $message) === 1) {
            return 'hi';
        }

        return 'en';
    }

    public static function fallbackReply(string $message): string
    {
        return match (self::detect($message)) {
            'bn' => 'এই মুহূর্তে আপনার অনুরোধটি সম্পন্ন করতে পারিনি।',
            'hi' => 'अभी यह अनुरोध पूरा नहीं हो सका।',
            'en' => 'I could not complete that request right now.',
        };
    }
}
