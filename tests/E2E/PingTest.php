<?php

declare(strict_types=1);

use Revolution\Copilot\Facades\Copilot;

beforeEach(function () {
    // Reset the facade before each test
    Copilot::clearResolvedInstances();
    Copilot::preventStrayRequests(allow: ['ping']);
});

test('ping() returns pong', function () {
    $response = Copilot::client()->start()->ping();

    expect($response)->toContain('pong');
});
