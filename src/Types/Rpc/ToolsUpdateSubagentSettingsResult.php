<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Empty result after applying subagent settings.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ToolsUpdateSubagentSettingsResult implements Arrayable
{
    public function __construct() {}

    public static function fromArray(array $data): self
    {
        return new self;
    }

    public function toArray(): array
    {
        return [];
    }
}
