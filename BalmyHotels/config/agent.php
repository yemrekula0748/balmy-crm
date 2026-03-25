<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Agent API Key
    |--------------------------------------------------------------------------
    | Windows IT Envanter Agent'ı bu key ile kimlik doğrular.
    | agent/config.py içindeki API_KEY ile aynı olmalıdır.
    */
    'key' => env('AGENT_API_KEY', ''),
];
