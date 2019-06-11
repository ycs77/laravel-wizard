<div class="form-group">
    <label for="phone">Phone</label>
    <input type="tel" name="phone" id="phone" class="form-control{{ $errors->has('phone') ? ' is-invalid' : '' }}" value="{{ old('phone') ?? $step->data('phone') }}">
    @if ($errors->has('phone'))
        <span class="invalid-feedback">{{ $errors->first('phone') }}</span>
    @endif
</div>
