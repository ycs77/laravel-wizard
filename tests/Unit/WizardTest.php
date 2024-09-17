<?php

namespace Ycs77\LaravelWizard\Test\Unit;

use Illuminate\Http\Request;
use Ycs77\LaravelWizard\Contracts\CacheStore;
use Ycs77\LaravelWizard\StepRepository;
use Ycs77\LaravelWizard\Test\Stubs\PostStepStub;
use Ycs77\LaravelWizard\Test\Stubs\UserStepStub;
use Ycs77\LaravelWizard\Test\TestCase;
use Ycs77\LaravelWizard\Wizard;

class WizardTest extends TestCase
{
    /**
     * The wizard instance.
     *
     * @var \Ycs77\LaravelWizard\Wizard|\Mockery\MockInterface
     */
    protected $wizard;

    protected function setUp(): void
    {
        parent::setUp();

        $this->wizard = new Wizard($this->app, 'test-wizard', 'Test');
    }

    protected function tearDown(): void
    {
        $this->wizard = null;

        parent::tearDown();
    }

    public function testCacheStepData()
    {
        // arrange
        $data = ['step' => ['field' => 'data']];
        /** @param \Mockery\MockInterface $mock */
        $cache = $this->mock('cache', function ($mock) {
            $mock->shouldReceive('set')->once();
            $mock->shouldReceive('get')->once()->andReturn(['Saved data.']);
        });
        $this->wizard->setCache($cache);

        // act
        $this->wizard->cacheStepData($data, 1);
        $actual = $this->wizard->cache()->get();

        // assert
        $this->assertEquals(['Saved data.'], $actual);
    }

    public function testGetNextStepIndex()
    {
        // arrange
        /** @param \Mockery\MockInterface $mock */
        $stepRepo = $this->mock(StepRepository::class, function ($mock) {
            $mock->shouldReceive('next')
                ->once()
                ->andReturn(new PostStepStub($this->wizard, 1));
        });
        $this->wizard->setStepRepo($stepRepo);

        // act
        $actual = $this->wizard->nextStepIndex();

        // assert
        $this->assertEquals(1, $actual);
    }

    public function testGetNextStepIndexReturnNull()
    {
        // arrange
        /** @param \Mockery\MockInterface $mock */
        $stepRepo = $this->mock(StepRepository::class, function ($mock) {
            $mock->shouldReceive('next')->once()->andReturn(null);
        });
        $this->wizard->setStepRepo($stepRepo);

        // act
        $actual = $this->wizard->nextStepIndex();

        // assert
        $this->assertNull($actual);
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
        $request = Request::create('http://example.com', 'GET', ['name' => 'Lucas Yang']);

        $step = new UserStepStub($this->wizard, 0);

        /** @param \Mockery\MockInterface $mock */
        $cache = $this->mock(CacheStore::class, function ($mock) use ($expected) {
            $mock->shouldReceive('get')
                ->twice()
                ->andReturn([], $expected);
            $mock->shouldReceive('set')
                ->with([
                    'user-step-stub' => [
                        'name' => 'Lucas Yang',
                    ],
                ], 1);
        });
        $this->wizard->setCache($cache);

        /** @param \Mockery\MockInterface $mock */
        $stepRepo = $this->mock(StepRepository::class, function ($mock) {
            $mock->shouldReceive('next')->once()->andReturn(new PostStepStub($this->wizard, 1));
        });
        $this->wizard->setStepRepo($stepRepo);

        // act
        $actual = $this->wizard->cacheProgress($request, $step);

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
        $request = Request::create('http://example.com', 'GET', ['phone' => '12345678']);

        $step = new PostStepStub($this->wizard, 1);

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
            $mock->shouldReceive('set')
                ->with([
                    'user-step-stub' => [
                        'name' => 'Lucas Yang',
                    ],
                    'post-step-stub' => [
                        'phone' => '12345678',
                    ],
                    '_last_index' => 1,
                ], null);
        });
        $this->wizard->setCache($cache);

        /** @param \Mockery\MockInterface $mock */
        $stepRepo = $this->mock(StepRepository::class, function ($mock) {
            $mock->shouldReceive('next')->once()->andReturnNull();
        });
        $this->wizard->setStepRepo($stepRepo);

        // act
        $actual = $this->wizard->cacheProgress($request, $step);

        // assert
        $this->assertEquals($expected, $actual);
    }

    public function testRedirectToOtherStep()
    {
        // arrange
        /** @param \Mockery\MockInterface $mock */
        $cache = $this->mock('cache', function ($mock) {
            $mock->shouldReceive('set')->once();
            $mock->shouldReceive('get')->once()->andReturn([
                'user-step-stub' => [
                    'name' => 'Lucas Yang',
                ],
            ]);
        });
        $this->wizard->setCache($cache);

        /** @param \Mockery\MockInterface $mock */
        $stepRepo = $this->mock(StepRepository::class);
        $this->wizard->setStepRepo($stepRepo);

        $this->wizard->resolveActionUrlUsing(function (string $method, $parameters = []) {
            return url('/wizard/test-wizard/'.$parameters['step']);
        });

        // act
        $actual = $this->wizard->redirectToStep(new PostStepStub($this->wizard, 1));

        // assert
        $this->assertEquals(url('/wizard/test-wizard/post-step-stub'), $actual->getTargetUrl());
    }
}
