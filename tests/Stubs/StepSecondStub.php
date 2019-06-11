<?php

namespace Ycs77\LaravelWizard\Test\Stubs;

use Illuminate\Http\Request;
use Ycs77\LaravelWizard\Step;

class StepSecondStub extends Step
{
    /**
     * The step slug.
     *
     * @var string
     */
    protected $slug = 'step-second-stub';

    /**
     * The step show label text.
     *
     * @var string
     */
    protected $label = 'Step second stub';

    /**
     * The step form view path.
     *
     * @var string
     */
    protected $view = 'steps.second';

    /**
     * Save this step form data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $data
     * @return void
     */
    public function saveData(Request $request, $data = [])
    {
        //
    }

    /**
     * Validation rules.
     *
     * @param  \Illuminate\Http\Request|null  $request
     * @return array
     */
    public function rules(Request $request = null)
    {
        return [];
    }
}
