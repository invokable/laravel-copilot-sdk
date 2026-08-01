<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * HTTP proxy the sandboxed process routes traffic through. Enforced on Windows
 * and cooperative (honored by well-behaved tools, not strictly enforced) on
 * Linux and macOS. Credentials go in the separate `username`/`password` fields.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SandboxConfigUserPolicyNetworkProxy implements Arrayable
{
    /**
     * @param  string  $url  Proxy URL (e.g. http://proxy.example.com:8080). Credentials must not be embedded here; put them in the separate `username`/`password` fields.
     * @param  ?string  $password  Optional password for proxy authentication, combined with the URL at spawn time.
     * @param  ?string  $username  Optional username for proxy authentication.
     */
    public function __construct(
        public string $url,
        public ?string $password = null,
        public ?string $username = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            url: Arr::string($data, 'url'),
            password: $data['password'] ?? null,
            username: $data['username'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'url' => $this->url,
            'password' => $this->password,
            'username' => $this->username,
        ], fn ($v) => $v !== null);
    }
}
