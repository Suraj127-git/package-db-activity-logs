<?php

use Shettyanna\DbActivityLogger\DbActivityLoggerServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Database\Events\QueryExecuted;

beforeEach(function () {
    // Set up any necessary preconditions for your tests
});

it('registers the service provider', function () {
    $provider = new DbActivityLoggerServiceProvider(app());
    $this->assertTrue($provider->register());
});

it('logs queries correctly', function () {
    Event::fake();

    // Simulate a query execution
    DB::table('users')->get();

    // Assert that the QueryExecuted event was dispatched
    Event::assertDispatched(QueryExecuted::class);
});