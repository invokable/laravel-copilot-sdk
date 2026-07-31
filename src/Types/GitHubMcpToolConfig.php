<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Configuration for the built-in GitHub MCP server.
 *
 * `disableFormDeferral` only applies to the built-in GitHub MCP server and
 * only has an effect when MCP Apps and form-backed GitHub tools are enabled.
 */
readonly class GitHubMcpToolConfig implements Arrayable
{
    /**
     * @param  ?bool  $enableAllTools  When true, enables all built-in GitHub MCP tools.
     * @param  ?array  $additionalToolsets  Additional GitHub MCP toolsets to enable.
     * @param  ?array  $additionalTools  Additional GitHub MCP tools to enable.
     * @param  ?bool  $enableInsidersMode  When true, enables insiders-mode GitHub MCP tools.
     * @param  ?bool  $disableFormDeferral  When true, disables form deferral for form-backed GitHub tools.
     *                                      Only applies to the built-in GitHub MCP server and only has an effect
     *                                      when MCP Apps and form-backed GitHub tools are enabled.
     */
    public function __construct(
        public ?bool $enableAllTools = null,
        public ?array $additionalToolsets = null,
        public ?array $additionalTools = null,
        public ?bool $enableInsidersMode = null,
        public ?bool $disableFormDeferral = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            enableAllTools: isset($data['enableAllTools']) ? Arr::boolean($data, 'enableAllTools') : null,
            additionalToolsets: isset($data['additionalToolsets']) ? Arr::array($data, 'additionalToolsets') : null,
            additionalTools: isset($data['additionalTools']) ? Arr::array($data, 'additionalTools') : null,
            enableInsidersMode: isset($data['enableInsidersMode']) ? Arr::boolean($data, 'enableInsidersMode') : null,
            disableFormDeferral: isset($data['disableFormDeferral']) ? Arr::boolean($data, 'disableFormDeferral') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'enableAllTools' => $this->enableAllTools,
            'additionalToolsets' => $this->additionalToolsets,
            'additionalTools' => $this->additionalTools,
            'enableInsidersMode' => $this->enableInsidersMode,
            'disableFormDeferral' => $this->disableFormDeferral,
        ], fn ($v) => $v !== null);
    }
}
