<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * MCP server configuration (stdio for local/subprocess servers, http/sse for remote servers).
 */
readonly class McpServerValue implements Arrayable
{
    /**
     * @param  ?string  $type  Server type: "stdio" (local subprocess, also accepts "local"), "http", or "sse" (remote). Defaults to "stdio".
     * @param  ?string  $command  Command to run (stdio servers)
     * @param  ?array<string>  $args  Arguments for the command (stdio servers)
     * @param  ?string  $cwd  Working directory (stdio servers)
     * @param  ?array<string, string>  $env  Environment variables (stdio servers)
     * @param  ?string  $url  URL for HTTP/SSE servers
     * @param  ?array<string, string>  $headers  Headers for HTTP/SSE servers
     * @param  ?string  $oauthClientId  OAuth client ID (HTTP/SSE servers)
     * @param  ?bool  $oauthPublicClient  OAuth public client flag (HTTP/SSE servers)
     * @param  ?array<string>  $tools  Tools to include (defaults to all)
     * @param  ?bool  $isDefaultServer  Whether this is a default server
     * @param  mixed  $filterMapping  Filter mapping configuration
     * @param  ?int  $timeout  Timeout in milliseconds
     */
    public function __construct(
        public ?string $type = null,
        public ?string $command = null,
        public ?array $args = null,
        public ?string $cwd = null,
        public ?array $env = null,
        public ?string $url = null,
        public ?array $headers = null,
        public ?string $oauthClientId = null,
        public ?bool $oauthPublicClient = null,
        public ?array $tools = null,
        public ?bool $isDefaultServer = null,
        public mixed $filterMapping = null,
        public ?int $timeout = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'] ?? null,
            command: $data['command'] ?? null,
            args: $data['args'] ?? null,
            cwd: $data['cwd'] ?? null,
            env: $data['env'] ?? null,
            url: $data['url'] ?? null,
            headers: $data['headers'] ?? null,
            oauthClientId: $data['oauthClientId'] ?? null,
            oauthPublicClient: $data['oauthPublicClient'] ?? null,
            tools: $data['tools'] ?? null,
            isDefaultServer: $data['isDefaultServer'] ?? null,
            filterMapping: $data['filterMapping'] ?? null,
            timeout: $data['timeout'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'type' => $this->type,
            'command' => $this->command,
            'args' => $this->args,
            'cwd' => $this->cwd,
            'env' => $this->env,
            'url' => $this->url,
            'headers' => $this->headers,
            'oauthClientId' => $this->oauthClientId,
            'oauthPublicClient' => $this->oauthPublicClient,
            'tools' => $this->tools,
            'isDefaultServer' => $this->isDefaultServer,
            'filterMapping' => $this->filterMapping,
            'timeout' => $this->timeout,
        ], fn ($v) => $v !== null);
    }
}
