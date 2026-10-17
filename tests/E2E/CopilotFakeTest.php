<?php

declare(strict_types=1);

use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\CopilotManager;
use Revolution\Copilot\Enums\AgentMode;
use Revolution\Copilot\Enums\LogLevel;
use Revolution\Copilot\Enums\ModelCallProviderType;
use Revolution\Copilot\Enums\ModelCallRequestBodyCompressionFallback;
use Revolution\Copilot\Enums\ModelCallRequestBodyEncoding;
use Revolution\Copilot\Enums\SessionEventType;
use Revolution\Copilot\Exceptions\StrayRequestException;
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\SessionEvent;

beforeEach(function () {
    // Reset the facade before each test
    Copilot::clearResolvedInstances();
    Copilot::preventStrayRequests();
});

describe('Copilot::fake()', function () {
    it('can be faked with a simple string response', function () {
        Copilot::fake('Hello World');

        $response = Copilot::run('Hi');

        expect($response)->not->toBeNull()
            ->and($response->content())->toBe('Hello World');
    });

    it('returns transient MCP server tools-loaded events from fake responses', function () {
        $event = SessionEvent::fromArray([
            'type' => 'session.mcp_server_tools_loaded',
            'data' => [
                'serverName' => 'github',
                'toolCount' => 4,
            ],
            'ephemeral' => true,
        ]);
        Copilot::fake(['*' => $event]);

        $response = Copilot::run('List available MCP tools');

        expect($response?->type)->toBe(SessionEventType::SESSION_MCP_SERVER_TOOLS_LOADED)
            ->and($response?->data)->toBe(['serverName' => 'github', 'toolCount' => 4])
            ->and($response?->ephemeral)->toBeTrue();
    });

    it('can be faked with array responses', function () {
        Copilot::fake([
            '*' => '42',
        ]);

        $response = Copilot::run('What is 6 * 7?');

        expect((string) $response)->toBe('42');
    });

    it('preserves model-call telemetry in fake usage and failure responses', function () {
        $usage = SessionEvent::fromArray([
            'type' => 'assistant.usage',
            'data' => [
                'providerType' => 'local_model',
                'requestBodyCompressionFallback' => 'retried',
                'requestBodyCompressionFallbackFrom' => 'gzip',
                'requestBodyCompressionRejectedStatus' => 415,
            ],
        ]);
        $failure = SessionEvent::fromArray([
            'type' => 'model.call_failure',
            'data' => [
                'providerType' => 'winml',
                'requestBodyCompressionFallback' => 'disabled',
                'requestBodyCompressionFallbackFrom' => 'zstd',
            ],
        ]);
        Copilot::fake([
            '*' => Copilot::sequence()->push($usage)->push($failure),
        ]);

        $usageResponse = Copilot::run('Usage event');
        $failureResponse = Copilot::run('Failure event');

        expect($usageResponse?->type)->toBe(SessionEventType::ASSISTANT_USAGE)
            ->and($usageResponse?->providerType())->toBe(ModelCallProviderType::LOCAL_MODEL)
            ->and($usageResponse?->requestBodyCompressionFallback())->toBe(ModelCallRequestBodyCompressionFallback::RETRIED)
            ->and($usageResponse?->requestBodyCompressionFallbackFrom())->toBe(ModelCallRequestBodyEncoding::GZIP)
            ->and($usageResponse?->requestBodyCompressionRejectedStatus())->toBe(415)
            ->and($usageResponse?->data)->not->toHaveKey('providerName');

        expect($failureResponse?->type)->toBe(SessionEventType::MODEL_CALL_FAILURE)
            ->and($failureResponse?->providerType())->toBe(ModelCallProviderType::WINML)
            ->and($failureResponse?->requestBodyCompressionFallback())->toBe(ModelCallRequestBodyCompressionFallback::DISABLED)
            ->and($failureResponse?->requestBodyCompressionFallbackFrom())->toBe(ModelCallRequestBodyEncoding::ZSTD)
            ->and($failureResponse?->requestBodyCompressionRejectedStatus())->toBeNull()
            ->and($failureResponse?->data)->not->toHaveKey('providerName');
    });

    it('records agent mode from run', function () {
        Copilot::fake('Planned');

        $response = Copilot::run('Plan this', agentMode: AgentMode::PLAN);
        $recorded = Copilot::recorded();

        expect($response?->content())->toBe('Planned')
            ->and($recorded)->toHaveCount(1)
            ->and($recorded[0]['agentMode'])->toBe('plan');
    });

    it('returns CopilotManager instance', function () {
        $fake = Copilot::fake();

        expect($fake)->toBeInstanceOf(CopilotManager::class)
            ->and($fake->isFake())->toBeTrue();
    });
});

