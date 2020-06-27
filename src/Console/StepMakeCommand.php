<?php

namespace Ycs77\LaravelWizard\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class StepMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:wizard:step';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new wizard step class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Wizard step';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/step.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        $wizardName = $this->option('wizard')
            ? '\\' . Str::studly($this->option('wizard'))
            : '';

        return $this->laravel['config']['wizard.namespace.steps'] . $wizardName;
    }

    /**
     * Build the class with the given name.
     *
     * Remove the base controller import if we are already in base namespace.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $replace = $this->buildStepSlugReplacement();
        $replace = $this->buildLabelSlugReplacement($replace);
        $replace = $this->buildViewPathSlugReplacement($replace);

        return str_replace(
            array_keys($replace),
            array_values($replace),
            parent::buildClass($name)
        );
    }

    /**
     * Build the step slug for step class replacement value.
     *
     * @return array
     */
    public function buildStepSlugReplacement()
    {
        return [
            'DummySlug' => $this->option('slug') ?? $this->getStepName(),
        ];
    }

    /**
     * Build the step label for step class replacement value.
     *
     * @param  array  $replace
     * @return array
     */
    public function buildLabelSlugReplacement(array $replace)
    {
        return array_merge($replace, [
            'DummyLabel' => $this->option('label')
                ?? ucfirst(str_replace('_', ' ', $this->getStepName())),
        ]);
    }

    /**
     * Build the step view path for step class replacement value.
     *
     * @param  array  $replace
     * @return array
     */
    public function buildViewPathSlugReplacement(array $replace)
    {
        $viewPath = $this->option('view');

        if (is_null($viewPath)) {
            return array_merge($replace, [
                "\n    DummyViewProperty\n" => '',
            ]);
        }

        return array_merge($replace, [
            'DummyViewProperty' => <<<EOT
/**
     * The step form view path.
     *
     * @var string
     */
    protected \$view = '$viewPath';
EOT
        ]);
    }

    /**
     * Get the wizard step name.
     *
     * @return string
     */
    protected function getStepName()
    {
        $step = class_basename($this->getNameInput());

        if (Str::endsWith($step, 'Step')) {
            $step = str_replace('Step', '', $step);
        }

        return Str::snake($step);
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the wizard steps already exists.'],

            ['label', 'l', InputOption::VALUE_REQUIRED, 'Set a step label.'],

            ['slug', 's', InputOption::VALUE_REQUIRED, 'Set a step slug.'],

            ['view', 'i', InputOption::VALUE_REQUIRED, 'Set a step view path.'],

            ['wizard', 'w', InputOption::VALUE_REQUIRED, 'Set a wizard name that the step belongs to.'],
        ];
    }
}
