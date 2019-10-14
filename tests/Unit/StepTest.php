<?php

namespace Ycs77\LaravelWizard\Test\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Ycs77\LaravelWizard\Test\Stubs\PostStepStub;
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

    protected function setUp()
    {
        parent::setUp();

        $this->wizard = $this->mock(Wizard::class)->makePartial();
        $this->step = $this->mock(UserStepStub::class, [$this->wizard, 0])->makePartial();
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
        $this->assertEquals('user-step-stub', $this->step->slug());
        $this->assertEquals('User step stub', $this->step->label());
        $this->assertEquals('steps.user', $this->step->view());
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

    public function testCacheProgress()
    {
        // arrange
        $expected = [
            'user-step-stub' => [
                'name' => 'Lucas Yang',
            ],
            '_last_index' => 1,
        ];
        $request = Request::create('http://example.com');

        $this->step->shouldReceive('getRequestData')
            ->once()
            ->andReturn(['name' => 'Lucas Yang']);

        /** @param \Mockery\MockInterface $mock */
        $cache = $this->mock(CacheStore::class, function ($mock) use ($expected) {
            $mock->shouldReceive('get')
                ->twice()
                ->andReturn([], $expected);
        });
        $this->wizard->shouldReceive('cache')->twice()->andReturn($cache);
        $this->wizard->shouldReceive('nextStepIndex')->once()->andReturn(1);
        $this->wizard->shouldReceive('cacheStepData')
            ->once()
            ->with([
                'user-step-stub' => [
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
            'user-step-stub' => [
                'name' => 'Lucas Yang',
            ],
            'post-step-stub' => [
                'phone' => '12345678',
            ],
            '_last_index' => 1,
        ];
        $request = Request::create('http://example.com');

        $this->step = $this->mock(PostStepStub::class, [$this->wizard, 1])->makePartial();
        $this->step->shouldReceive('getRequestData')
            ->once()
            ->andReturn(['phone' => '12345678']);

        /** @param \Mockery\MockInterface $mock */
        $cache = $this->mock(CacheStore::class, function ($mock) use ($expected) {
            $mock->shouldReceive('get')
                ->twice()
                ->andReturn([
                    'user-step-stub' => [
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
                'user-step-stub' => [
                    'name' => 'Lucas Yang',
                ],
                'post-step-stub' => [
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
