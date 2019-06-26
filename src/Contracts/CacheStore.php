<?php

namespace Ycs77\LaravelWizard\Contracts;

interface CacheStore
{
    /**
     * Get the store step data.
     *
     * @param  string  $key
     * @return mixed
     */
    public function get(string $key = '');

    /**
     * Get the last processed step index.
     *
     * @return int|null
     */
    public function getLastProcessedIndex();

    /**
     * Set data to the store.
     *
     * @param  array  $data
     * @param  int|null  $lastIndex
     * @return void
     */
    public function set(array $data, $lastIndex = null);

    /**
     * Put data to the store.
     *
     * @param  string  $key
     * @param  array  $value
     * @param  int|null  $lastIndex
     * @return void
     */
    public function put(string $key, array $value, $lastIndex = null);

    /**
     * Checks if an a step data.
     *
     * @param  string  $key
     * @return bool
     */
    public function has(string $key);

    /**
     * Clear the store data.
     *
     * @return void
     */
    public function clear();
}
