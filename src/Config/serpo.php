<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Repository Namespace
    |--------------------------------------------------------------------------
    |
    | The default namespace for generated repository classes. Uses the
    | SERPO_REPOSITORY_NAMESPACE env variable. Falls back to "Repositories"
    | under the application's root namespace.
    |
    */
    'repository' => [
        'namespace' => env('SERPO_REPOSITORY_NAMESPACE', 'Repositories'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Service Namespace
    |--------------------------------------------------------------------------
    |
    | The default namespace for generated service classes. Uses the
    | SERPO_SERVICE_NAMESPACE env variable. Falls back to "Services"
    | under the application's root namespace.
    |
    */
    'service' => [
        'namespace' => env('SERPO_SERVICE_NAMESPACE', 'Services'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Criteria Namespace
    |--------------------------------------------------------------------------
    |
    | The default namespace for generated criteria classes. Uses the
    | SERPO_CRITERIA_NAMESPACE env variable. Falls back to "Criteria"
    | under the application's root namespace.
    |
    */
    'criteria' => [
        'namespace' => env('SERPO_CRITERIA_NAMESPACE', 'Criteria'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Enable or disable caching. Only "redis" driver is supported.
    |
    | ttl — Default cache lifetime in seconds (900 = 15minutes).
    |
    */
    'cache' => [
        'enabled' => env('SERPO_CACHE_ENABLED', false),
        'driver'  => env('SERPO_CACHE_DRIVER', 'redis'),
        'ttl'     => env('SERPO_CACHE_TTL', 900),
        'prefix'  => env('SERPO_CACHE_PREFIX', 'serpo'),
    ],

];
