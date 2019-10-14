<?php

namespace Ycs77\LaravelWizard;

use Illuminate\Foundation\Application;
use Illuminate\Support\Manager;

class CacheManager extends Manager
{
    /**
     * The wizard instance.
     *
     * @var \Ycs77\LaravelWizard\Wizard
     */
    protected $wizard;

    /**
     * The wizard name.
     *
     * @var string
     */
    protected $wizardName;

    /**
     * Create a new Wizard Cache manager instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @param  \Ycs77\LaravelWizard\Wizard  $wizard
     * @return void
     */
    public function __construct(Application $app, Wizard $wizard)
    {
        $this->app = $app;
        $this->wizard = $wizard;
    }

    /**
     * Get the default wizard cache driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->wizard->option('driver');
    }

    /**
     * Create an instance of the "session" wizard cache driver.
     *
     * @return \Ycs77\LaravelWizard\Contracts\CacheStore
     */
    protected function createSessionDriver()
    {
        return new SessionStore(
            $this->app['session.store'],
            $this->getSessionKey()
        );
    }

    /**
     * Create an instance of the "database" wizard cache driver.
     *
     * @return \Ycs77\LaravelWizard\Contracts\CacheStore
     */
    protected function createDatabaseDriver()
    {
        $table = $this->wizard->option('table');

        return new DatabaseStore($this->getDatabaseConnection(), $table, $this->app);
    }

    /**
     * Get session key.
     *
     * @return string
     */
    protected function getSessionKey()
    {
        return 'laravel_wizard.' . $this->wizard->getName();
    }

    /**
     * Get the database connection for the database driver.
     *
     * @return \Illuminate\Database\Connection
     */
    protected function getDatabaseConnection()
    {
        return $this->app['db']->connection(
            $this->wizard->option('connection')
        );
    }
}
