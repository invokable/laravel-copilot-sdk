<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Facade;
use Revolt\EventLoop;
use Revolution\Copilot\Client;
use Revolution\Copilot\Contracts\Transport;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\JsonRpc\JsonRpcMessage;
use Revolution\Copilot\Process\ProcessManager;
use Revolution\Copilot\Protocol;
use Revolution\Copilot\Support\CancellationToken;
use Revolution\Copilot\Transport\StdioTransport;
use Revolution\Copilot\Types\AhpHostAuthentication;
use Revolution\Copilot\Types\Rpc\HostAuthenticationResult;
use Revolution\Copilot\Types\Rpc\HostMaterializeSessionRequest;
use Revolution\Copilot\Types\Rpc\HostValidateAuthenticationRequest;

beforeEach(function () {
    Facade::clearResolvedInstances();
});

it('starts an AHP host with custom authentication and changes policy without disconnecting clients', function () {
    [$client, $transport] = createAhpHostTestClient();
    $authentication = AhpHostAuthentication::custom(
        'tenant-1',
        fn (HostValidateAuthenticationRequest $request) => new HostAuthenticationResult(
            identity: 'tenant-1',
            expiresAt: '2099-01-01T00:00:00Z',
        ),
    );

    $host = $client->startAhpHost(
        authentication: $authentication,
        localServer: [],
        allowCreatingNewSessions: true,
        createSession: fn () => null,
    );
    $host->setSessionCreationPolicy(false);

    $hostStart = collect($transport->messages)->firstWhere('method', 'host.start');
    $policy = collect($transport->messages)->firstWhere('method', 'host.setSessionCreationPolicy');

    expect($hostStart['params']['authentication'])->toBe([
        'mode' => 'custom',
        'requireIdentity' => 'tenant-1',
    ])->and($hostStart['params']['allowCreatingNewSessions'])->toBeTrue()
        ->and($hostStart['params']['sessionFactory'])->toBeTrue()
        ->and($host->hostId)->toBe($hostStart['params']['hostId'])
        ->and($policy['params'])->toBe([
            'hostId' => $host->hostId,
            'allowCreatingNewSessions' => false,
        ])
        ->and(array_column($transport->messages, 'method'))->not->toContain('session.disconnect');
});

it('rejects invalid authentication configuration before starting the runtime', function () {
    $client = new Client;

    expect(fn () => $client->startAhpHost(
        authentication: ['mode' => 'password'],
        localServer: [],
    ))->toThrow(ValueError::class);

    expect(fn () => $client->startAhpHost(
        authentication: ['mode' => 'custom', 'requireIdentity' => 'tenant-1'],
        localServer: [],
    ))->toThrow(InvalidArgumentException::class);
});

it('returns validated credentials and denies rejected or mismatched identities', function () {
    [$client, $transport] = createAhpHostTestClient();
    $results = [
        ['identity' => 'tenant-1', 'expiresAt' => '2099-01-01T00:00:00Z'],
        null,
        ['identity' => 'tenant-2', 'expiresAt' => '2099-01-01T00:00:00Z'],
        ['identity' => 'tenant-1', 'expiresAt' => '2000-01-01T00:00:00Z'],
        ['identity' => 'tenant-1', 'expiresAt' => 'not-a-date'],
    ];
    $receivedTokens = [];
    $host = $client->startAhpHost(
        authentication: AhpHostAuthentication::custom(
            'tenant-1',
            function (HostValidateAuthenticationRequest $request, CancellationToken $signal) use (&$results, &$receivedTokens): ?array {
                $signal->onCancellationRequested(fn () => throw new RuntimeException('listener failed'));
                $receivedTokens[] = $request->token;

                return array_shift($results);
            },
        ),
        localServer: [],
    );

    foreach (['accepted', 'rejected', 'wrong-identity', 'expired', 'invalid-expiry'] as $requestId) {
        $transport->injectRequest($requestId, 'host.validateAuthentication', [
            'hostId' => $host->hostId,
            'requestId' => $requestId,
            'token' => 'admission-'.$requestId,
        ]);
    }

    expect($transport->responseFor('accepted')['result'])->toBe([
        'identity' => 'tenant-1',
        'expiresAt' => '2099-01-01T00:00:00Z',
    ]);

    expect($transport->responseFor('rejected')['result'])->toBeNull()
        ->and($transport->responseFor('wrong-identity')['result'])->toBeNull()
        ->and($transport->responseFor('expired')['result'])->toBeNull()
        ->and($transport->responseFor('invalid-expiry')['result'])->toBeNull()
        ->and($receivedTokens)->toBe([
            'admission-accepted',
            'admission-rejected',
            'admission-wrong-identity',
            'admission-expired',
            'admission-invalid-expiry',
        ]);
});

