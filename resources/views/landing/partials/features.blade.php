<section id="features" class="scroll-mt-24 border-y border-ink/5 bg-surface py-20 sm:py-24">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <h2 class="font-display text-3xl font-bold tracking-tight text-ink sm:text-4xl">Main Features</h2>
            <p class="mt-4 text-muted">Everything your store needs to guide shoppers from curiosity to checkout.</p>
        </div>

        <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ([
                [
                    'title' => 'AI Shopping Assistant',
                    'copy' => 'Answers customer questions naturally and recommends relevant products.',
                    'path' => 'M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z',
                ],
                [
                    'title' => 'Product Discovery',
                    'copy' => 'Helps customers find products based on their needs and preferences.',
                    'path' => 'm21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z',
                ],
                [
                    'title' => 'Order Assistance',
                    'copy' => 'Guides customers from product discovery toward purchasing.',
                    'path' => 'M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z',
                ],
                [
                    'title' => '24/7 Customer Support',
                    'copy' => 'Your AI assistant keeps working even when your team is offline.',
                    'path' => 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
                ],
                [
                    'title' => 'Conversation Insights',
                    'copy' => 'Understand what customers are asking before they purchase.',
                    'path' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z',
                ],
                [
                    'title' => 'Easy WooCommerce Integration',
                    'copy' => 'Install and start using it without complicated development.',
                    'path' => 'M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z',
                ],
            ] as $feature)
                <article class="rounded-2xl border border-ink/8 bg-canvas p-6 transition hover:border-brand-200 hover:shadow-lg hover:shadow-brand-800/5">
                    <div class="flex size-11 items-center justify-center rounded-xl bg-brand-100 text-brand-700">
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $feature['path'] }}" />
                        </svg>
                    </div>
                    <h3 class="mt-4 font-display text-lg font-bold text-ink">{{ $feature['title'] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-muted">{{ $feature['copy'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
