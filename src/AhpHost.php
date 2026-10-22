<?php

declare(strict_types=1);

namespace Revolution\Copilot;

use Closure;
use Revolution\Copilot\Rpc\PendingServerHost;
use Revolution\Copilot\Types\Rpc\HostDisposeRequest;
use Revolution\Copilot\Types\Rpc\HostDisposeResult;
use Revolution\Copilot\Types\Rpc\HostListSessionsRequest;
use Revolution\Copilot\Types\Rpc\HostListSessionsResult;
use Revolution\Copilot\Types\Rpc\HostPublishSessionRequest;
use Revolution\Copilot\Types\Rpc\HostPublishSessionResult;
use Revolution\Copilot\Types\Rpc\HostSetSessionCreationPolicyRequest;
use Revolution\Copilot\Types\Rpc\HostSetSessionCreationPolicyResult;
use Revolution\Copilot\Types\Rpc\HostStartResult;
use Revolution\Copilot\Types\Rpc\HostUnpublishSessionRequest;
use Revolution\Copilot\Types\Rpc\HostUnpublishSessionResult;

/** A connection-owned AHP listener supervised by the Copilot runtime. */
final readonly class AhpHost
{
    public string $hostId;

    public ?string $url;

    public ?string $token;

    public ?string $environmentId;

    public ?int $pid;

    public function __construct(
        HostStartResult $info,
        private PendingServerHost $rpc,
        private Closure $onDispose,
    ) {
        $this->hostId = $info->hostId;
        $this->url = $info->url;
        $this->token = $info->token;
        $this->environmentId = $info->environmentId;
        $this->pid = $info->pid;
    }

    public function dispose(): HostDisposeResult
    {
        $result = $this->rpc->dispose(new HostDisposeRequest($this->hostId));
        ($this->onDispose)();

        return $result;
    }

    public function setSessionCreationPolicy(bool $allowCreatingNewSessions): HostSetSessionCreationPolicyResult
    {
        return $this->rpc->setSessionCreationPolicy(new HostSetSessionCreationPolicyRequest(
            hostId: $this->hostId,
            allowCreatingNewSessions: $allowCreatingNewSessions,
        ));
    }

    public function publishSession(string $sessionId): HostPublishSessionResult
    {
        return $this->rpc->publishSession(new HostPublishSessionRequest(
            hostId: $this->hostId,
            sessionId: $sessionId,
        ));
    }

    public function unpublishSession(string $sessionId): HostUnpublishSessionResult
    {
        return $this->rpc->unpublishSession(new HostUnpublishSessionRequest(
            hostId: $this->hostId,
            sessionId: $sessionId,
        ));
    }

    public function listSessions(): HostListSessionsResult
    {
        return $this->rpc->listSessions(new HostListSessionsRequest($this->hostId));
    }
}
