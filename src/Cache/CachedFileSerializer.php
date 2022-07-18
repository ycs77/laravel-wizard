<?php

namespace Ycs77\LaravelWizard\Cache;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;

class CachedFileSerializer
{
    /**
     * Serialize the cached file.
     *
     * @param  \Ycs77\LaravelWizard\Cache\CachedFile|\Illuminate\Http\UploadedFile  $file
     * @param  string|null  $filename
     * @param  string|null  $tmpDir
     * @return string
     */
    public function serializeFile($file, string $filename = null, string $tmpDir = null)
    {
        if ($file instanceof UploadedFile) {
            $file = new CachedFile($file, $filename, $tmpDir);
        }

        return CachedFile::class.':'.serialize($file);
    }

    /**
     * Unserialize the cached file.
     *
     * @param  string  $data
     * @return \Ycs77\LaravelWizard\Cache\CachedFile
     */
    public function unserializeFile(string $data)
    {
        return unserialize((string) str_replace(CachedFile::class.':', '', $data));
    }

    /**
     * Serialize the cached files of payload.
     *
     * @param  array  $data
     * @param  array  $cachedData
     * @param  string|null  $filename
     * @param  string|null  $tmpDir
     * @return array
     */
    public function serializePayloadFiles(array $data, array $cachedData, string $filename = null, string $tmpDir = null)
    {
        $files = $data['_files'] ?? $cachedData['_files'] ?? [];

        foreach ($data as $stepKey => $stepData) {
            if (is_array($stepData)) {
                foreach ($stepData as $stepDataKey => $stepInput) {
                    if ($stepInput instanceof UploadedFile) {
                        // check if is exists the uploaded temp file.
                        if ($serializedData = Arr::get($cachedData, "$stepKey.$stepDataKey")) {
                            // keep exists temp file.
                            $data[$stepKey][$stepDataKey] = $serializedData;
                        } else {
                            // store temp file.
                            $cachedFile = new CachedFile($stepInput, $filename, $tmpDir);
                            $data[$stepKey][$stepDataKey] = $this->serializeFile($cachedFile);
                            $files[] = $cachedFile->tmpFullPath();
                        }
                    }
                }
            }
        }

        $data['_files'] = $files;

        return $data;
    }

    /**
     * Unserialize the cached files of payload.
     *
     * @param  array  $data
     * @return array
     */
    public function unserializePayloadFiles(array $data)
    {
        foreach ($data as $stepKey => $stepData) {
            if (is_array($stepData)) {
                foreach ($stepData as $stepDataKey => $stepInput) {
                    if ($this->canUnserializeFile($stepInput)) {
                        $data[$stepKey][$stepDataKey] = $this->unserializeFile($stepInput)->file();
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Check the data is can be unserialized.
     *
     * @param  mixed  $data
     * @return bool
     */
    public function canUnserializeFile($data)
    {
        return is_string($data) && substr($data, 0, strlen(CachedFile::class.':')) === CachedFile::class.':';
    }

    /**
     * Clean the temporary files.
     *
     * @param  array  $files
     * @return void
     */
    public function clearTmpFiles(array $files = [])
    {
        foreach ($files as $path) {
            @unlink($path);
        }
    }
}
