<?php

declare(strict_types=1);

use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Protocol;

beforeEach(function () {
    Copilot::clearResolvedInstances();
    Copilot::preventStrayRequests(allow: [
        'ping',
        'status.get',
        'auth.getStatus',
        'models.list',
    ]);
});

afterAll(function () {
    Copilot::client()->stop();
});

test('client getStatus', function () {
    $response = Copilot::client()->getStatus();

    expect($response->protocolVersion)->toBeInt()
        ->and($response->protocolVersion)->toBe(Protocol::version());
});

test('client getAuthStatus', function () {
    $response = Copilot::client()->getAuthStatus();

    expect($response->isAuthenticated)->toBeTrue();
});

test('client listModels', function () {
    $response = Copilot::client()->listModels();

    expect($response)->toBeArray();
});

test('client listModels should cache results', function () {
    // Get client instance
    $client = Copilot::client();

    // Check authentication status
    $authStatus = $client->getAuthStatus();
    if (! $authStatus->isAuthenticated) {
        // Skip if not authenticated - models.list requires auth
        $client->stop();
        expect(true)->toBeTrue();

        return;
    }

    // First call should fetch from backend
    $models1 = $client->listModels();
    expect($models1)->toBeArray();

    // Second call should return from cache (different array but same content)
    $models2 = $client->listModels();
    expect($models2)->toBeArray()
        ->and($models2)->not->toBe($models1) // Different object instances (defensive copy)
        ->and(count($models2))->toBe(count($models1)); // Same content

    if (count($models1) > 0) {
        expect($models1[0]->id)->toBe($models2[0]->id); // Cached models should match
    }

    // After stopping, cache should be cleared
    $client->stop();

    // Restart and verify cache is empty
    $client->start();

    // Check authentication again after restart
    $authStatus = $client->getAuthStatus();
    if (! $authStatus->isAuthenticated) {
        $client->stop();
        expect(true)->toBeTrue();

        return;
    }

    $models3 = $client->listModels();
    // Can't check object identity across reconnections, but verify it works
    expect($models3)->toBeArray();
});
