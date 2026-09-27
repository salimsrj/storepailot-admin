<?php

namespace Tests\Feature;

use Tests\TestCase;

class LandingPageTest extends TestCase
{
    public function test_landing_page_is_available_at_root(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('CommercePilot')
            ->assertSee('Turn Every Store Visitor Into a Confident Buyer')
            ->assertSee('Start Free');
    }

    public function test_landing_page_links_to_signup_and_login(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('signup.create'), false)
            ->assertSee(route('login'), false);
    }
}
