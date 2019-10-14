<?php

namespace Ycs77\LaravelWizard;

use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Arr;
use Ycs77\LaravelWizard\Contracts\CacheStore;

class SessionStore implements CacheStore
{
    /**
     * The session instance.
     *
     * @var \Illuminate\Contracts\Session\Session
     */
    protected $session;

    /**
     * The wizard key.
     *
     * @var string
     */
    protected $wizardKey;

    /**
     * Create a new wizard cache session store instance.
     *
     * @param  \Illuminate\Contracts\Session\Session  $session
     * @param  string  $key
     * @return void
     */
    public function __construct(Session $session, string $wizardKey)
    {
        $this->session = $session;
        $this->wizardKey = $wizardKey;
    }

    /**
     * Get the store step data.
     *
     * @param  string  $key
     * @return mixed
     */
    public function get(string $key = '')
    {
        $data = $this->session->get($this->wizardKey, []);

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
            $data['_last_index'] = (int)$lastIndex;
        }

        $this->session->put($this->wizardKey, $data);
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
        $this->session->forget($this->wizardKey);
    }
}
