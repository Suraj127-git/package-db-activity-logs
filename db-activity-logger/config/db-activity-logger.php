<?php

return [
    // Other configurations...
    
    'channel' => env('DB_ACTIVITY_LOG_CHANNEL', 'stack'), // Default to stack
    'log_file' => env('DB_ACTIVITY_LOG_FILE', 'laravel.log'), // Default log file
    'log_to_database' => env('DB_ACTIVITY_LOG_TO_DATABASE', false), // Enable database logging
];