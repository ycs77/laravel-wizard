<?php

namespace Ycs77\LaravelWizard\Test\Stubs;

use Ycs77\LaravelWizard\Http\Controllers\WizardController;

class WizardControllerOptionsStub extends WizardController
{
    /**
     * The wizard name.
     *
     * @var string
     */
    protected $wizardName = 'test';

    /**
     * The wizard options.
     *
     * Options reference in Ycs77\LaravelWizard\Wizard::$optionsKeys.
     *
     * @var array
     */
    protected $wizardOptions = [
        'cache' => false,
    ];

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
