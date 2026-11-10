<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Request for a decision model to answer questions about shared context.
 *
 * @experimental
 */
readonly class DecisionsAskRequest implements Arrayable
{
    /**
     * @param  array<int, DecisionQuestion>  $questions
     */
    public function __construct(
        public DecisionContext $context,
        public array $questions,
        public ?string $selectionId = null,
        public ?string $gitHubToken = null,
        public ?string $model = null,
        public ?int $timeoutMs = null,
        public ?string $purpose = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            context: DecisionContext::fromArray($data['context']),
            questions: array_map(
                fn (array $question) => DecisionQuestion::fromArray($question),
                $data['questions'],
            ),
            selectionId: $data['selectionId'] ?? null,
            gitHubToken: $data['gitHubToken'] ?? null,
            model: $data['model'] ?? null,
            timeoutMs: $data['timeoutMs'] ?? null,
            purpose: $data['purpose'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'selectionId' => $this->selectionId,
            'gitHubToken' => $this->gitHubToken,
            'model' => $this->model,
            'context' => $this->context->toArray(),
            'questions' => array_map(
                fn (DecisionQuestion $question) => $question->toArray(),
                $this->questions,
            ),
            'timeoutMs' => $this->timeoutMs,
            'purpose' => $this->purpose,
        ], fn ($value) => $value !== null);
    }
}
