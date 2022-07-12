<?php

namespace Ycs77\LaravelWizard\Test\Stubs;

class UserSkipStepStub extends UserStepStub
{
    /**
     * Is it possible to skip this step.
     *
     * @var bool
     */
    protected $skip = true;
}
