<?php

namespace Ycs77\LaravelWizard\Http\Controllers\Traits;

use Illuminate\Http\Request;
use Ycs77\LaravelWizard\Step;

trait WizardControllerEvents
{
    /**
     * On before wizard step create event.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|null
     */
    protected function beforeWizardStepCreate(Request $request)
    {
        //
    }

    /**
     * On wizard step created event.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Ycs77\LaravelWizard\Step $step
     * @return \Illuminate\Http\RedirectResponse|null
     */
    protected function wizardStepCreated(Request $request, Step $step)
    {
        //
    }

    /**
     * On before wizard step save event.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|null
     */
    protected function beforeWizardStepSave(Request $request)
    {
        //
    }

    /**
     * On wizard step validated event.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function wizardStepFormValidated(Request $request)
    {
        //
    }

    /**
     * On wizard step saved event.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Ycs77\LaravelWizard\Step $step
     * @return \Illuminate\Http\RedirectResponse|null
     */
    protected function wizardStepSaved(Request $request, Step $step)
    {
        //
    }

    /**
     * On before back wizard step event.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function beforeBackWizardStep(Request $request)
    {
        return true;
    }

    /**
     * On wizard ended event.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array|null $data
     * @return void
     */
    protected function wizardEnded(Request $request, $data)
    {
        //
    }
}
