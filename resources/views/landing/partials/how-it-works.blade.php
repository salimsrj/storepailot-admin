<section id="how-it-works" class="scroll-mt-24 py-20 sm:py-24">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <h2 class="font-display text-3xl font-bold tracking-tight text-ink sm:text-4xl">How It Works</h2>
            <p class="mt-4 text-muted">From install to first conversation in minutes.</p>
        </div>

        <ol class="mt-14 grid gap-8 md:grid-cols-3">
            @foreach ([
                ['01', 'Install', 'Connect your WooCommerce store in minutes.'],
                ['02', 'Train', 'CommercePilot learns from your products and store information.'],
                ['03', 'Sell', 'Your AI assistant chats with customers and helps them choose products.'],
            ] as [$step, $title, $copy])
                <li class="relative text-center md:text-left">
                    <p class="font-display text-4xl font-extrabold text-brand-200">{{ $step }}</p>
                    <h3 class="mt-2 font-display text-xl font-bold text-ink">{{ $title }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-muted">{{ $copy }}</p>
                </li>
            @endforeach
        </ol>

        <div class="mt-14 flex flex-wrap items-center justify-center gap-3 text-sm font-semibold text-ink">
            <span class="rounded-full bg-brand-50 px-4 py-2 text-brand-800">WooCommerce Store</span>
            <span class="text-brand-400" aria-hidden="true">→</span>
            <span class="rounded-full bg-brand-50 px-4 py-2 text-brand-800">AI Assistant</span>
            <span class="text-brand-400" aria-hidden="true">→</span>
            <span class="rounded-full bg-brand-50 px-4 py-2 text-brand-800">Customer</span>
            <span class="text-brand-400" aria-hidden="true">→</span>
            <span class="rounded-full bg-brand-600 px-4 py-2 text-white">Purchase</span>
        </div>
    </div>
</section>
