<?php

namespace Ycs77\LaravelWizard\Test\Stubs;

use Illuminate\Routing\Controller;
use Ycs77\LaravelWizard\Wizardable;

class WizardControllerStub extends Controller
{
    use Wizardable;

    /**
     * The wizard name.
     *
     * @var string
     */
    protected $wizardName = 'test';

    /**
     * The wizard title.
     *
     * @var string
     */
    protected $wizardTitle = 'Test';

    /**
     * The wizard steps instance.
     *
     * @var array
     */
    protected $steps = [
        UserStepStub::class,
        PostStepStub::class,
    ];
}
