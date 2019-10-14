<?php

namespace Ycs77\LaravelWizard\Test\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Ycs77\LaravelWizard\DatabaseStore;
use Ycs77\LaravelWizard\Test\TestCase;

class DatabaseStoreTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The wizard store instance.
     *
     * @var \Ycs77\LaravelWizard\DatabaseStore
     */
    protected $cache;

    protected function setUp()
    {
        parent::setUp();

        // Create db connection
        $connection = $this->app['db']->connection(
            $this->app['config']['wizard.connection']
        );
        $table = $this->app['config']['wizard.table'];

        // Make wizard cache database driver
        $this->cache = $this->app->makeWith(DatabaseStore::class, [
            'connection' => $connection,
            'table' => $table,
            'container' => $this->app,
        ]);
    }

    protected function tearDown()
    {
        $this->cache = null;

        parent::tearDown();
    }

    public function testGetAllData()
    {
        $this->authenticate();

        // arrange
        $expected = ['step' => ['field' => 'data']];
        $this->app['db']->table('wizards')->insert([
            'payload' => '{"step":{"field":"data"}}',
            'user_id' => 1,
        ]);

        // act
        $actual = $this->cache->get();

        // assert
        $this->assertEquals($expected, $actual);
    }

    public function testGetAllDataFromIpAddress()
    {
        // arrange
        $expected = ['step' => ['field' => 'data']];
        $this->app['db']->table('wizards')->insert([
            'payload' => '{"step":{"field":"data"}}',
            'ip_address' => '127.0.0.1',
        ]);

        // act
        $actual = $this->cache->get();

        // assert
        $this->assertEquals($expected, $actual);
    }

    public function testGetStepData()
    {
        $this->authenticate();

        // arrange
        $expected = ['field' => 'data'];
        $this->app['db']->table('wizards')->insert([
            'payload' => '{"step":{"field":"data"}}',
            'user_id' => 1,
        ]);

        // act
        $actual = $this->cache->get('step');

        // assert
        $this->assertEquals($expected, $actual);
    }

    public function testGetFieldData()
    {
        $this->authenticate();

        // arrange
        $expected = 'data';
        $this->app['db']->table('wizards')->insert([
            'payload' => '{"step":{"field":"data"}}',
            'user_id' => 1,
        ]);

        // act
        $actual = $this->cache->get('step.field');

        // assert
        $this->assertEquals($expected, $actual);
    }

    public function testGetLastProcessedIndexData()
    {
        $this->authenticate();

        // arrange
        $this->app['db']->table('wizards')->insert([
            'payload' => '{"step":{"field":"data"},"_last_index":0}',
            'user_id' => 1,
        ]);

        // act
        $actual = $this->cache->getLastProcessedIndex();

        // assert
        $this->assertEquals(0, $actual);
    }

    public function testSetData()
    {
        $this->authenticate();

        // act
        $this->cache->set(['step' => ['field' => 'data']]);

        // assert
        $this->assertDatabaseHas('wizards', [
            'payload' => '{"step":{"field":"data"}}',
            'user_id' => 1,
        ]);
    }

    public function testSetDataIncludeLastProcessed()
    {
        $this->authenticate();

        // act
        $this->cache->set(['step' => ['field' => 'data']], 1);

        // assert
        $this->assertDatabaseHas('wizards', [
            'payload' => '{"step":{"field":"data"},"_last_index":1}',
            'user_id' => 1,
        ]);
    }

    public function testPutData()
    {
        $this->authenticate();

        // act
        $this->cache->put('step', ['field' => 'data']);

        // assert
        $this->assertDatabaseHas('wizards', [
            'payload' => '{"step":{"field":"data"}}',
            'user_id' => 1,
        ]);
    }

    public function testCheckHasData()
    {
        $this->authenticate();

        // arrange
        $this->app['db']->table('wizards')->insert([
            'payload' => '{"step":{"field":"data"}}',
            'user_id' => 1,
        ]);

        // act
        $actual = $this->cache->has('step');

        // assert
        $this->assertTrue($actual);
    }

    public function testClearData()
    {
        $this->authenticate();

        // arrange
        $this->app['db']->table('wizards')->insert([
            'payload' => '{"step":{"field":"data"},"_last_index":1}',
            'user_id' => 1,
        ]);

        // act
        $this->cache->clear();

        // assert
        $this->assertDatabaseMissing('wizards', [
            'payload' => '{"step":{"field":"data"},"_last_index":1}',
            'user_id' => 1,
        ]);
    }
}
