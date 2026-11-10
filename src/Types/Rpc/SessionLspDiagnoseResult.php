<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Result of a language-server startup probe.
 *
 * @experimental
 */
readonly class SessionLspDiagnoseResult implements Arrayable
{
    /**
     * @param  string[]  $availableServers
     */
    public function __construct(
        public string $status,
        public array $availableServers,
        public ?int $pid,
        public float $spawnTimeMs,
        public bool $sandboxed,
        public ?LspProbeExitStatus $immediateExit = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            status: Arr::string($data, 'status', ''),
            availableServers: $data['availableServers'] ?? [],
            pid: isset($data['pid']) ? Arr::integer($data, 'pid') : null,
            spawnTimeMs: is_int($data['spawnTimeMs'] ?? null)
                ? (float) Arr::integer($data, 'spawnTimeMs')
                : Arr::float($data, 'spawnTimeMs', 0.0),
            sandboxed: Arr::boolean($data, 'sandboxed', false),
            immediateExit: isset($data['immediateExit']) ? LspProbeExitStatus::fromArray($data['immediateExit']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'availableServers' => $this->availableServers,
            'pid' => $this->pid,
            'spawnTimeMs' => $this->spawnTimeMs,
            'sandboxed' => $this->sandboxed,
            ...($this->immediateExit === null ? [] : ['immediateExit' => $this->immediateExit->toArray()]),
        ];
    }
}
