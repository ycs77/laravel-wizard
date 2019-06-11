<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Namespace
    |--------------------------------------------------------------------------
    |
    | This value is the namespaces for artisan make wizard.
    |
    */

    'namespace' => [
        'controllers' => 'App\Http\Controllers',
        'steps' => 'App\Steps',
    ],

    /*
    |--------------------------------------------------------------------------
    | The Steps View Path
    |--------------------------------------------------------------------------
    |
    | This value is the steps view path prefix.
    |
    */

    'view_path' => 'steps',

    /*
    |--------------------------------------------------------------------------
    | Auto Append Route
    |--------------------------------------------------------------------------
    |
    | This value is can control auto append route to web route
    | (routes/web.php).
    |
    */

    'append_route' => true,

    /*
    |--------------------------------------------------------------------------
    | Wizard Cache
    |--------------------------------------------------------------------------
    |
    | This option controls the wizard request data cache. If cache is enabled,
    | the data will be stored in the cache first when the step is executed,
    | and the data will be officially stored at the end of all steps. If it is
    | disabled, the data will be saved at the end of each step.
    |
    */

    'cache' => true,

    /*
    |--------------------------------------------------------------------------
    | Wizard Cache Default Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the default wizard cache "driver".
    |
    | Supported: "session"
    |
    */

    'driver' => 'session',

];
