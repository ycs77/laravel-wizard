<div class="form-group">
    <label for="title">Title</label>
    <input type="text" name="title" id="title" class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}" value="{{ old('title') ?? $step->data('title') }}">
    @if ($errors->has('title'))
        <span class="invalid-feedback">{{ $errors->first('title') }}</span>
    @endif
</div>

<div class="form-group">
    <label for="content">Content</label>
    <textarea name="content" id="content" class="form-control {{ $errors->has('content') ? 'is-invalid' : '' }}">
        {{ old('content') ?? $step->data('content') }}
    </textarea>
    @if ($errors->has('content'))
        <span class="invalid-feedback">{{ $errors->first('content') }}</span>
    @endif
</div>
