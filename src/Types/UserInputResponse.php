<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Response to a user input request.
 */
readonly class UserInputResponse implements Arrayable
{
    /**
     * @param  string  $answer  The user's answer
     * @param  bool  $wasFreeform  Whether the answer was freeform (not from choices)
     */
    public function __construct(
        public string $answer,
        public bool $wasFreeform = false,
    ) {}

    /**
     * Create from array data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            answer: $data['answer'] ?? '',
            wasFreeform: $data['wasFreeform'] ?? false,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'answer' => $this->answer,
            'wasFreeform' => $this->wasFreeform,
        ];
    }
}
