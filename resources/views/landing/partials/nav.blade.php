<header class="fixed inset-x-0 top-0 z-50 border-b border-ink/5 bg-surface/80 backdrop-blur-md">
    <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="font-display text-xl font-bold tracking-tight text-ink">
            Commerce<span class="text-brand-600">Pilot</span>
        </a>

        <nav class="hidden items-center gap-8 text-sm font-medium text-muted md:flex">
            <a href="#features" class="transition hover:text-ink">Features</a>
            <a href="#how-it-works" class="transition hover:text-ink">How It Works</a>
            <a href="#pricing" class="transition hover:text-ink">Pricing</a>
            <a href="#faq" class="transition hover:text-ink">FAQ</a>
        </nav>

        <div class="hidden items-center gap-3 md:flex">
            <a href="{{ route('login') }}" class="text-sm font-medium text-muted transition hover:text-ink">Log in</a>
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
            <a href="#features" @click="mobileNav = false">Features</a>
            <a href="#how-it-works" @click="mobileNav = false">How It Works</a>
            <a href="#pricing" @click="mobileNav = false">Pricing</a>
            <a href="#faq" @click="mobileNav = false">FAQ</a>
            <a href="{{ route('login') }}" class="pt-2 text-muted">Log in</a>
            <a
                href="{{ route('signup.create') }}"
                class="rounded-full bg-brand-600 px-4 py-2.5 text-center font-semibold text-white"
            >
                Start Free
            </a>
        </nav>
    </div>
</header>
