@extends('admin.layouts.app')

@section('title', 'AI settings')

@section('content')
    @php
        $openAi = \App\Enums\AiProvider::OpenAi->value;
        $gemini = \App\Enums\AiProvider::Gemini->value;
        $activeProvider = old('provider', $settings->provider?->value ?? $openAi);
    @endphp
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">AI provider</h3>
        </div>
        <form method="POST" action="{{ route('admin.settings.ai.update') }}" id="ai-settings-form"
              data-openai-model="{{ config('commercepilot.ai.model') }}"
              data-gemini-model="{{ config('commercepilot.ai.gemini_model') }}">
            @csrf
            @method('PUT')
            <div class="card-body">
                <p class="text-muted">
                    Saved values override <code>.env</code>. Leave an API key blank to keep the current key.
                    If no dashboard key is stored, CommercePilot falls back to <code>OPENAI_API_KEY</code> or <code>GEMINI_API_KEY</code>.
                </p>

                <div class="form-group">
                    <label>Active provider</label>
                    <div class="btn-group btn-group-toggle d-flex" data-toggle="buttons">
                        <label class="btn btn-outline-primary flex-fill {{ $activeProvider === $openAi ? 'active' : '' }}">
                            <input type="radio" name="provider" value="{{ $openAi }}" autocomplete="off" @checked($activeProvider === $openAi)>
                            OpenAI
                        </label>
                        <label class="btn btn-outline-primary flex-fill {{ $activeProvider === $gemini ? 'active' : '' }}">
                            <input type="radio" name="provider" value="{{ $gemini }}" autocomplete="off" @checked($activeProvider === $gemini)>
                            Gemini
                        </label>
                    </div>
                </div>

                <div data-provider-fields="{{ $openAi }}">
                    <div class="form-group">
                        <label>OpenAI API key</label>
                        <input type="password" name="openai_api_key" class="form-control" autocomplete="new-password" placeholder="sk-...">
                        <small class="form-text text-muted">
                            @if ($settings->maskedApiKey())
                                Current dashboard key: {{ $settings->maskedApiKey() }}
                            @elseif ($envKeyConfigured)
                                No dashboard key stored. Using the <code>.env</code> key.
                            @else
                                No OpenAI API key configured.
                            @endif
                        </small>
                    </div>

                    <div class="form-group">
                        <label>Organization (optional)</label>
                        <input type="text" name="openai_organization" class="form-control" value="{{ old('openai_organization', $settings->openai_organization) }}">
                    </div>
                </div>

                <div data-provider-fields="{{ $gemini }}">
                    <div class="form-group">
                        <label>Gemini API key</label>
                        <input type="password" name="gemini_api_key" class="form-control" autocomplete="new-password" placeholder="AIza...">
                        <small class="form-text text-muted">
                            @if ($settings->maskedGeminiApiKey())
                                Current dashboard key: {{ $settings->maskedGeminiApiKey() }}
                            @elseif ($envGeminiKeyConfigured)
                                No dashboard key stored. Using the <code>.env</code> key.
                            @else
                                No Gemini API key configured.
                            @endif
                        </small>
                    </div>
                </div>

                <div class="form-group">
                    <label>Model</label>
                    <input type="text" name="model" class="form-control" value="{{ old('model', $settings->model) }}" required>
                    <small class="form-text text-muted" data-model-hint="{{ $openAi }}">Example: gpt-4o-mini, gpt-4.1-mini</small>
                    <small class="form-text text-muted" data-model-hint="{{ $gemini }}">Example: gemini-3.8-flash, gemini-3.5-flash</small>
                </div>

                <div class="form-group">
                    <label>Timeout (seconds)</label>
                    <input type="number" name="timeout" class="form-control" min="5" max="120" value="{{ old('timeout', $settings->timeout) }}" required>
                </div>

                <div class="form-group">
                    <label>Max tool iterations</label>
                    <input type="number" name="max_tool_iterations" class="form-control" min="1" max="10" value="{{ old('max_tool_iterations', $settings->max_tool_iterations) }}" required>
                </div>

                <div class="form-group">
                    <label>Max context messages</label>
                    <input type="number" name="max_context_messages" class="form-control" min="4" max="50" value="{{ old('max_context_messages', $settings->max_context_messages) }}" required>
                </div>

                <hr>
                <h4>Greeting replies</h4>
                <p class="text-muted">
                    Whole-message greetings return a ready reply and skip the AI provider. Messages that include a greeting plus a shopping question still go to the model.
                </p>

                <div class="form-group">
                    <label>Greeting phrases</label>
                    <textarea name="greeting_phrases" class="form-control" rows="7">{{ is_array(old('greeting_phrases')) ? implode("\n", old('greeting_phrases')) : old('greeting_phrases', $settings->greetingPhrasesText()) }}</textarea>
                    <small class="form-text text-muted">One phrase per line. The entire visitor message must match after punctuation is ignored.</small>
                </div>

                <div class="form-group">
                    <label>Greeting reply (optional)</label>
                    <textarea name="greeting_reply" class="form-control" rows="2">{{ old('greeting_reply', $settings->greeting_reply) }}</textarea>
                    <small class="form-text text-muted">Leave blank to use each site’s welcome message.</small>
                </div>
            </div>
            <div class="card-footer">
                <button class="btn btn-primary">Save settings</button>
            </div>
        </form>
    </div>
    <script>
        (function () {
            var form = document.getElementById('ai-settings-form');
            if (!form) {
                return;
            }

            function selectedProvider() {
                var checked = form.querySelector('input[name="provider"]:checked');
                return checked ? checked.value : 'openai';
            }

            function syncProvider() {
                var provider = selectedProvider();
                var modelInput = form.querySelector('input[name="model"]');
                var model = modelInput ? modelInput.value : '';
                var openAiDefault = form.getAttribute('data-openai-model') || 'gpt-4o-mini';
                var geminiDefault = form.getAttribute('data-gemini-model') || 'gemini-3.8-flash';

                form.querySelectorAll('[data-provider-fields]').forEach(function (panel) {
                    panel.hidden = panel.getAttribute('data-provider-fields') !== provider;
                });
                form.querySelectorAll('[data-model-hint]').forEach(function (hint) {
                    hint.hidden = hint.getAttribute('data-model-hint') !== provider;
                });

                if (!modelInput) {
                    return;
                }

                if (provider === 'gemini' && (/^(gpt-|o1|o3|chatgpt)/i.test(model) || /^gemini-([12][.-]|3\.6[.-])/i.test(model))) {
                    modelInput.value = geminiDefault;
                }

                if (provider === 'openai' && /^gemini-/i.test(model)) {
                    modelInput.value = openAiDefault;
                }
            }

            form.querySelectorAll('input[name="provider"]').forEach(function (input) {
                input.addEventListener('change', syncProvider);
            });

            syncProvider();
        })();
    </script>
@endsection
