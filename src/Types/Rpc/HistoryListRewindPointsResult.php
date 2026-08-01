<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\HistoryRewindUnavailableReason;

/**
 * Result of listing available rewind points for a session.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class HistoryListRewindPointsResult implements Arrayable
{
    /**
     * @param  bool  $fileChangeTrackingEnabled  Whether file-change tracking is enabled for the session.
     * @param  array<HistoryRewindPoint>  $points  Available rewind points.
     * @param  HistoryRewindUnavailableReason|string|null  $unavailableReason  Why rewind points could not be produced, if any.
     */
    public function __construct(
        public bool $fileChangeTrackingEnabled,
        public array $points,
        public HistoryRewindUnavailableReason|string|null $unavailableReason = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            fileChangeTrackingEnabled: Arr::boolean($data, 'fileChangeTrackingEnabled', false),
            points: array_map(
                fn ($point) => $point instanceof HistoryRewindPoint ? $point : HistoryRewindPoint::fromArray($point),
                $data['points'] ?? [],
            ),
            unavailableReason: isset($data['unavailableReason'])
                ? ($data['unavailableReason'] instanceof HistoryRewindUnavailableReason
                    ? $data['unavailableReason']
                    : HistoryRewindUnavailableReason::from($data['unavailableReason']))
                : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'fileChangeTrackingEnabled' => $this->fileChangeTrackingEnabled,
            'points' => array_map(fn (HistoryRewindPoint $point) => $point->toArray(), $this->points),
            'unavailableReason' => $this->unavailableReason instanceof HistoryRewindUnavailableReason
                ? $this->unavailableReason->value
                : $this->unavailableReason,
        ], fn ($v) => $v !== null);
    }
}
