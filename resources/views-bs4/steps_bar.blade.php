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
