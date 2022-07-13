<?php

namespace Ycs77\LaravelWizard\Test\Stubs;

class WizardControllerUploadFileStub extends WizardControllerStub
{
    /**
     * The wizard steps instance.
     *
     * @var array
     */
    protected $steps = [
        AvatarStepStub::class,
        SaveAvatarStepStub::class,
    ];
}
