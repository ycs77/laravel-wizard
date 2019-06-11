<?php

namespace Ycs77\LaravelWizard\Test\Unit;

use Illuminate\Http\Request;
use Ycs77\LaravelWizard\CacheManager;
use Ycs77\LaravelWizard\Test\Stubs\StepFirstStub;
use Ycs77\LaravelWizard\Test\Stubs\StepSecondStub;
use Ycs77\LaravelWizard\Test\TestCase;
use Ycs77\LaravelWizard\Wizard;

class StepTest extends TestCase
{
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

    public function setUp()
    {
        parent::setUp();

        $this->wizard = $this->mock(Wizard::class)->makePartial();
        $this->step = $this->mock(StepFirstStub::class, [$this->wizard, 0])->makePartial();
    }

    protected function tearDown()
    {
        $this->step = null;
        $this->wizard = null;

        parent::tearDown();
    }

    public function testGetStepProperties()
    {
        $this->assertEquals(0, $this->step->index());
        $this->assertEquals(1, $this->step->number());
        $this->assertEquals('step-first-stub', $this->step->slug());
        $this->assertEquals('Step first stub', $this->step->label());
        $this->assertEquals('steps.first', $this->step->view());
    }

    public function testGetData()
    {
        // arrange
        $expected = ['field' => 'data'];

        $this->step->shouldReceive('getDataKey')
            ->once()
            ->andReturn('step-first-stub');
        /** @param \Mockery\MockInterface $mock */
        $cache = $this->mock(CacheManager::class, function ($mock) {
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
        $this->assertEquals('step-first-stub', $this->step->getDataKey());
        $this->assertEquals('step-first-stub.field', $this->step->getDataKey('field'));
    }

    public function testCacheProgress()
    {
        // arrange
        $expected = [
            'step-first-stub' => [
                'name' => 'Lucas Yang',
            ],
            '_last_index' => 1,
        ];
        $request = Request::create('http://example.com');

        $this->step->shouldReceive('getRequestData')
            ->once()
            ->andReturn(['name' => 'Lucas Yang']);

        /** @param \Mockery\MockInterface $mock */
        $cache = $this->mock(CacheManager::class, function ($mock) use ($expected) {
            $mock->shouldReceive('get')
                ->twice()
                ->andReturn([], $expected);
        });
        $this->wizard->shouldReceive('cache')->twice()->andReturn($cache);
        $this->wizard->shouldReceive('nextStepIndex')->once()->andReturn(1);
        $this->wizard->shouldReceive('cacheStepData')
            ->once()
            ->with([
                'step-first-stub' => [
                    'name' => 'Lucas Yang',
                ],
            ], 1);

        // act
        $actual = $this->step->cacheProgress($request);

        // assert
        $this->assertEquals($expected, $actual);
    }

    public function testSecondStepCacheProgress()
    {
        // arrange
        $expected = [
            'step-first-stub' => [
                'name' => 'Lucas Yang',
            ],
            'step-second-stub' => [
                'phone' => '12345678',
            ],
            '_last_index' => 1,
        ];
        $request = Request::create('http://example.com');

        $this->step = $this->mock(StepSecondStub::class, [$this->wizard, 1])->makePartial();
        $this->step->shouldReceive('getRequestData')
            ->once()
            ->andReturn(['phone' => '12345678']);

        /** @param \Mockery\MockInterface $mock */
        $cache = $this->mock(CacheManager::class, function ($mock) use ($expected) {
            $mock->shouldReceive('get')
                ->twice()
                ->andReturn([
                    'step-first-stub' => [
                        'name' => 'Lucas Yang',
                    ],
                    '_last_index' => 1,
                ], $expected);
        });
        $this->wizard->shouldReceive('cache')->twice()->andReturn($cache);
        $this->wizard->shouldReceive('nextStepIndex')->once()->andReturn(null);
        $this->wizard->shouldReceive('cacheStepData')
            ->once()
            ->with([
                'step-first-stub' => [
                    'name' => 'Lucas Yang',
                ],
                'step-second-stub' => [
                    'phone' => '12345678',
                ],
                '_last_index' => 1,
            ], null);

        // act
        $actual = $this->step->cacheProgress($request);

        // assert
        $this->assertEquals($expected, $actual);
    }
}
