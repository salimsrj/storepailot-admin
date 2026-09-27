<section class="border-y border-ink/5 bg-surface py-20 sm:py-24">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <h2 class="font-display text-3xl font-bold tracking-tight text-ink sm:text-4xl">Security & Privacy</h2>
            <p class="mt-4 text-lg text-muted">Built with your store and customer data in mind.</p>
        </div>

        <ul class="mx-auto mt-12 grid max-w-4xl gap-4 sm:grid-cols-2">
            @foreach ([
                ['Secure API communication', 'HMAC-signed requests between your WordPress plugin and CommercePilot.'],
                ['Protected API keys', 'Site tokens are stored encrypted and can be rotated from your dashboard.'],
                ['Store-level data isolation', 'Conversations and catalog access are scoped to each connected site.'],
                ['Usage monitoring', 'Message usage and rate limits help you stay in control of capacity.'],
                ['Customer data controls', 'Human handover lets your team take over conversations when needed.'],
            ] as [$title, $copy])
                <li class="rounded-2xl border border-ink/8 bg-canvas px-5 py-5">
                    <h3 class="font-display text-base font-bold text-ink">{{ $title }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-muted">{{ $copy }}</p>
                </li>
            @endforeach
        </ul>
    </div>
</section>
