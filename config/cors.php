<?php

return [

    /*
    | TanamYuk uses Authorization: Bearer tokens. Same-origin requests from
    | the Laravel host do not need credentialed CORS. Cross-origin clients
    | may call /api/* with a bearer token.
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
