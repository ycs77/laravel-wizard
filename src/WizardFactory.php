<?php

namespace Ycs77\LaravelWizard;

use Illuminate\Foundation\Application;

class WizardFactory
{
    /**
     * The application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * Create a new Wizard instance.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Make the new wizard.
     *
     * @param  string  $name
     * @param  string  $title
     * @param  array|string  $steps
     * @param  array  $options
     * @return \Ycs77\LaravelWizard\Wizard
     */
    public function make(string $name, string $title, $steps, $options = [])
    {
        $wizard = new Wizard($this->app, $name, $title, $options);

        $wizard->setCache();
        $wizard->setStepRepo();

        $wizard->stepRepo()->push($steps);

        return $wizard;
    }
}
