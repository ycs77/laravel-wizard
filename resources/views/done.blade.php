@extends('layouts.app')

@section('content')
    <div class="container my-5">
        <h1 class="text-center">
            {{ __($doneText ?? 'wizard::generic.done') }}
        </h1>
    </div>
@endsection
