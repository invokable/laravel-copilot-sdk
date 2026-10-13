<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Configuration for the runtime-managed remote-control singleton.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class RemoteControlConfig implements Arrayable
{
    public function __construct(
        public ?bool $remote = null,
        public ?bool $steerable = null,
        public ?bool $explicit = null,
        public ?bool $silent = null,
        public ?string $taskId = null,
        public RemoteControlConfigExistingMcSession|array|null $existingMcSession = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $existingMcSession = $data['existingMcSession'] ?? null;

        return new self(
            remote: isset($data['remote']) ? Arr::boolean($data, 'remote') : null,
            steerable: isset($data['steerable']) ? Arr::boolean($data, 'steerable') : null,
            explicit: isset($data['explicit']) ? Arr::boolean($data, 'explicit') : null,
            silent: isset($data['silent']) ? Arr::boolean($data, 'silent') : null,
            taskId: $data['taskId'] ?? null,
            existingMcSession: is_array($existingMcSession)
                ? RemoteControlConfigExistingMcSession::fromArray($existingMcSession)
                : $existingMcSession,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'remote' => $this->remote,
            'steerable' => $this->steerable,
            'explicit' => $this->explicit,
            'silent' => $this->silent,
            'taskId' => $this->taskId,
            'existingMcSession' => $this->existingMcSession instanceof RemoteControlConfigExistingMcSession
                ? $this->existingMcSession->toArray()
                : $this->existingMcSession,
        ], static fn ($value) => $value !== null);
    }
}
