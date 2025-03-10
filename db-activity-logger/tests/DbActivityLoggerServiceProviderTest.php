<?php

namespace Tests;

use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Shettyanna\DbActivityLogger\DbActivityLoggerServiceProvider;

class DbActivityLoggerTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Force the connection to use SQLite
        $this->app['config']->set('database.default', 'sqlite');
        $this->app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        
        // Disable logging during setup
        $this->app['config']->set('db-activity-logger.log_to_database', false);
        
        // Create the test tables
        Schema::create('db_activity_log', function (Blueprint $table) {
            $table->id();
            $table->text('sql');
            $table->text('bindings');
            $table->string('table_name');
            $table->integer('time');
            $table->integer('hit_count')->default(1);
            $table->timestamps();
        });
        
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
        
        // Enable logging after setup
        $this->app['config']->set('db-activity-logger.log_to_database', true);
    }
    
    /**
     * Define environment setup.
     *
     * @param  Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        
        // Configure the package
        $app['config']->set('db-activity-logger.log_to_database', true);
    }
    
    /**
     * Get package providers.
     *
     * @param  Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            DbActivityLoggerServiceProvider::class,
        ];
    }
}

// Convert class-based tests to Pest tests
uses(DbActivityLoggerTest::class)->group('db-activity-logger');

test('database connection is sqlite', function () {
    expect(DB::connection()->getDriverName())->toBe('sqlite');
});

test('it registers the service provider', function () {
    expect($this->app->bound('db-logger'))->toBeTrue();
});

test('it logs queries correctly', function () {
    // Clear any existing logs first
    DB::table('db_activity_log')->truncate();
    
    // Execute a query that should be logged
    DB::table('users')->insert(['name' => 'Test User']);
    
    // Get the latest log entry
    $logEntry = DB::table('db_activity_log')->orderBy('id', 'desc')->first();
    
    // Debug what was actually logged
    expect($logEntry)->not->toBeNull();
    
    // The SQL should contain the insert statement
    expect($logEntry->sql)->toContain('insert into "users"');
    expect($logEntry->table_name)->toBe('users');
});

test('it increments hit count for duplicate queries', function () {
    // Clear any existing logs first
    DB::table('db_activity_log')->truncate();
    
    // Run identical queries that should be grouped
    $sql = 'SELECT * FROM users';
    DB::select($sql);
    DB::select($sql);
    
    // Check the log
    $logEntry = DB::table('db_activity_log')->where('sql', 'LIKE', '%SELECT * FROM users%')->first();
    
    // Debug what we found
    if (!$logEntry) {
        $allLogs = DB::table('db_activity_log')->get();
        dump('Available logs:', $allLogs->toArray());
    }
    
    expect($logEntry)->not->toBeNull();
    expect($logEntry->hit_count)->toBe(2);
});