<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Pasted content to save as a UTF-8 workspace file.
 *
 * @experimental
 */
readonly class WorkspacesSaveLargePasteRequest implements Arrayable
{
    public function __construct(
        public string $content,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(content: Arr::string($data, 'content'));
    }

    public function toArray(): array
    {
        return ['content' => $this->content];
    }
}
