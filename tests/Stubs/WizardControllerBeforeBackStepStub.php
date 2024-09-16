<?php

namespace Ycs77\LaravelWizard\Test\Stubs;

use Illuminate\Http\Request;
use Ycs77\LaravelWizard\Step;

class WizardControllerBeforeBackStepStub extends WizardControllerStub
{
    /**
     * The wizard steps instance.
     *
     * @var array
     */
    protected $steps = [
        UserStepStub::class,
        PostStepStub::class,
        AvatarStepStub::class,
    ];

    /**
     * On before back wizard step event.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Ycs77\LaravelWizard\Step  $step
     * @return bool|\Illuminate\Http\RedirectResponse
     */
    protected function beforeBackWizardStep(Request $request, Step $step)
    {
        if ($step instanceof AvatarStepStub) {
            return $this->wizard()->redirectToStep('user-step-stub');
        }

        return true;
    }
}
