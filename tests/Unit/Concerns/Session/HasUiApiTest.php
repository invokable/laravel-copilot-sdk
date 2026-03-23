<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\ElicitationAction;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Session;
use Revolution\Copilot\Types\InputOptions;
use Revolution\Copilot\Types\Rpc\SessionUiElicitationResult;
use Revolution\Copilot\Types\SessionCapabilities;

describe('HasUiApi', function () {
    it('returns empty capabilities by default', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $session = new Session('test-session', $mockClient);

        $caps = $session->capabilities();

        expect($caps)->toBeInstanceOf(SessionCapabilities::class)
            ->and($caps->supportsElicitation())->toBeFalse();
    });

    it('can set capabilities from array', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $session = new Session('test-session', $mockClient);

        $session->setCapabilities(['ui' => ['elicitation' => true]]);

        expect($session->capabilities()->supportsElicitation())->toBeTrue();
    });

    it('can set null capabilities', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $session = new Session('test-session', $mockClient);

        $session->setCapabilities(null);

        expect($session->capabilities()->supportsElicitation())->toBeFalse();
    });

    it('throws RuntimeException when elicitation is not supported', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $session = new Session('test-session', $mockClient);

        expect(fn () => $session->confirm('Deploy?'))
            ->toThrow(RuntimeException::class, 'Elicitation is not supported by the host');
    });

    it('throws RuntimeException for select when not supported', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $session = new Session('test-session', $mockClient);

        expect(fn () => $session->select('Pick', ['a', 'b']))
            ->toThrow(RuntimeException::class, 'Elicitation is not supported by the host');
    });

    it('throws RuntimeException for input when not supported', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $session = new Session('test-session', $mockClient);

        expect(fn () => $session->input('Name?'))
            ->toThrow(RuntimeException::class, 'Elicitation is not supported by the host');
    });

    it('throws RuntimeException for raw elicitation when not supported', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $session = new Session('test-session', $mockClient);

        expect(fn () => $session->elicitation('msg', ['type' => 'object']))
            ->toThrow(RuntimeException::class, 'Elicitation is not supported by the host');
    });

    describe('supported path', function () {
        it('confirm returns true when user accepts', function () {
            $mockClient = Mockery::mock(JsonRpcClient::class);
            $mockClient->shouldReceive('request')
                ->once()
                ->with(
                    'session.ui.elicitation',
                    Mockery::on(fn ($params) => $params['sessionId'] === 'test-session'
                        && $params['message'] === 'Deploy?'
                        && $params['requestedSchema']['properties']['confirmed']['type'] === 'boolean'),
                )
                ->andReturn(['action' => 'accept', 'content' => ['confirmed' => true]]);

            $session = new Session('test-session', $mockClient);
            $session->setCapabilities(['ui' => ['elicitation' => true]]);

            expect($session->confirm('Deploy?'))->toBeTrue();
        });

        it('confirm returns false when user declines', function () {
            $mockClient = Mockery::mock(JsonRpcClient::class);
            $mockClient->shouldReceive('request')
                ->once()
                ->with('session.ui.elicitation', Mockery::any())
                ->andReturn(['action' => 'decline']);

            $session = new Session('test-session', $mockClient);
            $session->setCapabilities(['ui' => ['elicitation' => true]]);

            expect($session->confirm('Deploy?'))->toBeFalse();
        });

        it('confirm returns false when user cancels', function () {
            $mockClient = Mockery::mock(JsonRpcClient::class);
            $mockClient->shouldReceive('request')
                ->once()
                ->with('session.ui.elicitation', Mockery::any())
                ->andReturn(['action' => 'cancel']);

            $session = new Session('test-session', $mockClient);
            $session->setCapabilities(['ui' => ['elicitation' => true]]);

            expect($session->confirm('Deploy?'))->toBeFalse();
        });

        it('select returns chosen value when user accepts', function () {
            $mockClient = Mockery::mock(JsonRpcClient::class);
            $mockClient->shouldReceive('request')
                ->once()
                ->with(
                    'session.ui.elicitation',
                    Mockery::on(fn ($params) => $params['sessionId'] === 'test-session'
                        && $params['message'] === 'Pick env'
                        && $params['requestedSchema']['properties']['selection']['enum'] === ['staging', 'production']),
                )
                ->andReturn(['action' => 'accept', 'content' => ['selection' => 'production']]);

            $session = new Session('test-session', $mockClient);
            $session->setCapabilities(['ui' => ['elicitation' => true]]);

            expect($session->select('Pick env', ['staging', 'production']))->toBe('production');
        });

        it('select returns null when user declines', function () {
            $mockClient = Mockery::mock(JsonRpcClient::class);
            $mockClient->shouldReceive('request')
                ->once()
                ->with('session.ui.elicitation', Mockery::any())
                ->andReturn(['action' => 'decline']);

            $session = new Session('test-session', $mockClient);
            $session->setCapabilities(['ui' => ['elicitation' => true]]);

            expect($session->select('Pick env', ['staging', 'production']))->toBeNull();
        });

        it('select returns null when user cancels', function () {
            $mockClient = Mockery::mock(JsonRpcClient::class);
            $mockClient->shouldReceive('request')
                ->once()
                ->with('session.ui.elicitation', Mockery::any())
                ->andReturn(['action' => 'cancel']);

            $session = new Session('test-session', $mockClient);
            $session->setCapabilities(['ui' => ['elicitation' => true]]);

            expect($session->select('Pick env', ['staging', 'production']))->toBeNull();
        });

        it('input returns entered text when user accepts', function () {
            $mockClient = Mockery::mock(JsonRpcClient::class);
            $mockClient->shouldReceive('request')
                ->once()
                ->with(
                    'session.ui.elicitation',
                    Mockery::on(fn ($params) => $params['sessionId'] === 'test-session'
                        && $params['message'] === 'Enter name'
                        && $params['requestedSchema']['properties']['value']['type'] === 'string'),
                )
                ->andReturn(['action' => 'accept', 'content' => ['value' => 'Laravel']]);

            $session = new Session('test-session', $mockClient);
            $session->setCapabilities(['ui' => ['elicitation' => true]]);

            expect($session->input('Enter name'))->toBe('Laravel');
        });

        it('input passes InputOptions constraints in schema', function () {
            $mockClient = Mockery::mock(JsonRpcClient::class);
            $mockClient->shouldReceive('request')
                ->once()
                ->with(
                    'session.ui.elicitation',
                    Mockery::on(fn ($params) => $params['requestedSchema']['properties']['value']['maxLength'] === 50
                        && $params['requestedSchema']['properties']['value']['minLength'] === 2),
                )
                ->andReturn(['action' => 'accept', 'content' => ['value' => 'hi']]);

            $session = new Session('test-session', $mockClient);
            $session->setCapabilities(['ui' => ['elicitation' => true]]);

            $result = $session->input('Enter name', new InputOptions(maxLength: 50, minLength: 2));

            expect($result)->toBe('hi');
        });

        it('input returns null when user declines', function () {
            $mockClient = Mockery::mock(JsonRpcClient::class);
            $mockClient->shouldReceive('request')
                ->once()
                ->with('session.ui.elicitation', Mockery::any())
                ->andReturn(['action' => 'decline']);

            $session = new Session('test-session', $mockClient);
            $session->setCapabilities(['ui' => ['elicitation' => true]]);

            expect($session->input('Enter name'))->toBeNull();
        });

        it('raw elicitation returns SessionUiElicitationResult', function () {
            $schema = ['type' => 'object', 'properties' => ['age' => ['type' => 'integer']]];

            $mockClient = Mockery::mock(JsonRpcClient::class);
            $mockClient->shouldReceive('request')
                ->once()
                ->with(
                    'session.ui.elicitation',
                    Mockery::on(fn ($params) => $params['sessionId'] === 'test-session'
                        && $params['message'] === 'Enter details'
                        && $params['requestedSchema'] === $schema),
                )
                ->andReturn(['action' => 'accept', 'content' => ['age' => 30]]);

            $session = new Session('test-session', $mockClient);
            $session->setCapabilities(['ui' => ['elicitation' => true]]);

            $result = $session->elicitation('Enter details', $schema);

            expect($result)->toBeInstanceOf(SessionUiElicitationResult::class)
                ->and($result->action)->toBe(ElicitationAction::ACCEPT)
                ->and($result->content)->toBe(['age' => 30]);
        });

        it('raw elicitation returns decline result', function () {
            $mockClient = Mockery::mock(JsonRpcClient::class);
            $mockClient->shouldReceive('request')
                ->once()
                ->with('session.ui.elicitation', Mockery::any())
                ->andReturn(['action' => 'decline']);

            $session = new Session('test-session', $mockClient);
            $session->setCapabilities(['ui' => ['elicitation' => true]]);

            $result = $session->elicitation('Enter details', ['type' => 'object']);

            expect($result->action)->toBe(ElicitationAction::DECLINE)
                ->and($result->content)->toBeNull();
        });
    });
});
