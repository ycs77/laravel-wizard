<?php

namespace Ycs77\LaravelWizard\Test\Feature;

use Ycs77\LaravelWizard\Test\TestCase;
use Ycs77\LaravelWizard\Facades\Wizard;

class HttpTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $controllerClass = '\Ycs77\LaravelWizard\Test\Stubs\WizardControllerStub';

        /** @var \Illuminate\Routing\Router $router */
        $this->app['router']
            ->middleware('web')
            ->group(function ($router) use ($controllerClass) {
                Wizard::routes('/wizard/test', $controllerClass, 'wizard.test');
            });
    }

    public function testGetWizardFormFromFirstStep()
    {
        $response = $this->get('/wizard/test');
        $response->assertStatus(200);
        $response->assertSee('Name');
    }

    public function testGetWizardFormFromLastProcessedStep()
    {
        $this->session([
            'laravel_wizard.test' => [
                'step-first-stub' => [
                    'name' => 'Lucas Yang',
                ],
                '_last_index' => 1,
            ],
        ]);

        $response = $this->get('/wizard/test');
        $response->assertStatus(200);
        $response->assertSee('Phone');
    }

    public function testRunAllWizardSteps()
    {
        // Get first step
        $response = $this->get('/wizard/test/step-first-stub');
        $response->assertStatus(200);

        // Post first step
        $response = $this->post('/wizard/test/step-first-stub');
        $response->assertRedirect('/wizard/test/step-second-stub');

        // Get second step
        $response = $this->get('/wizard/test/step-second-stub');
        $response->assertStatus(200);

        // Post second step
        $response = $this->post('/wizard/test/step-second-stub');
        $response->assertRedirect('/wizard/test/done');
    }
}
