<?php

namespace Ycs77\LaravelWizard;

use Illuminate\Http\Request;

abstract class Step
{
    /**
     * The wizard instance.
     *
     * @var \Ycs77\LaravelWizard\Wizard
     */
    protected $wizard;

    /**
     * The step model instance or the relationships instance.
     *
     * @var \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\relation|null
     */
    protected $model;

    /**
     * The step index.
     *
     * @var int
     */
    protected $index;

    /**
     * The step slug.
     *
     * @var string
     */
    protected $slug;

    /**
     * The step show label text.
     *
     * @var string
     */
    protected $label;

    /**
     * The step form view path.
     *
     * @var string
     */
    protected $view;

    /**
     * The request input except input data.
     *
     * @var array
     */
    protected $inputExcept = ['_token', '_method'];

    /**
     * Create a new step instance.
     *
     * @param  \Ycs77\LaravelWizard\Wizard  $wizard
     * @param  int  $index
     * @return void
     */
    public function __construct(Wizard $wizard, int $index)
    {
        $this->wizard = $wizard;
        $this->index = $index;
    }

    /**
     * Make a new static step.
     *
     * @param  \Ycs77\LaravelWizard\Wizard  $wizard
     * @param  int $index
     * @return self
     */
    public static function make(Wizard $wizard, int $index)
    {
        return new static(...func_get_args());
    }

    /**
     * Get the step index.
     *
     * @return int
     */
    public function index()
    {
        return $this->index;
    }

    /**
     * Get the step number.
     *
     * @return int
     */
    public function number()
    {
        return $this->index + 1;
    }

    /**
     * Get the step slug.
     *
     * @return string
     */
    public function slug()
    {
        return $this->slug;
    }

    /**
     * Get the step show label text.
     *
     * @return string
     */
    public function label()
    {
        return $this->label;
    }

    /**
     * Get the step form view path.
     *
     * @return string
     */
    public function view()
    {
        return $this->view;
    }

    /**
     * Get the step model instance or the relationships instance.
     *
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\relation|null
     */
    public function model()
    {
        return $this->model;
    }

    /**
     * Set the step model instance or the relationships instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function setModel(Request $request)
    {
        //
    }

    /**
     * Save this step form data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array|null  $data
     * @param  \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\relation|null  $model
     * @return void
     */
    abstract public function saveData(Request $request, $data = null, $model = null);

    /**
     * Validation rules.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function rules(Request $request)
    {
        return [];
    }

    /**
     * Get request input data.
     *
     * @param  Request  $request
     * @return array
     */
    public function getRequestData(Request $request)
    {
        return $request->except($this->inputExcept);
    }

    /**
     * Get step cache data.
     *
     * @param  string  $key
     * @return array|string|null
     */
    public function data($key = '')
    {
        return $this->wizard->cache()->get($this->getDataKey($key));
    }

    /**
     * Get step data key.
     *
     * @param  string  $key
     * @return string
     */
    public function getDataKey($key = '')
    {
        return collect([$this->slug, $key])->filter()->implode('.');
    }

    /**
     * Cache progress data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $additionalData
     * @return array
     */
    public function cacheProgress(Request $request, array $additionalData = [])
    {
        // Get cache data, and push this step data.
        $cacheData = $this->wizard->cache()->get();
        $cacheData[$this->slug] = $this->getRequestData($request);
        $cacheData = array_merge($cacheData, $additionalData);

        $nextStepIndex = $this->wizard->nextStepIndex();

        // Save data to cache.
        $this->wizard->cacheStepData($cacheData, $nextStepIndex);

        return $this->wizard->cache()->get();
    }
}
