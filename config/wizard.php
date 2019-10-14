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
    | Supported: "session", "database"
    |
    */

    'driver' => 'session',

    /*
    |--------------------------------------------------------------------------
    | Wizard Cache Database Connection
    |--------------------------------------------------------------------------
    |
    | When using the "database" or "redis" wizard cache drivers, you may
    | specify a connection that should be used to manage these wizard caches.
    | This should correspond to a connection in your database configuration
    | options.
    |
    */

    'connection' => env('WIZARD_CACHE_CONNECTION', null),

    /*
    |--------------------------------------------------------------------------
    | Wizard Cache Database Table
    |--------------------------------------------------------------------------
    |
    | When using the "database" wizard cache driver, you may specify the table
    | we should use to manage the wizard caches. Of course, a sensible default
    | is provided for you; however, you are free to change this as needed.
    |
    */

    'table' => 'wizards',

];
