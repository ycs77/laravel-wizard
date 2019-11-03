<?php

namespace Ycs77\LaravelWizard\Exceptions;

use Ycs77\LaravelWizard\Wizard;

class StepNotFoundException extends InternalException
{
    /**
     * @var string
     */
    protected $stepSlug;

    /**
     * @var \Ycs77\LaravelWizard\Wizard
     */
    protected $wizard;

    /**
     * @var string
     */
    protected $controllerClass;

    public function __construct($stepSlug, Wizard $wizard, string $controllerClass)
    {
        $this->stepSlug = $stepSlug;
        $this->wizard = $wizard;
        $this->controllerClass = $controllerClass;

        parent::__construct("Step [$stepSlug] is not found to {$this->wizard->getTitle()} wizard.", 404);
    }
}
