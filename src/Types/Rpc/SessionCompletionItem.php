<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * A single host-driven completion item.
 * Accepting an item replaces [rangeStart, rangeEnd) in the composer with insertText.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionCompletionItem implements Arrayable
{
    /**
     * @param  string  $insertText  Text spliced into the composer when the item is accepted.
     * @param  ?int  $rangeStart  Start of the replacement range in text, in UTF-16 code units.
     * @param  ?int  $rangeEnd  End (exclusive) of the replacement range in text, in UTF-16 code units.
     * @param  ?string  $label  Primary display label for the picker row. Falls back to insertText when absent.
     * @param  ?string  $kind  Render-kind hint for the picker row (e.g. "document", "directory").
     */
    public function __construct(
        public string $insertText,
        public ?int $rangeStart = null,
        public ?int $rangeEnd = null,
        public ?string $label = null,
        public ?string $kind = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            insertText: Arr::string($data, 'insertText'),
            rangeStart: isset($data['rangeStart']) ? (int) $data['rangeStart'] : null,
            rangeEnd: isset($data['rangeEnd']) ? (int) $data['rangeEnd'] : null,
            label: $data['label'] ?? null,
            kind: $data['kind'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'insertText' => $this->insertText,
            'rangeStart' => $this->rangeStart,
            'rangeEnd' => $this->rangeEnd,
            'label' => $this->label,
            'kind' => $this->kind,
        ], fn ($v) => $v !== null);
    }
}
