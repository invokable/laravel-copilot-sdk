<?php

declare(strict_types=1);

use Revolution\Copilot\Facades\Copilot;

beforeEach(function () {
    Copilot::clearResolvedInstances();
    Copilot::preventStrayRequests(allow: [
        'ping',
        'status.get',
        'auth.getStatus',
        'models.list',
    ]);

    Copilot::client()->start();
});

afterAll(function () {
    Copilot::client()->stop();
});

test('client getStatus', function () {
    $response = Copilot::client()->getStatus();

    expect($response->protocolVersion)->toBeInt();
});

test('client getAuthStatus', function () {
    $response = Copilot::client()->getAuthStatus();

    expect($response->isAuthenticated)->toBeTrue();
});

test('client listModels', function () {
    $response = Copilot::client()->listModels();

    expect($response)->toBeArray();
});
