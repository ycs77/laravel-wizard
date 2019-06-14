@extends('layouts.app')

@section('content')
    <div class="container my-5">
        <h1 class="text-center">
            {{ __($doneText ?? 'wizard::generic.done') }}
        </h1>

        @isset ($wizardData)
            <div class="row justify-content-center mt-3">
                <div class="col-md-8">
                    <ul class="list-group">
                        @foreach ($wizardData as $slug => $stepData)
                            @if ($stepRepo->find($slug))
                                <li class="list-group-item">
                                    <h3 class="mb-1">{{ $stepRepo->find($slug)->label() }}</h3>
                                    <ul class="list-unstyled {{ $loop->last ? 'mb-0' : '' }}">
                                        @foreach ($stepData as $key => $data)
                                            <li>{{ $key }}: {{ $data }}</li>
                                        @endforeach
                                    </ul>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            </div>
        @endisset
    </div>
@endsection
