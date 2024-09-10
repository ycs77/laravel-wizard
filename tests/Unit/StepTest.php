<?php

namespace Ycs77\LaravelWizard\Test\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Ycs77\LaravelWizard\Contracts\CacheStore;
use Ycs77\LaravelWizard\Test\Stubs\StepStub;
use Ycs77\LaravelWizard\Test\Stubs\UserStepStub;
use Ycs77\LaravelWizard\Test\TestCase;
use Ycs77\LaravelWizard\Wizard;

class StepTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The wizard instance.
     *
     * @var \Ycs77\LaravelWizard\Wizard|\Mockery\MockInterface
     */
    protected $wizard;

    /**
     * The wizard step instance.
     *
     * @var \Ycs77\LaravelWizard\Step|\Mockery\MockInterface
     */
    protected $step;

    protected function setUp(): void
    {
        parent::setUp();

        $this->wizard = $this->mock(Wizard::class)->makePartial();
        $this->step = $this->mock(UserStepStub::class, [$this->wizard, 0])->makePartial();
    }

    protected function tearDown(): void
    {
        $this->step = null;
        $this->wizard = null;

        parent::tearDown();
    }

    public function testGetStepProperties()
    {
        $this->assertEquals(0, $this->step->index());
        $this->assertEquals(1, $this->step->number());
        $this->assertEquals('user-step-stub', $this->step->slug());
        $this->assertEquals('User step stub', $this->step->label());
        $this->assertEquals('steps.user', $this->step->view());
    }

    public function testGetStepViewFromNoViewPropertyStep()
    {
        $this->app['config']->set('wizard.step_view_path', 'steps-dir');

        $this->wizard
            ->shouldReceive('getName')
            ->once()
            ->andReturn('user');

        $step = $this->mock(StepStub::class, [$this->wizard, 0])->makePartial();

        $this->assertEquals('steps-dir.user.step-stub', $step->view());
    }

    public function testGetData()
    {
        // arrange
        $expected = ['field' => 'data'];

        $this->step->shouldReceive('getDataKey')
            ->once()
            ->andReturn('user-step-stub');
        /** @param \Mockery\MockInterface $mock */
        $cache = $this->mock(CacheStore::class, function ($mock) {
            $mock->shouldReceive('get')->once()->andReturn(['field' => 'data']);
        });
        $this->wizard->shouldReceive('cache')->once()->andReturn($cache);

        // act
        $actual = $this->step->data();

        // assert
        $this->assertEquals($expected, $actual);
    }

    public function testGetDataKey()
    {
        $this->assertEquals('user-step-stub', $this->step->getDataKey());
        $this->assertEquals('user-step-stub.field', $this->step->getDataKey('field'));
    }
}
