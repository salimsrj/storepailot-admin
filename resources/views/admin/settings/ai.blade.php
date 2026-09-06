@extends('admin.layouts.app')

@section('title', 'AI settings')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">OpenAI</h3>
        </div>
        <form method="POST" action="{{ route('admin.settings.ai.update') }}">
            @csrf
            @method('PUT')
            <div class="card-body">
                <p class="text-muted">
                    Saved values override <code>.env</code>. Leave the API key blank to keep the current key.
                    If no dashboard key is stored, CommercePilot falls back to <code>OPENAI_API_KEY</code>.
                </p>

                <div class="form-group">
                    <label>API key</label>
                    <input type="password" name="openai_api_key" class="form-control" autocomplete="new-password" placeholder="sk-...">
                    <small class="form-text text-muted">
                        @if ($settings->maskedApiKey())
                            Current dashboard key: {{ $settings->maskedApiKey() }}
                        @elseif ($envKeyConfigured)
                            No dashboard key stored. Using the <code>.env</code> key.
                        @else
                            No API key configured. Chat will return 503 until you add one.
                        @endif
                    </small>
                </div>

                <div class="form-group">
                    <label>Organization (optional)</label>
                    <input type="text" name="openai_organization" class="form-control" value="{{ old('openai_organization', $settings->openai_organization) }}">
                </div>

                <div class="form-group">
                    <label>Model</label>
                    <input type="text" name="model" class="form-control" value="{{ old('model', $settings->model) }}" required>
                    <small class="form-text text-muted">Example: gpt-4.1-mini, gpt-4o-mini</small>
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
                    Whole-message greetings return a ready reply and skip OpenAI. Messages that include a greeting plus a shopping question still go to the model.
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
@endsection
