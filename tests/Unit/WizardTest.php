<?php

namespace Ycs77\LaravelWizard\Test\Unit;

use Ycs77\LaravelWizard\CacheManager;
use Ycs77\LaravelWizard\Exceptions\StepNotFoundException;
use Ycs77\LaravelWizard\StepRepository;
use Ycs77\LaravelWizard\Test\Stubs\StepFirstStub;
use Ycs77\LaravelWizard\Test\Stubs\StepSecondStub;
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

    public function setUp()
    {
        parent::setUp();

        $this->wizard = $this->mock(Wizard::class, [$this->app])->makePartial();
    }

    protected function tearDown()
    {
        $this->wizard = null;

        parent::tearDown();
    }

    public function testGetLastProcessedStepIndex()
    {
        // arrange
        /** @param \Mockery\MockInterface $mock */
        $cache = $this->mock(CacheManager::class, [$this->wizard, $this->app]);
        $cache->shouldReceive('getLastProcessedIndex')->once()->andReturn(1);
        $this->wizard->setOptions();
        $this->wizard->setCache($cache);

        // act
        $actual = $this->wizard->getLastProcessedStepIndex();

        // assert
        $this->assertEquals(1, $actual);
    }

    public function testGetLastProcessedStepIndexFromNoCache()
    {
        // arrange
        $this->wizard->setOptions([
            'cache' => false,
        ]);

        // act
        $actual = $this->wizard->getLastProcessedStepIndex();

        // assert
        $this->assertEquals(0, $actual);
    }

    public function testGetStep()
    {
        // arrange
        $expected = new StepFirstStub($this->wizard, 0);
        /** @param \Mockery\MockInterface $mock */
        $stepRepo = $this->mock(StepRepository::class, function ($mock) {
            $mock->shouldReceive('find')
                ->once()
                ->andReturn(new StepFirstStub($this->wizard, 0));
            $mock->shouldReceive('setCurrentIndex')->once();
        });
        $this->wizard->setStepRepo($stepRepo);

        // act
        $actual = $this->wizard->getStep('step-first-stub');

        // assert
        $this->assertEquals($expected, $actual);
    }

    public function testGetFirstStep()
    {
        // arrange
        $expected = new StepFirstStub($this->wizard, 0);
        /** @param \Mockery\MockInterface $mock */
        $stepRepo = $this->mock(StepRepository::class, function ($mock) {
            $mock->shouldReceive('get')
                ->once()
                ->andReturn(new StepFirstStub($this->wizard, 0));
            $mock->shouldReceive('setCurrentIndex')->once();
        });
        $this->wizard->setStepRepo($stepRepo);
        $this->wizard->shouldReceive('getLastProcessedStepIndex')
            ->once()
            ->andReturn(0);

        // act
        $actual = $this->wizard->getStep();

        // assert
        $this->assertEquals($expected, $actual);
    }

    public function testGetLastProcessedStep()
    {
        // arrange
        $expected = new StepSecondStub($this->wizard, 1);
        /** @param \Mockery\MockInterface $mock */
        $stepRepo = $this->mock(StepRepository::class, function ($mock) {
            $mock->shouldReceive('get')
                ->once()
                ->andReturn(new StepSecondStub($this->wizard, 1));
            $mock->shouldReceive('setCurrentIndex')->once();
        });
        $this->wizard->setStepRepo($stepRepo);
        $this->wizard->shouldReceive('getLastProcessedStepIndex')->andReturn(1);

        // act
        $actual = $this->wizard->getStep();

        // assert
        $this->assertEquals($expected, $actual);
    }

    public function testGetStepThrowsStepNotFoundException()
    {
        // arrange
        /** @param \Mockery\MockInterface $mock */
        $stepRepo = $this->mock(StepRepository::class, function ($mock) {
            $mock->shouldReceive('find')
                ->once()
                ->andReturn(null);
        });
        $this->wizard->setStepRepo($stepRepo);

        // assert
        $this->expectException(StepNotFoundException::class);

        // act
        $this->wizard->getStep('not-found');
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
                ->andReturn(new StepSecondStub($this->wizard, 1));
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
}
