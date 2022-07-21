@extends('layouts.app')

@section('content')
    <div class="px-4 container mx-auto my-3">
        <div class="flex justify-center">
            <div class="w-full md:w-2/3">
                <h1 class="text-2xl text-center font-medium md:text-4xl">{{ $wizardTitle }}</h1>

                <div class="mb-4">
                    @include($getViewPath('steps_bar'))
                </div>

                <form action="{{ $getActionUrl($postAction, [$step->slug()]) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    @include($step->view(), compact('step', 'errors'))

                    <div class="flex items-center">
                        <div class="mr-auto">
                            @if ($stepRepo->hasPrev())
                                <button type="button" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm tracking-wide rounded-md transition-colors duration-200" onclick="this.form.action = '{{ $getActionUrl($postAction, [$step->slug(), '_trigger' => 'back']) }}'; this.form.submit();">
                                    @lang('wizard::generic.back')
                                </button>
                            @endif
                        </div>

                        <div class="ml-auto">
                            @if ($step->skip())
                                <button type="button" class="px-4 py-2 text-gray-400 hover:text-gray-500 text-sm tracking-wide rounded-md transition-colors duration-200 mr-2" onclick="this.form.action = '{{ $getActionUrl($postAction, [$step->slug(), '_trigger' => 'skip']) }}'; this.form.submit();">
                                    @lang('wizard::generic.skip')
                                </button>
                            @endif

                            @if ($stepRepo->hasNext())
                                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm tracking-wide rounded-md transition-colors duration-200">
                                    @lang('wizard::generic.next')
                                </button>
                            @else
                                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm tracking-wide rounded-md transition-colors duration-200">
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
