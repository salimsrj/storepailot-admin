<div class="form-group">
    <label>Name</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name ?? '') }}" required>
</div>
<div class="form-group">
    <label>Email</label>
    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email ?? '') }}" required>
</div>
<div class="form-group">
    <label>Password @isset($user)<small>(leave blank to keep)</small>@endisset</label>
    <input type="password" name="password" class="form-control" @isset($user) @else required @endisset>
</div>
<div class="form-group">
    <label>Status</label>
    <select name="status" class="form-control">
        @foreach ($statuses as $status)
            <option value="{{ $status->value }}" @selected(old('status', $user->status->value ?? 'active') === $status->value)>{{ $status->value }}</option>
        @endforeach
    </select>
</div>
<div class="form-group">
    <div class="form-check">
        <input type="hidden" name="is_admin" value="0">
        <input type="checkbox" name="is_admin" value="1" class="form-check-input" id="is_admin" @checked(old('is_admin', $user->is_admin ?? false))>
        <label class="form-check-label" for="is_admin">Administrator</label>
    </div>
</div>
