<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Request-local judge override for trajectory classification.
 *
 * @experimental
 */
readonly class TaskAnalyticsJudge implements Arrayable
{
    public function __construct(
        public string $model,
        public ?string $reasoningEffort = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            model: Arr::string($data, 'model'),
            reasoningEffort: isset($data['reasoningEffort']) ? Arr::string($data, 'reasoningEffort') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'model' => $this->model,
            'reasoningEffort' => $this->reasoningEffort,
        ], fn ($value) => $value !== null);
    }
}
