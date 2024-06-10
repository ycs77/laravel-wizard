<div class="form-group mb-3">
    <label for="name">Name</label>
    <input type="text" name="name" id="name" @class(['form-control', 'is-invalid' => $errors->has('name')]) value="{{ old('name', $step->data('name')) }}">

    @error('name')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>
