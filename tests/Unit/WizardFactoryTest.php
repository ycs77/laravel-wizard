<?php

namespace Ycs77\LaravelWizard\Test\Unit;

use Ycs77\LaravelWizard\Contracts\CacheStore;
use Ycs77\LaravelWizard\StepRepository;
use Ycs77\LaravelWizard\Test\Stubs\UserStepStub;
use Ycs77\LaravelWizard\Test\TestCase;
use Ycs77\LaravelWizard\WizardFactory;

class WizardFactoryTest extends TestCase
{
    public function testMakeWizard()
    {
        $factory = new WizardFactory($this->app);

        $wizard = $factory->make('test-wizard', 'Test', [UserStepStub::class]);

        $this->assertEquals('test-wizard', $wizard->getName());
        $this->assertInstanceOf(CacheStore::class, $wizard->cache());
        $this->assertInstanceOf(StepRepository::class, $wizard->stepRepo());
        $this->assertCount(1, $wizard->stepRepo()->original());
        $this->assertInstanceOf(UserStepStub::class, $wizard->stepRepo()->original()->first());
    }
}
