<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * A settings value to redact before showing it.
 *
 * `basePath` is where the value sits in the settings tree, which decides whether a field is secret.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SettingsRedactValueForDisplayRequest implements Arrayable
{
    /**
     * @param  array<string>  $basePath
     */
    public function __construct(
        public mixed $value,
        public array $basePath,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            value: $data['value'] ?? null,
            basePath: array_values(Arr::array($data, 'basePath', [])),
        );
    }

    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'basePath' => $this->basePath,
        ];
    }
}
