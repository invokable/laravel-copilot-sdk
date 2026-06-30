<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Request host-driven completions for the current composer input.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class CompletionsRequestRequest implements Arrayable
{
    /**
     * @param  string  $text  The full composed composer input.
     * @param  int  $offset  Cursor offset within $text, in UTF-16 code units.
     */
    public function __construct(
        public string $text,
        public int $offset,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            text: Arr::string($data, 'text'),
            offset: Arr::integer($data, 'offset'),
        );
    }

    public function toArray(): array
    {
        return [
            'text' => $this->text,
            'offset' => $this->offset,
        ];
    }
}
