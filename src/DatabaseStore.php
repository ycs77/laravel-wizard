<?php

namespace Ycs77\LaravelWizard;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Arr;
use Ycs77\LaravelWizard\Contracts\CacheStore;

class DatabaseStore implements CacheStore
{
    /**
     * The database connection instance.
     *
     * @var \Illuminate\Database\ConnectionInterface
     */
    protected $connection;

    /**
     * The name of the wizard table.
     *
     * @var string
     */
    protected $table;

    /**
     * The container instance.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * Create a new wizard cache database store instance.
     *
     * @param  \Illuminate\Database\ConnectionInterface  $connection
     * @param  string  $table
     * @param  \Illuminate\Contracts\Container\Container|null  $container
     * @return void
     */
    public function __construct(ConnectionInterface $connection, $table, Container $container = null)
    {
        $this->connection = $connection;
        $this->table = $table;
        $this->container = $container;
    }

    /**
     * Get the store step data.
     *
     * @param  string  $key
     * @return mixed
     */
    public function get(string $key = '')
    {
        $data = $this->getSelectedQuery()->first();

        if (!$data = (array)$this->getSelectedQuery()->first()) {
            return;
        }

        $data = json_decode($data['payload'], true);

        return $key ? Arr::get($data, $key) : $data;
    }

    /**
     * Get the last processed step index.
     *
     * @return int|null
     */
    public function getLastProcessedIndex()
    {
        return $this->get('_last_index');
    }

    /**
     * Set data to the store.
     *
     * @param  array  $data
     * @param  int|null  $lastIndex
     * @return void
     */
    public function set(array $data, $lastIndex = null)
    {
        if (isset($lastIndex) && is_numeric($lastIndex)) {
            $data['_last_index'] = $lastIndex;
        }

        $data = ['payload' => json_encode($data)];

        if ($this->userId()) {
            $this->getQuery()->updateOrInsert(['user_id' => $this->userId()], $data);
        } else {
            $this->getQuery()->updateOrInsert(['ip_address' => $this->ipAddress()], $data);
        }
    }

    /**
     * Put data to the store.
     *
     * @param  string  $key
     * @param  array  $value
     * @param  int|null  $lastIndex
     * @return void
     */
    public function put(string $key, array $value, $lastIndex = null)
    {
        $data = $this->get($key);
        Arr::set($data, $key, $value);
        $this->set($data, $lastIndex);
    }

    /**
     * Checks if an a step data.
     *
     * @param  string  $key
     * @return bool
     */
    public function has(string $key)
    {
        $data = $this->get($key);

        return isset($data);
    }

    /**
     * Clear the store data.
     *
     * @return void
     */
    public function clear()
    {
        $this->getSelectedQuery()->delete();
    }

    /**
     * Get the currently authenticated user's ID.
     *
     * @return mixed
     */
    protected function userId()
    {
        return $this->container->make(Guard::class)->id();
    }

    /**
     * Get the IP address for the current request.
     *
     * @return string
     */
    protected function ipAddress()
    {
        return $this->container->make('request')->ip();
    }

    /**
     * Get a fresh query builder instance for the table.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function getQuery()
    {
        return $this->connection->table($this->table);
    }

    /**
     * Get a selected user or ip address query builder instance.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function getSelectedQuery()
    {
        if ($this->userId()) {
            return $this->getQuery()->where('user_id', $this->userId());
        }

        return $this->getQuery()->where('ip_address', $this->ipAddress());
    }
}
