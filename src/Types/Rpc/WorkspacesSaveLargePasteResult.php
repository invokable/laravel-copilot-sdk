<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Saved-paste descriptor, or null when the workspace is unavailable.
 *
 * @experimental
 */
readonly class WorkspacesSaveLargePasteResult implements Arrayable
{
    public function __construct(
        public ?WorkspacesSaveLargePasteResultSaved $saved = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            saved: isset($data['saved'])
                ? WorkspacesSaveLargePasteResultSaved::fromArray($data['saved'])
                : null,
        );
    }

    public function toArray(): array
    {
        return ['saved' => $this->saved?->toArray()];
    }
}
