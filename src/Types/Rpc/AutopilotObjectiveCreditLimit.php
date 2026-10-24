<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Current per-window credit limit and consumption for an Autopilot objective.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class AutopilotObjectiveCreditLimit implements Arrayable
{
    /**
     * @param  int|float  $creditsUsed  Window consumption in fractional AI credits.
     * @param  string  $creditsUsedNanoAiu  Exact window consumption in nano-AIU.
     * @param  int|float|null  $credits  Configured AI-credit cap, when one is set.
     */
    public function __construct(
        public int|float $creditsUsed,
        public string $creditsUsedNanoAiu,
        public int|float|null $credits = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            creditsUsed: self::number($data['creditsUsed'] ?? 0),
            creditsUsedNanoAiu: Arr::string($data, 'creditsUsedNanoAiu'),
            credits: isset($data['credits']) && is_numeric($data['credits'])
                ? $data['credits'] + 0
                : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'credits' => $this->credits,
            'creditsUsed' => $this->creditsUsed,
            'creditsUsedNanoAiu' => $this->creditsUsedNanoAiu,
        ], fn ($value) => $value !== null);
    }

    private static function number(mixed $value): int|float
    {
        return is_numeric($value) ? $value + 0 : 0;
    }
}
