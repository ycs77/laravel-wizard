<?php

namespace Ycs77\LaravelWizard\Test\Stubs;

use Illuminate\Http\Request;
use Ycs77\LaravelWizard\Step;

class SaveAvatarStepStub extends Step
{
    /**
     * The step slug.
     *
     * @var string
     */
    protected $slug = 'save-avatar-step-stub';

    /**
     * The step show label text.
     *
     * @var string
     */
    protected $label = 'Save avatar step stub';

    /**
     * The step form view path.
     *
     * @var string
     */
    protected $view = 'steps.save-avatar';

    /**
     * Set the step model instance or the relationships instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\Relation|null
     */
    public function model(Request $request)
    {
        return $request->user();
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
        $data = $this->getStepsData();
        $data['avatar'] = $data['avatar']->storeAs('avatar', 'saved_avatar.jpg');
        $model->update($data);
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
