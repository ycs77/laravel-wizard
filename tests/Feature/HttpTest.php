<?php

namespace Ycs77\LaravelWizard\Test\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Ycs77\LaravelWizard\Facades\Wizard;
use Ycs77\LaravelWizard\Test\TestCase;

class HttpTest extends TestCase
{
    use RefreshDatabase;

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
        $response = $this->post('/wizard/test/step-first-stub', [
            //
        ]);
        $response->assertRedirect('/wizard/test/step-second-stub');

        // Get second step
        $response = $this->get('/wizard/test/step-second-stub');
        $response->assertStatus(200);

        // Post second step
        $response = $this->post('/wizard/test/step-second-stub');
        $response->assertRedirect('/wizard/test/done');

        // Get done page
        $response = $this->get('/wizard/test/done');
        $response->assertStatus(200);

        $this->assertEquals([
            'first' => true,
            'second' => true,
        ], $this->app['session']->get('test-steps-queue'));
    }

    public function testWizardFormThrowStepNotFoundException()
    {
        $response = $this->get('/wizard/test/step-not-found');
        $response->assertStatus(404);
    }

    public function testWizardStepNotEquialToLastProcessedStep()
    {
        $this->session([
            'laravel_wizard.test' => [
                '_last_index' => 1,
            ],
        ]);

        $response = $this->get('/wizard/test/step-first-stub');
        $response->assertRedirect('/wizard/test/step-second-stub');
    }

    public function testWizardStepTriggerFromBack()
    {
        $this->session([
            'laravel_wizard.test' => [
                '_last_index' => 1,
            ],
        ]);

        $response = $this->get('/wizard/test/step-first-stub?trigger=back');
        $response->assertRedirect('/wizard/test/step-first-stub');
    }

    public function testWizardCacheDatabaseDriver()
    {
        $this->app['config']->set('wizard.driver', 'database');

        $this->authenticate();

        $response = $this->post('/wizard/test/step-first-stub');
        $response->assertRedirect('/wizard/test/step-second-stub');

        $this->assertDatabaseHas('wizard', [
            'payload' => '{"step-first-stub":[],"_last_index":1}',
            'user_id' => 1,
        ]);
    }

    public function testWizardNoCacheNowRunStepSaveData()
    {
        $this->app['config']->set('wizard.cache', false);

        $response = $this->post('/wizard/test/step-first-stub');
        $response->assertRedirect('/wizard/test/step-second-stub');

        $this->assertEquals([
            'first' => true,
        ], $this->app['session']->get('test-steps-queue'));
    }
}