it('does not expose admission tokens when a custom authenticator throws', function () {
    [$client, $transport] = createAhpHostTestClient();
    $host = $client->startAhpHost(
        authentication: AhpHostAuthentication::custom(
            'tenant-1',
            fn (HostValidateAuthenticationRequest $request) => throw new RuntimeException($request->token),
        ),
        localServer: [],
    );

    $transport->injectRequest('auth-error', 'host.validateAuthentication', [
        'hostId' => $host->hostId,
        'requestId' => 'auth-error',
        'token' => 'private-admission-token',
    ]);

    expect($transport->responseFor('auth-error')['error']['message'])
        ->toContain('AHP authentication callback failed')
        ->not->toContain('private-admission-token');
});

it('cancels authentication callbacks when validation or the host ends', function () {
    [$client, $transport] = createAhpHostTestClient();
    $signals = [];
    $host = $client->startAhpHost(
        authentication: AhpHostAuthentication::custom(
            'tenant-1',
            function (HostValidateAuthenticationRequest $request, CancellationToken $signal) use (&$signals): HostAuthenticationResult {
                $signals[] = $signal;
                $suspension = EventLoop::getSuspension();
                $signal->onCancellationRequested(fn () => $suspension->resume());
                $suspension->suspend();

                return new HostAuthenticationResult('tenant-1', '2099-01-01T00:00:00Z');
            },
        ),
        localServer: [],
    );

    $transport->injectRequest('cancel-notification', 'host.validateAuthentication', [
        'hostId' => $host->hostId,
        'requestId' => 'cancel-notification',
        'token' => 'admission-token',
    ]);
    EventLoop::delay(0.01, fn () => $transport->injectNotification('host.authenticationCancelled', [
        'hostId' => $host->hostId,
        'requestId' => 'cancel-notification',
    ]));
    EventLoop::delay(0.02, function () use ($transport, $host): void {
        $transport->injectRequest('cancel-host-release', 'host.validateAuthentication', [
            'hostId' => $host->hostId,
            'requestId' => 'cancel-host-release',
            'token' => 'admission-token',
        ]);
    });
    EventLoop::delay(0.03, fn () => $transport->injectNotification('host.exited', [
        'hostId' => $host->hostId,
        'reason' => 'disposed',
    ]));
    EventLoop::delay(0.05, fn () => EventLoop::getDriver()->stop());
    EventLoop::run();

    expect($transport->responseFor('cancel-notification')['error']['code'])->toBe(-32800)
        ->and($transport->responseFor('cancel-host-release')['error']['code'])->toBe(-32800)
        ->and($signals)->toHaveCount(2)
        ->and($signals[0]->isCancellationRequested())->toBeTrue()
        ->and($signals[1]->isCancellationRequested())->toBeTrue();
});

it('passes trusted identity to create and resume callbacks and releases each handoff', function () {
    [$client, $transport] = createAhpHostTestClient();
    $receivedIdentities = [];
    $releasedSessions = [];
    $host = $client->startAhpHost(
        authentication: AhpHostAuthentication::custom(
            'tenant-1',
            fn () => new HostAuthenticationResult('tenant-1', '2099-01-01T00:00:00Z'),
        ),
        localServer: [],
        createSession: function (HostMaterializeSessionRequest $request, CancellationToken $signal) use ($client, &$receivedIdentities) {
            $receivedIdentities[] = $request->authentication?->identity;

            return $client->createSession([
                ...($request->config ?? []),
                'onPermissionRequest' => fn () => true,
            ]);
        },
        resumeSession: function (HostMaterializeSessionRequest $request, CancellationToken $signal) use ($client, &$receivedIdentities) {
            $receivedIdentities[] = $request->authentication?->identity;

            return $client->resumeSession(
                $request->config['sessionId'],
                ['onPermissionRequest' => fn () => true],
            );
        },
        onSessionReleased: function ($session) use (&$releasedSessions): void {
            $releasedSessions[] = $session->id();
        },
    );

    $transport->injectRequest('handoff-create', 'host.materializeSession', [
        'hostId' => $host->hostId,
        'handoffId' => 'handoff-create',
        'authentication' => ['mode' => 'custom', 'identity' => 'wrong-tenant'],
        'config' => ['sessionId' => 'created-session'],
    ]);
    $transport->injectRequest('handoff-missing-id', 'host.materializeSession', [
        'hostId' => $host->hostId,
        'handoffId' => 'handoff-missing-id',
        'authentication' => ['mode' => 'custom', 'identity' => 'tenant-1'],
        'config' => [],
    ]);
    $transport->injectRequest('handoff-create-valid', 'host.materializeSession', [
        'hostId' => $host->hostId,
        'handoffId' => 'handoff-create-valid',
        'authentication' => ['mode' => 'custom', 'identity' => 'tenant-1'],
        'config' => ['sessionId' => 'created-session'],
    ]);
    $transport->injectRequest('handoff-resume', 'host.materializeSession', [
        'hostId' => $host->hostId,
        'handoffId' => 'handoff-resume',
        'authentication' => ['mode' => 'custom', 'identity' => 'tenant-1'],
        'resume' => true,
        'config' => ['sessionId' => 'resumed-session'],
    ]);
    EventLoop::delay(0.05, fn () => EventLoop::getDriver()->stop());
    EventLoop::run();

    $transport->injectNotification('host.sessionReleased', [
        'hostId' => $host->hostId,
        'handoffId' => 'handoff-create-valid',
    ]);
    $transport->injectNotification('host.sessionReleased', [
        'hostId' => $host->hostId,
        'handoffId' => 'handoff-resume',
    ]);

    expect($transport->responseFor('handoff-create')['error']['message'])
        ->toContain('does not match host policy');

    expect($transport->responseFor('handoff-missing-id')['error']['message'])
        ->toContain('must include a nonempty sessionId');

    expect($transport->responseFor('handoff-create-valid')['result'])->toBe(['sessionId' => 'created-session'])
        ->and($transport->responseFor('handoff-resume')['result'])->toBe(['sessionId' => 'resumed-session'])
        ->and($receivedIdentities)->toBe(['tenant-1', 'tenant-1'])
        ->and($releasedSessions)->toBe(['created-session', 'resumed-session']);
});

