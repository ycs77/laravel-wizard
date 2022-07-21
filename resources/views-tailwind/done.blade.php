@extends('layouts.app')

@section('content')
    <div class="px-4 container mx-auto my-3">
        <h1 class="text-2xl text-center font-medium md:text-4xl">
            @lang($doneText ?? 'wizard::generic.done')
        </h1>
    </div>
@endsection
