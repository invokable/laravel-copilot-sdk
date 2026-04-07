<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\EntryType;

/**
 * A directory entry with type information.
 */
readonly class SessionFsEntry implements Arrayable
{
    /**
     * @param  string  $name  Entry name
     * @param  EntryType|string  $type  Entry type (file or directory)
     */
    public function __construct(
        public string $name,
        public EntryType|string $type,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            type: EntryType::tryFrom($data['type'] ?? '') ?? $data['type'],
        );
    }

    public function toArray(): array
    {
        $type = $this->type instanceof EntryType ? $this->type->value : $this->type;

        return [
            'name' => $this->name,
            'type' => $type,
        ];
    }
}
