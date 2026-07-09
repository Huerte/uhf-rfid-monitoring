<?php

return [
    'bridge_url'      => env('RFID_BRIDGE_URL', 'http://127.0.0.1:8001'),
    'bridge_timeout'  => env('RFID_BRIDGE_TIMEOUT', 10),

    'reader_ip'       => env('RFID_READER_IP', '192.168.1.168'),
    'reader_port'     => (int) env('RFID_READER_PORT', 8160),


    'antennas'        => array_map(
        'intval',
        array_filter(
            explode(',', env('RFID_ANTENNAS', '1')),
            fn ($v) => is_numeric(trim($v))
        )
    ),

    'default_antenna' => (int) env('RFID_DEFAULT_ANTENNA', 1),
];
