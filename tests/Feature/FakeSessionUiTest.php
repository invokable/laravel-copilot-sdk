<?php

declare(strict_types=1);

use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Enums\ElicitationAction;
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\InputOptions;
use Revolution\Copilot\Types\Rpc\SessionUiElicitationResult;
use Revolution\Copilot\Types\SessionCapabilities;

beforeEach(function () {
    Copilot::clearResolvedInstances();
    Copilot::preventStrayRequests();
});

describe('FakeSession UI methods', function () {
    it('capabilities returns empty SessionCapabilities', function () {
        Copilot::fake('test');

        Copilot::start(function (CopilotSession $session) {
            $caps = $session->capabilities();

            expect($caps)->toBeInstanceOf(SessionCapabilities::class)
                ->and($caps->supportsElicitation())->toBeFalse();
        });
    });

    it('confirm returns false', function () {
        Copilot::fake('test');

        Copilot::start(function (CopilotSession $session) {
            expect($session->confirm('Deploy?'))->toBeFalse();
        });
    });

    it('select returns null', function () {
        Copilot::fake('test');

        Copilot::start(function (CopilotSession $session) {
            expect($session->select('Pick', ['a', 'b']))->toBeNull();
        });
    });

    it('input returns null', function () {
        Copilot::fake('test');

        Copilot::start(function (CopilotSession $session) {
            expect($session->input('Name?'))->toBeNull();
        });
    });

    it('input with InputOptions returns null', function () {
        Copilot::fake('test');

        Copilot::start(function (CopilotSession $session) {
            expect($session->input('Name?', new InputOptions(maxLength: 50)))->toBeNull();
        });
    });

    it('input with array options returns null', function () {
        Copilot::fake('test');

        Copilot::start(function (CopilotSession $session) {
            expect($session->input('Name?', ['maxLength' => 50]))->toBeNull();
        });
    });

    it('elicitation returns cancel result', function () {
        Copilot::fake('test');

        Copilot::start(function (CopilotSession $session) {
            $result = $session->elicitation('msg', ['type' => 'object']);

            expect($result)->toBeInstanceOf(SessionUiElicitationResult::class)
                ->and($result->action)->toBe(ElicitationAction::CANCEL);
        });
    });
});
