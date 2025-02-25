<?php

namespace Shettyanna\DbActivityLogger;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Shettyanna\DbActivityLogger\Http\Controllers\LogController;
use Shettyanna\DbActivityLogger\Listeners\QueryLogger;

class DbActivityLoggerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'db-activity-logger');

        Route::get('/db-activity-web', [LogController::class, 'index']);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/db-activity-logger.php' => config_path('db-activity-logger.php'),
            ], 'config');
        }

        DB::listen(function ($query) {
            $tableName = $this->getTableNameFromQuery($query->sql);
        
            if ($tableName === 'db_activity_log' || $tableName === 'sqlite_master') {
                return;
            }

            $logData = [
                'sql'        => $query->sql,
                'bindings'   => json_encode($query->bindings),
                'time'       => $query->time,
                'table_name' => $tableName,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            QueryLogger::log($logData);

            if (config('db-activity-logger.log_to_database')) {
                $existing = DB::table('db_activity_log')
                    ->where('sql', $query->sql)
                    ->where('table_name', $tableName)
                    ->first();

                if ($existing) {
                    DB::table('db_activity_log')
                        ->where('id', $existing->id)
                        ->update([
                            'bindings'   => $logData['bindings'],
                            'time'       => $logData['time'],
                            'hit_count'  => $existing->hit_count + 1,
                            'updated_at' => $logData['updated_at'],
                        ]);
                } else {
                    DB::table('db_activity_log')->insert([
                        'sql'        => $logData['sql'],
                        'table_name' => $logData['table_name'],
                        'bindings'   => $logData['bindings'],
                        'time'       => $logData['time'],
                        'hit_count'  => 1,
                        'created_at' => $logData['created_at'],
                        'updated_at' => $logData['updated_at'],
                    ]);
                }
            }
        });
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/db-activity-logger.php',
            'db-activity-logger'
        );

        $this->app->singleton('db-logger', function () {
            return new QueryLogger();
        });
    }

    protected function getTableNameFromQuery(string $sql): ?string
    {
        $sql = strtolower($sql);
        $patterns = [
            '/insert\s+into\s+["`]?(\w+)["`]?/i',
            '/update\s+["`]?(\w+)["`]?/i',
            '/delete\s+from\s+["`]?(\w+)["`]?/i',
            '/from\s+["`]?(\w+)["`]?/i',
        ];
    
        foreach ($patterns as $pattern) {
            preg_match($pattern, $sql, $matches);
            if (!empty($matches[1])) {
                return $matches[1];
            }
        }
    
        return null;
    }
}