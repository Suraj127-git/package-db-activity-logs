<?php

namespace Shettyanna\DbActivityLogger\Listeners;

use Illuminate\Support\Facades\Log;

class QueryLogger
{
    public static function log(array $data)
    {
        $tableName = $data['table'] ?? 'unknown_table';
        $logFile = config('db-activity-logger.log_file');
    
        Log::build([
            'driver' => 'single',
            'path' => storage_path("logs/{$logFile}"),
            'level' => 'info',
        ])->info("Query executed on {$tableName}", $data);
    }
}
