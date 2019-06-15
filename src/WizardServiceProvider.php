<?php

namespace Ycs77\LaravelWizard;

use Illuminate\Support\ServiceProvider;
use Ycs77\LaravelWizard\Console\StepMakeCommand;
use Ycs77\LaravelWizard\Console\TableCommand;
use Ycs77\LaravelWizard\Console\WizardControllerMakeCommand;
use Ycs77\LaravelWizard\Console\WizardMakeCommand;

class WizardServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('wizard', function ($app) {
            return new Wizard($app);
        });

        $this->app->singleton('wizard.cache', function ($app) {
            return new CacheManager($app['wizard'], $app);
        });

        $this->app->singleton('wizard.cache.store', function ($app) {
            return $app['wizard.cache']->driver();
        });

        $this->app->alias('wizard', Wizard::class);
        $this->app->alias('wizard.cache', CacheManager::class);

        $this->mergeConfigFrom(__DIR__ . '/../config/wizard.php', 'wizard');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->commands(WizardMakeCommand::class);
        $this->commands(WizardControllerMakeCommand::class);
        $this->commands(StepMakeCommand::class);
        $this->commands(TableCommand::class);

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'wizard');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'wizard');

        $this->publishes([
            __DIR__ . '/../config/wizard.php' => config_path('wizard.php'),
        ], 'wizard-config');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/wizard'),
            __DIR__ . '/../resources/lang' => resource_path('lang/vendor/wizard'),
        ], 'wizard-resources');
    }
}
