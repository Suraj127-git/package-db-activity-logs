<?php

use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Shettyanna\DbActivityLogger\DbActivityLoggerServiceProvider;

class DbActivityLoggerServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [DbActivityLoggerServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Use in-memory SQLite for cleaner testing
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
        $app['config']->set('db-activity-logger.log_to_database', true);
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Disable logging during setup
        config(['db-activity-logger.log_to_database' => false]);

        // Run migration manually
        include_once __DIR__.'/../database/migrations/2025_02_24_000000_create_db_activity_log_table.php';
        (new \CreateDbActivityLogTable())->up();

        // Create users table
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        // Re-enable logging
        config(['db-activity-logger.log_to_database' => true]);
    }

    /** @test */
    public function it_registers_the_service_provider()
    {
        $this->assertTrue($this->app->bound('db-logger'));
    }

    /** @test */
    public function it_logs_queries_correctly()
    {
        DB::table('users')->insert(['name' => 'Test User']);
        $logEntry = DB::table('db_activity_log')->first();

        $this->assertNotNull($logEntry);
        $this->assertStringContainsString('insert into "users"', $logEntry->sql);
        $this->assertEquals('users', $logEntry->table_name);
    }

    /** @test */
    public function it_increments_hit_count_for_duplicate_queries()
    {
        DB::table('users')->get();
        DB::table('users')->get();

        $logEntry = DB::table('db_activity_log')->first();
        $this->assertEquals(2, $logEntry->hit_count);
    }
}