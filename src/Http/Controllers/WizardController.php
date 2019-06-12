<?php

namespace Ycs77\LaravelWizard\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Ycs77\LaravelWizard\Exceptions\StepNotFoundException;
use Ycs77\LaravelWizard\Wizard;

class WizardController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

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
     * The wizard steps instance.
     *
     * @var array
     */
    protected $steps = [];

    /**
     * The wizard done show texts.
     *
     * @var string
     */
    protected $doneText;

    /**
     * Create new wizard controller.
     *
     * @param  \Ycs77\LaravelWizard\Wizard  $wizard
     * @return void
     */
    public function __construct(Wizard $wizard)
    {
        $this->wizard = $wizard->make($this->wizardName, $this->steps);
    }

    /**
     * Show the wizard form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string|null  $step
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request, $step = null)
    {
        $step = $this->getWizardStep($step);
        $lastProcessedIndex = $this->wizard()->getLastProcessedStepIndex();

        // Check this step is not last processed step.
        if ($step->index() !== $lastProcessedIndex) {

            // If trigger from 'back',
            // Set this step index and redirect to this step.
            if ($request->query('trigger') === 'back') {
                $cacheData = $this->wizard()->cache()->get();
                $this->wizard()->cacheStepData($cacheData, $step->index());
                return redirect()->route($request->route()->getName(), [$step->slug()]);
            }

            // Redirect to last processed step.
            $lastProcessedStep = $this->wizard()->stepRepo()->get($lastProcessedIndex);
            return redirect()->route(
                $request->route()->getName(),
                [$lastProcessedStep->slug()]
            );
        }

        $wizard = $this->wizard();
        $stepRepo = $this->wizard()->stepRepo();
        $formAction = $this->getActionMethod('create');
        $postAction = $this->getActionMethod('store');

        return view('wizard::base', compact('wizard', 'stepRepo', 'step', 'formAction', 'postAction'));
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
        $step = $this->getWizardStep($step);

        $this->validate($request, $step->rules($request));

        if (config('wizard.cache')) {
            $step->cacheProgress($request);
        } else {
            $step->saveData($step->getRequestData($request));
        }

        if (!$this->getNextStepSlug()) {
            // Wizard done...
            $data = config('wizard.cache') ? $this->save($request) : null;

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
        $wizardData = $request->session()->get('wizard_data');
        $doneText = $this->doneText;

        return view('wizard::done', compact('wizardData', 'doneText'));
    }

    /**
     * Step redirect response.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectTo()
    {
        return redirect($this->getActionUrl('create', [$this->getNextStepSlug()]));
    }

    /**
     * Done redirect response.
     *
     * @param  array|null  $withData
     * @return \Illuminate\Http\RedirectResponse
     */
    public function doneRedirectTo($withData = null)
    {
        return redirect($this->getActionUrl('done'))->with('wizard_data', $withData);
    }

    /**
     * Get action class method name.
     *
     * @param  string  $method
     * @return string
     */
    protected function getActionMethod(string $method)
    {
        $className = $this->getControllerClass();
        return "$className@$method";
    }

    /**
     * Get controller main class name.
     *
     * @return string
     */
    public function getControllerClass()
    {
        return Str::replaceFirst(
            'App\\Http\\Controller\\',
            '',
            class_basename(static::class)
        );
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
     * Get wizard step.
     *
     * @param  string|null $slug
     * @return \Ycs77\LaravelWizard\Step
     *
     * @throws \Ycs77\LaravelWizard\Exceptions\StepNotFoundException
     */
    protected function getWizardStep($slug)
    {
        try {
            return $this->wizard()->getStep($slug);
        } catch (StepNotFoundException $e) {
            abort(404);
        }
    }

    /**
     * Get the next step slug.
     *
     * @return string|null
     */
    public function getNextStepSlug()
    {
        return $this->wizard()->stepRepo()->nextSlug();
    }

    /**
     * Save wizard data.
     *
     * @return void
     */
    protected function save()
    {
        /** @var \Ycs77\LaravelWizard\Step $step */
        foreach ($this->wizard()->stepRepo()->all() as $step) {
            $step->saveData($step->data());
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
