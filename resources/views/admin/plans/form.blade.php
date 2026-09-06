<div class="form-group">
    <label>Name</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $plan->name ?? '') }}" required>
</div>
<div class="form-group">
    <label>Slug</label>
    <input type="text" name="slug" class="form-control" value="{{ old('slug', $plan->slug ?? '') }}" required>
</div>
<div class="form-group">
    <label>Monthly messages</label>
    <input type="number" name="monthly_messages" class="form-control" value="{{ old('monthly_messages', $plan->monthly_messages ?? 50) }}" required>
</div>
<div class="form-group">
    <label>Max sites</label>
    <input type="number" name="max_sites" class="form-control" value="{{ old('max_sites', $plan->max_sites ?? 1) }}" required>
</div>
<div class="form-group">
    <label>Monthly price</label>
    <input type="number" step="0.01" name="monthly_price" class="form-control" value="{{ old('monthly_price', $plan->monthly_price ?? '0.00') }}" required>
</div>
<div class="form-group">
    <label>Currency</label>
    <input type="text" name="currency" class="form-control" maxlength="3" value="{{ old('currency', $plan->currency ?? 'USD') }}" required>
</div>
<div class="form-group">
    <div class="form-check">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" @checked(old('is_active', $plan->is_active ?? true))>
        <label class="form-check-label" for="is_active">Active</label>
    </div>
</div>
