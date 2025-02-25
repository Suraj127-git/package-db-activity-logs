<?php

return [
    'log_to_database' => env('DB_ACTIVITY_LOG_TO_DATABASE', false),
    'log_file' => env('DB_ACTIVITY_LOG_FILE', 'db-activity.log'),
    'table_name' => 'db_activity_log',
];