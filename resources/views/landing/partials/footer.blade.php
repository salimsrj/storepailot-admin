<footer class="border-t border-ink/5 bg-ink py-12 text-white">
    <div class="mx-auto flex max-w-6xl flex-col gap-8 px-4 sm:flex-row sm:items-start sm:justify-between sm:px-6 lg:px-8">
        <div>
            <p class="font-display text-xl font-bold">Commerce<span class="text-brand-400">Pilot</span></p>
            <p class="mt-2 max-w-sm text-sm text-white/60">
                AI shopping assistants for WooCommerce—discover, answer, and guide shoppers to checkout.
            </p>
        </div>

        <div class="flex flex-wrap gap-x-8 gap-y-3 text-sm text-white/70">
            <a href="#features" class="hover:text-white">Features</a>
            <a href="#how-it-works" class="hover:text-white">How It Works</a>
            <a href="#pricing" class="hover:text-white">Pricing</a>
            <a href="#faq" class="hover:text-white">FAQ</a>
            <a href="{{ route('login') }}" class="hover:text-white">Log in</a>
            <a href="{{ route('signup.create') }}" class="hover:text-white">Start Free</a>
        </div>
    </div>
    <div class="mx-auto mt-10 max-w-6xl px-4 text-xs text-white/40 sm:px-6 lg:px-8">
        &copy; {{ date('Y') }} CommercePilot. All rights reserved.
    </div>
</footer>
