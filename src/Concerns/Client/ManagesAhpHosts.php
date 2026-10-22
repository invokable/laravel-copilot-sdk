<?php

declare(strict_types=1);

namespace Revolution\Copilot\Concerns\Client;

use Closure;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Revolution\Copilot\AhpHost;
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Enums\ConnectionState;
use Revolution\Copilot\Enums\HostAuthenticationMode;
use Revolution\Copilot\Exceptions\JsonRpcException;
use Revolution\Copilot\JsonRpc\JsonRpcNullResult;
use Revolution\Copilot\Support\CancellationToken;
use Revolution\Copilot\Types\AhpHostAuthentication;
use Revolution\Copilot\Types\Rpc\HostAuthenticationCancelledNotification;
use Revolution\Copilot\Types\Rpc\HostAuthenticationResult;
use Revolution\Copilot\Types\Rpc\HostGitHubEnvironmentOptions;
use Revolution\Copilot\Types\Rpc\HostLocalServerOptions;
use Revolution\Copilot\Types\Rpc\HostMaterializeSessionRequest;
use Revolution\Copilot\Types\Rpc\HostSessionAuthentication;
use Revolution\Copilot\Types\Rpc\HostSessionReleasedNotification;
use Revolution\Copilot\Types\Rpc\HostStartRequest;
use Revolution\Copilot\Types\Rpc\HostValidateAuthenticationRequest;
use Throwable;

/** AHP host startup, admission callbacks, and session handoff lifecycle. */
trait ManagesAhpHosts
{
    /**
     * @var array<string, array{
     *     authentication: AhpHostAuthentication,
     *     createSession: Closure|null,
     *     resumeSession: Closure|null,
     *     onSessionReleased: Closure|null
     * }>
     */
    protected array $ahpHosts = [];

    /**
     * @var array<string, array{hostId: string, signal: CancellationToken}>
     */
    protected array $ahpAuthentications = [];

    /**
     * @var array<string, array{
     *     hostId: string,
     *     handoffId: string,
     *     signal: CancellationToken,
     *     session: CopilotSession|null,
     *     onSessionReleased: Closure|null
     * }>
     */
    protected array $ahpHandoffs = [];

    /**
     * Start an AHP listener supervised by the connected runtime.
     *
     * @param  AhpHostAuthentication|array{mode: string, requireIdentity?: string, authenticate?: callable}  $authentication
     * @param  callable(HostMaterializeSessionRequest, CancellationToken): CopilotSession|null  $createSession
     * @param  callable(HostMaterializeSessionRequest, CancellationToken): CopilotSession|null  $resumeSession
     */
    public function startAhpHost(
        AhpHostAuthentication|array $authentication,
        ?string $computeId = null,
        HostLocalServerOptions|array|null $localServer = null,
        HostGitHubEnvironmentOptions|array|null $githubEnvironment = null,
        ?bool $allowCreatingNewSessions = null,
        ?callable $createSession = null,
        ?callable $resumeSession = null,
        ?callable $onSessionReleased = null,
    ): AhpHost {
        $authentication = $authentication instanceof AhpHostAuthentication
            ? $authentication
            : AhpHostAuthentication::fromArray($authentication);

        if ($authentication->mode !== HostAuthenticationMode::GitHub && $githubEnvironment !== null) {
            throw new InvalidArgumentException('githubEnvironment requires github authentication.');
        }

        if ($localServer === null && $githubEnvironment === null) {
            throw new InvalidArgumentException('At least one of localServer or githubEnvironment is required.');
        }

        if ($this->state !== ConnectionState::CONNECTED) {
            $this->start();
        }

        $hostId = (string) Str::uuid();
        $this->ahpHosts[$hostId] = [
            'authentication' => $authentication,
            'createSession' => $createSession === null ? null : Closure::fromCallable($createSession),
            'resumeSession' => $resumeSession === null ? null : Closure::fromCallable($resumeSession),
            'onSessionReleased' => $onSessionReleased === null ? null : Closure::fromCallable($onSessionReleased),
        ];

        $hostRpc = $this->rpc()->host();

        try {
            $result = $hostRpc->start(new HostStartRequest(
                hostId: $hostId,
                computeId: $computeId,
                localServer: $localServer,
                githubEnvironment: $githubEnvironment,
                sessionFactory: $createSession !== null,
                resumeFactory: $resumeSession !== null,
                authentication: $authentication->toRpcType(),
                allowCreatingNewSessions: $allowCreatingNewSessions,
            ));

            return new AhpHost(
                info: $result,
                rpc: $hostRpc,
                onDispose: fn () => $this->releaseAhpHost($hostId),
            );
        } catch (Throwable $e) {
            $this->releaseAhpHost($hostId);

            throw $e;
        }
    }

