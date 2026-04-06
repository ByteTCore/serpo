<?php

return [
    'repository' => [
        'namespace' => env('SERPO_REPOSITORY_NAMESPACE', 'Repositories'),
    ],

    'service' => [
        'namespace' => env('SERPO_SERVICE_NAMESPACE', 'Services'),
    ],

    'criteria' => [
        'namespace' => env('SERPO_CRITERIA_NAMESPACE', 'Criteria'),
    ],
];
