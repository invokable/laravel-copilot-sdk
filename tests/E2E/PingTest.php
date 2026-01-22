<?php

declare(strict_types=1);

use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Facades\Copilot;

beforeEach(function () {
    // Reset the facade before each test
    Copilot::clearResolvedInstances();
    Copilot::preventStrayRequests(allow: ['ping']);
});

test('ping() returns pong', function () {
    Copilot::client()->start();
    $response = Copilot::client()->ping();

    expect($response)->toContain('pong');
});
