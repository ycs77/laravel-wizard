<?php

if (! function_exists('jpg')) {
    /**
     * Polyfill guess .jpg / .jpeg extension.
     *
     * @return string
     */
    function jpg()
    {
        if (app()->bound('test.polyfill-jpg')) {
            return app('test.polyfill-jpg');
        }

        if (class_exists(\Symfony\Component\Mime\MimeTypes::class)) {
            $jpg = \Symfony\Component\Mime\MimeTypes::getDefault()->getExtensions('image/jpeg')[0];
            app()->instance('test.polyfill-jpg', $jpg);

            return $jpg;
        }

        if (class_exists(\Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser::class)) {
            $jpg = \Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuessers::getInstance()->guess('image/jpeg');
            app()->instance('test.polyfill-jpg', $jpg);

            return $jpg;
        }

        return 'jpg';
    }
}
