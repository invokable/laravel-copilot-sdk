<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Live agent metadata returned by local registry discovery.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class AgentRegistryLiveTargetEntry implements Arrayable
{
    public function __construct(
        public int $schemaVersion,
        public string $kind,
        public int $pid,
        public string $host,
        public int $port,
        public string $startedAt,
        public string $copilotVersion,
        public int $lastSeenMs,
        public ?string $sessionId = null,
        public ?string $sessionName = null,
        public ?string $cwd = null,
        public ?string $branch = null,
        public ?string $model = null,
        public ?string $status = null,
        public ?string $attentionKind = null,
        public ?int $statusRevision = null,
        public ?string $lastTerminalEvent = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            schemaVersion: Arr::integer($data, 'schemaVersion'),
            kind: Arr::string($data, 'kind'),
            pid: Arr::integer($data, 'pid'),
            host: Arr::string($data, 'host'),
            port: Arr::integer($data, 'port'),
            startedAt: Arr::string($data, 'startedAt'),
            copilotVersion: Arr::string($data, 'copilotVersion'),
            lastSeenMs: Arr::integer($data, 'lastSeenMs'),
            sessionId: isset($data['sessionId']) ? Arr::string($data, 'sessionId') : null,
            sessionName: isset($data['sessionName']) ? Arr::string($data, 'sessionName') : null,
            cwd: isset($data['cwd']) ? Arr::string($data, 'cwd') : null,
            branch: isset($data['branch']) ? Arr::string($data, 'branch') : null,
            model: isset($data['model']) ? Arr::string($data, 'model') : null,
            status: isset($data['status']) ? Arr::string($data, 'status') : null,
            attentionKind: isset($data['attentionKind']) ? Arr::string($data, 'attentionKind') : null,
            statusRevision: isset($data['statusRevision']) ? Arr::integer($data, 'statusRevision') : null,
            lastTerminalEvent: isset($data['lastTerminalEvent']) ? Arr::string($data, 'lastTerminalEvent') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'schemaVersion' => $this->schemaVersion,
            'kind' => $this->kind,
            'pid' => $this->pid,
            'host' => $this->host,
            'port' => $this->port,
            'sessionId' => $this->sessionId,
            'sessionName' => $this->sessionName,
            'cwd' => $this->cwd,
            'branch' => $this->branch,
            'model' => $this->model,
            'status' => $this->status,
            'attentionKind' => $this->attentionKind,
            'statusRevision' => $this->statusRevision,
            'lastTerminalEvent' => $this->lastTerminalEvent,
            'startedAt' => $this->startedAt,
            'copilotVersion' => $this->copilotVersion,
            'lastSeenMs' => $this->lastSeenMs,
        ], fn ($value) => $value !== null);
    }
}
