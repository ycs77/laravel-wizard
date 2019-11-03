<?php

namespace Ycs77\LaravelWizard\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Symfony\Component\Console\Input\InputOption;

class WizardControllerMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:wizard:controller';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new wizard controller class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Wizard controller';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/controller.wizard.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $this->laravel['config']['wizard.namespace.controllers'];
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
        $controllerNamespace = $this->getNamespace($name);

        $replace = $this->buildWizardNameReplacement();

        if ($this->option('steps')) {
            $replace = $this->buildStepsReplacements($replace);
        } else {
            $replace["DummyFullStepsClasses\n"] = '';
            $replace['DummyStepsClasses'] = '';
        }

        $replace["use {$controllerNamespace}\Controller;\n"] = '';

        return str_replace(
            array_keys($replace),
            array_values($replace),
            parent::buildClass($name)
        );
    }

    /**
     * Build the wizard name for controller class replacement value.
     *
     * @param  array  $replace
     * @return array
     */
    public function buildWizardNameReplacement()
    {
        return [
            'DummyWizardName' => $this->getWizardName(),
            'DummyWizardTitle' => ucfirst(str_replace('_', ' ', $this->getWizardName())),
        ];
    }

    /**
     * Build the wizard name for controller class replacement value.
     *
     * @param  array  $replace
     * @return array
     */
    public function getWizardName()
    {
        $wizardName = $this->option('wizard');

        if (is_null($wizardName)) {
            $wizardName = $this->getNameInput();

            if (Str::endsWith($wizardName, 'WizardController')) {
                $wizardName = Str::replaceLast('WizardController', '', $wizardName);
            } elseif (Str::endsWith($wizardName, 'Controller')) {
                $wizardName = Str::replaceLast('Controller', '', $wizardName);
            }

            $wizardName = Str::snake(class_basename($wizardName));
        }

        return $wizardName;
    }

    /**
     * Build the step replacement values.
     *
     * @param  array  $replace
     * @return array
     */
    protected function buildStepsReplacements(array $replace)
    {
        $stepsClass = explode(',', $this->option('steps'));
        $fullStepsClasses = [];
        $stepsClasses = [];

        foreach ($stepsClass as $stepClass) {
            $stepClass = $this->parseStep($stepClass);

            if (!class_exists($stepClass)) {
                if ($this->option('force')) {
                    $this->createWizardStep($stepClass, true);
                } else {
                    if ($this->confirm("A {$stepClass} step does not exist. Do you want to generate it?", true)) {
                        $this->createWizardStep($stepClass);
                    }
                }
            }

            $fullStepsClasses[] = "use {$stepClass};";
            $stepsClasses[] = class_basename($stepClass) . '::class,';
        }

        sort($fullStepsClasses);

        $fullStepsClassesText = implode(PHP_EOL, $fullStepsClasses);
        $stepsClassesText = implode(PHP_EOL . '        ', $stepsClasses);
        $stepsClassesText = implode('', [
            PHP_EOL,
            '        ',
            $stepsClassesText,
            PHP_EOL,
            '    ',
        ]);

        return array_merge($replace, [
            'DummyFullStepsClasses' => $fullStepsClassesText,
            'DummyStepsClasses' => $stepsClassesText,
        ]);
    }

    /**
     * Create a step for the wizard.
     *
     * @param  string  $stepClass
     * @param  bool  $force
     * @return void
     */
    public function createWizardStep(string $stepClass, bool $force = false)
    {
        $this->call('make:wizard:step', [
            'name' => $stepClass,
            '--wizard' => $this->getWizardName(),
            '--force' => $force,
        ]);
    }

    /**
     * Get the fully-qualified step class name.
     *
     * @param  string  $step
     * @return string
     */
    protected function parseStep($step)
    {
        if (preg_match('([^A-Za-z0-9_/\\\\])', $step)) {
            throw new InvalidArgumentException('Wizard step name contains invalid characters.');
        }

        $step = trim(str_replace('/', '\\', $step), '\\');
        $stepNamespace = $this->laravel['config']['wizard.namespace.steps'];
        $stepNamespace .= '\\' . Str::studly($this->getWizardName());
        $rootNamespace = trim(str_replace('/', '\\', $stepNamespace), '\\');

        if (!Str::startsWith($step, $rootNamespace)) {
            $step = $rootNamespace . '\\' . $step;
        }

        return $step;
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the controller or wizard steps already exists.'],

            ['steps', 's', InputOption::VALUE_REQUIRED, 'The given wizard steps name.'],

            ['wizard', 'w', InputOption::VALUE_REQUIRED, 'Set a wizard name for the controller.'],
        ];
    }
}
