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
        $controllerFullClass = $this->laravel['config']['wizard.namespace.controllers'].'\\'.$this->getControllerName();
        $nameAry = ['wizard', Str::snake(class_basename($this->getNameInput()))];

        $routesText = file_get_contents(base_path('routes/web.php'));

        if (! Str::contains($routesText, $controllerFullClass)) {
            $routesText = str_replace(
                "<?php\n",
                "<?php\n\nuse $controllerFullClass;",
                $routesText
            );
        }

        if (! Str::contains($routesText, 'use Ycs77\LaravelWizard\Facades\Wizard;')) {
            $routesText = str_replace(
                "use Illuminate\Support\Facades\Route;",
                'use Illuminate\Support\Facades\Route;'."\n".'use Ycs77\LaravelWizard\Facades\Wizard;',
                $routesText
            );
        }

        $routeText = file_get_contents(__DIR__.'/stubs/routes.stub');
        $routeText = str_replace('DummyUri', implode('/', $nameAry), $routeText);
        $routeText = str_replace('DummyController', $this->getControllerName().'::class', $routeText);
        $routeText = str_replace('DummyName', implode('.', $nameAry), $routeText);

        $routesText .= $routeText;

        file_put_contents(base_path('routes/web.php'), $routesText);
    }

    /**
     * Get the wizard controller name.
     *
     * @return string
     */
    public function getControllerName()
    {
        return $this->getNameInput().'WizardController';
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
