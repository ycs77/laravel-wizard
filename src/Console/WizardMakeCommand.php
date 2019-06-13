<?php

namespace Ycs77\LaravelWizard\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;

class WizardMakeCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:wizard';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new wizard controller and steps classes';

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        $this->call('make:wizard:controller', [
            'name' => $this->getControllerName(),
            '--steps' => $this->argument('steps'),
            '--force' => true,
        ]);

        if ($this->laravel['config']['wizard.append_route']) {
            $this->appendRoute();
        }
    }

    /**
     * Append wizard route to "routes/web.php".
     *
     * @return void
     */
    public function appendRoute()
    {
        $nameAry = ['wizard', Str::snake(class_basename($this->getNameInput()))];

        $routeText = file_get_contents(__DIR__ . '/stubs/routes.stub');
        $routeText = str_replace('DummyUri', implode('/', $nameAry), $routeText);
        $routeText = str_replace('DummyController', $this->getControllerName(), $routeText);
        $routeText = str_replace('DummyName', implode('.', $nameAry), $routeText);

        file_put_contents(
            base_path('routes/web.php'),
            $routeText,
            FILE_APPEND
        );
    }

    /**
     * Get the wizard controller name.
     *
     * @return string
     */
    public function getControllerName()
    {
        return $this->getNameInput() . 'WizardController';
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        return trim(str_replace('/', '\\', $this->argument('name')));
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the class'],
            ['steps', InputArgument::REQUIRED, 'The given wizard steps name.'],
        ];
    }
}
