<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sign in to your CommercePilot account.">
    <title>Sign In | CommercePilot</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen overflow-x-hidden" x-data="{ mobileNav: false }">
    <header class="fixed inset-x-0 top-0 z-50 border-b border-ink/5 bg-surface/80 backdrop-blur-md">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="font-display text-xl font-bold tracking-tight text-ink">
                Commerce<span class="text-brand-600">Pilot</span>
            </a>

            <nav class="hidden items-center gap-8 text-sm font-medium text-muted md:flex">
                <a href="{{ route('home') }}#features" class="transition hover:text-ink">Features</a>
                <a href="{{ route('home') }}#how-it-works" class="transition hover:text-ink">How It Works</a>
                <a href="{{ route('home') }}#pricing" class="transition hover:text-ink">Pricing</a>
                <a href="{{ route('home') }}#faq" class="transition hover:text-ink">FAQ</a>
            </nav>

            <div class="hidden items-center gap-3 md:flex">
                <span class="text-sm font-medium text-ink">Log in</span>
                <a
                    href="{{ route('signup.create') }}"
                    class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700"
                >
                    Start Free
                </a>
            </div>

            <button
                type="button"
                class="inline-flex items-center justify-center rounded-lg p-2 text-ink md:hidden"
                @click="mobileNav = !mobileNav"
                :aria-expanded="mobileNav.toString()"
                aria-label="Toggle navigation"
            >
                <svg x-show="!mobileNav" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
                <svg x-cloak x-show="mobileNav" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div
            x-cloak
            x-show="mobileNav"
            x-transition
            class="border-t border-ink/5 bg-surface px-4 py-4 md:hidden"
        >
            <nav class="flex flex-col gap-3 text-sm font-medium text-ink">
                <a href="{{ route('home') }}#features" @click="mobileNav = false">Features</a>
                <a href="{{ route('home') }}#how-it-works" @click="mobileNav = false">How It Works</a>
                <a href="{{ route('home') }}#pricing" @click="mobileNav = false">Pricing</a>
                <a href="{{ route('home') }}#faq" @click="mobileNav = false">FAQ</a>
                <a
                    href="{{ route('signup.create') }}"
                    class="rounded-full bg-brand-600 px-4 py-2.5 text-center font-semibold text-white"
                >
                    Start Free
                </a>
            </nav>
        </div>
    </header>

    <main class="relative overflow-hidden pt-24 sm:pt-28">
        <div class="pointer-events-none absolute inset-0 -z-10">
            <div class="absolute -left-24 top-10 size-72 rounded-full bg-brand-200/50 blur-3xl"></div>
            <div class="absolute right-0 top-32 size-96 rounded-full bg-brand-100/80 blur-3xl"></div>
            <div class="absolute inset-x-0 bottom-0 h-40 bg-gradient-to-t from-canvas to-transparent"></div>
        </div>

        <div class="mx-auto grid max-w-6xl items-start gap-12 px-4 pb-20 sm:px-6 lg:grid-cols-2 lg:gap-16 lg:px-8 lg:pb-28">
            <div class="animate-fade-up lg:pt-6">
                <p class="font-display text-3xl font-extrabold tracking-tight text-ink sm:text-4xl lg:text-5xl">
                    Commerce<span class="text-brand-600">Pilot</span>
                </p>
                <h1 class="mt-4 font-display text-3xl font-bold leading-tight tracking-tight text-ink sm:text-4xl">
                    Sign in to your account
                </h1>
                <p class="mt-5 max-w-xl text-lg leading-relaxed text-muted">
                    Manage your WooCommerce AI assistant, connection credentials, and conversation insights from one place.
                </p>

                <ul class="mt-8 space-y-3 text-sm text-muted">
                    <li class="flex items-start gap-3">
                        <span class="mt-1 flex size-5 shrink-0 items-center justify-center rounded-full bg-brand-100 text-brand-700">
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                        </span>
                        Access site credentials for the WordPress plugin
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="mt-1 flex size-5 shrink-0 items-center justify-center rounded-full bg-brand-100 text-brand-700">
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                        </span>
                        Review conversations and usage
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="mt-1 flex size-5 shrink-0 items-center justify-center rounded-full bg-brand-100 text-brand-700">
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                        </span>
                        Keep your store assistant running 24/7
                    </li>
                </ul>
            </div>

            <div class="animate-fade-up w-full" style="animation-delay: 0.12s">
                <div class="rounded-2xl border border-ink/8 bg-surface p-6 shadow-xl shadow-brand-800/5 sm:p-8">
                    <p class="font-display text-xl font-bold tracking-tight text-ink">Welcome back</p>
                    <p class="mt-1 text-sm text-muted">
                        Sign in with the email you used to create your account.
                    </p>

                    @if (session('status'))
                        <div class="mt-4 rounded-xl border border-brand-200 bg-brand-50 px-4 py-3 text-sm text-brand-800">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-4">
                        @csrf

                        <div>
                            <label for="email" class="mb-1.5 block text-sm font-medium text-ink">Email</label>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="you@store.com"
                                required
                                autofocus
                                class="w-full rounded-xl border border-ink/10 bg-canvas px-4 py-3 text-sm text-ink outline-none transition placeholder:text-muted/70 focus:border-brand-400 focus:ring-2 focus:ring-brand-200 @error('email') border-red-400 focus:border-red-400 focus:ring-red-100 @enderror"
                            >
                            @error('email')
                                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="mb-1.5 block text-sm font-medium text-ink">Password</label>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                placeholder="Your password"
                                required
                                class="w-full rounded-xl border border-ink/10 bg-canvas px-4 py-3 text-sm text-ink outline-none transition placeholder:text-muted/70 focus:border-brand-400 focus:ring-2 focus:ring-brand-200 @error('password') border-red-400 focus:border-red-400 focus:ring-red-100 @enderror"
                            >
                            @error('password')
                                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <label for="remember" class="flex items-center gap-2 text-sm text-muted">
                                <input
                                    id="remember"
                                    type="checkbox"
                                    name="remember"
                                    class="size-4 rounded border-ink/20 text-brand-600 focus:ring-brand-400"
                                >
                                Remember me
                            </label>
                        </div>

                        <button
                            type="submit"
                            class="mt-2 w-full rounded-full bg-brand-600 px-6 py-3.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700"
                        >
                            Sign in
                        </button>
                    </form>

                    <p class="mt-6 text-center text-sm text-muted">
                        Need an account?
                        <a href="{{ route('signup.create') }}" class="font-semibold text-brand-700 transition hover:text-brand-800">Sign up</a>
                    </p>
                </div>
            </div>
        </div>
    </main>

    <footer class="border-t border-ink/5 bg-ink py-12 text-white">
        <div class="mx-auto flex max-w-6xl flex-col gap-8 px-4 sm:flex-row sm:items-start sm:justify-between sm:px-6 lg:px-8">
            <div>
                <p class="font-display text-xl font-bold">Commerce<span class="text-brand-400">Pilot</span></p>
                <p class="mt-2 max-w-sm text-sm text-white/60">
                    AI shopping assistants for WooCommerce—discover, answer, and guide shoppers to checkout.
                </p>
            </div>

            <div class="flex flex-wrap gap-x-8 gap-y-3 text-sm text-white/70">
                <a href="{{ route('home') }}#features" class="hover:text-white">Features</a>
                <a href="{{ route('home') }}#pricing" class="hover:text-white">Pricing</a>
                <a href="{{ route('signup.create') }}" class="hover:text-white">Start Free</a>
            </div>
        </div>
        <div class="mx-auto mt-10 max-w-6xl px-4 text-xs text-white/40 sm:px-6 lg:px-8">
            &copy; {{ date('Y') }} CommercePilot. All rights reserved.
        </div>
    </footer>
</body>
</html>
