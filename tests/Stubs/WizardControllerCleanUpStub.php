<?php

namespace Ycs77\LaravelWizard\Test\Stubs;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Ycs77\LaravelWizard\Step;

class WizardControllerCleanUpStub extends WizardControllerStub
{
    /**
     * The wizard steps instance.
     *
     * @var array
     */
    protected $steps = [
        UserStepStub::class,
        SetCacheToCleanUpStepStub::class,
        PostStepStub::class,
    ];

    /**
     * On wizard step saved event.
     *
     * @return \Illuminate\Http\RedirectResponse|null
     */
    protected function wizardStepSaved(Request $request, Step $step)
    {
        /** @var mixed $step */
        if (method_exists($step, 'onStepSaved')) {
            return $step->onStepSaved($request);
        }
    }

    /**
     * Clean up the wizard event.
     *
     * @return void
     */
    protected function cleanUpWizard(Request $request)
    {
        Cache::forget('wizard.clean-up.cached');
    }
}
