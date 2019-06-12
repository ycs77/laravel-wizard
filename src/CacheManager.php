<?php

namespace Ycs77\LaravelWizard;

use Illuminate\Support\Manager;

class CacheManager extends Manager
{
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
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Get the default wizard cache driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app['config']['wizard.driver'];
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
        $table = $this->app['config']['wizard.table'];

        return new DatabaseStore($this->getDatabaseConnection(), $table, $this->app);
    }

    /**
     * Set the wizard key.
     *
     * @param  string  $wizardName
     * @return self
     */
    public function setWizardName(string $wizardName)
    {
        $this->wizardName = $wizardName;
        return $this;
    }

    /**
     * Get session key.
     *
     * @return string
     */
    protected function getSessionKey()
    {
        return 'laravel_wizard.' . $this->wizardName;
    }

    /**
     * Get the database connection for the database driver.
     *
     * @return \Illuminate\Database\Connection
     */
    protected function getDatabaseConnection()
    {
        return $this->app['db']->connection(
            $this->app['config']['wizard.connection']
        );
    }
}
