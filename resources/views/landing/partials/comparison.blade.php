<section class="border-y border-ink/5 bg-surface py-20 sm:py-24">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <h2 class="font-display text-3xl font-bold tracking-tight text-ink sm:text-4xl">Why CommercePilot?</h2>
            <p class="mt-4 text-muted">A clearer shopping experience—without changing how you run your store.</p>
        </div>

        <div class="mx-auto mt-12 max-w-3xl overflow-hidden rounded-2xl border border-ink/8">
            <div class="grid grid-cols-2 bg-brand-600 text-sm font-semibold text-white">
                <div class="border-r border-white/15 px-4 py-3 sm:px-6">Traditional Store</div>
                <div class="px-4 py-3 sm:px-6">With CommercePilot</div>
            </div>
            @foreach ([
                ['Static product pages', 'Interactive shopping'],
                ['Customer searches alone', 'AI-guided discovery'],
                ['Limited support hours', '24/7 assistance'],
                ['Repetitive support questions', 'Automated answers'],
                ['One-size-fits-all experience', 'Personalized conversations'],
            ] as [$left, $right])
                <div class="grid grid-cols-2 border-t border-ink/8 text-sm">
                    <div class="border-r border-ink/8 bg-canvas px-4 py-4 text-muted sm:px-6">{{ $left }}</div>
                    <div class="bg-brand-50/60 px-4 py-4 font-medium text-brand-800 sm:px-6">{{ $right }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>
