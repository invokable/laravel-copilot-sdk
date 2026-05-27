<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\RuntimeConnectionKind;

/**
 * Describes how the SDK connects to the Copilot runtime.
 */
readonly class RuntimeConnection implements Arrayable
{
    /**
     * @param  string  $kind  One of: stdio, tcp, uri.
     * @param  ?string  $path  Runtime executable path for child-process connections.
     * @param  ?array<int, string>  $args  Runtime command arguments for child-process connections.
     * @param  ?string  $url  Existing runtime URL for URI connections.
     * @param  ?int  $port  Reserved for future SDK-spawned TCP runtime support.
     * @param  ?string  $connectionToken  Token sent during the connect handshake for authenticated TCP/URI connections.
     */
    public function __construct(
        public RuntimeConnectionKind|string $kind,
        public ?string $path = null,
        public ?array $args = null,
        public ?string $url = null,
        public ?int $port = null,
        public ?string $connectionToken = null,
    ) {}

    public static function forStdio(?string $path = null, ?array $args = null): self
    {
        return new self(
            kind: RuntimeConnectionKind::STDIO,
            path: $path,
            args: $args,
        );
    }

    public static function forTcp(?int $port = null, ?string $connectionToken = null, ?string $path = null, ?array $args = null): self
    {
        return new self(
            kind: RuntimeConnectionKind::TCP,
            path: $path,
            args: $args,
            port: $port,
            connectionToken: $connectionToken,
        );
    }

    public static function forUri(string $url, ?string $connectionToken = null): self
    {
        return new self(
            kind: RuntimeConnectionKind::URI,
            url: $url,
            connectionToken: $connectionToken,
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            kind: $data['kind'] ?? RuntimeConnectionKind::STDIO,
            path: $data['path'] ?? null,
            args: $data['args'] ?? null,
            url: $data['url'] ?? null,
            port: $data['port'] ?? null,
            connectionToken: $data['connectionToken'] ?? null,
        );
    }

    public function kindValue(): string
    {
        return $this->kind instanceof RuntimeConnectionKind
            ? $this->kind->value
            : $this->kind;
    }

    public function toArray(): array
    {
        return array_filter([
            'kind' => $this->kindValue(),
            'path' => $this->path,
            'args' => $this->args,
            'url' => $this->url,
            'port' => $this->port,
            'connectionToken' => $this->connectionToken,
        ], fn ($value) => $value !== null);
    }
}
