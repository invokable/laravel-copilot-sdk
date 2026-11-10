<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Answers returned by a decision model.
 *
 * @experimental
 */
readonly class DecisionsAskResult implements Arrayable
{
    /**
     * @param  array<int, DecisionAnswer>  $answers
     */
    public function __construct(
        public string $model,
        public array $answers,
        public int $latencyMs,
        public ?string $upstreamModel = null,
        public ?DecisionUsage $usage = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            model: $data['model'],
            answers: array_map(
                fn (array $answer) => DecisionAnswer::fromArray($answer),
                $data['answers'],
            ),
            latencyMs: $data['latencyMs'],
            upstreamModel: $data['upstreamModel'] ?? null,
            usage: isset($data['usage']) ? DecisionUsage::fromArray($data['usage']) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'model' => $this->model,
            'upstreamModel' => $this->upstreamModel,
            'answers' => array_map(
                fn (DecisionAnswer $answer) => $answer->toArray(),
                $this->answers,
            ),
            'usage' => $this->usage?->toArray(),
            'latencyMs' => $this->latencyMs,
        ], fn ($value) => $value !== null);
    }
}
