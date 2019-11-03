<?php

namespace Ycs77\LaravelWizard;

use Illuminate\Foundation\Application;
use Illuminate\Support\Arr;

class Wizard
{
    /**
     * The application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * The wizard cache manager instance.
     *
     * @var \Ycs77\LaravelWizard\Contracts\CacheStore
     */
    protected $cache;

    /**
     * The step repository instance.
     *
     * @var \Ycs77\LaravelWizard\StepRepository
     */
    protected $stepRepo;

    /**
     * The wizard name.
     *
     * @var string
     */
    protected $name;

    /**
     * The wizard title.
     *
     * @var string
     */
    protected $title;

    /**
     * The wizard options.
     *
     * @var array
     */
    protected $options = [];

    /**
     * The wizard options extract key from config.
     *
     * @var array
     */
    protected $optionsKeys = [
        'cache',
        'driver',
        'connection',
        'table',
    ];

    /**
     * Create a new Wizard instance.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @param  string  $name
     * @param  string  $title
     * @param  array  $options
     * @return void
     */
    public function __construct(Application $app, string $name, string $title, $options = [])
    {
        $this->app = $app;
        $this->name = $name;
        $this->title = $title;

        $this->setOptions($options);
    }

    /**
     * Cache step data to store.
     *
     * @param  array  $data
     * @param  int|null  $nextStepIndex
     * @return void
     */
    public function cacheStepData(array $data, $nextStepIndex = null)
    {
        $this->cache->set($data, $nextStepIndex);
    }

    /**
     * Get the next step index.
     *
     * @return int|null
     */
    public function nextStepIndex()
    {
        $nextStepIndex = null;

        if ($nextStep = $this->stepRepo->next()) {
            $nextStepIndex = $nextStep->index();
        }

        return $nextStepIndex;
    }

    /**
     * Get the wizard cache instance.
     *
     * @return \Ycs77\LaravelWizard\Contracts\CacheStore
     */
    public function cache()
    {
        return $this->cache;
    }

    /**
     * Set the wizard cache instance.
     *
     * @param  \Ycs77\LaravelWizard\Contracts\CacheStore|null  $cache
     * @return self
     */
    public function setCache($cache = null)
    {
        $this->cache = $cache ?? (new CacheManager($this->app, $this))->driver();

        return $this;
    }

    /**
     * Get the step repository instance.
     *
     * @return \Ycs77\LaravelWizard\StepRepository
     */
    public function stepRepo()
    {
        return $this->stepRepo;
    }

    /**
     * Set the step repository instance.
     *
     * @param  \Ycs77\LaravelWizard\StepRepository|null  $stepRepo
     * @return self
     */
    public function setStepRepo($stepRepo = null)
    {
        $this->stepRepo = $stepRepo ?? new StepRepository($this);

        return $this;
    }

    /**
     * Get the wizard options.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Get the wizard option.
     *
     * @param  string  $key
     * @return mixed
     */
    public function option(string $key)
    {
        return $this->options[$key] ?? null;
    }

    /**
     * Get the wizard options.
     *
     * @param  array  $options
     * @return self
     */
    public function setOptions(array $options = [])
    {
        $config = Arr::only(
            $this->app['config']['wizard'],
            $this->optionsKeys
        );

        $this->options = array_merge($config, $options);

        return $this;
    }

    /**
     * Get the application instance.
     *
     * @return  \Illuminate\Foundation\Application
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * Get the wizard name.
     *
     * @return  string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the wizard title.
     *
     * @return  string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Handle dynamic method calls into the wizard.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->stepRepo->$method(...$parameters);
    }
}
