<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Complete external-tool set supplied by this connection for a session. */
readonly class ToolsSetRequest implements Arrayable
{
    /** @param array<array{name: string, description?: string, parameters?: array}> $tools */
    public function __construct(public array $tools) {}

    public static function fromArray(array $data): self
    {
        return new self($data['tools'] ?? []);
    }

    public function toArray(): array
    {
        return ['tools' => $this->tools];
    }
}
