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

    public function setUp()
    {
        parent::setUp();

        $this->cache = $this->app->makeWith(SessionStore::class, [
            'session' => $this->app['session.store'],
            'wizardKey' => 'laravel_wizard.test',
        ]);
    }

    protected function tearDown()
    {
        $this->cache = null;

        parent::tearDown();
    }

    public function testGetAllData()
    {
        // arrange
        $expected = ['step' => ['field' => 'data']];

        // act
        $this->session([
            'laravel_wizard.test' => [
                'step' => ['field' => 'data'],
            ],
        ]);
        $actual = $this->cache->get();

        // assert
        $this->assertEquals($expected, $actual);
    }

    public function testGetStepData()
    {
        // arrange
        $expected = ['field' => 'data'];

        // act
        $this->session([
            'laravel_wizard.test' => [
                'step' => ['field' => 'data'],
            ],
        ]);
        $actual = $this->cache->get('step');

        // assert
        $this->assertEquals($expected, $actual);
    }

    public function testGetFieldData()
    {
        // arrange
        $expected = 'data';

        // act
        $this->session([
            'laravel_wizard.test' => [
                'step' => ['field' => 'data'],
            ],
        ]);
        $actual = $this->cache->get('step.field');

        // assert
        $this->assertEquals($expected, $actual);
    }

    public function testGetLastProcessedIndexData()
    {
        // act
        $this->session([
            'laravel_wizard.test' => [
                '_last_index' => 0,
            ],
        ]);
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

    public function testCheckHasData()
    {
        // act
        $this->session([
            'laravel_wizard.test' => [
                'step' => ['field' => 'data'],
            ],
        ]);
        $actual = $this->cache->has('step');

        // assert
        $this->assertTrue($actual);
    }

    public function testClearData()
    {
        // act
        $this->app['session']->put('laravel_wizard.test', [
            'step' => ['field' => 'data'],
        ]);
        $this->cache->clear();

        // assert
        $this->assertNull($this->app['session']->get('laravel_wizard.test'));
    }
}
