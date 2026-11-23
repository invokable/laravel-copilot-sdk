<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

readonly class UIServiceTask implements Arrayable
{
    /**
     * @param  UIServiceLogEntry[]  $log
     */
    public function __construct(
        public string $type,
        public string $id,
        public string $serviceKind,
        public string $serviceId,
        public string $description,
        public string $status,
        public bool $ready,
        public int|float $startedAt,
        public array $log,
        public ?string $phase = null,
        public int|float|null $percentage = null,
        public int|float|null $completedAt = null,
        public ?string $error = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $percentage = $data['percentage'] ?? null;
        $startedAt = $data['startedAt'] ?? 0;
        $completedAt = $data['completedAt'] ?? null;

        return new self(
            type: Arr::string($data, 'type', 'service'),
            id: Arr::string($data, 'id', ''),
            serviceKind: Arr::string($data, 'serviceKind', ''),
            serviceId: Arr::string($data, 'serviceId', ''),
            description: Arr::string($data, 'description', ''),
            status: Arr::string($data, 'status', ''),
            ready: Arr::boolean($data, 'ready', false),
            phase: isset($data['phase']) ? Arr::string($data, 'phase') : null,
            percentage: is_int($percentage) || is_float($percentage) ? $percentage : null,
            startedAt: is_int($startedAt) || is_float($startedAt) ? $startedAt : 0,
            completedAt: is_int($completedAt) || is_float($completedAt) ? $completedAt : null,
            error: isset($data['error']) ? Arr::string($data, 'error') : null,
            log: array_map(
                static fn (array $entry): UIServiceLogEntry => UIServiceLogEntry::fromArray($entry),
                array_values(Arr::array($data, 'log', [])),
            ),
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'type' => $this->type,
            'id' => $this->id,
            'serviceKind' => $this->serviceKind,
            'serviceId' => $this->serviceId,
            'description' => $this->description,
            'status' => $this->status,
            'ready' => $this->ready,
            'phase' => $this->phase,
            'percentage' => $this->percentage,
            'startedAt' => $this->startedAt,
            'completedAt' => $this->completedAt,
            'error' => $this->error,
            'log' => array_map(static fn (UIServiceLogEntry $entry): array => $entry->toArray(), $this->log),
        ], static fn ($value): bool => $value !== null);
    }
}
