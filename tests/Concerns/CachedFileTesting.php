<?php

namespace Ycs77\LaravelWizard\Test\Concerns;

use Illuminate\Support\Facades\Storage;

trait CachedFileTesting
{
    protected function getSerializedCachedFile()
    {
        if (jpg() === 'jpeg') {
            return "Ycs77\LaravelWizard\Cache\CachedFile:O:36:\"Ycs77\LaravelWizard\Cache\CachedFile\":5:{s:7:\"\x00*\x00disk\";s:5:\"local\";s:11:\"\x00*\x00filename\";s:19:\"test_temp_file.jpeg\";s:9:\"\x00*\x00tmpDir\";s:18:\"laravel-wizard-tmp\";s:10:\"\x00*\x00tmpPath\";s:38:\"laravel-wizard-tmp/test_temp_file.jpeg\";s:11:\"\x00*\x00mimeType\";s:10:\"image/jpeg\";}";
        }

        return "Ycs77\LaravelWizard\Cache\CachedFile:O:36:\"Ycs77\LaravelWizard\Cache\CachedFile\":5:{s:7:\"\x00*\x00disk\";s:5:\"local\";s:11:\"\x00*\x00filename\";s:18:\"test_temp_file.jpg\";s:9:\"\x00*\x00tmpDir\";s:18:\"laravel-wizard-tmp\";s:10:\"\x00*\x00tmpPath\";s:37:\"laravel-wizard-tmp/test_temp_file.jpg\";s:11:\"\x00*\x00mimeType\";s:10:\"image/jpeg\";}";
    }

    protected function tempFileFullPath($num = '')
    {
        return str_replace('\\', '/', Storage::path('laravel-wizard-tmp/test_temp_file'.$num.'.'.jpg()));
    }
}
