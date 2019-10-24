<?php

namespace Ycs77\LaravelWizard\Test\Unit;

use Ycs77\LaravelWizard\StepRepository;
use Ycs77\LaravelWizard\Test\Stubs\PostStepStub;
use Ycs77\LaravelWizard\Test\Stubs\UserStepStub;
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

    protected function setUp(): void
    {
        parent::setUp();

        $this->wizard = $this->mock(Wizard:: class)->makePartial();
        $this->stepRepo = $this->app->makeWith(StepRepository::class, [$this->wizard]);

        $this->stepsStub = [
            new UserStepStub($this->wizard, 0),
            new PostStepStub($this->wizard, 1),
        ];
    }

    protected function tearDown(): void
    {
        $this->stepRepo = null;
        $this->wizard = null;

        parent::tearDown();
    }

    protected function initStepsItems()
    {
        $this->stepRepo->set($this->stepsStub);
    }

    public function testGetStep()
    {
        $this->initStepsItems();

        $this->assertEquals($this->stepsStub[0], $this->stepRepo->get(0));
        $this->assertEquals($this->stepsStub[1], $this->stepRepo->get(1));
        $this->assertNull($this->stepRepo->get(2));
    }

    public function testFindStep()
    {
        $this->initStepsItems();

        $this->assertEquals($this->stepsStub[0], $this->stepRepo->find('user-step-stub'));
        $this->assertEquals($this->stepsStub[1], $this->stepRepo->find('post-step-stub'));
        $this->assertNull($this->stepRepo->find('not-found'));
    }

    public function testFindStepKey()
    {
        $this->initStepsItems();

        $this->assertEquals(0, $this->stepRepo->findKey('user-step-stub'));
        $this->assertEquals(1, $this->stepRepo->findKey('post-step-stub'));
        $this->assertNull($this->stepRepo->findKey('not-found'));
        $this->assertEquals(0, $this->stepRepo->findKey('not-found', 0));
    }

    public function testPushStepFormStepClassName()
    {
        $this->stepRepo->push([
            UserStepStub::class,
            PostStepStub::class,
        ]);

        $this->assertEquals($this->stepsStub, $this->stepRepo->all());
    }

    public function testPushStepFormStepInstance()
    {
        $this->stepRepo->push(UserStepStub::class, 0);

        $this->assertEquals([$this->stepsStub[0]], $this->stepRepo->all());
    }

    public function testGetAllSteps()
    {
        $this->initStepsItems();

        $this->assertEquals($this->stepsStub, $this->stepRepo->all());
    }

    public function testStepsCount()
    {
        $this->initStepsItems();

        $this->assertEquals(2, $this->stepRepo->count());
    }

    public function testGetOriginalSteps()
    {
        $this->initStepsItems();

        $this->assertEquals(collect($this->stepsStub), $this->stepRepo->original());
    }

    public function testGetAndSetCurrentStep()
    {
        $this->initStepsItems();

        $this->assertEquals(0, $this->stepRepo->current()->index());

        $this->stepRepo->current($this->stepsStub[1]);
        $this->assertEquals(1, $this->stepRepo->current()->index());

        $this->stepRepo->setCurrentIndex(0);
        $this->assertEquals(0, $this->stepRepo->current()->index());
    }

    public function testFirstStep()
    {
        $this->initStepsItems();

        $this->assertEquals($this->stepsStub[0], $this->stepRepo->first());
    }

    public function testLastStep()
    {
        $this->initStepsItems();

        $this->assertEquals($this->stepsStub[1], $this->stepRepo->last());
    }

    public function testPrevStep()
    {
        $this->initStepsItems();

        $this->stepRepo->setCurrentIndex(1);
        $this->assertEquals($this->stepsStub[0], $this->stepRepo->prev());

        $this->stepRepo->setCurrentIndex(0);
        $this->assertNull($this->stepRepo->prev());
    }

    public function testNextStep()
    {
        $this->initStepsItems();

        $this->stepRepo->setCurrentIndex(0);
        $this->assertEquals($this->stepsStub[1], $this->stepRepo->next());

        $this->stepRepo->setCurrentIndex(1);
        $this->assertNull($this->stepRepo->next());
    }

    public function testHasStep()
    {
        $this->initStepsItems();

        $this->assertTrue($this->stepRepo->has(0));
        $this->assertTrue($this->stepRepo->has(1));
        $this->assertFalse($this->stepRepo->has(2));
    }

    public function testHasPrevStep()
    {
        $this->initStepsItems();

        $this->stepRepo->setCurrentIndex(1);
        $this->assertTrue($this->stepRepo->hasPrev());

        $this->stepRepo->setCurrentIndex(0);
        $this->assertFalse($this->stepRepo->hasPrev());
    }

    public function testHasNextStep()
    {
        $this->initStepsItems();

        $this->stepRepo->setCurrentIndex(0);
        $this->assertTrue($this->stepRepo->hasNext());

        $this->stepRepo->setCurrentIndex(1);
        $this->assertFalse($this->stepRepo->hasNext());
    }

    public function testPrevSlugStep()
    {
        $this->initStepsItems();

        $this->stepRepo->setCurrentIndex(1);
        $this->assertEquals('user-step-stub', $this->stepRepo->prevSlug());

        $this->stepRepo->setCurrentIndex(0);
        $this->assertNull($this->stepRepo->prevSlug());
    }

    public function testNextSlugStep()
    {
        $this->initStepsItems();

        $this->stepRepo->setCurrentIndex(0);
        $this->assertEquals('post-step-stub', $this->stepRepo->nextSlug());

        $this->stepRepo->setCurrentIndex(1);
        $this->assertNull($this->stepRepo->nextSlug());
    }

    public function testIsEmptyStep()
    {
        $this->stepRepo->set([]);
        $this->assertTrue($this->stepRepo->isEmpty());

        $this->stepRepo->set($this->stepsStub);
        $this->assertFalse($this->stepRepo->isEmpty());
    }

    public function testIsNotEmptyStep()
    {
        $this->stepRepo->set($this->stepsStub);
        $this->assertTrue($this->stepRepo->isNotEmpty());

        $this->stepRepo->set([]);
        $this->assertFalse($this->stepRepo->isNotEmpty());
    }
}
