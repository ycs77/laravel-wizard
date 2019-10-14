<?php

namespace Ycs77\LaravelWizard\Test\Unit;

use Ycs77\LaravelWizard\SessionStore;
use Ycs77\LaravelWizard\Test\TestCase;

class SessionStoreTest extends TestCase
{
    /**
     * The wizard store instance.
     *
     * @var \Ycs77\LaravelWizard\SessionStore
     */
    protected $cache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cache = $this->app->makeWith(SessionStore::class, [
            'session' => $this->app['session.store'],
            'wizardKey' => 'laravel_wizard.test',
        ]);
    }

    protected function tearDown(): void
    {
        $this->cache = null;

        parent::tearDown();
    }

    public function testGetAllData()
    {
        // arrange
        $expected = ['step' => ['field' => 'data']];
        $this->session([
            'laravel_wizard.test' => [
                'step' => ['field' => 'data'],
            ],
        ]);

        // act
        $actual = $this->cache->get();

        // assert
        $this->assertEquals($expected, $actual);
    }

    public function testGetStepData()
    {
        // arrange
        $expected = ['field' => 'data'];
        $this->session([
            'laravel_wizard.test' => [
                'step' => ['field' => 'data'],
            ],
        ]);

        // act
        $actual = $this->cache->get('step');

        // assert
        $this->assertEquals($expected, $actual);
    }

    public function testGetFieldData()
    {
        // arrange
        $expected = 'data';
        $this->session([
            'laravel_wizard.test' => [
                'step' => ['field' => 'data'],
            ],
        ]);

        // act
        $actual = $this->cache->get('step.field');

        // assert
        $this->assertEquals($expected, $actual);
    }

    public function testGetLastProcessedIndexData()
    {
        // arrange
        $this->session([
            'laravel_wizard.test' => [
                '_last_index' => 0,
            ],
        ]);

        // act
        $actual = $this->cache->getLastProcessedIndex();

        // assert
        $this->assertEquals(0, $actual);
    }

    public function testSetData()
    {
        // arrange
        $expected = ['step' => ['field' => 'data']];

        // act
        $this->cache->set(['step' => ['field' => 'data']]);

        // assert
        $this->assertEquals($expected, $this->app['session']->get('laravel_wizard.test'));
    }

    public function testSetDataIncludeLastProcessed()
    {
        // arrange
        $expected = [
            'step' => ['field' => 'data'],
            '_last_index' => 1,
        ];

        // act
        $this->cache->set(['step' => ['field' => 'data']], 1);

        // assert
        $this->assertEquals($expected, $this->app['session']->get('laravel_wizard.test'));
    }

    public function testPutData()
    {
        // arrange
        $expected = ['step' => ['field' => 'data']];

        // act
        $this->cache->put('step', ['field' => 'data']);

        // assert
        $this->assertEquals($expected, $this->app['session']->get('laravel_wizard.test'));
    }

    public function testCheckHasData()
    {
        // arrange
        $this->session([
            'laravel_wizard.test' => [
                'step' => ['field' => 'data'],
            ],
        ]);

        // act
        $actual = $this->cache->has('step');

        // assert
        $this->assertTrue($actual);
    }

    public function testClearData()
    {
        // arrange
        $this->app['session']->put('laravel_wizard.test', [
            'step' => ['field' => 'data'],
        ]);

        // act
        $this->cache->clear();

        // assert
        $this->assertNull($this->app['session']->get('laravel_wizard.test'));
    }
}
