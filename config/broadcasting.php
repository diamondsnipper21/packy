<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Broadcaster
    |--------------------------------------------------------------------------
    |
    | This option controls the default broadcaster that will be used by the
    | framework when an event needs to be broadcast. You may set this to
    | any of the connections defined in the "connections" array below.
    |
    | Supported: "pusher", "redis", "log", "null"
    |
    */

    'default' => env('BROADCAST_DRIVER', 'pusher'),

    /*
    |--------------------------------------------------------------------------
    | Broadcast Connections
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the broadcast connections that will be used
    | to broadcast events to other systems or over websockets. Samples of
    | each available type of connection are provided inside this array.
    |
    */

    'connections' => [

        'pusher' => [
            'driver' => 'pusher',
            'key' => env('PUSHER_APP_KEY', '1ca6ac28164f32071ec6'),
            'secret' => env('PUSHER_APP_SECRET', '6196755106a9a584ace4'),
            'app_id' => env('PUSHER_APP_ID', '1755583'),
            'options' => [
                'cluster'    => 'eu',
                'encrypted'  => true,
//                'host'       => env('WEBSOCKET_BROADCAST_HOST', '127.0.0.1'),
//                'port'       => env('LARAVEL_WEBSOCKETS_PORT', 6001),
//                'scheme'     => 'http',
//                'useTLS'     => true
            ],
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
        ],

        'log' => [
            'driver' => 'log',
        ],

        'null' => [
            'driver' => 'null',
        ],

    ],

];
