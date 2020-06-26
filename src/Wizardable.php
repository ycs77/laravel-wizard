<?php

namespace Ycs77\LaravelWizard;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Ycs77\LaravelWizard\Exceptions\StepNotFoundException;

trait Wizardable
{
    /**
     * The wizard instance.
     *
     * @var \Ycs77\LaravelWizard\Wizard
     */
    protected $wizard;

    /**
     * The data with to the wizard form view.
     *
     * @var array
     */
    protected $withViewData = [];

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
            'wizardTitle' => $this->wizardTitle(),
            'step' => $step,
            'stepRepo' => $this->wizard()->stepRepo(),
            'formAction' => 'create',
            'postAction' => 'store',
            'getViewPath' => Closure::fromCallable([$this, 'getViewPath']),
            'getActionUrl' => Closure::fromCallable([$this, 'getActionUrl']),
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

        // Form validation.
        if ($this->canValidate($request)) {
            $this->validate(
                $request,
                $step->rules($request),
                $step->validateMessages($request),
                $step->validateAttributes($request)
            );
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

        if ($this->isLastStep()) {
            $data = null;

            // If cache is open, then now can save cache data.
            if ($this->wizard()->option('cache')) {
                $data = $this->save($request);
            }

            // Wizard ended event.
            $this->wizardEnded($request, $data);

            return $this->redirectToDone($data);
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
        $doneText = $this->doneText();

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

        return $this->redirectTo($step->slug());
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

        return $this->redirectTo($lastProcessedStep->slug());
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
     * @param  string|null $step
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectTo($step = null)
    {
        if (is_null($step)) {
            $step = $this->getNextStepSlug();
        }

        return redirect($this->getActionUrl('create', [$step]));
    }

    /**
     * Redirect to done page.
     *
     * @param  array|null  $withData
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectToDone($withData = null)
    {
        return redirect($this->getActionUrl('done'));
    }

    /**
     * Redirect to done page.
     *
     * @param  array|null  $withData
     * @return \Illuminate\Http\RedirectResponse
     *
     * @deprecated Please use the "redirectToDone" method
     */
    protected function doneRedirectTo($withData = null)
    {
        return $this->redirectToDone($withData);
    }

    /**
     * Get the action class method name.
     *
     * @param  string  $method
     * @return string
     */
    public function getActionMethod(string $method)
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
     * Get the action URL.
     *
     * @param  string  $method
     * @return string
     */
    public function getActionUrl(string $method, $parameters = [])
    {
        // If the method string does not match @, it must be converted
        // to the action method name.
        if (preg_match('/^[^@]+$/', $method)) {
            $method = $this->getActionMethod($method);
        }

        return action($method, $parameters);
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
            throw new StepNotFoundException($slug, $this->wizard, static::class);
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
     * Check if the step is last.
     *
     * @return bool
     */
    protected function isLastStep()
    {
        return is_null($this->getNextStepSlug());
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
        $viewPath = config('wizard.wizard_view_path') . ".{$this->wizardName()}.$view";

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
        if (!$this->wizard) {
            /** @var \Ycs77\LaravelWizard\WizardFactory $factory */
            $factory = app(WizardFactory::class);

            $this->wizard = $factory->make(
                $this->wizardName(),
                $this->wizardTitle(),
                $this->steps(),
                $this->wizardOptions()
            );
        }

        return $this->wizard;
    }

    /**
     * Get the wizard name.
     *
     * @return string
     */
    protected function wizardName()
    {
        return $this->wizardName;
    }

    /**
     * Get the wizard title.
     *
     * @return string
     */
    protected function wizardTitle()
    {
        return $this->wizardTitle;
    }

    /**
     * Get the wizard options.
     *
     * Available options reference from Ycs77\LaravelWizard\Wizard::$optionsKeys.
     *
     * @return array
     */
    protected function wizardOptions()
    {
        return property_exists($this, 'wizardOptions') ? $this->wizardOptions : [];
    }

    /**
     * Get the wizard done text.
     *
     * @return string
     */
    protected function doneText()
    {
        return property_exists($this, 'doneText') ? $this->doneText : 'Done';
    }

    /**
     * Get the wizard steps.
     *
     * @return array
     */
    protected function steps()
    {
        return $this->steps;
    }

    /**
     * On before wizard step create event.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|null
     */
    protected function beforeWizardStepCreate(Request $request)
    {
        //
    }

    /**
     * On wizard step created event.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Ycs77\LaravelWizard\Step  $step
     * @return \Illuminate\Http\RedirectResponse|null
     */
    protected function wizardStepCreated(Request $request, Step $step)
    {
        //
    }

    /**
     * On before wizard step save event.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|null
     */
    protected function beforeWizardStepSave(Request $request)
    {
        //
    }

    /**
     * On wizard step validated event.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function wizardStepFormValidated(Request $request)
    {
        //
    }

    /**
     * On wizard step saved event.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Ycs77\LaravelWizard\Step  $step
     * @return \Illuminate\Http\RedirectResponse|null
     */
    protected function wizardStepSaved(Request $request, Step $step)
    {
        //
    }

    /**
     * On before back wizard step event.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function beforeBackWizardStep(Request $request)
    {
        return true;
    }

    /**
     * On wizard ended event.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array|null  $data
     * @return void
     */
    protected function wizardEnded(Request $request, $data)
    {
        //
    }
}
