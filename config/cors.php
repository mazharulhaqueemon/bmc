<?php

return [

    'paths' => ['api/*', 'login', 'signup', 'user/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'], // Or your frontend URL: 'http://localhost:3000'

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];
