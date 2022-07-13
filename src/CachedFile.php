<?php

namespace Ycs77\LaravelWizard;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CachedFile
{
    /**
     * The temporary storage disk.
     *
     * @var string
     */
    protected $disk;

    /**
     * The uploaded file instance.
     *
     * @var \Illuminate\Http\UploadedFile|null
     */
    protected $file;

    /**
     * The temporary filename.
     *
     * @var string
     */
    protected $filename;

    /**
     * The fake temporary filename for testing.
     *
     * @var string|null
     */
    protected static $fakeFilename;

    /**
     * The uploaded file temporary directory in app storage.
     *
     * @var string
     */
    protected $tmpDir;

    /**
     * The fake temporary directory for testing.
     *
     * @var string|null
     */
    protected static $fakeTmpDir;

    /**
     * The uploaded file temporary path in app storage.
     *
     * @var string
     */
    protected $tmpPath;

    /**
     * The uploaded file mimeType.
     *
     * @var string
     */
    protected $mimeType;

    /**
     * Create a new cached file instance.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  string|null  $filename
     * @param  string|null  $tmpDir
     * @return void
     */
    public function __construct(UploadedFile $file, string $filename = null, string $tmpDir = null)
    {
        $this->disk = 'local';
        $this->file = $file;
        $this->filename = $this->generateFilename($file, $filename);
        $this->tmpDir = $tmpDir ?? static::$fakeTmpDir ?? 'laravel-wizard-tmp';
        $this->mimeType = $file->getMimeType();
    }

    /**
     * Get the uploaded file instance.
     *
     * @return \Illuminate\Http\UploadedFile|null
     */
    public function file()
    {
        return $this->file;
    }

    /**
     * Get the temporary filename.
     *
     * @return string
     */
    public function filename()
    {
        return $this->filename;
    }

    /**
     * Generate the temporary filename.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  string|null  $filename
     * @return string
     */
    public function generateFilename(UploadedFile $file, string $filename = null)
    {
        if ($filename = $filename ?? static::$fakeFilename) {
            if ($extension = $file->extension()) {
                $filename .= '.'.$extension;
            }

            return $filename;
        }

        return $file->hashName();
    }

    /**
     * Set the fake temporary filename for testing.
     *
     * @param  string  $filename
     * @return void
     */
    public static function setFakeFilename(string $filename)
    {
        static::$fakeFilename = $filename;
    }

    /**
     * Get the fake temporary directory for testing.
     *
     * @return string
     */
    public function tmpDir()
    {
        return $this->tmpDir;
    }

    /**
     * Set the fake temporary directory for testing.
     *
     * @param  string  $dir
     * @return void
     */
    public static function setFakeTmpDir(string $dir)
    {
        static::$fakeTmpDir = $dir;
    }

    /**
     * Get the temporary file path.
     *
     * @return string
     */
    public function tmpPath()
    {
        return $this->tmpPath;
    }

    /**
     * Set the temporary file path.
     *
     * @param  string  $path
     * @return $this
     */
    public function setTmpPath(string $path)
    {
        $this->tmpPath = $path;

        return $this;
    }

    /**
     * Get the full temporary file path.
     *
     * @return string
     */
    public function tmpFullPath()
    {
        return str_replace('\\', '/', $this->storage()->path($this->tmpPath));
    }

    /**
     * Get the temporary file MIME type.
     *
     * @return string
     */
    public function mimeType()
    {
        return $this->mimeType;
    }

    /**
     * Serialize the cached file.
     *
     * @return void
     */
    public function serialize()
    {
        if (! $this->tmpPath) {
            $this->tmpPath = $this->file->storeAs($this->tmpDir, $this->filename, [
                'disk' => $this->disk,
            ]);
        }
    }

    /**
     * Unserialize the cached file.
     *
     * @return void
     */
    public function unserialize()
    {
        if (is_file($this->tmpFullPath()) && ! $this->file) {
            $tmpFileInOs = tempnam(sys_get_temp_dir(), 'php');
            copy($this->tmpFullPath(), $tmpFileInOs);
            $this->file = new UploadedFile($tmpFileInOs, basename($tmpFileInOs), $this->mimeType);
        }
    }

    /**
     * Get the stoage disk instance.
     *
     * @return \Illuminate\Filesystem\FilesystemAdapter
     */
    protected function storage()
    {
        return Storage::disk($this->disk);
    }

    /**
     * Get the stoage disk.
     *
     * @return string
     */
    public function disk()
    {
        return $this->disk;
    }

    /**
     * Set the stoage disk.
     *
     * @param  string  $disk
     * @return $this
     */
    public function setDisk(string $disk)
    {
        $this->disk = $disk;

        return $this;
    }

    /**
     * Prepare the object for serialization.
     *
     * @return array
     */
    public function __sleep()
    {
        $this->serialize();

        return ['disk', 'filename', 'tmpDir', 'tmpPath', 'mimeType'];
    }

    /**
     * When a CachedFile is being unserialized.
     *
     * @return void
     */
    public function __wakeup()
    {
        $this->unserialize();
    }
}
