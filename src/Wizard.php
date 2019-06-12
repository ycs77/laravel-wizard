<?php

namespace Ycs77\LaravelWizard;

use Illuminate\Foundation\Application;
use Ycs77\LaravelWizard\Exceptions\StepNotFoundException;

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
     * @var \Ycs77\LaravelWizard\Contracts\CacheStore|\Ycs77\LaravelWizard\CacheManager
     */
    protected $cache;

    /**
     * The step repository instance.
     *
     * @var \Ycs77\LaravelWizard\StepRepository
     */
    protected $stepRepo;

    /**
     * Create a new Wizard instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Make a new wizard.
     *
     * @param  string  $name
     * @param  mixed  $steps
     * @return self
     */
    public function make($name, $steps)
    {
        $this->setCache();
        $this->setStepRepo();

        $this->cache->setWizardName($name);
        $this->stepRepo->make($steps);

        return $this;
    }

    /**
     * Get first step or last processed step.
     *
     * @param  string|null $slug
     * @return \Ycs77\LaravelWizard\Step
     *
     * @throws \Ycs77\LaravelWizard\Exceptions\StepNotFoundException
     */
    public function getStep($slug = null)
    {
        $step = isset($slug)
            ? $this->stepRepo->find($slug)
            : $this->stepRepo->get($this->getLastProcessedStepIndex());

        if (is_null($step)) {
            throw new StepNotFoundException();
        }

        $this->stepRepo->setCurrentIndex($step->index());
        return $step;
    }

    /**
     * Get the last processed step index.
     *
     * @return int
     */
    public function getLastProcessedStepIndex()
    {
        if ($this->app['config']['wizard.cache']) {
            return $this->cache->getLastProcessedIndex() ?? 0;
        }

        return 0;
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
     * @return \Ycs77\LaravelWizard\Contracts\CacheStore|\Ycs77\LaravelWizard\CacheManager
     */
    public function cache()
    {
        return $this->cache;
    }

    /**
     * Set the wizard cache instance.
     *
     * @param  \Ycs77\LaravelWizard\Contracts\CacheStore|null
     * @return self
     */
    public function setCache($cache = null)
    {
        $this->cache = $cache ?? new CacheManager($this->app);
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
     * @param  \Ycs77\LaravelWizard\StepRepository|null
     * @return self
     */
    public function setStepRepo($stepRepo = null)
    {
        $this->stepRepo = $stepRepo ?? new StepRepository($this);
        return $this;
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
