<?php

namespace Ycs77\LaravelWizard\Test\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Ycs77\LaravelWizard\Facades\Wizard;
use Ycs77\LaravelWizard\Test\TestCase;

class HttpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp()
    {
        parent::setUp();

        $this->setWizardRoutes(
            '/wizard/test',
            '\Ycs77\LaravelWizard\Test\Stubs\WizardControllerStub',
            'wizard.test'
        );

        $this->authenticate();
    }

    protected function setWizardRoutes($uri, $controllerClass, $name)
    {
        $this->app['router']
            ->middleware('web')
            ->group(function () use ($uri, $controllerClass, $name) {
                Wizard::routes($uri, $controllerClass, $name);
            });
    }

    public function testGetWizardFormFromFirstStep()
    {
        $response = $this->get('/wizard/test');
        $response->assertRedirect('/wizard/test/user-step-stub');
    }

    public function testGetWizardFormFromLastProcessedStep()
    {
        $this->session([
            'laravel_wizard.test' => [
                'user-step-stub' => [
                    'name' => 'Lucas Yang',
                ],
                '_last_index' => 1,
            ],
        ]);

        $response = $this->get('/wizard/test');
        $response->assertRedirect('/wizard/test/post-step-stub');
    }

    public function testRunAllWizardSteps()
    {
        // Get first step
        $response = $this->get('/wizard/test/user-step-stub');
        $response->assertStatus(200);

        // Post first step
        $response = $this->post('/wizard/test/user-step-stub', [
            'name' => 'John',
        ]);
        $response->assertRedirect('/wizard/test/post-step-stub');

        // Get second step
        $response = $this->get('/wizard/test/post-step-stub');
        $response->assertStatus(200);

        // Post second step
        $response = $this->post('/wizard/test/post-step-stub', [
            'title' => 'Title',
            'content' => 'Content.',
        ]);
        $response->assertRedirect('/wizard/test/done');

        // Get done page
        $response = $this->get('/wizard/test/done');
        $response->assertStatus(200);

        // Assert data from database
        $this->assertDatabaseHas('users', [
            'name' => 'John',
        ]);
        $this->assertDatabaseHas('posts', [
            'title' => 'Title',
            'content' => 'Content.',
        ]);
    }

    public function testRunAllWizardStepsCloseCache()
    {
        $this->app['config']->set('wizard.cache', false);

        // Get first step
        $response = $this->get('/wizard/test/user-step-stub');
        $response->assertStatus(200);

        // Post first step
        $response = $this->post('/wizard/test/user-step-stub', [
            'name' => 'John',
        ]);
        $response->assertRedirect('/wizard/test/post-step-stub');

        // Assert user data from database
        $this->assertDatabaseHas('users', [
            'name' => 'John',
        ]);

        // Get second step
        $response = $this->get('/wizard/test/post-step-stub');
        $response->assertStatus(200);

        // Post second step
        $response = $this->post('/wizard/test/post-step-stub', [
            'title' => 'Title',
            'content' => 'Content.',
        ]);
        $response->assertRedirect('/wizard/test/done');

        // Get done page
        $response = $this->get('/wizard/test/done');
        $response->assertStatus(200);

        // Assert post data from database
        $this->assertDatabaseHas('posts', [
            'title' => 'Title',
            'content' => 'Content.',
        ]);
    }

    public function testThrowStepNotFoundException()
    {
        $this->app['config']->set('app.debug', false);

        $response = $this->get('/wizard/test/step-not-found');
        $response->assertStatus(404);
    }

    public function testThrowStepNotFoundExceptionFromDebugMode()
    {
        $response = $this->get('/wizard/test/step-not-found');
        $response->assertStatus(500);
        $response->assertSee('Step [step-not-found] is not found to Test wizard.');
    }

    public function testWizardStepNotEquialToLastProcessedStep()
    {
        $this->session([
            'laravel_wizard.test' => [
                '_last_index' => 1,
            ],
        ]);

        $response = $this->get('/wizard/test/user-step-stub');
        $response->assertRedirect('/wizard/test/post-step-stub');
    }

    public function testWizardStepTriggerToBack()
    {
        $this->session([
            'laravel_wizard.test' => [
                'user-step-stub' => [
                    'name' => 'John',
                ],
                '_last_index' => 1,
            ],
        ]);

        $response = $this->post('/wizard/test/post-step-stub?_trigger=back', [
            'title' => 'Title',
            'content' => 'Content.',
        ]);
        $response->assertRedirect('/wizard/test/user-step-stub');

        $this->assertEquals([
            'user-step-stub' => [
                'name' => 'John',
            ],
            'post-step-stub' => [
                'title' => 'Title',
                'content' => 'Content.',
            ],
            '_last_index' => 0,
        ], $this->app['session']->get('laravel_wizard.test'));
    }

    public function testWizardStepTriggerToBackNoValidate()
    {
        $this->session([
            'laravel_wizard.test' => [
                'user-step-stub' => [
                    'name' => 'John',
                ],
                '_last_index' => 1,
            ],
        ]);

        $response = $this->post('/wizard/test/post-step-stub?_trigger=back', [
            'title' => 'Over 50 words title.........................................',
            'content' => null,
        ]);
        $response->assertRedirect('/wizard/test/user-step-stub');

        $this->assertEquals([
            'user-step-stub' => [
                'name' => 'John',
            ],
            'post-step-stub' => [
                'title' => 'Over 50 words title.........................................',
                'content' => null,
            ],
            '_last_index' => 0,
        ], $this->app['session']->get('laravel_wizard.test'));
    }

    public function testWizardCacheDatabaseDriver()
    {
        $this->app['config']->set('wizard.driver', 'database');

        $response = $this->post('/wizard/test/user-step-stub', [
            'name' => 'John',
        ]);
        $response->assertRedirect('/wizard/test/post-step-stub');

        $this->assertDatabaseHas('wizards', [
            'payload' => '{"user-step-stub":{"name":"John"},"_last_index":1}',
            'user_id' => 1,
        ]);
    }

    public function testWizardNoCacheNowRunStepSaveData()
    {
        $this->app['config']->set('wizard.cache', false);

        $response = $this->post('/wizard/test/user-step-stub', [
            'name' => 'John',
        ]);
        $response->assertRedirect('/wizard/test/post-step-stub');

        $this->assertDatabaseHas('users', [
            'name' => 'John',
        ]);
    }

    public function testWizardSetNoCacheFromControllerNowRunStepSaveData()
    {
        $this->setWizardRoutes(
            '/wizard/no-cache',
            '\Ycs77\LaravelWizard\Test\Stubs\WizardControllerOptionsStub',
            'wizard.no-cache'
        );

        $response = $this->post('/wizard/no-cache/user-step-stub', [
            'name' => 'John',
        ]);
        $response->assertRedirect('/wizard/no-cache/post-step-stub');

        $this->assertDatabaseHas('users', [
            'name' => 'John',
        ]);
    }
}
