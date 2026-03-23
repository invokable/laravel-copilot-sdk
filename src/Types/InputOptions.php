<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Options for the `input()` UI convenience method.
 */
readonly class InputOptions implements Arrayable
{
    /**
     * @param  ?string  $title  Title label for the input field
     * @param  ?string  $description  Descriptive text shown below the field
     * @param  ?int  $minLength  Minimum character length
     * @param  ?int  $maxLength  Maximum character length
     * @param  ?string  $format  Semantic format hint: "email", "uri", "date", "date-time"
     * @param  ?string  $default  Default value pre-populated in the field
     */
    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
        public ?int $minLength = null,
        public ?int $maxLength = null,
        public ?string $format = null,
        public ?string $default = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'] ?? null,
            description: $data['description'] ?? null,
            minLength: $data['minLength'] ?? null,
            maxLength: $data['maxLength'] ?? null,
            format: $data['format'] ?? null,
            default: $data['default'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'title' => $this->title,
            'description' => $this->description,
            'minLength' => $this->minLength,
            'maxLength' => $this->maxLength,
            'format' => $this->format,
            'default' => $this->default,
        ], fn ($v) => $v !== null);
    }
}
