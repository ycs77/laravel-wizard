<ul class="steps">
    <div class="w-full py-6">
        <div class="flex justify-center w-full">
            @foreach ($stepRepo->all() as $key => $_step)
            @php
            $stepClass = '';
            $stepBarClass = 'bg-gray-200';
            if ($step->index() == $_step->index()) {
            $stepClass = $errors->isEmpty() ? 'step-active' : 'step-error';
            $stepBarClass = $errors->isEmpty() ? 'bg-green-400' :  'bg-gray-200';

            } elseif ($step->index() > $_step->index()) {
            $stepClass = 'step-success';
            $stepBarClass = 'bg-green-400';
            }
            @endphp
            <div class="w-1/4 {{ $stepClass }}">
                <div class="relative mb-2">
                    @if($loop->index >= 1)
                    <div
                        class="absolute flex items-center content-center align-middle align-center"
                        style="width: calc(100% - 2.5rem - 1rem); top: 50%; transform: translate(-50%, -50%)"
                    >
                        <div class="items-center flex-1 w-full align-middle bg-gray-200 rounded align-center">
                            <div
                                class="w-0 py-1 {{$stepBarClass}} rounded"
                                style="width: 100%;"
                            ></div>
                        </div>
                    </div>
                    @endif
                    <div class="flex items-center w-10 h-10 mx-auto text-lg text-white {{$stepBarClass}} rounded-full">
                        <span class="w-full text-center text-white">
                            {{ $_step->number() }}
                        </span>
                    </div>
                </div>

                <div class="text-xs text-center md:text-base">@lang($_step->label())</div>
            </div>
            @endforeach
        </div>
    </div>
</ul>
