<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="CommercePilot is an AI shopping assistant for WooCommerce that helps customers discover products, get answers, and check out—24/7.">
    <title>CommercePilot — AI Shopping Assistant for WooCommerce</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen overflow-x-hidden" x-data="{ mobileNav: false }">
    @include('landing.partials.nav')

    <main>
        @include('landing.partials.hero')
        @include('landing.partials.problem')
        @include('landing.partials.how-it-works')
        @include('landing.partials.features')
        @include('landing.partials.demo')
        @include('landing.partials.comparison')
        @include('landing.partials.pricing')
        @include('landing.partials.roi')
        @include('landing.partials.integrations')
        @include('landing.partials.security')
        @include('landing.partials.faq')
        @include('landing.partials.cta')
    </main>

    @include('landing.partials.footer')
</body>
</html>