    /**
     * Register inbound host callbacks on the active JSON-RPC connection.
     */
    protected function registerAhpHostRequestHandlers(): void
    {
        $this->rpcClient->setRequestHandler(
            'host.validateAuthentication',
            fn (array $params, CancellationToken $token) => $this->handleAhpAuthentication($params, $token),
        );

        $this->rpcClient->setRequestHandler(
            'host.materializeSession',
            fn (array $params, CancellationToken $token) => $this->handleAhpSessionMaterialization($params, $token),
        );
    }

    /**
     * @param  array<string, mixed>  $params
     */
    protected function handleAhpAuthentication(array $params, CancellationToken $requestToken): mixed
    {
        $request = HostValidateAuthenticationRequest::fromArray($params);
        $host = $this->ahpHosts[$request->hostId] ?? null;
        if ($host === null || $host['authentication']->authenticate === null) {
            throw new InvalidArgumentException('AHP custom authenticator is unavailable.');
        }

        $key = $this->ahpRequestKey($request->hostId, $request->requestId);
        if (isset($this->ahpAuthentications[$key])) {
            throw new InvalidArgumentException('AHP authentication request already exists.');
        }

        $signal = new CancellationToken;
        $entry = ['hostId' => $request->hostId, 'signal' => $signal];
        $this->ahpAuthentications[$key] = $entry;
        $removeCancellationListener = $requestToken->onCancellationRequested(
            fn () => $signal->cancel(),
        );

        try {
            try {
                $result = ($host['authentication']->authenticate)($request, $signal);
            } catch (Throwable $e) {
                if ($signal->isCancellationRequested()) {
                    throw new JsonRpcException(-32800, 'AHP authentication cancelled.', previous: $e);
                }

                throw new \RuntimeException('AHP authentication callback failed.', previous: $e);
            }

            if ($signal->isCancellationRequested()) {
                throw new JsonRpcException(-32800, 'AHP authentication cancelled.');
            }

            if ($result === null) {
                return new JsonRpcNullResult;
            }

            $result = $result instanceof HostAuthenticationResult
                ? $result
                : (is_array($result)
                    ? HostAuthenticationResult::fromArray($result)
                    : throw new InvalidArgumentException('AHP authenticator must return a result or null.'));

            if ($result->identity !== $host['authentication']->requireIdentity) {
                return new JsonRpcNullResult;
            }

            return $result->toArray();
        } finally {
            if (($this->ahpAuthentications[$key] ?? null) === $entry) {
                unset($this->ahpAuthentications[$key]);
            }
            $signal->cancel();
            $removeCancellationListener();
        }
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array{sessionId: string}
     */
    protected function handleAhpSessionMaterialization(array $params, CancellationToken $requestToken): array
    {
        $request = HostMaterializeSessionRequest::fromArray($params);
        $host = $this->ahpHosts[$request->hostId] ?? null;
        if ($host === null || $request->handoffId === '' || $request->authentication === null) {
            throw new InvalidArgumentException('AHP session factory or trusted authentication is unavailable.');
        }

        $this->assertAhpSessionAuthentication($host['authentication'], $request->authentication);
        $factory = $request->resume === true ? $host['resumeSession'] : $host['createSession'];
        if ($factory === null) {
            throw new InvalidArgumentException('AHP session factory is unavailable.');
        }

        $key = $this->ahpRequestKey($request->hostId, $request->handoffId);
        if (isset($this->ahpHandoffs[$key])) {
            throw new InvalidArgumentException('AHP session handoff already exists.');
        }

        $signal = new CancellationToken;
        $entry = [
            'hostId' => $request->hostId,
            'handoffId' => $request->handoffId,
            'signal' => $signal,
            'session' => null,
            'onSessionReleased' => $host['onSessionReleased'],
        ];
        $this->ahpHandoffs[$key] = $entry;
        $removeCancellationListener = $requestToken->onCancellationRequested(
            fn () => $signal->cancel(),
        );
        $completed = false;

        try {
            if ($signal->isCancellationRequested()) {
                throw new JsonRpcException(-32800, 'AHP session handoff cancelled.');
            }

            $session = $factory($request, $signal);
            if (! $session instanceof CopilotSession) {
                throw new InvalidArgumentException('AHP session factory must return a CopilotSession.');
            }

            $this->ahpHandoffs[$key]['session'] = $session;
            if ($signal->isCancellationRequested()) {
                throw new JsonRpcException(-32800, 'AHP session handoff cancelled.');
            }

            if (($this->sessions[$session->id()] ?? null) !== $session) {
                throw new InvalidArgumentException('AHP session factory must return a session owned by this client.');
            }

            $requestedSessionId = $request->config['sessionId'] ?? null;
            if (is_string($requestedSessionId) && $requestedSessionId !== $session->id()) {
                throw new InvalidArgumentException('AHP session factory must preserve the requested sessionId.');
            }

            $completed = true;

            return ['sessionId' => $session->id()];
        } finally {
            $removeCancellationListener();

            if (! $completed) {
                $this->releaseAhpHandoff($request->hostId, $request->handoffId);
            }
        }
    }

    protected function handleAhpAuthenticationCancelled(array $params): void
    {
        $notification = HostAuthenticationCancelledNotification::fromArray($params);
        $key = $this->ahpRequestKey($notification->hostId, $notification->requestId);
        $entry = $this->ahpAuthentications[$key] ?? null;
        if ($entry !== null && $entry['hostId'] === $notification->hostId) {
            $entry['signal']->cancel();
        }
    }

    protected function handleAhpSessionReleased(array $params): void
    {
        $notification = HostSessionReleasedNotification::fromArray($params);
        $this->releaseAhpHandoff($notification->hostId, $notification->handoffId);
    }

    protected function releaseAhpHost(string $hostId): void
    {
        unset($this->ahpHosts[$hostId]);

        foreach ($this->ahpAuthentications as $key => $entry) {
            if ($entry['hostId'] === $hostId) {
                unset($this->ahpAuthentications[$key]);
                $entry['signal']->cancel();
            }
        }

        foreach (array_keys($this->ahpHandoffs) as $key) {
            $entry = $this->ahpHandoffs[$key] ?? null;
            if ($entry === null) {
                continue;
            }

            if ($entry['hostId'] === $hostId) {
                $this->releaseAhpHandoff($hostId, $entry['handoffId']);
            }
        }
    }

    protected function releaseAllAhpHosts(): void
    {
        foreach (array_keys($this->ahpHosts) as $hostId) {
            $this->releaseAhpHost($hostId);
        }
    }

    private function assertAhpSessionAuthentication(
        AhpHostAuthentication $policy,
        HostSessionAuthentication $authentication,
    ): void {
        if ($authentication->mode !== $policy->mode) {
            throw new InvalidArgumentException('AHP trusted authentication does not match host policy.');
        }

        if ($authentication->mode === HostAuthenticationMode::None) {
            if ($authentication->identity !== null) {
                throw new InvalidArgumentException('AHP anonymous authentication cannot include an identity.');
            }

            return;
        }

        if (! is_string($authentication->identity) || trim($authentication->identity) === '') {
            throw new InvalidArgumentException('AHP trusted authentication requires a nonempty identity.');
        }

        if ($policy->mode === HostAuthenticationMode::Custom
            && $authentication->identity !== $policy->requireIdentity) {
            throw new InvalidArgumentException('AHP trusted authentication does not match host policy.');
        }
    }

    private function releaseAhpHandoff(string $hostId, string $handoffId): void
    {
        $key = $this->ahpRequestKey($hostId, $handoffId);
        $entry = $this->ahpHandoffs[$key] ?? null;
        if ($entry === null) {
            return;
        }

        unset($this->ahpHandoffs[$key]);
        $entry['signal']->cancel();

        if ($entry['session'] !== null && $entry['onSessionReleased'] !== null) {
            try {
                ($entry['onSessionReleased'])($entry['session']);
            } catch (Throwable) {
                //
            }
        }
    }

    private function ahpRequestKey(string $hostId, string $requestId): string
    {
        return $hostId."\0".$requestId;
    }
}
