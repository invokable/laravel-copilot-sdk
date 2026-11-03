<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Closes one live opted-in descendant without deleting its saved history or closing its children.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionManagementCloseRequest implements Arrayable
{
    public function __construct(
        public string $targetSessionId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            targetSessionId: $data['targetSessionId'],
        );
    }

    public function toArray(): array
    {
        return [
            'targetSessionId' => $this->targetSessionId,
        ];
    }
}
