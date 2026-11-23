<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

readonly class UIUserInputPrompt implements Arrayable
{
    /**
     * @param  ?string[]  $choices
     */
    public function __construct(
        public string $question,
        public ?array $choices = null,
        public ?bool $allowFreeform = null,
        public ?string $toolCallId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            question: Arr::string($data, 'question', ''),
            choices: isset($data['choices']) ? array_values(Arr::array($data, 'choices')) : null,
            allowFreeform: isset($data['allowFreeform']) ? Arr::boolean($data, 'allowFreeform') : null,
            toolCallId: isset($data['toolCallId']) ? Arr::string($data, 'toolCallId') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'question' => $this->question,
            'choices' => $this->choices,
            'allowFreeform' => $this->allowFreeform,
            'toolCallId' => $this->toolCallId,
        ], static fn ($value): bool => $value !== null);
    }
}
