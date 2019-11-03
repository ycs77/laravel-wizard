<?php

namespace Ycs77\LaravelWizard\Exceptions;

use Exception;
use Throwable;

class InternalException extends Exception
{
    public function __construct(string $message = '', int $code = 0, Throwable $previous = null)
    {
        if (!config('app.debug')) {
            abort($code);
        }

        parent::__construct($message, $code, $previous);
    }
}
