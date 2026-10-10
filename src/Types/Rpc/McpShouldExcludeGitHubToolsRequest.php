<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\OptionsUpdateToolFilterPrecedence;

/**
 * Session tool filters used to determine whether GitHub MCP tools can be excluded.
 *
 * @experimental
 *
 * @internal Internal SDK API; not part of the public surface.
 */
readonly class McpShouldExcludeGitHubToolsRequest implements Arrayable
{
    /**
     * @param  list<string>|null  $availableTools  Session tool allowlist
     * @param  list<string>|null  $excludedTools  Session tool denylist
     */
    public function __construct(
        public ?array $availableTools = null,
        public ?array $excludedTools = null,
        public ?OptionsUpdateToolFilterPrecedence $toolFilterPrecedence = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            availableTools: isset($data['availableTools']) ? Arr::array($data, 'availableTools') : null,
            excludedTools: isset($data['excludedTools']) ? Arr::array($data, 'excludedTools') : null,
            toolFilterPrecedence: isset($data['toolFilterPrecedence'])
                ? OptionsUpdateToolFilterPrecedence::from($data['toolFilterPrecedence'])
                : null,
        );
    }

    public function toArray(): array
    {
        $result = [];

        if ($this->availableTools !== null) {
            $result['availableTools'] = $this->availableTools;
        }

        if ($this->excludedTools !== null) {
            $result['excludedTools'] = $this->excludedTools;
        }

        if ($this->toolFilterPrecedence !== null) {
            $result['toolFilterPrecedence'] = $this->toolFilterPrecedence->value;
        }

        return $result;
    }
}
