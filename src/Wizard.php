<?php

namespace Ycs77\LaravelWizard;

use Closure;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use RuntimeException;
use Ycs77\LaravelWizard\Cache\CacheManager;

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
     * The action url resolver instance.
     *
     * @var \Closure|null
     */
    protected $actionUrlResolver;

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
     * Cache progress data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Ycs77\LaravelWizard\Step  $step
     * @param  array  $additionalData
     * @return array
     */
    public function cacheProgress(Request $request, Step $step, array $additionalData = [])
    {
        if (! $this->option('cache')) {
            return;
        }

        // Get cache data, and push this step data.
        $cacheData = $this->cache->get();
        $cacheData[$step->slug()] = $step->getRequestData($request);
        $cacheData = array_merge($cacheData, $additionalData);

        $nextStepIndex = $this->nextStepIndex();

        // Save data to cache.
        $this->cacheStepData($cacheData, $nextStepIndex);

        return $this->cache->get();
    }

    /**
     * Set the last processed step index.
     *
     * @return self
     */
    public function setLastProcessedIndex($stepIndex)
    {
        if (! $this->option('cache')) {
            return;
        }

        $this->cacheStepData($this->cache->get(), $stepIndex);

        return $this;
    }

    /**
     * Get the last processed step index.
     *
     * @return int|null
     */
    public function getLastProcessedIndex()
    {
        return $this->cache->getLastProcessedIndex();
    }

    /**
     * Get the action URL.
     *
     * @param  string  $method
     * @param  mixed  $parameters
     * @return string
     */
    public function getActionUrl(string $method, $parameters = [])
    {
        if (! $this->actionUrlResolver) {
            throw new RuntimeException('Action url resolver is not set.');
        }

        return call_user_func($this->actionUrlResolver, $method, $parameters);
    }

    /**
     * Redirect to the given step.
     *
     * @param  string|\Ycs77\LaravelWizard\Step|null  $step
     * @param  bool  $setLastIndex
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToStep($step = null, $setLastIndex = true)
    {
        if (is_null($step)) {
            $step = $this->stepRepo()->next();
        } elseif (is_string($step)) {
            $step = $this->stepRepo()->find($step);
        }

        if ($this->option('cache') && $setLastIndex) {
            $this->setLastProcessedIndex($step->index());
        }

        return redirect($this->getActionUrl('create', [$step->slug()]));
    }

    /**
     * Redirect to the done page.
     *
     * @param  string|null  $stepSlug
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToDone()
    {
        return redirect($this->getActionUrl('done'));
    }

    /**
     * Set the action url resolver.
     *
     * @param  \Closure  $resolver
     * @return self
     */
    public function resolveActionUrlUsing(Closure $resolver)
    {
        $this->actionUrlResolver = $resolver;

        return $this;
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
     * @return \Illuminate\Foundation\Application
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * Get the wizard name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the wizard title.
     *
     * @return string
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
