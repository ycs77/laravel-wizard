<div class="form-group mb-3">
    <label for="avatar">Avatar</label>
    <input type="file" name="avatar" id="avatar" @class(['form-control', 'is-invalid' => $errors->has('avatar')])>

    @error('avatar')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>
