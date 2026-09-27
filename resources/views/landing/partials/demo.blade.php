<section
    id="demo"
    class="scroll-mt-24 py-20 sm:py-24"
    x-data="{
        started: false,
        step: 0,
        start() {
            if (this.started) return;
            this.started = true;
            this.step = 1;
            setTimeout(() => this.step = 2, 900);
            setTimeout(() => this.step = 3, 1800);
            setTimeout(() => this.step = 4, 2800);
        }
    }"
>
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <h2 class="font-display text-3xl font-bold tracking-tight text-ink sm:text-4xl">
                See Your AI Assistant In Action
            </h2>
            <p class="mt-4 text-muted">A realistic shopping conversation—product cards included.</p>
        </div>

        <div class="mx-auto mt-12 max-w-xl overflow-hidden rounded-3xl border border-ink/8 bg-surface shadow-2xl shadow-brand-800/10">
            <div class="flex items-center gap-3 border-b border-ink/5 bg-brand-600 px-5 py-4 text-white">
                <span class="flex size-10 items-center justify-center rounded-full bg-white/15 text-sm font-bold">AI</span>
                <div>
                    <p class="font-semibold">CommercePilot Assistant</p>
                    <p class="text-xs text-brand-100">Helping shoppers find the right fit</p>
                </div>
            </div>

            <div class="space-y-4 bg-canvas/60 p-5 min-h-[22rem]">
                <div
                    x-show="step >= 1"
                    x-cloak
                    class="animate-chat-in ml-auto max-w-[85%] rounded-2xl rounded-br-md bg-brand-600 px-4 py-3 text-sm text-white"
                >
                    I need a waterproof backpack for travel under $100.
                </div>

                <div
                    x-show="step >= 2"
                    x-cloak
                    class="animate-chat-in max-w-[90%] rounded-2xl rounded-bl-md bg-surface px-4 py-3 text-sm text-ink shadow-sm"
                    style="animation-delay: 0.05s"
                >
                    Absolutely! I found 3 options that match your requirements. Here are the best choices…
                </div>

                <div
                    x-show="step >= 3"
                    x-cloak
                    class="animate-chat-in grid gap-2"
                    style="animation-delay: 0.1s"
                >
                    @foreach ([
                        ['TrailGuard 28L', '$89', 'IPX6 · Laptop sleeve'],
                        ['Harbor Pack Lite', '$74', 'Water-resistant · Carry-on'],
                        ['Summit DryRoll', '$96', 'Roll-top · Lifetime warranty'],
                    ] as [$name, $price, $meta])
                        <div class="flex items-center gap-3 rounded-xl border border-ink/8 bg-surface p-3">
                            <div class="size-12 shrink-0 rounded-lg bg-gradient-to-br from-brand-200 to-brand-50"></div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-ink">{{ $name }}</p>
                                <p class="text-xs text-muted">{{ $meta }}</p>
                            </div>
                            <p class="text-sm font-bold text-brand-700">{{ $price }}</p>
                        </div>
                    @endforeach
                </div>

                <div
                    x-show="step >= 4"
                    x-cloak
                    class="animate-chat-in max-w-[90%] rounded-2xl rounded-bl-md bg-surface px-4 py-3 text-sm text-ink shadow-sm"
                >
                    Want me to add the TrailGuard 28L to your cart, or compare materials first?
                </div>

                <div x-show="!started" class="flex h-full min-h-[16rem] items-center justify-center">
                    <button
                        type="button"
                        @click="start()"
                        class="rounded-full bg-brand-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-brand-700"
                    >
                        Try the Demo
                    </button>
                </div>
            </div>
        </div>

        <div class="mt-8 text-center" x-show="started" x-cloak>
            <a
                href="{{ route('signup.create') }}"
                class="inline-flex rounded-full bg-brand-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-brand-700"
            >
                Start Free
            </a>
        </div>
    </div>
</section>
