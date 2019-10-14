<?php

namespace Ycs77\LaravelWizard\Test\Unit;

use Ycs77\LaravelWizard\StepRepository;
use Ycs77\LaravelWizard\Test\Stubs\PostStepStub;
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

        $this->wizard = new Wizard($this->app, 'test-wizard');
    }

    protected function tearDown()
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
}
