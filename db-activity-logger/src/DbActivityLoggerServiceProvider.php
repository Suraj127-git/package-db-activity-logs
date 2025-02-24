<?php

namespace Shettyanna\DbActivityLogger;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Shettyanna\DbActivityLogger\Listeners\QueryLogger;

class DbActivityLoggerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/db-activity-logger.php' => config_path('db-activity-logger.php'),
            ], 'config');
        }

        // Store query hit count for each table
        $queryCounts = [];

        DB::listen(function ($query) use (&$queryCounts) {
            $logData = [
                'sql'      => $query->sql,
                'bindings' => $query->bindings,
                'time'     => $query->time,
            ];
        
            $tableName = $this->getTableNameFromQuery($query->sql);
            $logData['table_name'] = $tableName;
        
            if (isset($queryCounts[$tableName])) {
                $queryCounts[$tableName]++;
            } else {
                $queryCounts[$tableName] = 1;
            }
        
            $logData['hit_count'] = $queryCounts[$tableName];
        
            // Log to file
            QueryLogger::log($logData);
        
            // Log to database if enabled
            if (config('db-activity-logger.log_to_database')) {
                \DB::table('db_activity_log')->updateOrInsert(
                    ['sql' => $query->sql, 'table_name' => $tableName],
                    [
                        'bindings' => json_encode($query->bindings),
                        'time' => $query->time,
                        'hit_count' => $logData['hit_count'],
                        'updated_at' => now(),
                    ]
                );
            }
        });
    }

    public function register()
    {
        // Merge default configuration
        $this->mergeConfigFrom(
            __DIR__.'/../config/db-activity-logger.php', 'db-activity-logger'
        );
    }

    /**
     * Get the table name from a given SQL query
     *
     * @param string $sql The SQL query
     * @return string|null The table name (or null if not found)
     */
    protected function getTableNameFromQuery(string $sql): ?string
    {
        // This is a naive implementation and won't work for all cases
        // e.g. subqueries, joins, etc. You may need to improve it
        // depending on your use case.
        preg_match('/FROM\s+([\w\d_]+)/', $sql, $matches);
        return $matches[1] ?? null;
    }
}
