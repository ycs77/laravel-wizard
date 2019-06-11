<?php

namespace Ycs77\LaravelWizard\Test;

use Mockery;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Ycs77\LaravelWizard\Facades\Wizard as WizardFacade;
use Ycs77\LaravelWizard\WizardServiceProvider;

class TestCase extends OrchestraTestCase
{
    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->setUpTheTestEnvironment();

        $this->app['view']->addLocation(__DIR__ . '/../resources/stub_views');
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.key', 'base64:tqASP1YzC4hhdT1nMEc+DFGMRq6WQmfMzYFW522Ce8g=');

        $app['config']->set('wizard', [
            'cache' => true,
            'driver' => 'session',
        ]);
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            WizardServiceProvider::class,
        ];
    }

    /**
     * Get package aliases.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'Wizard' => WizardFacade::class,
        ];
    }

    /**
     * Mock an instance of an object in the container.
     *
     * @param  string  $abstract
     * @param  \Closure|null  $mock
     * @return object
     */
    protected function mock($abstract, $mock = null)
    {
        return $this->app->instance($abstract, Mockery::mock(...array_filter(func_get_args())));
    }
}
