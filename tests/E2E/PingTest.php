<?php

declare(strict_types=1);

use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Facades\Copilot;

beforeEach(function () {
    // Reset the facade before each test
    Copilot::clearResolvedInstances();
    Copilot::preventStrayRequests(allow: ['session.create', 'session.destroy', 'ping']);
});

test('ping() returns pong', function () {
    $response = Copilot::start(function (CopilotSession $session) {
        return Copilot::getClient()->ping();
    });

    expect($response)->toContain('pong');
});
