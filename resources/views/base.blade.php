@extends('layouts.app')

@section('content')
    <div class="container my-3">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h1 class="text-center">{{ $wizardTitle }}</h1>

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

                    <div class="d-flex align-items-center">
                        <div class="mr-auto">
                            @if ($stepRepo->hasPrev())
                                <button type="button" class="btn btn-primary" onclick="this.form.action = '{{ action($postAction, [$step->slug(), '_trigger' => 'back']) }}'; this.form.submit();">
                                    @lang('wizard::generic.back')
                                </button>
                            @endif
                        </div>

                        <div class="ml-auto">
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
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
