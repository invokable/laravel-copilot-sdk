<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * OAuth token acquired by the host for an MCP server.
 */
readonly class McpAuthToken implements Arrayable
{
    public function __construct(
        public string $accessToken,
        public ?string $tokenType = null,
        public ?int $expiresIn = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            accessToken: Arr::string($data, 'accessToken', ''),
            tokenType: $data['tokenType'] ?? null,
            expiresIn: $data['expiresIn'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'accessToken' => $this->accessToken,
            'tokenType' => $this->tokenType,
            'expiresIn' => $this->expiresIn,
        ], fn ($value) => $value !== null);
    }
}
