<section class="relative overflow-hidden pt-24 sm:pt-28">
    <div class="pointer-events-none absolute inset-0 -z-10">
        <div class="absolute -left-24 top-10 size-72 rounded-full bg-brand-200/50 blur-3xl"></div>
        <div class="absolute right-0 top-32 size-96 rounded-full bg-brand-100/80 blur-3xl"></div>
        <div class="absolute inset-x-0 bottom-0 h-40 bg-gradient-to-t from-canvas to-transparent"></div>
    </div>

    <div class="mx-auto grid max-w-6xl items-center gap-12 px-4 pb-16 sm:px-6 lg:grid-cols-2 lg:gap-16 lg:px-8 lg:pb-24">
        <div class="animate-fade-up">
            <p class="font-display text-3xl font-extrabold tracking-tight text-ink sm:text-4xl lg:text-5xl">
                Commerce<span class="text-brand-600">Pilot</span>
            </p>
            <h1 class="mt-4 font-display text-3xl font-bold leading-tight tracking-tight text-ink sm:text-4xl lg:text-[2.75rem]">
                Turn Every Store Visitor Into a Confident Buyer
            </h1>
            <p class="mt-5 max-w-xl text-lg leading-relaxed text-muted">
                An AI shopping assistant that helps customers discover products, answers their questions, and guides them toward checkout—24/7.
            </p>

            <div class="mt-8 flex flex-wrap items-center gap-3">
                <a
                    href="{{ route('signup.create') }}"
                    class="rounded-full bg-brand-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700"
                >
                    Start Free
                </a>
                <a
                    href="#demo"
                    class="rounded-full border border-ink/15 bg-surface px-6 py-3 text-sm font-semibold text-ink transition hover:border-brand-400 hover:text-brand-700"
                >
                    Watch Demo
                </a>
            </div>

            <p class="mt-5 text-sm text-muted">
                No credit card required · Easy WooCommerce setup · Free plan available
            </p>
        </div>

        <div class="animate-float relative mx-auto w-full max-w-lg lg:mx-0" style="animation-delay: 0.2s">
            <div class="grid gap-4 sm:grid-cols-[0.9fr_1.1fr]">
                <div class="rounded-2xl border border-ink/8 bg-surface p-4 shadow-xl shadow-brand-800/5">
                    <div class="mb-3 flex items-center gap-2">
                        <span class="size-2.5 rounded-full bg-red-400"></span>
                        <span class="size-2.5 rounded-full bg-amber-400"></span>
                        <span class="size-2.5 rounded-full bg-emerald-400"></span>
                        <span class="ml-2 text-xs font-medium text-muted">yourstore.com</span>
                    </div>
                    <div class="space-y-3">
                        <div class="h-24 rounded-xl bg-gradient-to-br from-brand-100 to-brand-50"></div>
                        <div class="space-y-2">
                            <div class="h-2.5 w-3/4 rounded-full bg-ink/10"></div>
                            <div class="h-2.5 w-1/2 rounded-full bg-ink/10"></div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="h-16 rounded-lg bg-canvas"></div>
                            <div class="h-16 rounded-lg bg-canvas"></div>
                        </div>
                        <p class="text-center text-[11px] font-medium text-muted">WooCommerce storefront</p>
                    </div>
                </div>

                <div class="rounded-2xl border border-brand-200 bg-surface p-4 shadow-xl shadow-brand-600/10">
                    <div class="mb-3 flex items-center gap-2 border-b border-ink/5 pb-3">
                        <span class="flex size-8 items-center justify-center rounded-full bg-brand-600 text-xs font-bold text-white">AI</span>
                        <div>
                            <p class="text-sm font-semibold text-ink">Store Assistant</p>
                            <p class="text-[11px] text-brand-600">Online now</p>
                        </div>
                    </div>
                    <div class="space-y-3 text-sm">
                        <div class="ml-auto max-w-[90%] rounded-2xl rounded-br-md bg-brand-600 px-3 py-2 text-white">
                            Looking for a gift under $50?
                        </div>
                        <div class="max-w-[95%] rounded-2xl rounded-bl-md bg-canvas px-3 py-2 text-ink">
                            Sure! Here are three bestsellers that match that budget.
                        </div>
                        <div class="rounded-xl border border-ink/8 bg-canvas p-2">
                            <div class="flex gap-2">
                                <div class="size-10 shrink-0 rounded-lg bg-brand-100"></div>
                                <div class="min-w-0">
                                    <p class="truncate text-xs font-semibold">Ceramic Pour-Over Set</p>
                                    <p class="text-xs text-brand-700">$42 · In stock</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
