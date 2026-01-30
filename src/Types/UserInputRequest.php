<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Request for user input from the agent (enables ask_user tool).
 */
readonly class UserInputRequest implements Arrayable
{
    public function __construct(
        /**
         * The question to ask the user.
         */
        public string $question,
        /**
         * Optional choices for multiple choice questions.
         */
        public ?array $choices = null,
        /**
         * Whether to allow freeform text input in addition to choices.
         *
         * @default true
         */
        public ?bool $allowFreeform = null,
    ) {}

    /**
     * Create from array data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            question: $data['question'] ?? '',
            choices: $data['choices'] ?? null,
            allowFreeform: $data['allowFreeform'] ?? null,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return array_filter([
            'question' => $this->question,
            'choices' => $this->choices,
            'allowFreeform' => $this->allowFreeform,
        ], fn ($value) => $value !== null);
    }
}
