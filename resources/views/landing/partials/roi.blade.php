<section class="border-y border-ink/5 bg-surface py-20 sm:py-24">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <h2 class="font-display text-3xl font-bold tracking-tight text-ink sm:text-4xl">
                Your Store Works 24/7. Why Should Your Sales Assistant?
            </h2>
            <p class="mt-4 text-muted">Focus on business value—more guided conversations, better discovery, more chances to buy.</p>
        </div>

        <div class="mx-auto mt-14 flex max-w-3xl flex-col items-center gap-4 sm:flex-row sm:justify-center sm:gap-6">
            @foreach ([
                'More conversations',
                'Better product discovery',
                'More purchase opportunities',
            ] as $index => $label)
                <div class="w-full rounded-2xl border border-brand-200 bg-brand-50 px-6 py-5 text-center sm:w-auto sm:min-w-[11rem]">
                    <p class="font-display text-lg font-bold text-brand-800">{{ $label }}</p>
                </div>
                @if ($index < 2)
                    <span class="hidden text-2xl font-light text-brand-400 sm:inline" aria-hidden="true">↓</span>
                    <span class="text-2xl font-light text-brand-400 sm:hidden" aria-hidden="true">↓</span>
                @endif
            @endforeach
        </div>
    </div>
</section>
