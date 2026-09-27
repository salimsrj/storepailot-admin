<section id="pricing" class="scroll-mt-24 py-20 sm:py-24">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <h2 class="font-display text-3xl font-bold tracking-tight text-ink sm:text-4xl">Pricing</h2>
            <p class="mt-4 text-muted">Start free. Scale as conversations grow.</p>
        </div>

        <div class="mt-14 grid gap-6 lg:grid-cols-4">
            @foreach ([
                [
                    'name' => 'Free',
                    'price' => '$0',
                    'period' => '/month',
                    'cta' => 'Start Free',
                    'featured' => false,
                    'features' => [
                        '100 AI conversations',
                        '1 WooCommerce store',
                        'Basic AI assistant',
                        'Product assistance',
                    ],
                ],
                [
                    'name' => 'Starter',
                    'price' => '$9',
                    'period' => '/month',
                    'cta' => 'Start Starter',
                    'featured' => false,
                    'features' => [
                        '2,000 AI messages',
                        '1 store',
                        'Product recommendations',
                        'AI customer support',
                        'Conversation history',
                    ],
                ],
                [
                    'name' => 'Growth',
                    'price' => '$19',
                    'period' => '/month',
                    'cta' => 'Start Growth',
                    'featured' => true,
                    'features' => [
                        '5,000 AI messages',
                        '1 store',
                        'Advanced product assistance',
                        'Order assistance',
                        'Analytics',
                    ],
                ],
                [
                    'name' => 'Pro',
                    'price' => '$39',
                    'period' => '/month',
                    'cta' => 'Start Pro',
                    'featured' => false,
                    'features' => [
                        '15,000 AI messages',
                        '1 store',
                        'Advanced AI features',
                        'Priority support',
                        'Advanced analytics',
                    ],
                ],
            ] as $plan)
                <article @class([
                    'flex flex-col rounded-2xl border p-6',
                    'border-brand-500 bg-brand-600 text-white shadow-xl shadow-brand-800/20' => $plan['featured'],
                    'border-ink/8 bg-surface' => ! $plan['featured'],
                ])>
                    <h3 @class([
                        'font-display text-lg font-bold',
                        'text-white' => $plan['featured'],
                        'text-ink' => ! $plan['featured'],
                    ])>{{ $plan['name'] }}</h3>
                    <p class="mt-4">
                        <span class="font-display text-4xl font-extrabold">{{ $plan['price'] }}</span>
                        <span @class(['text-sm', 'text-brand-100' => $plan['featured'], 'text-muted' => ! $plan['featured']])>{{ $plan['period'] }}</span>
                    </p>
                    <ul class="mt-6 flex-1 space-y-3 text-sm">
                        @foreach ($plan['features'] as $feature)
                            <li class="flex gap-2">
                                <span @class(['mt-0.5', 'text-brand-100' => $plan['featured'], 'text-brand-600' => ! $plan['featured']]) aria-hidden="true">✓</span>
                                <span @class(['text-brand-50' => $plan['featured'], 'text-muted' => ! $plan['featured']])>{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <a
                        href="{{ route('signup.create') }}"
                        @class([
                            'mt-8 block rounded-full px-4 py-2.5 text-center text-sm font-semibold transition',
                            'bg-white text-brand-700 hover:bg-brand-50' => $plan['featured'],
                            'bg-brand-600 text-white hover:bg-brand-700' => ! $plan['featured'],
                        ])
                    >
                        {{ $plan['cta'] }}
                    </a>
                </article>
            @endforeach
        </div>

        <p class="mt-10 text-center text-sm text-muted">
            Need more? Contact us for custom usage plans.
        </p>
    </div>
</section>
