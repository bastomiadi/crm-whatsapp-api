<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Chatery WhatsApp API Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the Chatery WhatsApp API connection settings.
    |
    */

    'api_url' => env('CHATERY_API_URL', 'http://localhost:3000'),
    'api_key' => env('CHATERY_API_KEY', ''),
    'timeout' => env('CHATERY_TIMEOUT', 30),
    
    /*
    |--------------------------------------------------------------------------
    | WebSocket Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the WebSocket connection for real-time messaging.
    | The WebSocket URL is derived from the API URL by replacing http with ws.
    */
    
    'websocket_url' => env('CHATERY_WS_URL', 'http://localhost:3000'),
];
