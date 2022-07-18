@extends('layout')

@section('content')
    <div class="container mx-auto my-3">
        <div class="flex justify-center w-full">
            <div class="w-full">
                <h1 class="text-center">{{ $wizardTitle }}</h1>
                <div class="mb-3">
                    @include($getViewPath('steps_bar'))
                </div>
                <form action="{{ $getActionUrl($postAction, [$step->slug()]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @include($step->view(), compact('step', 'errors'))
                    <div class="flex items-center">
                        <div class="mx-auto">
                            @if ($stepRepo->hasPrev())
                                <button type="button" class="px-4 py-2 transition duration-300 bg-gray-200 rounded-md hover:bg-gray-400" onclick="this.form.action = '{{ $getActionUrl($postAction, [$step->slug(), '_trigger' => 'back']) }}'; this.form.submit();">
                                    @lang('wizard::generic.back')
                                </button>
                            @endif
                        </div>
                        <div class="ms-auto">
                            @if ($step->skip())
                                <button type="button" class="px-4 py-2 transition duration-300 bg-gray-200 rounded-md hover:bg-gray-400" onclick="this.form.action = '{{ $getActionUrl($postAction, [$step->slug(), '_trigger' => 'skip']) }}'; this.form.submit();">
                                    @lang('wizard::generic.skip')
                                </button>
                            @endif
                            @if ($stepRepo->hasNext())
                                <button type="submit" class="px-4 py-2 transition duration-300 bg-gray-200 rounded-md hover:bg-gray-400">
                                    @lang('wizard::generic.next')
                                </button>
                            @else
                                <button type="submit" class="px-4 py-2 transition duration-300 bg-gray-200 rounded-md hover:bg-gray-400">
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
