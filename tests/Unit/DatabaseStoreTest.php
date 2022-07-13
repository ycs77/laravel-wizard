<?php

namespace Ycs77\LaravelWizard\Test\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Ycs77\LaravelWizard\CachedFile;
use Ycs77\LaravelWizard\CachedFileSerializer;
use Ycs77\LaravelWizard\DatabaseStore;
use Ycs77\LaravelWizard\Test\Concerns\CachedFileTesting;
use Ycs77\LaravelWizard\Test\TestCase;

class DatabaseStoreTest extends TestCase
{
    use CachedFileTesting;
    use RefreshDatabase;

    /**
     * The wizard store instance.
     *
     * @var \Ycs77\LaravelWizard\DatabaseStore
     */
    protected $cache;

    protected function setUp(): void
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
            'serializer' => $this->app->make(CachedFileSerializer::class),
            'container' => $this->app,
        ]);
    }

    protected function tearDown(): void
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

    public function testGetDataWithFile()
    {
        $this->authenticate();

        // arrange
        $file = UploadedFile::fake()->image('avatar.jpg');
        $file->storeAs('laravel-wizard-tmp', 'test_temp_file.'.jpg());
        $this->app['db']->table('wizards')->insert([
            'payload' => '{"avatar_step":{"avatar":'.json_encode($this->getSerializedCachedFile()).'}}',
            'user_id' => 1,
        ]);

        // act
        $actual = $this->cache->get('avatar_step.avatar');

        // assert
        $this->assertIsTemporaryFile($actual);
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

    public function testSetAllData()
    {
        $this->authenticate();

        // act
        $this->cache->set(['step' => ['field' => 'data']]);

        // assert
        $this->assertDatabaseHas('wizards', [
            'payload' => '{"step":{"field":"data"},"_files":[]}',
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
            'payload' => '{"step":{"field":"data"},"_files":[],"_last_index":1}',
            'user_id' => 1,
        ]);
    }

    public function testSetDataWithFile()
    {
        $this->authenticate();

        // arrange
        CachedFile::setFakeFilename('test_temp_file');
        $file = UploadedFile::fake()->image('avatar.jpg');

        // act
        $this->cache->set([
            'other_step' => ['field' => 'data'],
            'avatar_step' => ['avatar' => $file],
        ]);

        // assert
        $this->assertDatabaseHas('wizards', [
            'payload' => '{"other_step":{"field":"data"},"avatar_step":{"avatar":'.json_encode($this->getSerializedCachedFile()).'},"_files":['.json_encode($this->tempFileFullPath()).']}',
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
            'payload' => '{"step":{"field":"data"},"_files":[]}',
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
            'payload' => '{"step":{"field":"data"},"_files":[],"_last_index":1}',
            'user_id' => 1,
        ]);

        // act
        $this->cache->clear();

        // assert
        $this->assertDatabaseMissing('wizards', [
            'payload' => '{"step":{"field":"data"},"_files":[],"_last_index":1}',
            'user_id' => 1,
        ]);
    }

    public function testClearDataAndFile()
    {
        $this->authenticate();

        // arrange
        $file = UploadedFile::fake()->image('avatar.jpg');
        $file->storeAs('laravel-wizard-tmp', 'test_temp_file.'.jpg());
        $this->app['db']->table('wizards')->insert([
            'payload' => '{"step":{"field":"data"},"_files":['.json_encode($this->tempFileFullPath()).'],"_last_index":1}',
            'user_id' => 1,
        ]);

        // act
        Storage::assertExists('laravel-wizard-tmp/test_temp_file.'.jpg());
        $this->cache->clear();

        // assert
        Storage::assertMissing('laravel-wizard-tmp/test_temp_file.'.jpg());
    }
}
