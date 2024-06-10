<div class="py-6">
    <div class="flex justify-center">
        @foreach ($stepRepo->all() as $key => $_step)
            @php
                $stepClass = 'bg-gray-200';
                $stepTextClass = 'text-gray-300';

                if ($step->index() == $_step->index()) {
                    $stepClass = 'bg-indigo-500';
                    $stepTextClass = 'text-indigo-500 font-semibold';
                } elseif ($step->index() > $_step->index()) {
                    $stepClass = 'bg-indigo-500';
                    $stepTextClass = 'text-indigo-500 font-semibold';
                }
            @endphp

            <div class="w-1/4">
                <div class="relative mb-2">
                    @if ($loop->index >= 1)
                        <div class="absolute top-1/2 w-[calc(100%-2.5rem-1rem)] flex transform translate-x-[-50%] translate-y-[-50%]">
                            <div @class(['flex-1 w-full h-2', $stepClass, 'rounded'])></div>
                        </div>
                    @endif

                    <div @class(['flex items-center mx-auto w-10 h-10', $stepClass, 'text-lg text-white rounded-full'])>
                        <span class="w-full text-center text-white">
                            {{ $_step->number() }}
                        </span>
                    </div>
                </div>

                <div @class(['text-center text-sm', $stepTextClass, 'md:text-base'])>@lang($_step->label())</div>
            </div>
        @endforeach
    </div>
</div>
