<?php

namespace Ycs77\LaravelWizard\Test\Stubs;

use Illuminate\Http\Request;
use Ycs77\LaravelWizard\Step;

class AvatarStepStub extends Step
{
    /**
     * The step slug.
     *
     * @var string
     */
    protected $slug = 'avatar-step-stub';

    /**
     * The step show label text.
     *
     * @var string
     */
    protected $label = 'Avatar step stub';

    /**
     * The step form view path.
     *
     * @var string
     */
    protected $view = 'steps.avatar';

    /**
     * Set the step model instance or the relationships instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\Relation|null
     */
    public function model(Request $request)
    {
        //
    }

    /**
     * Save this step form data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array|null  $data
     * @param  \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\Relation|null  $model
     * @return void
     */
    public function saveData(Request $request, $data = null, $model = null)
    {
        // cache avatar and store file on SaveAvatarStepStub.
    }

    /**
     * Validation rules.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function rules(Request $request)
    {
        return [
            //
        ];
    }
}
