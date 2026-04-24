<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Request for listing models with optional per-user token.
 */
readonly class ModelsListRequest implements Arrayable
{
    /**
     * @param  ?string  $gitHubToken  GitHub token for per-user model listing. When provided, resolves this token to determine
     *                                the user's Copilot plan and available models instead of using the global auth.
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
