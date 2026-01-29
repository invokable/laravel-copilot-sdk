<?php

declare(strict_types=1);

use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Contracts\Factory;
use Revolution\Copilot\Facades\Copilot;

use function Revolution\Copilot\copilot;

beforeEach(function () {
    Copilot::clearResolvedInstances();
    Copilot::preventStrayRequests();
});

describe('copilot() helper', function () {
    test('string', function () {
        Copilot::fake('Hello World');

        $response = copilot('Hi');

        expect($response->content())->toBe('Hello World');
    });

    test('callable', function () {
        Copilot::fake('Hello World');

        $response = copilot(fn (CopilotSession $session) => $session->sendAndWait('Hi'));

        expect($response->content())->toBe('Hello World');
    });

    test('instance', function () {
        expect(copilot())->toBeInstanceOf(Factory::class);
    });
});
