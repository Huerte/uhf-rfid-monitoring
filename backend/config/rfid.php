<?php

return [
    'bridge_url'     => env('RFID_BRIDGE_URL', 'http://127.0.0.1:8000'),
    'bridge_timeout' => env('RFID_BRIDGE_TIMEOUT', 10),
    'reader_ip'      => env('RFID_READER_IP', '192.168.1.168'),
    'reader_port'    => (int) env('RFID_READER_PORT', 8160),
];
