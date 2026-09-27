<section class="border-y border-ink/5 bg-surface py-20 sm:py-24">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <h2 class="font-display text-3xl font-bold tracking-tight text-ink sm:text-4xl">
                Customers Have Questions. Your Store Can't Answer Everyone.
            </h2>
        </div>

        <ul class="mx-auto mt-12 grid max-w-3xl gap-4 sm:grid-cols-2">
            @foreach ([
                'Customers can\'t find the right product',
                'Product questions remain unanswered',
                'Visitors leave without buying',
                'Support teams answer the same questions repeatedly',
            ] as $problem)
                <li class="flex items-start gap-3 rounded-2xl border border-ink/8 bg-canvas px-5 py-4">
                    <span class="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full bg-red-100 text-sm font-bold text-red-600" aria-hidden="true">×</span>
                    <span class="text-sm font-medium leading-relaxed text-ink">{{ $problem }}</span>
                </li>
            @endforeach
        </ul>

        <p class="mx-auto mt-12 max-w-2xl text-center text-lg font-medium text-brand-700">
            CommercePilot gives every visitor a personal shopping assistant.
        </p>
    </div>
</section>
