@extends('layouts.app')

@section('content')
    <div class="container my-3">
        <div class="steps-scroll mb-3">
            <ul class="steps">
                @foreach ($stepRepo->all() as $key => $_step)
                    <?php
                        $stepClass = '';
                        if ($step->index() == $_step->index()) {
                            $stepClass = $errors->isEmpty() ? 'step-active' : 'step-error';
                        } elseif ($step->index() > $_step->index()) {
                            $stepClass = 'step-success';
                        }
                    ?>
                    <li class="step {{ $stepClass }}">
                        <div class="step-content">
                            <span class="step-circle">{{ $_step->number() }}</span>
                            <span class="step-text">@lang($_step->label())</span>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        <form action="{{ action($postAction, [$step->slug()]) }}" method="POST" enctype="multipart/form-data">
            {{ csrf_field() }}

            @include($step->view(), compact('step', 'errors'))

            <div class="d-flex justify-content-between align-items-center">
                @if ($stepRepo->hasPrev())
                    <a href="{{ action($formAction, ['step' => $stepRepo->prevSlug()]) }}" class="btn btn-primary">
                        @lang('wizard::generic.back')
                    </a>
                @else
                    <button class="btn btn-primary" disabled>
                        @lang('wizard::generic.back')
                    </button>
                @endif

                @if ($stepRepo->hasNext())
                    <button type="submit" class="btn btn-primary">
                        @lang('wizard::generic.next')
                    </button>
                @else
                    <button type="submit" class="btn btn-primary">
                        @lang('wizard::generic.done')
                    </button>
                @endif
            </div>
        </form>
    </div>
@endsection
