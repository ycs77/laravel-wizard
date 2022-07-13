<?php

namespace Ycs77\LaravelWizard\Test\Unit;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Ycs77\LaravelWizard\CachedFile;
use Ycs77\LaravelWizard\CachedFileSerializer;
use Ycs77\LaravelWizard\SessionStore;
use Ycs77\LaravelWizard\Test\Concerns\CachedFileTesting;
use Ycs77\LaravelWizard\Test\TestCase;

class SessionStoreTest extends TestCase
{
    use CachedFileTesting;

    /**
     * The wizard store instance.
     *
     * @var \Ycs77\LaravelWizard\SessionStore
     */
    protected $cache;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->cache = $this->app->makeWith(SessionStore::class, [
            'session' => $this->app['session.store'],
            'serializer' => $this->app->make(CachedFileSerializer::class),
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

    public function testGetDataWithFile()
    {
        // arrange
        $file = UploadedFile::fake()->image('avatar.jpg');
        $file->storeAs('laravel-wizard-tmp', 'test_temp_file.'.jpg());
        $this->session([
            'laravel_wizard.test' => [
                'avatar_step' => ['avatar' => $this->getSerializedCachedFile()],
            ],
        ]);

        // act
        $actual = $this->cache->get('avatar_step.avatar');

        // assert
        $this->assertIsTemporaryFile($actual);
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

    public function testSetAllData()
    {
        // arrange
        $expected = [
            'step' => ['field' => 'data'],
            '_files' => [],
        ];

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
            '_files' => [],
        ];

        // act
        $this->cache->set(['step' => ['field' => 'data']], 1);

        // assert
        $this->assertEquals($expected, $this->app['session']->get('laravel_wizard.test'));
    }

    public function testSetDataWithFile()
    {
        // arrange
        $expected = [
            'other_step' => ['field' => 'data'],
            'avatar_step' => ['avatar' => $this->getSerializedCachedFile()],
            '_files' => [$this->tempFileFullPath()],
        ];
        CachedFile::setFakeFilename('test_temp_file');
        $file = UploadedFile::fake()->image('avatar.jpg');

        // act
        $this->cache->set([
            'other_step' => ['field' => 'data'],
            'avatar_step' => ['avatar' => $file],
        ]);

        // assert
        $this->assertEquals($expected, $this->app['session']->get('laravel_wizard.test'));
    }

    public function testPutData()
    {
        // arrange
        $expected = [
            'step' => ['field' => 'data'],
            '_files' => [],
        ];

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
        $this->session([
            'laravel_wizard.test' => [
                'step' => ['field' => 'data'],
                '_files' => [],
            ],
        ]);

        // act
        $this->cache->clear();

        // assert
        $this->assertNull($this->app['session']->get('laravel_wizard.test'));
    }

    public function testClearDataAndFile()
    {
        // arrange
        $file = UploadedFile::fake()->image('avatar.jpg');
        $file->storeAs('laravel-wizard-tmp', 'test_temp_file.'.jpg());
        $this->session([
            'laravel_wizard.test' => [
                'step' => ['field' => 'data'],
                '_files' => [$this->tempFileFullPath()],
            ],
        ]);

        // act
        Storage::assertExists('laravel-wizard-tmp/test_temp_file.'.jpg());
        $this->cache->clear();

        // assert
        Storage::assertMissing('laravel-wizard-tmp/test_temp_file.'.jpg());
    }
}
