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
     * Set the step model.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function setModel(Request $request)
    {
        //
    }

    /**
     * Save this step form data.
     *
     * @param  array|null  $data
     * @param  \Illuminate\Database\Eloquent\Model|null  $data
     * @return void
     */
    public function saveData($data = null, $model = null)
    {
        $queue = session('test-steps-queue', []);
        $queue['second'] = true;
        session()->put('test-steps-queue', $queue);
    }

    /**
     * Validation rules.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function rules(Request $request)
    {
        return [];
    }
}
