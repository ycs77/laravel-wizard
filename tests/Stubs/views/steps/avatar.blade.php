<div class="form-group mb-3">
    <label for="avatar">Avatar</label>
    <input type="file" name="avatar" id="avatar" class="form-control">
    <div class="form-control d-none {{ $errors->has('avatar') ? 'is-invalid' : '' }}"></div>
    @if ($errors->has('avatar'))
        <span class="invalid-feedback">{{ $errors->first('avatar') }}</span>
    @endif
</div>
