<?php

namespace Ycs77\LaravelWizard\Test\Stubs;

class WizardControllerOptionsStub extends WizardControllerStub
{
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
}
