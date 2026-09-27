<section id="faq" class="scroll-mt-24 py-20 sm:py-24" x-data="{ open: 0 }">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <h2 class="font-display text-3xl font-bold tracking-tight text-ink sm:text-4xl">FAQ</h2>
            <p class="mt-4 text-muted">Straight answers about how CommercePilot works.</p>
        </div>

        <div class="mx-auto mt-12 max-w-3xl divide-y divide-ink/10 border-y border-ink/10">
            @foreach ([
                [
                    'What is CommercePilot?',
                    'CommercePilot is an AI shopping assistant for WooCommerce stores. It helps visitors discover products, get answers, and move toward checkout—around the clock.',
                ],
                [
                    'How does the AI learn about my products?',
                    'Once connected, CommercePilot uses your live WooCommerce catalog—product data, availability, and cart tools—so answers stay grounded in what you actually sell.',
                ],
                [
                    'Do I need coding knowledge?',
                    'No. Connect your store with the plugin credentials from your dashboard and configure assistant settings without writing code.',
                ],
                [
                    'How many AI messages are included?',
                    'Each plan includes a monthly message allowance. Free starts you with a smaller quota; paid plans scale up as traffic grows.',
                ],
                [
                    'What happens when I reach my limit?',
                    'Chat capacity is metered against your plan. When you approach or hit the limit, upgrade to keep conversations flowing.',
                ],
                [
                    'Can the AI help customers choose products?',
                    'Yes. It can search products, check stock, recommend options, manage cart items, and share a checkout link.',
                ],
                [
                    'Can I cancel anytime?',
                    'Yes. You can change or stop using a paid plan according to your subscription settings—no long-term lock-in required to get started.',
                ],
                [
                    'Which stores can use it?',
                    'CommercePilot is built for WooCommerce stores running on WordPress with the CommercePilot plugin connection.',
                ],
            ] as $index => [$question, $answer])
                <div>
                    <button
                        type="button"
                        class="flex w-full items-center justify-between gap-4 py-5 text-left"
                        @click="open = open === {{ $index }} ? null : {{ $index }}"
                        :aria-expanded="(open === {{ $index }}).toString()"
                    >
                        <span class="font-display text-base font-semibold text-ink">{{ $question }}</span>
                        <span
                            class="text-brand-600 transition"
                            :class="open === {{ $index }} ? 'rotate-45' : ''"
                            aria-hidden="true"
                        >+</span>
                    </button>
                    <div
                        x-show="open === {{ $index }}"
                        x-cloak
                        x-transition
                        class="pb-5 pr-8 text-sm leading-relaxed text-muted"
                    >
                        {{ $answer }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
