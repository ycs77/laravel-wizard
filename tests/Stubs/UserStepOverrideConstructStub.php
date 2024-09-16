<?php

namespace Ycs77\LaravelWizard\Test\Stubs;

use Illuminate\Contracts\Session\Session;
use Ycs77\LaravelWizard\Wizard;

class UserStepOverrideConstructStub extends UserStepStub
{
    protected Session $session;

    public function __construct(Wizard $wizard, int $index, Session $session)
    {
        parent::__construct($wizard, $index);

        $this->session = $session;
    }

    public function getSessionFromConstruct(): Session
    {
        return $this->session;
    }
}
