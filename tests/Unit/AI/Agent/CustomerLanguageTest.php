<?php

namespace Tests\Unit\AI\Agent;

use App\AI\Agent\CustomerLanguage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CustomerLanguageTest extends TestCase
{
    #[DataProvider('scriptMessages')]
    public function test_detects_unicode_script(string $message, string $script): void
    {
        $this->assertSame($script, CustomerLanguage::detect($message));
    }

    #[DataProvider('fallbackReplies')]
    public function test_fallback_reply_matches_the_customer_script(string $message, string $reply): void
    {
        $this->assertSame($reply, CustomerLanguage::fallbackReply($message));
    }

    /**
     * @return array<string, list{string, string}>
     */
    public static function scriptMessages(): array
    {
        return [
            'bangla' => ['কালো জুতো', 'bn'],
            'hindi' => ['काले जूते', 'hi'],
            'english' => ['black shoes', 'en'],
            'banglish' => ['kalo juta dekhaw', 'en'],
        ];
    }

    /**
     * @return array<string, list{string, string}>
     */
    public static function fallbackReplies(): array
    {
        return [
            'bangla' => ['কালো জুতো', 'এই মুহূর্তে আপনার অনুরোধটি সম্পন্ন করতে পারিনি।'],
            'hindi' => ['काले जूते', 'अभी यह अनुरोध पूरा नहीं हो सका।'],
            'english' => ['black shoes', 'I could not complete that request right now.'],
            'banglish' => ['kalo juta dekhaw', 'I could not complete that request right now.'],
        ];
    }
}
