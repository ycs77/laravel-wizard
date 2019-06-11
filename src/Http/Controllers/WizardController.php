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
     * @param  string|null  $step
     * @return \Illuminate\Http\Response
     */
    public function create($step = null)
    {
        $wizard = $this->wizard();
        $stepRepo = $this->wizard()->stepRepo();
        $step = $this->getWizardStep($step);
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
            $step->saveData($request, $step->getRequestData($request));
        }

        if (!$this->getNextStepSlug()) {
            // Wizard done...
            if (config('wizard.cache')) {
                $this->save($request);
            }

            return $this->doneRedirectTo()
                ?: redirect($this->getActionUrl('done'));
        }

        return $this->redirectTo()
            ?: redirect($this->getActionUrl('create', [$this->getNextStepSlug()]));
    }

    /**
     * Show the done page.
     *
     * @return \Illuminate\Http\Response
     */
    public function done()
    {
        return view('wizard::done');
    }

    /**
     * Step redirect response.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectTo()
    {
        //
    }

    /**
     * Done redirect response.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function doneRedirectTo()
    {
        //
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
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function save(Request $request)
    {
        /** @var \Ycs77\LaravelWizard\Step $step */
        foreach ($this->wizard()->all() as $step) {
            $step->saveData($request, $step->data());
        }

        $this->wizard()->cache()->clear();
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
