<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Parameters for resolving managed settings without creating a session. */
readonly class ManagedSettingsResolveRequest implements Arrayable
{
    public function __construct(
        public ?string $selectionId = null,
        public ?string $gitHubToken = null,
        public ?string $clientName = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            selectionId: $data['selectionId'] ?? null,
            gitHubToken: $data['gitHubToken'] ?? null,
            clientName: $data['clientName'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'selectionId' => $this->selectionId,
            'gitHubToken' => $this->gitHubToken,
            'clientName' => $this->clientName,
        ], fn ($value) => $value !== null);
    }
}
