@php($subscription = $subscription ?? null)
<div class="form-group">
    <label>User</label>
    <select name="user_id" class="form-control" required>
        @foreach ($users as $user)
            <option value="{{ $user->id }}" @selected(old('user_id', $subscription?->user_id) == $user->id)>{{ $user->name }} ({{ $user->email }})</option>
        @endforeach
    </select>
</div>
<div class="form-group">
    <label>Plan</label>
    <select name="plan_id" class="form-control" required>
        @foreach ($plans as $plan)
            <option value="{{ $plan->id }}" @selected(old('plan_id', $subscription?->plan_id) == $plan->id)>{{ $plan->name }}</option>
        @endforeach
    </select>
</div>
<div class="form-group">
    <label>Provider</label>
    <select name="provider" class="form-control">
        @foreach ($providers as $provider)
            <option value="{{ $provider->value }}" @selected(old('provider', $subscription?->provider?->value ?? 'manual') === $provider->value)>{{ $provider->value }}</option>
        @endforeach
    </select>
</div>
<div class="form-group">
    <label>Status</label>
    <select name="status" class="form-control">
        @foreach ($statuses as $status)
            <option value="{{ $status->value }}" @selected(old('status', $subscription?->status?->value ?? 'active') === $status->value)>{{ $status->value }}</option>
        @endforeach
    </select>
</div>
<div class="form-group">
    <label>Provider customer ID</label>
    <input type="text" name="provider_customer_id" class="form-control" value="{{ old('provider_customer_id', $subscription?->provider_customer_id) }}">
</div>
<div class="form-group">
    <label>Provider subscription ID</label>
    <input type="text" name="provider_subscription_id" class="form-control" value="{{ old('provider_subscription_id', $subscription?->provider_subscription_id) }}">
</div>
<div class="form-group">
    <label>Starts at</label>
    <input type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at', $subscription?->starts_at?->format('Y-m-d\\TH:i')) }}">
</div>
<div class="form-group">
    <label>Ends at</label>
    <input type="datetime-local" name="ends_at" class="form-control" value="{{ old('ends_at', $subscription?->ends_at?->format('Y-m-d\\TH:i')) }}">
</div>
<div class="form-group">
    <div class="form-check">
        <input type="hidden" name="cancel_at_period_end" value="0">
        <input type="checkbox" name="cancel_at_period_end" value="1" class="form-check-input" id="cancel_at_period_end" @checked(old('cancel_at_period_end', $subscription?->cancel_at_period_end ?? false))>
        <label class="form-check-label" for="cancel_at_period_end">Cancel at period end</label>
    </div>
</div>
