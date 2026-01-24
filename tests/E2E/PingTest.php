<?php

declare(strict_types=1);

use Revolution\Copilot\Facades\Copilot;

beforeEach(function () {
    // Reset the facade before each test
    Copilot::clearResolvedInstances();
    Copilot::preventStrayRequests(allow: ['ping', 'status.get']);
});

test('ping() returns pong', function () {
    $response = Copilot::client()->ping();
    Copilot::client()->stop();

    expect($response)->toContain('pong');
});
