<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Optional unstructured input hint for a slash command.
 */
readonly class SlashCommandInput implements Arrayable
{
    /**
     * @param  string  $hint  Hint to display when command input has not been provided.
     * @param  ?string  $completion  Optional completion hint for the input (e.g. 'directory' for filesystem path completion).
     * @param  SlashCommandInputChoice[]|null  $choices  Optional literal choices the input accepts; clients may render these as selectable options.
     * @param  ?bool  $preserveMultilineInput  When true, clients should pass the full text after the command name as a single argument.
     * @param  ?bool  $required  When true, the command requires non-empty input.
     */
    public function __construct(
        public string $hint,
        public ?string $completion = null,
        public ?array $choices = null,
        public ?bool $preserveMultilineInput = null,
        public ?bool $required = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            hint: Arr::string($data, 'hint', ''),
            completion: $data['completion'] ?? null,
            choices: isset($data['choices']) ? array_map(
                fn (array $c) => SlashCommandInputChoice::fromArray($c),
                $data['choices'],
            ) : null,
            preserveMultilineInput: $data['preserveMultilineInput'] ?? null,
            required: $data['required'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'hint' => $this->hint,
            'completion' => $this->completion,
            'choices' => $this->choices !== null ? array_map(fn ($c) => $c->toArray(), $this->choices) : null,
            'preserveMultilineInput' => $this->preserveMultilineInput,
            'required' => $this->required,
        ], fn ($value) => $value !== null);
    }
}
