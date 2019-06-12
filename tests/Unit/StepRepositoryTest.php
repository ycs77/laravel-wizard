<?php

namespace Ycs77\LaravelWizard\Test\Unit;

use Ycs77\LaravelWizard\StepRepository;
use Ycs77\LaravelWizard\Test\Stubs\StepFirstStub;
use Ycs77\LaravelWizard\Test\Stubs\StepSecondStub;
use Ycs77\LaravelWizard\Test\TestCase;
use Ycs77\LaravelWizard\Wizard;

class StepRepositoryTest extends TestCase
{
    /**
     * The wizard instance.
     *
     * @var \Ycs77\LaravelWizard\Wizard|\Mockery\MockInterface
     */
    protected $wizard;

    /**
     * The wizard step repository instance.
     *
     * @var \Ycs77\LaravelWizard\StepRepository
     */
    protected $step;

    /**
     * The wizard steps stub.
     *
     * @var array
     */
    protected $stepsStub;

    public function setUp()
    {
        parent::setUp();

        $this->wizard = $this->mock(Wizard:: class, [$this->app]);
        $this->step = $this->app->makeWith(StepRepository::class, [
            'wizard' => $this->wizard,
        ]);

        $this->stepsStub = [
            new StepFirstStub($this->wizard, 0),
            new StepSecondStub($this->wizard, 1),
        ];
    }

    protected function tearDown()
    {
        $this->step = null;
        $this->wizard = null;

        parent::tearDown();
    }

    protected function initStepsItems()
    {
        $this->step->set($this->stepsStub);
    }

    public function testGetStep()
    {
        $this->initStepsItems();

        $this->assertEquals($this->stepsStub[0], $this->step->get(0));
        $this->assertEquals($this->stepsStub[1], $this->step->get(1));
        $this->assertNull($this->step->get(2));
    }

    public function testFindStep()
    {
        $this->initStepsItems();

        $this->assertEquals($this->stepsStub[0], $this->step->find('step-first-stub'));
        $this->assertEquals($this->stepsStub[1], $this->step->find('step-second-stub'));
        $this->assertNull($this->step->find('not-found'));
    }

    public function testMakeStepFormStepClassName()
    {
        $this->step->make([
            StepFirstStub::class,
            StepSecondStub::class,
        ]);

        $this->assertEquals($this->stepsStub, $this->step->all());
    }

    public function testMakeStepFormStepInstance()
    {
        $this->step->make(StepFirstStub::class, 0);

        $this->assertEquals([$this->stepsStub[0]], $this->step->all());
    }

    public function testGetAllSteps()
    {
        $this->initStepsItems();

        $this->assertEquals($this->stepsStub, $this->step->all());
    }

    public function testStepsCount()
    {
        $this->initStepsItems();

        $this->assertEquals(2, $this->step->count());
    }

    public function testGetOriginalSteps()
    {
        $this->initStepsItems();

        $this->assertEquals(collect($this->stepsStub), $this->step->original());
    }

    public function testGetAndSetCurrentStep()
    {
        $this->initStepsItems();

        $this->assertEquals(0, $this->step->current()->index());

        $this->step->current($this->stepsStub[1]);
        $this->assertEquals(1, $this->step->current()->index());

        $this->step->setCurrentIndex(0);
        $this->assertEquals(0, $this->step->current()->index());
    }

    public function testFirstStep()
    {
        $this->initStepsItems();

        $this->assertEquals($this->stepsStub[0], $this->step->first());
    }

    public function testLastStep()
    {
        $this->initStepsItems();

        $this->assertEquals($this->stepsStub[1], $this->step->last());
    }

    public function testPrevStep()
    {
        $this->initStepsItems();

        $this->step->setCurrentIndex(1);
        $this->assertEquals($this->stepsStub[0], $this->step->prev());

        $this->step->setCurrentIndex(0);
        $this->assertNull($this->step->prev());
    }

    public function testNextStep()
    {
        $this->initStepsItems();

        $this->step->setCurrentIndex(0);
        $this->assertEquals($this->stepsStub[1], $this->step->next());

        $this->step->setCurrentIndex(1);
        $this->assertNull($this->step->next());
    }

    public function testHasStep()
    {
        $this->initStepsItems();

        $this->assertTrue($this->step->has(0));
        $this->assertTrue($this->step->has(1));
        $this->assertFalse($this->step->has(2));
    }

    public function testHasPrevStep()
    {
        $this->initStepsItems();

        $this->step->setCurrentIndex(1);
        $this->assertTrue($this->step->hasPrev());

        $this->step->setCurrentIndex(0);
        $this->assertFalse($this->step->hasPrev());
    }

    public function testHasNextStep()
    {
        $this->initStepsItems();

        $this->step->setCurrentIndex(0);
        $this->assertTrue($this->step->hasNext());

        $this->step->setCurrentIndex(1);
        $this->assertFalse($this->step->hasNext());
    }

    public function testPrevSlugStep()
    {
        $this->initStepsItems();

        $this->step->setCurrentIndex(1);
        $this->assertEquals('step-first-stub', $this->step->prevSlug());

        $this->step->setCurrentIndex(0);
        $this->assertNull($this->step->prevSlug());
    }

    public function testNextSlugStep()
    {
        $this->initStepsItems();

        $this->step->setCurrentIndex(0);
        $this->assertEquals('step-second-stub', $this->step->nextSlug());

        $this->step->setCurrentIndex(1);
        $this->assertNull($this->step->nextSlug());
    }

    public function testIsEmptyStep()
    {
        $this->step->set([]);
        $this->assertTrue($this->step->isEmpty());

        $this->step->set($this->stepsStub);
        $this->assertFalse($this->step->isEmpty());
    }

    public function testIsNotEmptyStep()
    {
        $this->step->set($this->stepsStub);
        $this->assertTrue($this->step->isNotEmpty());

        $this->step->set([]);
        $this->assertFalse($this->step->isNotEmpty());
    }
}
