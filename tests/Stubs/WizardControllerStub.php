<?php

namespace Ycs77\LaravelWizard\Test\Stubs;

use Ycs77\LaravelWizard\Http\Controllers\WizardController;

class WizardControllerStub extends WizardController
{
    /**
     * The wizard name.
     *
     * @var string
     */
    protected $wizardName = 'test';

    /**
     * The wizard steps instance.
     *
     * @var array
     */
    protected $steps = [
        StepFirstStub::class,
        StepSecondStub::class,
    ];

    /**
     * Get controller main class name.
     *
     * @return string
     */
    public function getControllerClass()
    {
        return static::class;
    }
}