describe('Copilot::sequence()', function () {
    it('can push multiple responses in sequence', function () {
        Copilot::fake([
            '*' => Copilot::sequence()
                ->push(Copilot::response('First'))
                ->push(Copilot::response('Second'))
                ->push(Copilot::response('Third')),
        ]);

        $first = Copilot::run('1');
        $second = Copilot::run('2');
        $third = Copilot::run('3');

        expect($first->content())->toBe('First')
            ->and($second->content())->toBe('Second')
            ->and($third->content())->toBe('Third');
    });

    it('returns null when sequence is exhausted without fallback', function () {
        Copilot::fake(
            [
                '*' => Copilot::sequence()
                    ->push(Copilot::response('Only One')),
            ],
        );

        $first = Copilot::run('1');
        $second = Copilot::run('2');

        expect($first->content())->toBe('Only One')
            ->and($second)->toBeNull();
    });

    it('uses fallback when sequence is exhausted', function () {
        Copilot::fake([
            '*' => Copilot::sequence()
                ->push(Copilot::response('First'))
                ->whenEmpty(Copilot::response('Fallback')),
        ]);

        $first = Copilot::run('1');
        $second = Copilot::run('2');
        $third = Copilot::run('3');

        expect($first->content())->toBe('First')
            ->and($second->content())->toBe('Fallback')
            ->and($third->content())->toBe('Fallback');
    });
});

describe('Copilot::start()', function () {
    it('can start a session with fake', function () {
        Copilot::fake([
            '*' => Copilot::sequence()
                ->push(Copilot::response('2'))
                ->push(Copilot::response('4')),
        ]);

        $results = [];

        Copilot::start(function (CopilotSession $session) use (&$results) {
            $results[] = $session->sendAndWait('1 + 1')?->content();
            $results[] = $session->sendAndWait('2 + 2')?->content();
        });

        expect($results)->toBe(['2', '4']);
    });

    it('records prompts from session', function () {
        Copilot::fake('response');

        Copilot::start(function (CopilotSession $session) {
            $session->sendAndWait('Hello');
            $session->sendAndWait('World');
        });

        $recorded = Copilot::recorded();

        expect($recorded)->toHaveCount(2)
            ->and($recorded[0]['prompt'])->toBe('Hello')
            ->and($recorded[1]['prompt'])->toBe('World');
    });
});

describe('Copilot fake prompt assertions', function () {
    it('can assert prompt was sent', function () {
        Copilot::fake('response');

        Copilot::run('What is 1 + 1?');

        Copilot::assertPrompt('What is 1 + 1?');
    });

    it('can assert prompt with wildcard pattern', function () {
        Copilot::fake('response');

        Copilot::run('What is 1 + 1?');

        Copilot::assertPrompt('What is *');
    });

    it('can assert prompt was NOT sent', function () {
        Copilot::fake('response');

        Copilot::run('Hello');

        Copilot::assertNotPrompt('Goodbye');
    });

    it('can assert prompt count', function () {
        Copilot::fake('response');

        Copilot::run('One');
        Copilot::run('Two');
        Copilot::run('Three');

        Copilot::assertPromptCount(3);
    });

    it('can assert nothing was sent', function () {
        Copilot::fake('response');

        Copilot::assertNothingSent();
    });
});

describe('preventStrayRequests()', function () {
    it('throws exception when no fake response matches', function () {
        Copilot::preventStrayRequests();

        expect(fn () => Copilot::run('Unexpected prompt'))
            ->toThrow(StrayRequestException::class, 'Attempted request to [connect] without a matching fake.');
    });

    it('allows requests that have matching responses', function () {
        Copilot::fake([
            '*' => 'response',
        ]);
        Copilot::preventStrayRequests();

        $response = Copilot::run('Any prompt');

        expect($response->content())->toBe('response');
    });
});

describe('createSession()', function () {
    it('can create a fake session', function () {
        Copilot::fake('test');

        $session = Copilot::createSession();

        expect($session)->toBeInstanceOf(CopilotSession::class)
            ->and($session->id())->toStartWith('fake-session-');
    });

    it('fake session log() is a no-op', function () {
        Copilot::fake('test');

        Copilot::start(function (CopilotSession $session) {
            // Should not throw any exceptions
            $session->log('Processing started');
            $session->log('Warning message', level: LogLevel::WARNING);
            $session->log('Debug info', level: LogLevel::INFO, ephemeral: true);
        });

        // If we get here without exceptions, the test passes
        expect(true)->toBeTrue();
    });
});
