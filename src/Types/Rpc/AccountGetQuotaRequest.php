<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Request for account quota with optional per-user token.
 */
readonly class AccountGetQuotaRequest implements Arrayable
{
    /**
     * @param  ?string  $gitHubToken  GitHub token for per-user quota lookup. When provided, resolves this token to determine
     *                                the user's quota instead of using the global auth.
     */
    public function __construct(
        public ?string $gitHubToken = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            gitHubToken: $data['gitHubToken'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'gitHubToken' => $this->gitHubToken,
        ], fn ($v) => $v !== null);
    }
}
