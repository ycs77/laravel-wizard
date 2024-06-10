<div class="form-group mb-3">
    <label for="title">Title</label>
    <input type="text" name="title" id="title" @class(['form-control', 'is-invalid' => $errors->has('title')]) value="{{ old('title', $step->data('title')) }}">

    @error('title')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group mb-3">
    <label for="content">Content</label>
    <textarea name="content" id="content" @class(['form-control', 'is-invalid' => $errors->has('content')])>
        {{ old('content', $step->data('content')) }}
    </textarea>

    @error('content')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>
