<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Controls the default log sources and paths included in a debug bundle.
 *
 * @experimental
 */
readonly class DebugCollectLogsInclude implements Arrayable
{
    public function __construct(
        public ?bool $events = null,
        public ?bool $processLogs = null,
        public ?bool $shellLogs = null,
        public ?string $eventsPath = null,
        public ?string $currentProcessLogPath = null,
        public ?string $processLogDirectory = null,
        public ?int $previousProcessLogLimit = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            events: $data['events'] ?? null,
            processLogs: $data['processLogs'] ?? null,
            shellLogs: $data['shellLogs'] ?? null,
            eventsPath: $data['eventsPath'] ?? null,
            currentProcessLogPath: $data['currentProcessLogPath'] ?? null,
            processLogDirectory: $data['processLogDirectory'] ?? null,
            previousProcessLogLimit: $data['previousProcessLogLimit'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'events' => $this->events,
            'processLogs' => $this->processLogs,
            'shellLogs' => $this->shellLogs,
            'eventsPath' => $this->eventsPath,
            'currentProcessLogPath' => $this->currentProcessLogPath,
            'processLogDirectory' => $this->processLogDirectory,
            'previousProcessLogLimit' => $this->previousProcessLogLimit,
        ], fn ($value) => $value !== null);
    }
}