it('releases a session returned after its handoff was cancelled', function () {
    [$client, $transport] = createAhpHostTestClient();
    $releasedSessions = [];
    $host = $client->startAhpHost(
        authentication: AhpHostAuthentication::custom(
            'tenant-1',
            fn () => new HostAuthenticationResult('tenant-1', '2099-01-01T00:00:00Z'),
        ),
        localServer: [],
        createSession: function (HostMaterializeSessionRequest $request, CancellationToken $signal) use ($client) {
            $suspension = EventLoop::getSuspension();
            $signal->onCancellationRequested(fn () => $suspension->resume());
            $suspension->suspend();

            return $client->createSession([
                ...$request->config,
                'onPermissionRequest' => fn () => true,
            ]);
        },
        onSessionReleased: function ($session) use (&$releasedSessions): void {
            $releasedSessions[] = $session->id();
        },
    );

    $transport->injectRequest('late-handoff', 'host.materializeSession', [
        'hostId' => $host->hostId,
        'handoffId' => 'late-handoff',
        'authentication' => ['mode' => 'custom', 'identity' => 'tenant-1'],
        'config' => ['sessionId' => 'late-session'],
    ]);
    EventLoop::delay(0.01, fn () => $transport->injectNotification('host.sessionReleased', [
        'hostId' => $host->hostId,
        'handoffId' => 'late-handoff',
    ]));
    EventLoop::delay(0.05, fn () => EventLoop::getDriver()->stop());
    EventLoop::run();

    expect($transport->responseFor('late-handoff')['error']['code'])->toBe(-32800)
        ->and($releasedSessions)->toBe(['late-session']);
});

/**
 * @return array{Client, object}
 */
function createAhpHostTestClient(): array
{
    $transport = new class implements Transport
    {
        public array $messages = [];

        protected ?Closure $receiveHandler = null;

        public function start(): void {}

        public function stop(): void {}

        public function onReceive(Closure $handler): void
        {
            $this->receiveHandler = $handler;
        }

        public function send(string $message): void
        {
            $content = substr($message, strpos($message, "\r\n\r\n") + 4);
            $request = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
            $this->messages[] = $request;
            if (! isset($request['method'])) {
                return;
            }

            $params = $request['params'] ?? [];
            $result = match ($request['method']) {
                'connect' => ['protocolVersion' => Protocol::version()],
                'host.start' => ['hostId' => $params['hostId'], 'url' => 'ws://127.0.0.1:4312'],
                'session.create' => ['sessionId' => $params['sessionId'] ?? 'created-session'],
                'session.resume' => ['sessionId' => $params['sessionId']],
                default => [],
            };

            if (isset($request['id'])) {
                EventLoop::queue(function () use ($request, $result): void {
                    ($this->receiveHandler)(JsonRpcMessage::response($request['id'], $result)->toJson());
                });
            }
        }

        public function injectRequest(string $id, string $method, array $params): void
        {
            ($this->receiveHandler)(JsonRpcMessage::request($id, $method, $params)->toJson());
        }

        public function injectNotification(string $method, array $params): void
        {
            ($this->receiveHandler)(JsonRpcMessage::notification($method, $params)->toJson());
        }

        public function responseFor(string $id): ?array
        {
            foreach (array_reverse($this->messages) as $message) {
                if (($message['id'] ?? null) === $id) {
                    return $message;
                }
            }

            return null;
        }
    };

    $stdioTransport = Mockery::mock(StdioTransport::class);
    $processManager = Mockery::mock(ProcessManager::class);
    $processManager->shouldReceive('start')->once();
    $processManager->shouldReceive('getStdioTransport')->andReturn($stdioTransport);

    app()->bind(ProcessManager::class, fn () => $processManager);
    app()->bind(JsonRpcClient::class, fn () => new JsonRpcClient($transport));

    return [new Client, $transport];
}
