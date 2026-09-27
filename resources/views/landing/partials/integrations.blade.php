<section class="py-20 sm:py-24">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <h2 class="font-display text-3xl font-bold tracking-tight text-ink sm:text-4xl">Integrations</h2>
            <p class="mt-4 text-muted">Built for the stack your store already uses.</p>
        </div>

        <div class="mx-auto mt-12 grid max-w-3xl gap-4 sm:grid-cols-3">
            @foreach ([
                ['WooCommerce', 'Connect products, cart, and checkout via the WordPress plugin.'],
                ['OpenAI', 'Power natural shopping conversations with leading language models.'],
                ['Google Gemini', 'Optional provider support for flexible AI configuration.'],
            ] as [$name, $copy])
                <div class="rounded-2xl border border-ink/8 bg-surface px-5 py-6 text-center">
                    <p class="font-display text-lg font-bold text-ink">{{ $name }}</p>
                    <p class="mt-2 text-sm text-muted">{{ $copy }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
