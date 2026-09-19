<?php

return [
    'mode' => env('OFFLINE_POS_MODE', 'hosted'),
    'remote_url' => rtrim((string) env('OFFLINE_POS_REMOTE_URL', ''), '/'),
    'sync_token' => env('OFFLINE_POS_SYNC_TOKEN'),
    'source_id' => env('OFFLINE_POS_SOURCE_ID', env('APP_NAME', 'ONJECASA-LOCAL')),
    'timeout' => (int) env('OFFLINE_POS_SYNC_TIMEOUT', 20),
    'batch_size' => (int) env('OFFLINE_POS_SYNC_BATCH_SIZE', 25),
];
