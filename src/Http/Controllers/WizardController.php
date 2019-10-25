<?php

namespace Ycs77\LaravelWizard\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Ycs77\LaravelWizard\Closure;
use Ycs77\LaravelWizard\Exceptions\StepNotFoundException;
use Ycs77\LaravelWizard\Http\Controllers\Traits\WizardControllerEvents;
use Ycs77\LaravelWizard\Step;
use Ycs77\LaravelWizard\WizardFactory;

class WizardController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, WizardControllerEvents;

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
    protected $wizardName = '';

    /**
     * The wizard title.
     *
     * @var string
     */
    protected $wizardTitle = '';

    /**
     * The wizard options.
     *
     * Available options reference from Ycs77\LaravelWizard\Wizard::$optionsKeys.
     *
     * @var array
     */
    protected $wizardOptions = [];

    /**
     * The wizard steps instance.
     *
     * @var array
     */
    protected $steps = [];

    /**
     * The data with to the wizard form view.
     *
     * @var array
     */
    protected $withViewData = [];

    /**
     * The wizard done show texts.
     *
     * @var string
     */
    protected $doneText;

    /**
     * Create new wizard controller.
     *
     * @param  \Ycs77\LaravelWizard\WizardFactory  $factory
     * @return void
     */
    public function __construct(WizardFactory $factory)
    {
        $this->wizard = $factory->make($this->wizardName, $this->steps, $this->wizardOptions);
    }

    /**
     * Show the wizard form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string|null  $step
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function create(Request $request, $step = null)
    {
        // Before wizard step create event.
        if ($redirectTo = $this->beforeWizardStepCreate($request)) {
            return $redirectTo;
        }

        $lastProcessedIndex = $this->getLastProcessedStepIndex($request, $step);

        // If step is null, redirect to last processed index.
        if (is_null($step)) {
            return $this->redirectToLastProcessedStep(
                $request,
                $lastProcessedIndex
            );
        }

        $step = $this->getWizardStep($request, $step);

        // Check this step is not last processed step.
        if ($step->index() !== $lastProcessedIndex) {
            // Redirect to last processed step.
            return $this->redirectToLastProcessedStep(
                $request,
                $lastProcessedIndex
            );
        }

        $this->pushWithViewData([
            'wizard' => $this->wizard(),
            'wizardTitle' => $this->wizardTitle,
            'step' => $step,
            'stepRepo' => $this->wizard()->stepRepo(),
            'formAction' => $this->getActionMethod('create'),
            'postAction' => $this->getActionMethod('store'),
            'getViewPath' => Closure::fromCallable([$this, 'getViewPath']),
        ]);

        // Wizard step created event.
        if ($redirectTo = $this->wizardStepCreated($request, $step)) {
            return $redirectTo;
        }

        return view($this->getViewPath('base'), $this->withViewData);
    }

    /**
     * Store wizard form data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $step
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, string $step)
    {
        // Before wizard step save event.
        if ($redirectTo = $this->beforeWizardStepSave($request)) {
            return $redirectTo;
        }

        $step = $this->getWizardStep($request, $step);

        if ($this->canValidate($request)) {
            $this->validate($request, $step->rules($request));
        }

        // Wizard step validated event.
        $this->wizardStepFormValidated($request);

        if ($this->wizard()->option('cache')) {
            $step->cacheProgress($request);
        } else {
            $step->saveData($request, $step->getRequestData($request), $step->getModel());
        }

        // Wizard step saved event.
        if ($redirectTo = $this->wizardStepSaved($request, $step)) {
            return $redirectTo;
        }

        // If trigger from 'back',
        // Set this step index and redirect to prev step.
        if ($request->query('_trigger') === 'back' && $this->beforeBackWizardStep($request)) {
            $prevStep = $this->wizard()->stepRepo()->prev();

            return $this->setThisStepAndRedirectTo($request, $prevStep);
        }

        if (!$this->getNextStepSlug()) {
            $data = null;

            // Wizard done...
            if ($this->wizard()->option('cache')) {
                $data = $this->save($request);
            }

            // Wizard ended event.
            $this->wizardEnded($request, $data);

            return $this->doneRedirectTo($data);
        }

        return $this->redirectTo();
    }

    /**
     * Show the done page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function done(Request $request)
    {
        $stepRepo = $this->wizard()->stepRepo();
        $doneText = $this->doneText;

        return view($this->getViewPath('done'), compact('stepRepo', 'doneText'));
    }

    /**
     * Set this step and redirect to this step.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Ycs77\LaravelWizard\Step  $step
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function setThisStepAndRedirectTo(Request $request, Step $step)
    {
        if ($this->wizard()->option('cache')) {
            $this->wizard()->cacheStepData(
                $this->wizard()->cache()->get(),
                $step->index()
            );
        }

        return redirect()->route(
            $request->route()->getName(),
            [$step->slug()]
        );
    }

    /**
     * Redirect to last processed step.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $lastProcessedIndex
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectToLastProcessedStep(Request $request, int $lastProcessedIndex)
    {
        $lastProcessedStep = $this->wizard()->stepRepo()->get($lastProcessedIndex);

        return redirect()->route(
            $request->route()->getName(),
            [$lastProcessedStep->slug()]
        );
    }

    /**
     * Return whether to validate.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function canValidate(Request $request)
    {
        return $request->query('_trigger') !== 'back';
    }

    /**
     * Step redirect response.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectTo()
    {
        return redirect($this->getActionUrl('create', [$this->getNextStepSlug()]));
    }

    /**
     * Done redirect response.
     *
     * @param  array|null  $withData
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function doneRedirectTo($withData = null)
    {
        return redirect($this->getActionUrl('done'));
    }

    /**
     * Get action class method name.
     *
     * @param  string  $method
     * @return string
     */
    protected function getActionMethod(string $method)
    {
        $className = static::class;
        $stepNamespace = config('wizard.namespace.controllers');
        $rootNamespace = trim(str_replace('/', '\\', $stepNamespace), '\\');

        if (Str::startsWith($className, $rootNamespace)) {
            $className = trim(str_replace($rootNamespace, '', $className), '\\');
        } else {
            $className = '\\' . trim($className, '\\');
        }

        return "$className@$method";
    }

    /**
     * Get action URL.
     *
     * @param  string  $method
     * @return string
     */
    protected function getActionUrl(string $method, $parameters = [])
    {
        return action($this->getActionMethod($method), $parameters);
    }

    /**
     * Get the last processed step index.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string|null  $stepSlug
     * @return int
     */
    protected function getLastProcessedStepIndex(Request $request, string $stepSlug = null)
    {
        if ($this->wizard()->option('cache')) {
            return $this->wizard()->cache()->getLastProcessedIndex() ?? 0;
        } elseif (is_string($stepSlug)) {
            return $this->wizard()->stepRepo()->findKey($stepSlug, 0);
        }

        return 0;
    }

    /**
     * Get wizard step.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string|null  $slug
     * @return \Ycs77\LaravelWizard\Step
     *
     * @throws \Ycs77\LaravelWizard\Exceptions\StepNotFoundException
     */
    protected function getWizardStep(Request $request, string $slug = null)
    {
        /** @var \Ycs77\LaravelWizard\Step|null $step */
        if (isset($slug)) {
            $step = $this->wizard()->stepRepo()->find($slug);
        } else {
            $lastProcessedStepIndex = $this->getLastProcessedStepIndex($request);
            $step = $this->wizard()->stepRepo()->get($lastProcessedStepIndex);
        }

        if (is_null($step)) {
            throw new StepNotFoundException($this->wizardTitle, $slug);
        }

        $this->wizard()->stepRepo()->setCurrentIndex($step->index());

        $step->setModel($request);

        return $step;
    }

    /**
     * Get the next step slug.
     *
     * @return string|null
     */
    protected function getNextStepSlug()
    {
        return $this->wizard()->stepRepo()->nextSlug();
    }

    /**
     * Push the data with to the wizard form view.
     *
     * Example:
     *
     * $this->pushWithViewData(compact(
     *     'data'
     * ));
     *
     * @param  array  $data
     * @return void
     */
    protected function pushWithViewData(array $data)
    {
        foreach ($data as $key => $value) {
            $this->withViewData[$key] = $value;
        }
    }

    /**
     * Get view path.
     *
     * @param  string  $view
     * @return string
     */
    public function getViewPath($view)
    {
        $viewPath = "wizards.{$this->wizardName}.$view";

        if (view()->exists($viewPath)) {
            return $viewPath;
        }

        return "wizard::$view";
    }

    /**
     * Save wizard data.
     *
     * Notice: If
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function save(Request $request)
    {
        /** @var \Ycs77\LaravelWizard\Step $step */
        foreach ($this->wizard()->stepRepo()->all() as $step) {
            $step->setModel($request);
            $step->saveData($request, $step->data(), $step->getModel());
        }

        $data = $this->wizard()->cache()->get();
        $this->wizard()->cache()->clear();

        return $data;
    }

    /**
     * Get the wizard instance.
     *
     * @return \Ycs77\LaravelWizard\Wizard
     */
    protected function wizard()
    {
        return $this->wizard;
    }
}
