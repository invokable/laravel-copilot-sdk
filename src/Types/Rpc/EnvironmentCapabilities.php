<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Hosting capabilities and session capacity advertised by an environment.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class EnvironmentCapabilities implements Arrayable
{
    /**
     * @param  list<string>  $features  Feature identifiers advertised by the environment.
     */
    public function __construct(
        public array $features = [],
        public ?string $ahpVersion = null,
        public ?int $currentSessions = null,
        public ?int $maxSessions = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            features: array_values(Arr::array($data, 'features', [])),
            ahpVersion: isset($data['ahpVersion']) ? (string) $data['ahpVersion'] : null,
            currentSessions: isset($data['currentSessions']) ? (int) $data['currentSessions'] : null,
            maxSessions: isset($data['maxSessions']) ? (int) $data['maxSessions'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'features' => $this->features,
            'ahpVersion' => $this->ahpVersion,
            'currentSessions' => $this->currentSessions,
            'maxSessions' => $this->maxSessions,
        ], fn ($v) => $v !== null);
    }
}
