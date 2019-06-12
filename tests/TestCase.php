<?php

namespace Ycs77\LaravelWizard\Test;

use Mockery;
use Orchestra\Database\ConsoleServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Ycs77\LaravelWizard\Facades\Wizard as WizardFacade;
use Ycs77\LaravelWizard\Test\App\User;
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

        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // App
        $app['config']->set('app.key', 'base64:tqASP1YzC4hhdT1nMEc+DFGMRq6WQmfMzYFW522Ce8g=');

        // Database
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.database', ':memory:');

        // Wizard
        $app['config']->set('wizard', require __DIR__ . '/../config/wizard.php');
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
            ConsoleServiceProvider::class,
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

    /**
     * Create and authenticate user.
     *
     * @return void
     */
    protected function authenticate()
    {
        $user = User::create([
            'name' => 'Name',
            'email' => 'example@email.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);
    }
}
